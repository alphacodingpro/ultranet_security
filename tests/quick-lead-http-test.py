import http.cookiejar
import json
import re
import subprocess
import urllib.error
import urllib.parse
import urllib.request

BASE = 'http://127.0.0.1:8080'
opener = urllib.request.build_opener(
    urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar())
)


def request(path, fields=None):
    body = urllib.parse.urlencode(fields).encode() if fields is not None else None
    req = urllib.request.Request(BASE + path, data=body)
    try:
        response = opener.open(req, timeout=15)
    except urllib.error.HTTPError as exc:
        response = exc
    return response.status, response.read().decode()


status, html = request('/')
assert status == 200
assert 'href="' + BASE + '/calculator.php"' in html
assert 'id="visitorLeadModal"' in html and 'data-lead-close' in html
token = re.search(
    r'<form id="visitorLeadForm"[\s\S]*?name="csrf_token" value="([a-f0-9]+)"',
    html
)
assert token, 'Popup form lacks a session-bound CSRF token'
status, text = request('/quick-lead-submit.php', {'email': 'visitor@example.com'})
assert status == 403 and not json.loads(text)['ok'], 'Lead submission must reject missing CSRF'
status, text = request('/quick-lead-submit.php', {'csrf_token': token.group(1)})
assert status == 400 and not json.loads(text)['ok'], 'At least one contact detail required'
status, text = request('/quick-lead-submit.php', {
    'csrf_token': token.group(1),
    'email': 'fixture-popup@example.com',
})
assert status == 200 and json.loads(text)['ok'], 'Email-only lead must be accepted'

result = subprocess.check_output([
    'php', '-r',
    "require 'config/config.php'; require 'includes/functions.php'; "
    "echo getDB()->query(\"SELECT COUNT(*) FROM contact_messages "
    "WHERE source='sitewide_popup' AND email='fixture-popup@example.com' "
    "AND phone=''\")->fetchColumn();"
], text=True).strip()
assert result == '1', 'Popup lead must be saved in Admin contact_messages'
status, text = request('/quick-lead-submit.php', {
    'csrf_token': token.group(1),
    'phone': '+923091234567',
})
assert status == 200 and json.loads(text)['ok'], 'Phone-only lead must be accepted'
result = subprocess.check_output([
    'php', '-r',
    "require 'config/config.php'; require 'includes/functions.php'; "
    "echo getDB()->query(\"SELECT COUNT(*) FROM contact_messages "
    "WHERE source='sitewide_popup' AND phone='+923091234567' "
    "AND email IS NULL\")->fetchColumn();"
], text=True).strip()
assert result == '1', 'Phone-only lead must be saved without an email'

print('Popup lead validation, CSRF and Admin persistence passed.')
