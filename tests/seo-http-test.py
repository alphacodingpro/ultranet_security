import json, re, urllib.request, urllib.error, xml.etree.ElementTree as ET
from html.parser import HTMLParser
BASE='http://127.0.0.1:8080'
class Page(HTMLParser):
    def __init__(self,text):
        super().__init__(); self.canonical=''; self.robots=''; self.mains=0; self.h1=0; self.schemas=[]; self.script=False; self.buf='';self.feed(text)
    def handle_starttag(self,tag,attrs):
        a=dict(attrs)
        if tag=='link' and a.get('rel')=='canonical':self.canonical=a.get('href','')
        if tag=='meta' and a.get('name')=='robots':self.robots=a.get('content','')
        if tag=='main':self.mains+=1
        if tag=='h1':self.h1+=1
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
home='/home-cctv-installation-karachi'
s,u,h,t,p=get(home)
check(s==200 and p.canonical==BASE+home and p.robots=='index, follow' and p.h1==1,'Home CCTV canonical, indexing and single H1')
check('home-cctv.css' in t and t.find('home-cctv.css')<t.find('</head>'),'Home CCTV stylesheet loads in head')
check('id="plan"' in t and 'id="coverage"' in t and 'id="equipment"' in t and 'id="questions"' in t,'Home CCTV planning sections render')
check(t.count('class="homecam-faq"')>=15 and 'Will the cameras record if the internet is down?' in t,'Crawlable, visible homeowner questions')
check(any(x.get('@type')=='Service' and x['areaServed']['name']=='Karachi' for x in p.schemas),'Home CCTV service schema')
check('FAQPage' not in [x.get('@type') for x in p.schemas],'No deprecated FAQ rich-result markup')
check('wa.me/923091243189' in t and '/calculator.php' in t,'Home CCTV contact and calculator actions')
check(get(home+'.php')[1]==BASE+home and get(home+'/')[1]==BASE+home,'Home CCTV URL aliases redirect')
s,u,h,t,p=get('/')
check('href="'+BASE+home+'"' in t and 'Explore the Guide' in t,'Homepage service card links to guide')
office='/office-cctv-installation-karachi'
s,u,h,t,p=get(office)
check(s==200 and p.canonical==BASE+office and p.robots=='index, follow' and p.h1==1 and p.mains==1,'Office CCTV canonical, indexability and one main/H1')
check('Office CCTV Installation' in t and 'id="office-plan"' in t and 'id="office-coverage"' in t and 'id="office-system"' in t and 'id="office-handover"' in t,'Office service content is rendered')
check(t.count('class="homecam-faq"')>=10 and 'Can managers view different cameras' in t and 'Will office CCTV continue recording' in t,'Useful visible office questions')
check(any(x.get('@type')=='Service' and x['areaServed']['name']=='Karachi' for x in p.schemas),'Office service schema')
check('FAQPage' not in [x.get('@type') for x in p.schemas],'Avoid ineligible office FAQ markup')
check('home-cctv.css' in t and t.find('home-cctv.css')<t.find('</head>') and 'service-office' in t,'Office CSS and existing illustration')
check('wa.me/923091243189' in t and '/calculator.php' in t,'Office survey and calculator actions')
check(get(office+'.php')[1]==BASE+office and get(office+'/')[1]==BASE+office,'Office aliases redirect')
s,u,h,t,p=get('/')
check('href="'+BASE+office+'"' in t and 'Office &amp; Commercial CCTV' in t,'Homepage office service card links to guide')
shop='/shop-cctv-installation-karachi'
s,u,h,t,p=get(shop)
check(s==200 and p.canonical==BASE+shop and p.robots=='index, follow' and p.h1==1 and p.mains==1,'Shop CCTV canonical, indexability and one main/H1')
check('Shop CCTV Installation' in t and 'id="retail-plan"' in t and 'id="retail-coverage"' in t and 'id="retail-system"' in t and 'id="retail-incident"' in t,'Retail service content is rendered')
check(t.count('class="homecam-faq"')>=12 and 'Should a camera point directly at the card terminal?' in t and 'Does a CCTV system replace stock control or POS records?' in t,'Useful visible retail questions')
check(any(x.get('@type')=='Service' and x['areaServed']['name']=='Karachi' for x in p.schemas),'Shop CCTV service schema')
check('FAQPage' not in [x.get('@type') for x in p.schemas],'Avoid ineligible shop FAQ markup')
check('home-cctv.css' in t and t.find('home-cctv.css')<t.find('</head>') and 'service-shop' in t,'Shop CSS and existing illustration')
check('wa.me/923091243189' in t and '/calculator.php' in t and '/products.php' in t,'Shop contact, calculator and product actions')
check(get(shop+'.php')[1]==BASE+shop and get(shop+'/')[1]==BASE+shop,'Shop aliases redirect')
s,u,h,t,p=get('/')
check('href="'+BASE+shop+'"' in t and 'Shop &amp; Retail CCTV' in t,'Homepage shop service card links to guide')
check(get('/office-cctv-installation-karachi')[4].h1==1,'Office CCTV page still works')
check(get('/home-cctv-installation-karachi')[4].h1==1,'Home CCTV page still works')
dha='/cctv-camera-installation-dha-karachi'
s,u,h,t,p=get(dha);check(s==200 and p.canonical==BASE+dha and p.robots=='index, follow' and p.h1==1,'DHA canonical, indexability and H1')
check('Phases 1–8' in t and all(('Phase '+str(i)) in t for i in range(1,9)),'DHA phases 1–8')
check('dha.css' in t and t.find('dha.css')<t.find('</head>') and 'assets/optimized/service-home.webp' in t,'DHA CSS in head and real compressed photo')
check(any(x.get('@type')=='Service' and x['areaServed']['name']=='DHA Karachi, Phases 1–8' for x in p.schemas),'DHA Service schema')
check(any(x.get('@type')=='FAQPage' for x in p.schemas) and 'wa.me/923091243189' in t and 'tel:+923091243189' in t,'DHA FAQs and direct CTAs')
check(get(dha+'.php')[1]==BASE+dha and get(dha+'/')[1]==BASE+dha,'DHA aliases redirect')
s,u,h,t,p=get('/sitemap.xml');root=ET.fromstring(t);ns={'s':'http://www.sitemaps.org/schemas/sitemap/0.9'};urls=[n.text for n in root.findall('s:url/s:loc',ns)]
check(BASE+home in urls and BASE+office in urls and BASE+shop in urls and BASE+dha in urls and BASE+series in urls and not any('empty-category' in x or 'inactive-camera' in x for x in urls),'Sitemap canonical and active content only')
for url in urls:
    s,u,h,t,p=get(url[len(BASE):]);check(s==200 and p.canonical==url and 'noindex' not in p.robots,'Sitemap target valid: '+url)
print('SEO HTTP regressions passed, including all '+str(len(urls))+' fixture sitemap targets.')
