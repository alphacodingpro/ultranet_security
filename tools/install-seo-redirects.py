"""Add one legacy redirect while preserving the hosting provider's .htaccess.
Uses the existing deployment FTP account. Never reads .env or prints credentials.
Restores the original bytes if the public redirect check fails.
"""
import ftplib, io, os, urllib.request, urllib.error
BLOCK=b'''# BEGIN UltraNet legacy package redirect
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule ^packages/?$ /calculator.php [R=301,L]
</IfModule>
# END UltraNet legacy package redirect
'''
with ftplib.FTP(timeout=30) as ftp:
    ftp.connect(os.environ['FTP_SERVER'])
    ftp.login(os.environ['FTP_USERNAME'],os.environ['FTP_PASSWORD'])
    old=io.BytesIO()
    # Refuse to create a new per-directory config that might override inherited rules.
    ftp.retrbinary('RETR .htaccess',old.write)
    original=old.getvalue()
    if BLOCK in original:
        print('Legacy redirect already installed; hosting rules preserved.')
    else:
        ftp.storbinary('STOR .htaccess',io.BytesIO(BLOCK+b'\n'+original))
        try:
            for path in ['/packages/','/packages']:
                with urllib.request.urlopen('https://ultranetsecurity.com'+path,timeout=20) as response:
                    print('Legacy check:',path,response.status,response.geturl())
                    if response.status!=200 or response.geturl()!='https://ultranetsecurity.com/calculator.php':
                        raise RuntimeError('Legacy redirect verification failed')
            print('Legacy package URLs now redirect to the calculator; hosting rules preserved.')
        except Exception as exc:
            print('Legacy check failed:',type(exc).__name__,str(exc))
            ftp.storbinary('STOR .htaccess',io.BytesIO(original))
            raise RuntimeError('Legacy redirect validation failed; original hosting rules restored.') from None
