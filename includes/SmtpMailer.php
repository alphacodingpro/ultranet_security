<?php
/**
 * SmtpMailer — minimal, dependency-free SMTP client
 * Supports STARTTLS (port 587) and implicit SSL (port 465),
 * AUTH LOGIN, and HTML email. Works with Gmail SMTP using an
 * "App Password" (see README for setup instructions).
 *
 * No Composer / PHPMailer required — pure PHP sockets.
 */
class SmtpMailer
{
    private string $host;
    private int    $port;
    private string $encryption; // tls | ssl | none
    private string $username;
    private string $password;
    private string $fromEmail;
    private string $fromName;
    private array  $lastLog = [];

    public function __construct(
        string $host, int $port, string $encryption,
        string $username, string $password,
        string $fromEmail, string $fromName
    ) {
        $this->host       = $host;
        $this->port       = $port;
        $this->encryption = strtolower($encryption);
        $this->username   = $username;
        $this->password   = $password;
        $this->fromEmail  = $fromEmail;
        $this->fromName   = $fromName;
    }

    public function getLog(): array
    {
        return $this->lastLog;
    }

    /**
     * Send an HTML email.
     * @param string      $to
     * @param string      $subject
     * @param string      $htmlBody
     * @param string|null $replyToEmail
     * @param string|null $replyToName
     * @return bool  true on success
     * @throws RuntimeException with a human-readable message on failure
     */
    public function send(
        string $to, string $subject, string $htmlBody,
        ?string $replyToEmail = null, ?string $replyToName = null
    ): bool {
        $this->lastLog = [];

        // Strip CR/LF from every value that ends up in a raw SMTP header —
        // without this, a field like "name" containing a newline could be
        // used to inject extra headers (e.g. Bcc:) into the outgoing email
        // (classic "email header injection" attack).
        $to           = $this->stripCrlf($to);
        $subject      = $this->stripCrlf($subject);
        $replyToEmail = $replyToEmail !== null ? $this->stripCrlf($replyToEmail) : null;
        $replyToName  = $replyToName  !== null ? $this->stripCrlf($replyToName)  : null;

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Invalid recipient email address.');
        }
        if ($replyToEmail && !filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
            $replyToEmail = null; // silently drop rather than fail the whole send
        }

        if (!$this->username || !$this->password) {
            throw new RuntimeException('SMTP is not configured. Set SMTP_USERNAME and SMTP_PASSWORD in .env');
        }

        $connectHost = $this->encryption === 'ssl' ? 'ssl://' . $this->host : $this->host;
        $fp = @stream_socket_client(
            $connectHost . ':' . $this->port,
            $errno, $errstr, 15,
            STREAM_CLIENT_CONNECT,
            stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]])
        );
        if (!$fp) {
            throw new RuntimeException("Could not connect to {$this->host}:{$this->port} — $errstr ($errno)");
        }
        stream_set_timeout($fp, 15);

        $this->readResponse($fp); // greeting

        $this->command($fp, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost'), 250);

        if ($this->encryption === 'tls') {
            $this->command($fp, "STARTTLS", 220);
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($fp);
                throw new RuntimeException('Failed to enable TLS encryption.');
            }
            $this->command($fp, "EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost'), 250);
        }

        $this->command($fp, "AUTH LOGIN", 334);
        $this->command($fp, base64_encode($this->username), 334);
        $this->command($fp, base64_encode($this->password), 235, 'Authentication failed. Check SMTP_USERNAME / SMTP_PASSWORD (use a Gmail App Password, not your normal password).');

        $this->command($fp, "MAIL FROM: <{$this->fromEmail}>", 250);
        $this->command($fp, "RCPT TO: <{$to}>", 250);
        $this->command($fp, "DATA", 354);

        $boundaryDate = date('r');
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . $this->encodeHeader($this->fromName) . ' <' . $this->fromEmail . '>';
        $headers[] = 'To: <' . $to . '>';
        if ($replyToEmail) {
            $rtName = $replyToName ? $this->encodeHeader($replyToName) . ' ' : '';
            $headers[] = 'Reply-To: ' . $rtName . '<' . $replyToEmail . '>';
        }
        $headers[] = 'Subject: ' . $this->encodeHeader($subject);
        $headers[] = 'Date: ' . $boundaryDate;
        $headers[] = 'X-Mailer: UltraNetSecurity-SmtpMailer';

        // Dot-stuff lines starting with a lone "." per SMTP spec
        $body = preg_replace('/^\./m', '..', $htmlBody);

        $message = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
        $this->command($fp, $message, 250, 'Server rejected the message body.');

        $this->command($fp, "QUIT", 221);
        fclose($fp);

        return true;
    }

    private function command($fp, string $cmd, int $expectCode, ?string $friendlyError = null): string
    {
        fwrite($fp, $cmd . "\r\n");
        $this->lastLog[] = '> ' . (strlen($cmd) > 60 ? substr($cmd, 0, 60) . '...' : $cmd);
        $response = $this->readResponse($fp);
        $code = (int)substr($response, 0, 3);
        if ($code !== $expectCode) {
            $msg = $friendlyError ?: "SMTP server returned unexpected response ($code): " . trim($response);
            throw new RuntimeException($msg);
        }
        return $response;
    }

    private function readResponse($fp): string
    {
        $data = '';
        while ($line = fgets($fp, 515)) {
            $data .= $line;
            $this->lastLog[] = '< ' . trim($line);
            // Multi-line responses have a dash after the code, e.g. "250-"; the
            // final line has a space, e.g. "250 ". Stop reading on the final line.
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $data;
    }

    private function stripCrlf(string $str): string
    {
        return trim(str_replace(["\r", "\n", "\0"], '', $str));
    }

    private function encodeHeader(string $str): string
    {
        $str = $this->stripCrlf($str);
        // MIME-encode if it contains non-ASCII characters, otherwise leave as-is
        if (preg_match('/[^\x20-\x7E]/', $str)) {
            return '=?UTF-8?B?' . base64_encode($str) . '?=';
        }
        return $str;
    }
}
