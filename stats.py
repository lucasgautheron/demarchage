import json
import datetime
from dateutil import relativedelta
import pandas as pd
import sys
import math
import numpy as np
import statsmodels as sm 
import statsmodels.stats.proportion
#import scipy.stats as stats
import requests
import os

def fetch_emails(email):
    auth = (os.getenv('DEMARCHAGE_USER'), os.getenv('DEMARCHAGE_PASS'))
    r = requests.get("http://51.15.183.177/demarchage/emails.php?method=json&email=" + email, auth = auth, timeout = 10)
    emails = json.loads(r.text)

    for _email in emails:
       _email["to"] = email

    return emails

socios = pd.read_csv('ciblage/socios.csv')
socios['birthday'] = pd.to_datetime(socios['birthday'], format='%Y-%m-%d', errors = 'coerce')
socios['age'] = (datetime.date.today() - socios['birthday'].dt.date).dt.days/365.25
socios['decade'] = socios['age'].map(lambda x: 10*math.floor((0 if np.isnan(x) else x)/10))
socios['createdAt'] = pd.to_datetime(socios['createdAt'], format='%Y-%m-%d', errors = 'coerce')
socios['time_since_registration'] = (datetime.date.today() - socios['createdAt'].dt.date).dt.days/(30*12)
socios['time_since_registration'] = socios['time_since_registration'].map(lambda x: 12*math.floor((0 if np.isnan(x) else x)))

print(socios)

demarchage = pd.read_json('done.json', orient = 'index')
demarchage['date'] = pd.to_datetime(demarchage['datetraitement'], format='%Y-%m-%d', errors = 'coerce')

month = datetime.date(2020, 1, 1)
end = datetime.date.today()
end = end.replace(day = 1)

all_events = []
while (month <= end):
    events = pd.read_csv(sys.argv[1] + '/events__' + month.strftime('%d-%m-%Y') + ".csv")
    if not 'type' in events:
        continue
    all_events.append(events)
    
    month = month + relativedelta.relativedelta(months=1)


events = pd.concat(all_events)

cards_updated = events[events['type'].isin(['card_deleted', 'card_added'])]
cards_updated = cards_updated.groupby(['date', 'customer']).agg('count')
cards_updated = cards_updated[cards_updated['type'] == 2]
cards_updated['updated'] = 1
cards_updated.reset_index(inplace = True)
cards_updated['date'] = pd.to_datetime(cards_updated['date'], format = '%Y-%m-%d %H:%M:%S')
cards_updated.sort_values('date', ascending = True, inplace = True)
cards_updated = cards_updated.drop_duplicates(['customer'], keep = 'last').set_index('customer')

demarchage = demarchage.merge(cards_updated[['date', 'updated']], how = 'left', left_index = True, right_index = True)
demarchage = demarchage[~(demarchage['date_x'].isnull() | (demarchage['done'].isnull()))]
demarchage['month'] = demarchage['date_x'].dt.strftime('%Y-%m')
demarchage['causal'] = demarchage['date_y'] >= demarchage['date_x']
demarchage['updated'] = demarchage['updated'].fillna(0)
demarchage['updated'] = (demarchage['updated'] == 1) & demarchage['causal']
demarchage = demarchage.merge(socios, how = 'inner', left_index = True, right_index = False, right_on = 'chargebee_id')

#email_addresses = list(set(demarchage['email'].tolist()))
#emails = []
#count = 0
#for address in email_addresses:
#    emails.append(fetch_emails(address))
#    count += 1
#    print(count, len(email_addresses))

#flat_list = [item for sublist in emails for item in sublist]
#pd.DataFrame(flat_list).to_csv('emails.csv', index = False)

emails = pd.read_csv('emails.csv')
emails = emails.groupby(['to', 'Status']).size().unstack(fill_value = 0)
emails = emails[emails['sent'] > 0]
emails['clickrate'] = (emails['clicked']/emails.sum(axis=1))
emails['openrate'] = (emails['opened']/emails.sum(axis=1))
emails['high_clickrate'] = emails['clicked'] > 0
emails['high_openrate'] = emails['openrate'] >= 0.01

demarchage = demarchage.merge(emails, how = 'left', left_on = 'email', right_on = 'to')

def error_margin(row):
    row = row['updated']
    n = row['count']
    p = row['mean']
    q = 1-p
    # stats.binom.ppf(0.025, n, p)/n
    ci_low, ci_upp = sm.stats.proportion.proportion_confint(row['sum'], row['count'], alpha = 0.1, method = 'beta')
    return pd.Series({'low': ci_low, 'high': ci_upp})


print(demarchage.groupby(['month','done']).agg({'updated': ['mean', 'count', 'sum']}))
status = demarchage.groupby('done').agg({'updated': ['mean', 'count', 'sum']})
decade = demarchage.groupby('decade').agg({'updated': ['mean', 'count', 'sum']})
amount = demarchage.groupby('amount').agg({'updated': ['mean', 'count', 'sum']})
titres = demarchage.groupby('titres').agg({'updated': ['mean', 'count', 'sum']})
clicked = demarchage.groupby('high_clickrate').agg({'updated': ['mean', 'count', 'sum']})
opened = demarchage.groupby('high_openrate').agg({'updated': ['mean', 'count', 'sum']})

tsr = demarchage.groupby('time_since_registration').agg({'updated': ['mean', 'count', 'sum']})


clicked = pd.concat([clicked, clicked.apply(error_margin, axis = 1)], axis = 1)
opened = pd.concat([opened, opened.apply(error_margin, axis = 1)], axis = 1)
tsr = pd.concat([tsr, tsr.apply(error_margin, axis = 1)], axis = 1)



print(clicked, opened, tsr)
