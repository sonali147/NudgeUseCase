import requests
import json

url = "http://0.0.0.0:5000/get_nudge"
url = "http://0.0.0.0:5000/get_leaderboard"
url = "http://0.0.0.0:5000/get_stats"

headers = {
    'cache-control': "no-cache",
    'postman-token': "82fb57e2-aefe-68c1-12ad-428f495cf7f4"
    }
payload = {
    'user_id' : 3965,
    'filter' : "area"
}
response = requests.request("GET", url, headers=headers, data=json.dumps(payload))
nudge = json.loads(response.text)
if nudge:
    print(nudge)
    if isinstance(nudge, list):
        for txt in nudge:
            print(txt)
            print()