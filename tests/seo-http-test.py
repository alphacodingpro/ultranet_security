import json, re, urllib.request, urllib.error, xml.etree.ElementTree as ET
from html.parser import HTMLParser
BASE='http://127.0.0.1:8080'
class Page(HTMLParser):
    def __init__(self,text):
        super().__init__(); self.canonical=''; self.robots=''; self.mains=0; self.schemas=[]; self.script=False; self.buf='';self.feed(text)
    def handle_starttag(self,tag,attrs):
        a=dict(attrs)
        if tag=='link' and a.get('rel')=='canonical':self.canonical=a.get('href','')
        if tag=='meta' and a.get('name')=='robots':self.robots=a.get('content','')
        if tag=='main':self.mains+=1
        if tag=='script' and a.get('type')=='application/ld+json':self.script=True;self.buf=''
    def handle_data(self,data):
        if self.script:self.buf+=data
    def handle_endtag(self,tag):
        if tag=='script' and self.script:self.schemas.append(json.loads(self.buf));self.script=False

def get(path):
    try:r=urllib.request.urlopen(BASE+path,timeout=15)
    except urllib.error.HTTPError as e:r=e
    text=r.read().decode();return r.status,r.geturl(),r.headers,text,Page(text)
def check(ok,msg):
    if not ok:raise AssertionError(msg)
series='/products.php?category=test-cameras&brand=Hikvision&series=test-series'
s,u,h,t,p=get(series+'&page=2');check(s==200 and p.canonical==BASE+series+'&page=2','Self-referencing page 2');check(t.count('class="prod-card h-100"')==2,'Page 2 has remaining two products');check('Page 2' in t and p.mains==1,'Page title and single main');check(p.robots=='index, follow','Series indexable')
for path in [series+'&page=99',series+'&page=abc',series+'&page=0','/products.php?category=test-cameras&series=missing','/products.php?category=missing','/products.php?category=test-cameras&brand=Unknown',series.replace('Hikvision','Dahua'),'/product/inactive-camera','/missing-page']:
    check(get(path)[0]==404,'404: '+path)
s,u,h,t,p=get('/products.php?category=test-series');check(u==BASE+series and p.canonical==BASE+series,'Legacy child redirect')
s,u,h,t,p=get('/products.php?category=test-cameras&brand=Hikvision');check('Other Hikvision products' in t and 'test-series' in t,'Series and direct access links')
s,u,h,t,p=get('/products.php?category=test-cameras&brand=Dahua&direct=1');check('Legacy Camera' in t,'Parent products reachable')
s,u,h,t,p=get('/products.php?category=test-cameras&brand=Dahua&series=mixed-series');check('Mixed camera' in t and 'noindex' in p.robots,'Mixed-brand filter')
s,u,h,t,p=get('/products.php?category=empty-category');check('noindex' in p.robots,'Empty noindex')
s,u,h,t,p=get('/search.php?q=Test&page=2');check('/products.php?q=Test&page=2' in u and t.count('class="prod-card h-100"')==2 and 'noindex' in p.robots,'Search paginated')
s,u,h,t,p=get('/product/test-camera-1');product=next(x for x in p.schemas if x['@type']=='Product');check('offers' not in product and 'Price on request' in t and '100% OFF' not in t,'Visible and structured price agree')
check(get('/product.php?slug=test-camera-2')[1]==BASE+'/product/test-camera-2','Product alias redirect')
check(get('/index.php')[1]==BASE+'/','Homepage alias redirect')
for path in ['/products.php?q%5B%5D=bad','/product.php?slug%5B%5D=bad','/search.php?q%5B%5D=bad']:
    check(get(path)[0]<500,'Array input handled')
s,u,h,t,p=get('/estimate.php?ref=unknown');check(s==404 and 'noindex' in h.get('X-Robots-Tag','') and 'no-store' in h.get('Cache-Control',''),'Private estimates')
s,u,h,t,p=get('/sitemap.xml');root=ET.fromstring(t);ns={'s':'http://www.sitemaps.org/schemas/sitemap/0.9'};urls=[n.text for n in root.findall('s:url/s:loc',ns)]
check(BASE+series in urls and not any('empty-category' in x or 'inactive-camera' in x for x in urls),'Sitemap canonical and active content only')
for url in urls:
    s,u,h,t,p=get(url[len(BASE):]);check(s==200 and p.canonical==url and 'noindex' not in p.robots,'Sitemap target valid: '+url)
print('SEO HTTP regressions passed, including all '+str(len(urls))+' fixture sitemap targets.')
