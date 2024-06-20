#!/usr/bin/python
import argparse
import dill
import json
import re
import os
from bs4 import BeautifulSoup as bs
import xmltodict
#from get_acl_article import get_acl_by_title
#from get_arxiv_article import get_arxiv_by_title, norm_title
import xml.etree.ElementTree as ET
parser = argparse.ArgumentParser()
parser.add_argument('pubs')
parser.add_argument('--structures', help='File containing a list of structures to match (one per line)')
parser.add_argument('--after_year', type=int)
parser.add_argument('--parallel', action='store_true', default=False, help='Instead of getting English abstracts only, get those with both French and English')
parser.add_argument('--open_licences_only', default=False, action='store_true')
args = parser.parse_args()

# Find the minimal list of venues so that there are no redundant entries (e.g. no EMNLP'22 if EMNLP exists)
def minimal_list(orig_list):
    minimal = []
    minimal_str = '|'
    for element in sorted(orig_list, key=len, reverse=True):
        if '|' + element + '|' not in minimal_str:
            minimal_str += element + '|'
            minimal.append(element)
    return minimal

# get venues (N/A here)
venues = []

# get structures
structures = []
if args.structures is not None:
    with open(args.structures) as fp:
        for line in fp:
            if line.strip() != '':
                structures.append(line.strip())

        
# get keywords (N/A here)
keywords = []
search_keywords = []
norm_keywords = []

search_keywords = minimal_list(search_keywords)
norm_keywords = minimal_list(norm_keywords)
keywords = minimal_list(keywords)
            
def match_venue(venue):
    prep_venue = '|' + '|'.join(re.sub('([\'\-.,;:\(\)\[\]]+)', r' \1 ', venue).split()) + '|'
    for known_venue in search_venues:
        if known_venue in prep_venue:
            return known_venue
    return ''

def match_keyword(keywords):
    found = [] # make a list for debugging purposes (i.e. to make it easier to collect all keywords)
    for scheme in keywords:
        for lang, keyword in keywords[scheme]:
            # compare lowercase with no spaces and punctuation
            if re.sub('\W', '', keyword.lower().strip()) in norm_keywords:
                found.append(keyword)
                return found
    return found

def match_keyword_abstract(abstract):
    found = [] # make a list for debugging purposes (i.e. to make it easier to collect all keywords)
    lc_abstract = '|' + '|'.join(re.sub('([\'\-\.,;:\(\)\[\]]+)', r' \1 ', abstract.lower()).split()) + '|'
    for keyword in search_keywords:
        if keyword in lc_abstract:
            found.append(keyword)
            return found
    return found

def get_author_list(tei):
    author_list = []
    people = re.findall('<author role="(?:aut|crp|co_last_author|co_first_author)"> *<pers[Nn]ame>.+?</pers[nN]ame>', re.sub(' +', ' ', tei.replace('\n', '')))
    for person in people:
        firstname_match = re.match('.+?<forename type="first">(.+?)</forename>', person)
        if not firstname_match:
            continue
        firstname = firstname_match.group(1)
        surname = re.match('.+?<surname>(.+?)</surname>', person).group(1)
        if (firstname, surname) not in author_list:
            author_list.append((firstname, surname))
    return author_list


def get_from_acl_or_arxiv(title, author_list, abstract):
    acl_paper = get_acl_by_title(title, author_list)
    acl_abstract_match = False
    acl_licence = None
    if acl_paper is not None:
        acl_abstract_match = identical_abstract(acl_paper.get_abstract(), abstract)
        # note on ACL licences: https://aclanthology.org/faq/copyright/
        if int(acl_paper.as_dict()['year']) >= 2016:
            acl_licence = 'Creative Commons 3.0 BY-NC-SA'
        else:
            acl_licence = 'Creative Commons 4.0 BY'
    arxiv_abstract_match = False
    arxiv_licence = None
    arxiv_paper = None
    if acl_paper is None:
        #arxiv_paper = get_arxiv_by_title(title, author_list, 0, 3)
        if arxiv_paper is not None:
            arxiv_abstract_match = identical_abstract(arxiv_paper['summary'], abstract)
    return (acl_paper, acl_abstract_match, acl_licence), (arxiv_paper, arxiv_abstract_match, arxiv_licence) 

def extract_values_by_key(obj, target_key, parent_key=''):
    if isinstance(obj, dict):
        for k, v in obj.items():
            full_key = f"{parent_key}.{k}" if parent_key else k
            if k in target_key:
                yield full_key, v
            if isinstance(v, dict) or isinstance(v, list):
                yield from extract_values_by_key(v, target_key, full_key)
    elif isinstance(obj, list):
        for index, item in enumerate(obj):
            full_key = f"{parent_key}[{index}]"
            if isinstance(item, dict) or isinstance(item, list):
                yield from extract_values_by_key(item, target_key, full_key)

def flatten_list(nested_list):
    for item in nested_list:
        if isinstance(item, list):
            yield from flatten_list(item)
        else:
            yield item
                
def words_in_orgs(tei_dict, words):
    org_names = list(extract_values_by_key(tei_dict, ['orgName', '@xml:id']))
    for word in words:
        for k, v in org_names:
            org_matches = list(flatten_list([word_in_org_aux(v, word)]))
            if any(org_matches):
                return [x for x in org_matches if x is not None]
    return [None]

def word_in_org_aux(element, word):
    if type(element) == str:
        if word.lower() in element.lower():
            return element
    elif type(element) == list:
        return [word_in_org_aux(x, word) for x in element]
    elif type(element) == dict:
        return [word_in_org_aux(element[x], word) for x in element]
    return None

def tei2dict(tei):
    return xmltodict.parse(tei)

def identical_abstract(abstract1, abstract2):
    return norm_title(abstract1) == norm_title(abstract2)
        
num_acl = 0
num_arxiv = 0

# go through publications and find matches in venues and keywords
pubs = dill.load(open(args.pubs, 'rb'))
keep_pubs = {}
for hal_id in pubs:
    reason_chosen = ''
    pub = pubs[hal_id]
    
    tei_dict = tei2dict(pub.tei)

    if args.structures is not None:
        org_matched = words_in_orgs(tei_dict, structures)
        if type(org_matched) != list:
            org_matched = [org_matched]
        if any(org_matched):
            reason_chosen = 'ORG_MATCH\t' + str(org_matched)
        pub.info['reason'] = {'org_matched': org_matched}
    else:
        reason_chosen = 'NO FILTER'
        pub.info['reason'] = 'no filter applied'

    # if there is a reason to select the article, print it out
    if reason_chosen != '':
        
        if (not args.parallel and pub.abstract.get('en', '') != '' and pub.text_lang == 'English' and pub.abstract.get('fr', '') == '') or \
           (args.parallel and pub.abstract.get('en', '') != '' and pub.abstract.get('fr', '') != ''):
            # add reason for being selected
            
            # dump as a jsonl line
            hal_keywords = []
            for scheme in pub.info['keywords']:
                for hal_keyword in pub.info['keywords'][scheme]:
                    hal_keywords.append(hal_keyword[1])
                
            prep_dict = {'title': pub.title,
                         'abstract_en': pub.abstract['en'],
                         'lang': pub.text_lang,
                         'hal_cat': pub.hal_cat,
                         'authors': pub.authors,
                         'author_names': get_author_list(pub.tei.decode('utf-8')),
                         'venue': pub.venue,
                         'pub_type': pub.pub_type,
                         'idhal': pub.idhal,
                         'file_url': pub.file_url,
                         'url': pub.url,
                         'keywords': ';'.join(hal_keywords),
                         'licence': pub.info['licence'],
                         'peer-reviewed': pub.info['peer-reviewed'],
                         'edition-dates': pub.info['edition_dates'],
                         'reason_selected': pub.info['reason']}
            if args.parallel:
                prep_dict['abstract_fr'] = pub.abstract['fr']

            # with licences that are recognised
            open_licences = ['Attribution - NonCommercial',
                             'Attribution',
                             'Attribution', 'Attribution - ShareAlike',
                             'Attribution - NonCommercial - ShareAlike',
                             'CC0 - Public Domain Dedication',
                             'Public Domain',
                             'Public Domain Mark',
                             'GNU Lesser General Public License v3.0 or later',
                             'GNU General Public License v3.0 or later',
                             'GNU General Public License v3.0 only',
                             'GNU General Public License v2.0 only',
                             'GNU Library General Public License v2 or later',
                             'Creative Commons Attribution Non Commercial 4.0',
                             'Open licence - etalab',
                             'Apache License 2.0']
            
            # use this to get only open licences
            if args.open_licences_only and prep_dict['licence'] not in open_licences:
                continue

            # only those after a certain year
            year = re.match('.*?(\d\d\d\d)', prep_dict["edition-dates"]['whenProduced'])
            if args.after_year is not None and year and int(year.group(1)) <= args.after_year:
                continue
            
            print(json.dumps(prep_dict))
