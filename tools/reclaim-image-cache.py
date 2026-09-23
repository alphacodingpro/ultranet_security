"""Free hosting quota by replacing two generated WebP derivatives with smaller copies.
All originals in assets/img remain untouched. The current derivative is kept in
memory and restored if replacement fails.
"""
import ftplib, io, os
from pathlib import Path
candidates=('service-access.webp','service-shop.webp')
with ftplib.FTP(timeout=30) as ftp:
    ftp.connect(os.environ['FTP_SERVER'])
    ftp.login(os.environ['FTP_USERNAME'],os.environ['FTP_PASSWORD'])
    for name in candidates:
        path='assets/optimized/'+name
        old=io.BytesIO()
        ftp.retrbinary('RETR '+path,old.write)
        old_bytes=old.getvalue()
        replacement=(Path('assets/optimized')/name).read_bytes()
        if len(replacement)>=len(old_bytes):
            print(name,'is already as small as this build; left unchanged')
            continue
        ftp.delete(path)
        try:
            ftp.storbinary('STOR '+path,io.BytesIO(replacement))
            if ftp.size(path)!=len(replacement):
                raise RuntimeError('Short upload of generated image')
        except Exception:
            try:ftp.delete(path)
            except ftplib.error_perm:pass
            ftp.storbinary('STOR '+path,io.BytesIO(old_bytes))
            raise
        print(name,': reclaimed',len(old_bytes)-len(replacement),'bytes')
