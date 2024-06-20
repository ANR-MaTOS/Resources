#!/usr/bin/python
import dill
import argparse

parser = argparse.ArgumentParser()
parser.add_argument('hal_export')
args = parser.parse_args()

pubs = dill.load(open(args.hal_export, 'rb'))

print('#pubs analysed = ' + str(len(pubs)))

# get all keywords
keywords = {}
for hal_id in pubs:
    pub = pubs[hal_id]
    for kw_scheme in pub.info['keywords']:
        for lg, kw in pub.info['keywords'][kw_scheme]:
            if kw.lower().strip() not in keywords:
                keywords[kw.lower().strip()] = 0
            keywords[kw.lower().strip()] += 1

# print out most common keywords
for kw, num in sorted(keywords.items(), key=lambda x: x[1], reverse=True)[2700:]:
    print(kw, num)
