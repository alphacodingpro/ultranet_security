import urllib.request, urllib.error
class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self,*args,**kwargs): return None
opener=urllib.request.build_opener(NoRedirect)
def request(host,proto='',method='GET'):
    h={'Host':host}
    if proto:h['X-Forwarded-Proto']=proto
    req=urllib.request.Request('http://127.0.0.1:8082/robots.php?check=1',headers=h,method=method)
    try:r=opener.open(req)
    except urllib.error.HTTPError as e:r=e
    return r.status,r.headers
s,h=request('ultranetsecurity.com');assert s==301 and h['Location']=='https://ultranetsecurity.com/robots.php?check=1'
s,h=request('www.ultranetsecurity.com','https');assert s==301 and h['Location']=='https://ultranetsecurity.com/robots.php?check=1'
s,h=request('ultranetsecurity.com','https');assert s==200,'No redirect loop behind HTTPS proxy'
s,h=request('ultranetsecurity.com',method='POST');assert s==308,'Preserve POST method'
print('Canonical origin regressions passed.')
