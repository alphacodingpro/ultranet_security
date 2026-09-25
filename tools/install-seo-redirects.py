"""Add one legacy redirect while preserving the hosting provider's .htaccess.
Uses the existing deployment FTP account. Never reads .env or prints credentials.
Restores the original bytes if the public redirect check fails.
"""
import ftplib, io, os, time, http.client, urllib.request, urllib.error
BLOCK=b'''# BEGIN UltraNet legacy package redirect
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule ^packages/?$ /calculator.php [R=301,L]
</IfModule>
# END UltraNet legacy package redirect
'''
DHA_BLOCK=b'''# BEGIN UltraNet DHA landing page
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule ^cctv-camera-installation-dha-karachi/?$ cctv-camera-installation-dha-karachi.php [END]
</IfModule>
# END UltraNet DHA landing page
'''
HOME_BLOCK=b'''# BEGIN UltraNet home CCTV landing page
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule ^home-cctv-installation-karachi/?$ home-cctv-installation-karachi.php [END]
</IfModule>
# END UltraNet home CCTV landing page
'''
OFFICE_BLOCK=b'''# BEGIN UltraNet office CCTV landing page
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule ^office-cctv-installation-karachi/?$ office-cctv-installation-karachi.php [END]
</IfModule>
# END UltraNet office CCTV landing page
'''
with ftplib.FTP(timeout=30) as ftp:
    ftp.connect(os.environ['FTP_SERVER'])
    ftp.login(os.environ['FTP_USERNAME'],os.environ['FTP_PASSWORD'])
    old=io.BytesIO()
    # Refuse to create a new per-directory config that might override inherited rules.
    ftp.retrbinary('RETR .htaccess',old.write)
    original=old.getvalue()
    additions=b''
    if OFFICE_BLOCK not in original: additions+=OFFICE_BLOCK+b'\n'
    if HOME_BLOCK not in original: additions+=HOME_BLOCK+b'\n'
    if DHA_BLOCK not in original: additions+=DHA_BLOCK+b'\n'
    if BLOCK not in original: additions+=BLOCK+b'\n'
    if not additions:
        print('Public redirects already installed; hosting rules preserved.')
    else:
        ftp.storbinary('STOR .htaccess',io.BytesIO(additions+original))
        try:
            for path,final in [
                ('/office-cctv-installation-karachi','/office-cctv-installation-karachi'),
                ('/office-cctv-installation-karachi.php','/office-cctv-installation-karachi'),
                ('/home-cctv-installation-karachi','/home-cctv-installation-karachi'),
                ('/home-cctv-installation-karachi.php','/home-cctv-installation-karachi'),
                ('/cctv-camera-installation-dha-karachi','/cctv-camera-installation-dha-karachi'),
                ('/cctv-camera-installation-dha-karachi.php','/cctv-camera-installation-dha-karachi'),
                ('/packages/','/calculator.php'),('/packages','/calculator.php'),
            ]:
                request=urllib.request.Request('https://ultranetsecurity.com'+path, headers={'User-Agent':'UltraNet-Owner-SEO-Audit/1.0'})
                for attempt in range(3):
                    try:
                        response=urllib.request.urlopen(request,timeout=20)
                        break
                    except (http.client.RemoteDisconnected, urllib.error.URLError):
                        if attempt==2: raise
                        time.sleep(2)
                with response:
                    print('Public route check:',path,response.status,response.geturl())
                    if response.status!=200 or response.geturl()!='https://ultranetsecurity.com'+final:
                        raise RuntimeError('Public route verification failed')
            print('Office, home and DHA landing pages and legacy redirect live; hosting rules preserved.')
        except Exception as exc:
            print('Public route check failed:',type(exc).__name__,str(exc))
            ftp.storbinary('STOR .htaccess',io.BytesIO(original))
            raise RuntimeError('Route validation failed; original hosting rules restored.') from None
