"""Read-only public sitemap crawl; no logins, forms or private estimate URLs."""
import collections, concurrent.futures, json, sys, time, urllib.request, urllib.error, urllib.parse, xml.etree.ElementTree as ET
from html.parser import HTMLParser
BASE='https://ultranetsecurity.com'
class Page(HTMLParser):
    def __init__(self,text):
        super().__init__();self.title='';self.in_title=False;self.description='';self.canonical='';self.robots='';self.h1=0;self.images=[];self.schema_errors=0;self.schema=False;self.buf='';self.feed(text)
    def handle_starttag(self,tag,attrs):
        a=dict(attrs)
        if tag=='title':self.in_title=True
        if tag=='h1':self.h1+=1
        if tag=='meta' and a.get('name')=='description':self.description=a.get('content','')
        if tag=='meta' and a.get('name')=='robots':self.robots=a.get('content','')
        if tag=='link' and a.get('rel')=='canonical':self.canonical=a.get('href','')
        if tag=='img':self.images.append({'src':a.get('src',''),'alt':a.get('alt')})
        if tag=='script' and a.get('type')=='application/ld+json':self.schema=True;self.buf=''
    def handle_data(self,data):
        if self.in_title:self.title+=data
        if self.schema:self.buf+=data
    def handle_endtag(self,tag):
        if tag=='title':self.in_title=False
        if tag=='script' and self.schema:
            try:json.loads(self.buf)
            except ValueError:self.schema_errors+=1
            self.schema=False

def fetch(url):
    time.sleep(.2)
    req=urllib.request.Request(url,headers={'User-Agent':'UltraNet-Owner-SEO-Audit/1.0'})
    try:
        with urllib.request.urlopen(req,timeout=25) as r:return r.status,r.geturl(),dict(r.headers),r.read(4_000_000).decode('utf-8','replace')
    except urllib.error.HTTPError as e:return e.code,e.geturl(),dict(e.headers),e.read(200_000).decode('utf-8','replace')
    except Exception as e:return 0,url,{},type(e).__name__+': '+str(e)
def audit(url):
    status,final,headers,body=fetch(url);p=Page(body);issues=[]
    if status!=200:issues.append('HTTP '+str(status))
    if not p.title:issues.append('Missing title')
    if not p.description:issues.append('Missing description')
    if p.h1!=1:issues.append('H1 count '+str(p.h1))
    if p.canonical!=url:issues.append('Canonical differs')
    if 'noindex' in p.robots:issues.append('Noindex sitemap target')
    if p.schema_errors:issues.append('Invalid JSON-LD')
    return {'url':url,'status':status,'final_url':final,'title':p.title,'description':p.description,'canonical':p.canonical,'robots':p.robots,'issues':issues,'missing_alt':sum(i['alt'] is None for i in p.images),'images':list({urllib.parse.urljoin(url,i['src']) for i in p.images if i['src']})}
report={'checks':{},'pages':[]}
for path in ['/robots.txt','/sitemap.xml','/packages/','/index.php','/seo-audit-nonexistent-page-404']:
    status,url,headers,body=fetch(BASE+path)
    report['checks'][path]={'status':status,'final_url':url,'content_type':headers.get('Content-Type','')}
    if path=='/robots.txt':report['checks'][path]['body']=body[:2000]
    if path=='/sitemap.xml':xml=body
for url in ['http://ultranetsecurity.com/','https://www.ultranetsecurity.com/']:
    status,final,headers,body=fetch(url);report['checks'][url]={'status':status,'final_url':final}
try:urls=[x.text for x in ET.fromstring(xml).findall('{http://www.sitemaps.org/schemas/sitemap/0.9}url/{http://www.sitemaps.org/schemas/sitemap/0.9}loc')]
except Exception as e:urls=[BASE+'/',BASE+'/products.php',BASE+'/calculator.php',BASE+'/privacy-policy.php'];report['sitemap_error']=str(e)
urls=[u for u in dict.fromkeys(urls) if urllib.parse.urlparse(u).netloc=='ultranetsecurity.com'][:2000]
report['sitemap_url_count']=len(urls)
if '--sample' in sys.argv:urls=urls[:6]+[u for u in urls if '/product/' in u][:3]
with concurrent.futures.ThreadPoolExecutor(max_workers=2) as pool:report['pages']=list(pool.map(audit,dict.fromkeys(urls)))
report['duplicate_titles']={t:count for t,count in collections.Counter(p['title'] for p in report['pages']).items() if t and count>1}
report['duplicate_descriptions']={t:count for t,count in collections.Counter(p['description'] for p in report['pages']).items() if t and count>1}
with open('seo-live-audit.json','w') as f:json.dump(report,f,indent=2)
print(json.dumps({'checks':report['checks'],'sitemap_url_count':report['sitemap_url_count'],'crawled':len(report['pages']),'pages_with_issues':sum(bool(p['issues']) for p in report['pages']),'duplicate_titles':report['duplicate_titles'],'duplicate_descriptions':report['duplicate_descriptions']},indent=2))
for p in report['pages']:
    if p['issues']:print(json.dumps({k:p[k] for k in ['url','status','canonical','issues']}))
