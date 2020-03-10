import pandas as pd
import datetime

socios = pd.read_csv('socios.csv', escapechar = '\\')

socios['next_billing_at'] = pd.to_datetime(socios['next_billing_at'], format='%Y-%m-%d')
socios['card_expiry'] = pd.to_datetime(socios['card_expiry'], format='%Y-%m-%d')

now = pd.to_datetime('now')
socios['birthday'] = pd.to_datetime(socios['birthday'], format='%Y-%m-%d %H:%M:%S', errors = 'coerce')
socios['age'] = (now.year - socios['birthday'].dt.year) - ((now.month - socios['birthday'].dt.month) < 0)

expiry_limit = datetime.date(2020, 1, 1)
ciblage = socios[socios['card_expiry'] < expiry_limit]

next_billing = {'from': datetime.date(2020, 2, 1),'to': datetime.date(2020, 4, 1)}
ciblage = ciblage[(ciblage['next_billing_at'] >= next_billing['from'])
                & (ciblage['next_billing_at'] <= next_billing['to'])]

#ciblage = ciblage[(ciblage['age'] > 50)
#                & (ciblage['age'] < 100)]

ciblage = ciblage[(ciblage['phone'] != u'') & (ciblage['phone'].notnull())]
ciblage['amount'] = ciblage['amount'] / 100
ciblage.sort_values(by='amount', ascending = False, inplace = True)
ciblage.to_csv('ciblage.csv')
ciblage.to_json('ciblage.json', orient = 'records')
