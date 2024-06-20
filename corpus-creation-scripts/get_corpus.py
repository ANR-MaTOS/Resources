#!/usr/bin/python
import argparse
import dill
import json
import re
import os
from get_acl_article import get_acl_by_title
from get_arxiv_article import get_arxiv_by_title, norm_title
import xml.etree.ElementTree as ET
parser = argparse.ArgumentParser()
parser.add_argument('pubs')
parser.add_argument('venues')
parser.add_argument('keywords')
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

# get venues
venues = []
search_venues = []
with open(args.venues) as fp:
    for line in fp:
        venues.append(line.strip())
        search_venues.append('|' + '|'.join(re.sub('([\'\-\.,;:\(\)\[\]]+)', r' \1 ', line.strip()).split()) + '|')

venues = minimal_list(venues)
        
# get keywords (lowercase, normalise)
keywords = []
search_keywords = []
norm_keywords = []
with open(args.keywords) as fp:
    for line in fp:
        orig_line = ' '.join(line.strip().lower().replace('’', "'").split()).strip()
        norm_line = re.sub('\W', '', orig_line).strip()
        search_line = '|' + '|'.join(re.sub('([\'\-\.,;:\(\)\[\]]+)', r' \1 ', orig_line).split()).strip() + '|'
        if orig_line not in keywords:
            keywords.append(orig_line)
        if norm_line not in norm_keywords:
            norm_keywords.append(norm_line)
        if search_line not in search_keywords:
            search_keywords.append(search_line)

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
    
    venue_matched = match_venue(pub.venue)
    if len(venue_matched) > 1:
        keep_pubs[hal_id] = pub
        reason_chosen = 'VENUE\t' + pub.title + '\t' + pub.venue + '\t' + pub.hal_cat + '\t' + venue_matched
    else:
        keywords = match_keyword(pub.info['keywords'])
        if len(keywords) > 1:
            keep_pubs[hal_id] = pub
            reason_chosen = 'KW\t' + pub.title + '\t' + pub.venue + '\t' + pub.hal_cat + '\t' + str(keywords)
        else:
            keywords_title = match_keyword_abstract(pub.title)
            if len(keywords_title) > 0:
                 keep_pubs[hal_id] = pub
                 reason_chosen = 'TITLE KW\t' + pub.title + '\t' + pub.venue + '\t' + pub.hal_cat + '\t' + str(keywords_abstract_en)
            else:
                keywords_abstract_en = match_keyword_abstract(pub.abstract.get('en', ''))
                if len(keywords_abstract_en) > 0:
                    keep_pubs[hal_id] = pub
                    reason_chosen = 'ABSTRACT (en) KW\t' + pub.title + '\t' + pub.venue + '\t' + pub.hal_cat + '\t' + str(keywords_abstract_en)
                else:
                    keywords_abstract_fr = match_keyword_abstract(pub.abstract.get('fr', ''))
                    if len(keywords_abstract_fr) > 0:
                        keep_pubs[hal_id] = pub
                        reason_chosen = 'ABSTRACT (fr) KW\t' + pub.title + '\t' + pub.venue + '\t' + pub.hal_cat + '\t' + str(keywords_abstract_fr)

    # if there is a reason to select the article, print it out
    if reason_chosen != '':
        # only those that
        #  (i) have an English abstract
        #  (ii) originally in English (i.e. not a translation)
        #  (iii) do not already have a French abstract
        if pub.abstract.get('en', '') != '' and pub.text_lang == 'English' and pub.abstract.get('fr', '') == '':
            # add reason for being selected
            pub.info['reason'] = {
                             'venue': venue_matched, 
                             'keyword': keywords[0] if len(keywords) > 0 else '',
                             'keyword_in_title': keywords_title[0] if len(keywords_title) > 0 else '',
                             'keyword_in_abstract_en': keywords_abstract_en[0] if len(keywords_abstract_en) > 0 else ''}

            # match with ACL or arxiv
            author_surnames = [x[1] for x in get_author_list(pub.tei.decode('utf-8'))]
            acl_match, arxiv_match = get_from_acl_or_arxiv(pub.title, author_surnames, pub.abstract['en'])

            # count number of each
            if acl_match[0] is not None:
                num_acl += 1
            if arxiv_match[0] is not None:
                num_arxiv += 1
            
            # dump as a jsonl line
            hal_keywords = []
            for scheme in pub.info['keywords']:
                for hal_keyword in pub.info['keywords'][scheme]:
                    hal_keywords.append(hal_keyword[1])
            acl_dict = None
            # make sure dict content of acl_dict does not contain any elements that are incompatible with json
            if acl_match[0] is not None:
                acl_dict = acl_match[0].as_dict().copy()
                #print(type(acl_dict))
                for element in ['xml_booktitle', 'xml_title', 'xml_abstract']:
                    if element in acl_dict:
                        acl_dict[element] = ET.tostring(acl_dict[element]).decode('utf-8')
                acl_dict['citation_html'] = acl_match[0].as_citation_html()
                acl_dict['author'] = [str(x[0]) for x in acl_dict.get('author', [])]
                #print(acl_dict)
                #for k, v in acl_dict.items():
                #    print(k, v, type(v))
                
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
                         'reason_selected': pub.info['reason'],
                         'acl_licence': None if acl_match[0] is None else acl_match[2],
                         'acl_dict': None if acl_match[0] is None else acl_dict,
                         'acl_abstract': None if acl_match[0] is None else acl_match[0].get_abstract(),
                         'acl_same_abstract': acl_match[1],
                         'arxiv_licence': None if arxiv_match[0] is None else acl_match[2],
                         'arxiv_dict': None if arxiv_match[0] is None else arxiv_match[0],
                         'arxiv_same_abstract': arxiv_match[1]}

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
            #authornames = "-".join(['.'.join(x) for x in  prep_dict['author_names']]) # to help debuggin
            
            # use this to get only open licences
            if prep_dict['licence'] in open_licences or prep_dict['acl_licence'] is not None:
                print(json.dumps(prep_dict))

            # only those after 2019
            #year = re.match('.*?(\d\d\d\d)', prep_dict["edition-dates"]['whenProduced'])
            #if year and int(year.group(1)) > 2019:
            #    print(json.dumps(prep_dict))

            # all
            #print(json.dumps(prep_dict))
