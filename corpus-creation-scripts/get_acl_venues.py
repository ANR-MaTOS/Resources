#!/usr/bin/python
import pandas as pd
import argparse
import re
from pylatexenc.latex2text import LatexNodes2Text

parser = argparse.ArgumentParser()
parser.add_argument('-p', '--publications', default='data/acl-publication-info.74k.parquet')
args = parser.parse_args()

def clean_venue(venue):
    venue = LatexNodes2Text().latex_to_text(venue)
    return venue.replace('{', '').replace('}', '').replace('--', '—')

def extract_name(venue):
    confmatch0 = re.match('Proceedings of the.+?Annual Meeting of the (.+?)$', venue)
    confmatch1 = re.match('Proceedings of (?:the)?.*?(?:Workshop|Conference) .n (.+?)$', venue)
    confmatch1b = re.match('Proceedings of (?:the)?.*?((?:Workshop|Conference) .n .+?)$', venue)
    confmatch2 = re.match('International Conference .n (.+?)$', venue)
    confmatch3 = re.match('Proceedings of (?:the)?(.+?)$', venue)
    confmatch4 = re.match('^.+?\((.+?)[\'’- ]?(?:\d+)?\)$', venue)
    no_acronym = re.compile('^(.+?) \(.+?\)$')    
    extracted = []
    for confmatch in [confmatch0, confmatch1, confmatch1b, confmatch2, confmatch3, confmatch4]:
        if confmatch:
            extracted.append(confmatch.group(1))
            noacrmatch = no_acronym.match(extracted[-1])
            if noacrmatch:
                extracted.append(noacrmatch.group(1))
    return extracted
        
all_extracted = []
df = pd.read_parquet(args.publications)
for booktitle in set(df['booktitle'].values).union(set(df['journal'].values)):
    if booktitle is not None:
        print(clean_venue(booktitle).strip())
        extracted = extract_name(clean_venue(booktitle))
        if extracted not in all_extracted and extracted not in [None, []]:
            for ex in extracted:
                all_extracted.append(ex)
                print(ex.strip())
            
        
    
