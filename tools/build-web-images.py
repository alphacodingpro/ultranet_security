"""Create delivery-size WebP copies of the seven large homepage photos.
Original files are preserved. Deployment reads the live originals so a custom
photo uploaded in cPanel is not replaced by an older repository image.
"""
import argparse, ftplib, io, os
from pathlib import Path
from PIL import Image, ImageOps
parser=argparse.ArgumentParser();parser.add_argument('--live',action='store_true');args=parser.parse_args()
files=['service-home.jpg','service-office.jpg','service-shop.jpg','service-amc.jpg','service-ip.jpg','service-access.jpg','why-us.jpg']
out=Path('assets/optimized');out.mkdir(parents=True,exist_ok=True)
ftp=None
if args.live:
    ftp=ftplib.FTP(timeout=30);ftp.connect(os.environ['FTP_SERVER']);ftp.login(os.environ['FTP_USERNAME'],os.environ['FTP_PASSWORD'])
before=after=0
try:
    for filename in files:
        if ftp:
            content=io.BytesIO();ftp.retrbinary('RETR assets/img/'+filename,content.write);data=content.getvalue()
        else:data=(Path('assets/img')/filename).read_bytes()
        with Image.open(io.BytesIO(data)) as im:
            im=ImageOps.exif_transpose(im).convert('RGB');im.thumbnail((1000,1000),Image.Resampling.LANCZOS)
            target=out/(Path(filename).stem+'.webp');im.save(target,format='WEBP',quality=78,method=6)
            with Image.open(target) as check:check.verify()
        before+=len(data);after+=target.stat().st_size
    print(f'Homepage photos: {before:,} original bytes -> {after:,} delivery bytes ({100*(1-after/before):.1f}% smaller). Originals preserved.')
finally:
    if ftp:ftp.quit()
