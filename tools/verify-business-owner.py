"""Verify the visible owner details and matching public JSON-LD after deployment."""
import json
import urllib.request
from html.parser import HTMLParser

BASE = 'https://ultranetsecurity.com'

class IdentityPage(HTMLParser):
    def __init__(self, html):
        super().__init__()
        self.in_schema = False
        self.schema_text = ''
        self.schemas = []
        self.visible = []
        self.hidden_depth = 0
        self.feed(html)

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag in ('script', 'style'):
            self.hidden_depth += 1
        if tag == 'script' and attrs.get('type') == 'application/ld+json':
            self.in_schema = True
            self.schema_text = ''

    def handle_data(self, data):
        if self.in_schema:
            self.schema_text += data
        if not self.hidden_depth:
            self.visible.append(data)

    def handle_endtag(self, tag):
        if tag == 'script' and self.in_schema:
            self.schemas.append(json.loads(self.schema_text))
            self.in_schema = False
        if tag in ('script', 'style'):
            self.hidden_depth = max(0, self.hidden_depth - 1)

for path in ('/', '/products.php'):
    request = urllib.request.Request(BASE + path, headers={'User-Agent': 'UltraNet-Owner-SEO-Audit/1.0'})
    with urllib.request.urlopen(request, timeout=30) as response:
        if response.status != 200:
            raise RuntimeError('Owner verification HTTP failure: ' + path)
        response_body = response.read().decode('utf-8')
        page = IdentityPage(response_body)
    text = ' '.join(page.visible)
    person = next(x for x in page.schemas if x.get('@type') == 'Person' and x.get('@id') == BASE + '/#muhammad-ubaid')
    business = next(x for x in page.schemas if x.get('@type') == 'LocalBusiness')
    if not (
        'Muhammad Ubaid' in text
        and 'Owner, UltraNet Security' in text
        and person['name'] == 'Muhammad Ubaid'
        and person['jobTitle'] == 'Owner'
        and person['url'] == BASE + '/#owner'
        and person['worksFor']['@id'] == business['@id']
        and business['employee']['@id'] == person['@id']
    ):
        raise RuntimeError('Public owner identity mismatch: ' + path)
    if path == '/' and ('owner-heading' in response_body or 'Who owns UltraNet Security?' in text):
        raise RuntimeError('Homepage owner profile was not removed')
    if 'id="owner"' not in response_body:
        raise RuntimeError('Public footer owner anchor missing')
    print('Verified public owner identity:', BASE + path, 'Muhammad Ubaid — Owner, UltraNet Security')
