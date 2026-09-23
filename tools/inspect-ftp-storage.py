"""Read-only hosting storage inventory for a failed DHA deployment."""
import ftplib, os
ftp=ftplib.FTP(timeout=30)
ftp.connect(os.environ['FTP_SERVER'])
ftp.login(os.environ['FTP_USERNAME'],os.environ['FTP_PASSWORD'])
for directory in ['assets/optimized','assets/css']:
    print('Directory:',directory)
    ftp.cwd('/'+directory)
    for name in ftp.nlst():
        base=name.rsplit('/',1)[-1]
        if directory.endswith('optimized') or base in ('dha.css','style.css'):
            try:size=ftp.size(base)
            except ftplib.all_errors:size=None
            print('  ',base,size)
    ftp.cwd('/')
print('DHA page:',end=' ')
try:print(ftp.size('cctv-camera-installation-dha-karachi.php'))
except ftplib.all_errors:print('not present')
try:
    extras=[]
    for name,facts in ftp.mlsd():
        if name.endswith(('.zip','.log','.bak','.sql','.tmp')) and facts.get('type')=='file':
            extras.append((int(facts.get('size') or 0),name))
    print('Root archived files:',sorted(extras,reverse=True)[:12])
except ftplib.all_errors:print('Root archive listing unavailable')
ftp.quit()
