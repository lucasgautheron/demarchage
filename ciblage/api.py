import requests
import json
import pandas as pd
import sys
import os

MEDIAPI_EMAIL = os.environ['MEDIAPI_EMAIL']
MEDIAPI_PWD = os.environ['MEDIAPI_PWD']

def exec_query(query, token = ""):
    r = requests.post("https://www.lemediatv.fr/mediapi", json = {'query': query}, headers = {'Authorization': token})
    ret = json.loads(r.text)
    return ret

def get_users():

    login = '''
    mutation {
        login (email: "%s", password: "%s") {
            email
        }
    }
    '''

    get_users = '''
    query {
        users(page: %d, pageSize: %d) {
            id,
            email,
            firstname,
            lastname,
            birthday,
            phone,
            address,
            zipcode,
            city,
            country,
            status,
            contribution,
            nextBilling,
            paymentMethod,
            chargebeeId,
            authToken
        }
    }
    '''

    login_res = exec_query(login % (MEDIAPI_EMAIL, MEDIAPI_PWD))
    token = login_res['extensions']['token']

    users = []
    page = 1
    while True:
        ret = exec_query(get_users % (page, 1000), token)['data']['users']
        print(ret)

        if (len(ret) <= 0):
            break

        users = users + ret
        page += 1

    return users

df = pd.DataFrame.from_dict(get_users())
df.set_index('id', inplace = True)

subscriptions = pd.read_csv('../pognon/active_subscriptions.csv')
subscriptions.set_index('chargebee_id', inplace = True)
subscriptions = subscriptions.merge(df, left_index = True, right_index = False, right_on = 'chargebeeId', how = 'inner')
subscriptions['updateCardUrl'] = "https://www.lemediatv.fr/communaute/moyen-de-paiement?auth=" + subscriptions.authToken.map(str)
subscriptions.reset_index(inplace = True)
subscriptions['chargebee_id'] = subscriptions['chargebeeId']
subscriptions.to_csv('socios.csv', index = False)

print(subscriptions)
