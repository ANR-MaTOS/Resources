#!/usr/bin/python
import json

import argparse
parser = argparse.ArgumentParser()
parser.add_argument('fichier1')
parser.add_argument('fichier2')
args = parser.parse_args()

hal_id=[]
with open(args.fichier1) as fp:
    for line in fp:
        article = json.loads(line)
        hal_id.append(article['idhal'])
        print(json.dumps(article))

with open(args.fichier2) as fp:
    for line in fp:
        article = json.loads(line)
        if article['idhal'] not in hal_id:
            print(json.dumps(article))


