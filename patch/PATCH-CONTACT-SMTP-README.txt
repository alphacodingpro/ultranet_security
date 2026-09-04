============================================================
  UPDATE: Contact Form → Admin Panel + Gmail SMTP Email
============================================================

WHAT'S NEW
----------
✓ The homepage "Get Free Quote" contact form is now fully
  connected — every submission is saved to the database AND
  shown live in the admin panel.
✓ New Admin section "INBOX":
    - Contact Messages → every enquiry from the homepage form,
      with Call / WhatsApp / Email quick-action buttons and a
      status flow: New → Read → Replied
    - Email / SMTP Setup → shows your current SMTP config
      (safely masked), step-by-step Gmail setup guide, and a
      "Send Test Email" button to confirm it's working
✓ Gmail SMTP integration — when a new contact form OR CCTV
  Calculator package request comes in, an email notification
  is automatically sent to you (ADMIN_NOTIFY_EMAIL in .env)
  with all the details and a link straight into the admin panel.
✓ No external libraries needed — a small dependency-free SMTP
  client (includes/SmtpMailer.php) handles everything in pure
  PHP. No Composer, no PHPMailer download required.
✓ Added an email field + Area dropdown to the homepage contact
  form (same visual style/grid as before — no design change).
✓ Basic honeypot spam protection on the contact form.

DESIGN: unchanged — new admin pages reuse your existing card /
table / button styles exactly.


============================================================
  FILES CHANGED / ADDED
============================================================

  .env                              ← added SMTP_* settings (edit these!)
  config/config.php                 ← added SMTP constants
  includes/SmtpMailer.php           ← NEW — pure-PHP SMTP client
  includes/functions.php            ← added email + contact-message functions
  contact-submit.php                ← NEW — saves lead + sends email
  calculator-submit.php             ← now also sends an email notification
  index.php                         ← contact form: added name attrs, email
                                        + area fields, honeypot, data-action
  assets/js/main.js                 ← contact form now really submits via
                                        AJAX (previously just faked success)
  admin/includes/header.php         ← added "INBOX" sidebar section
  admin/index.php                   ← added Contact Messages stat + quick link
  admin/contact-messages.php        ← NEW — admin inbox
  admin/email-test.php              ← NEW — SMTP diagnostic + test-send tool
  patch/migration-contact.sql       ← NEW — run once if upgrading
  database/schema.sql               ← updated (fresh installs)


============================================================
  HOW TO APPLY THIS UPDATE
============================================================

IF THIS IS A FRESH INSTALL
---------------------------
Just import database/schema.sql — the contact_messages table
is already included.


IF YOU ALREADY HAVE THE SITE RUNNING
--------------------------------------
1. Open phpMyAdmin → select your `ultranet_security` database
2. Click "SQL" tab
3. Open  patch/migration-contact.sql  in a text editor, copy
   all contents, paste into the SQL box, click "Go"
   (creates the `contact_messages` table — nothing else is touched)

4. Copy ALL the changed/new files listed above into your project,
   overwriting the old ones.

5. Open your .env file and fill in the SMTP section (see below).


============================================================
  GMAIL SMTP SETUP (step by step)
============================================================

Gmail does NOT allow your normal account password for SMTP —
you must create an "App Password":

1. Go to https://myaccount.google.com/security
2. Turn ON "2-Step Verification" (required — App Passwords are
   hidden until this is enabled)
3. Go to https://myaccount.google.com/apppasswords
4. Create a new App Password → choose "Mail" → copy the
   16-character password Google shows you (no spaces needed)
5. Open your .env file and fill in:

     SMTP_HOST=smtp.gmail.com
     SMTP_PORT=587
     SMTP_ENCRYPTION=tls
     SMTP_USERNAME=youraddress@gmail.com
     SMTP_PASSWORD=the16characterapppassword
     SMTP_FROM_EMAIL=youraddress@gmail.com
     SMTP_FROM_NAME=UltraNet Security
     ADMIN_NOTIFY_EMAIL=youraddress@gmail.com

6. Save the file, then go to:
     Admin → Email / SMTP Setup → "Send Test Email"
   to confirm everything works.

Not using Gmail? Any standard SMTP provider works the same way
— just change SMTP_HOST / SMTP_PORT / SMTP_ENCRYPTION to match
your provider (e.g. Outlook, a hosting cPanel mailbox, SendGrid,
Mailgun's SMTP relay, etc.)


============================================================
  TROUBLESHOOTING
============================================================

"Authentication failed"
  → You used your normal Gmail password instead of an App
    Password, OR 2-Step Verification isn't turned on yet.

"Could not connect to smtp.gmail.com:587"
  → Your host/firewall is blocking outbound port 587. Some
    shared hosting blocks this — try port 465 with
    SMTP_ENCRYPTION=ssl instead, or contact your host.

Email not received but no error shown
  → Check the spam/junk folder. Also double-check
    ADMIN_NOTIFY_EMAIL is set correctly in .env.

Contact form shows "Could not save your message"
  → The contact_messages table is missing — run
    patch/migration-contact.sql in phpMyAdmin.

Emails failing is NEVER fatal — if SMTP is misconfigured, the
contact form and calculator will still save the lead correctly
to the database and show it in Admin; only the email
notification step is skipped (logged quietly, doesn't crash
anything for your website visitors).

============================================================
