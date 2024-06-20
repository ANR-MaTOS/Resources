#!/usr/bin/python
from bs4 import BeautifulSoup as bs
import requests
import urllib.request as urllib
import urllib.parse as parse
import os, sys
import ssl
import re
import json
import xmltodict
import lxml.etree as etree
import dill
import argparse
ssl._create_default_https_context = ssl._create_unverified_context
sys.setrecursionlimit(100)

langcodes = ['en', 'fr'] #, 'pt', 'es', 'pl', 'ru', 'ro', 'sl', 'de', 'it', 'bg', 'tr', 'ja', 'sq', 'wo', 'ar', '0', 'ko', 'cs']

class Author():
    def __init__(self, author_xml):
        self.lastname = author_xml.find('surname')
        self.firstname = author_xml.find('forename')
        #self.idhal_str = author_xml.find('idno', {'type': 'idhal', 'notation': 'string'}).text
        #self.idhal_num = author_xml.find('idno', {'type': 'idhal', 'notation': 'numeric'}).text
        self.idhal_num = author_xml.find('idno', {'type': 'halauthorid'}).text
        self.affiliations = None
        #if author_xml.findAll('affiliation'):
        #    self.affiliations = [x['ref'] for x in author_xml.findAll('affiliation')] 

class Publication():
    def __init__(self, tei, url, idhal):
        self.tei = tei
        self.url = url
        self.title = ''
        self.abstract = {}
        self.authors = []
        self.idhal = idhal
        self.info = {}
        self.get_fields()

    def get_fields(self):
        tei_tree = bs(self.tei, "xml")
        
        # meta info: lang, category and pub type
        meta_info = tei_tree.find('biblFull').find('profileDesc')
        self.text_lang = meta_info.find('language').text
        self.hal_cat = meta_info.find('classCode', {'scheme': 'halDomain'}).text
        self.pub_type = meta_info.find('classCode', {'scheme': 'halTypology'}).text
        self.info['keywords'] = {}
        if meta_info.find('keywords'):
            for keyword_block in meta_info.findAll('keywords'):
                scheme = keyword_block['scheme']
                if scheme not in self.info['keywords']:
                    self.info['keywords'][scheme] = []
                for keyword in keyword_block.findAll('term'):
                    lang = keyword['xml:lang']
                    self.info['keywords'][scheme].append((lang, keyword.text))
        self.info['abstract'] = {} #'en': None, 'fr': None, 'pt': None}
        for ab in meta_info.findAll('abstract'):
            lang = ab['xml:lang']
            self.abstract[lang] = ab.text.strip()

        

        # title and authors
        pub_info = tei_tree.find('biblFull').find('titleStmt')
        if pub_info.find('title'):
            self.title = pub_info.find('title').text
        for author in pub_info.findAll('author', {'role': re.compile("(aut|crp|co_last_author|co_first_author)")}):
            #self.authors.append(Author(author))
            self.authors.append(author.find('idno', {'type': 'halauthorid'}).text)

        # publication info
        pub_info2 = tei_tree.find('biblFull').find('publicationStmt')
        self.info['idhal'] = pub_info2.find('idno', {'type:' 'halId'})
        self.info['uri'] = pub_info2.find('idno', {'type:' 'halUri'})
        self.info['venue'] = pub_info2.find('idno', {'type': 'halref'})
        if self.info['venue'] is not None:
            self.info['venue'] = self.info['venue'].text
        #self.info['date_pub_hal'] = pub_info2.find('date')['when']
        self.info['licence'] = None
        if pub_info2.find('licence'):
            self.info['licence'] = pub_info2.find('licence').text

        # venue
        venue_info = tei_tree.find('monogr')
        #self.pub_date = venue_info.find('date', {'type': 'datePub'}).text
        self.venue = None
        if venue_info.find('meeting'): # conferences
            if venue_info.find('meeting').find('title'):
                self.venue = venue_info.find('meeting').find('title').text
            else:
                self.venue = 'Proceedings'
        elif venue_info.find('title'): # books
            self.venue = venue_info.find('title').text
        elif tei_tree.find('idno', {'type': 'arxiv'}): # arxiv papers
            self.venue = 'arxiv: ' + tei_tree.find('idno', {'type': 'arxiv'}).text
        elif tei_tree.find('notesStmt').find('note', {'type': 'report'}): # reports
            self.venue = tei_tree.find('notesStmt').find('note', {'type': 'report'}).text
        elif 'Reports' in self.pub_type:
            self.venue = 'Unspecified'
        elif 'Other publications' in self.pub_type:
            self.venue = 'Unspecified'
        elif venue_info.find('biblScope'): # book series
            self.venue = venue_info.find('biblScope').text
        elif 'Preprint' in self.pub_type: # preprints
            self.venue = 'Unspecified'
        elif venue_info.find('authority'):
            self.venue = 'Thesis: ' + venue_info.find('authority').text
        elif self.pub_type in ['Videos', 'Image']:
            self.venue = 'Unspecified'
        elif self.pub_type == 'Software':
            self.venue = 'Unspecified'
        elif self.pub_type == 'Books':
            if venue_info.find('publisher'):
                self.venue = venue_info.find('publisher').text
            else:
                self.venue = 'Unspecified'
        else:
            self.venue = 'Unspecified'
        assert self.venue
            
        notes_info = tei_tree.find('notesStmt')
        #self.info['audience'] = tei_tree.find('note', {'type': 'audience'}).text
        #self.info['popularisation'] = tei_tree.find('note', {'type': 'popular'}).text
        self.info['peer-reviewed'] = tei_tree.find('note', {'type': 'peer'})
        if self.info['peer-reviewed']:
            self.info['peer-reviewed'] = self.info['peer-reviewed'].text

        ed_info = tei_tree.find('editionStmt')
        self.file_url = ed_info.find('ref', {'type': 'file'})
        if self.file_url:
             self.file_url =  self.file_url['target']
        self.info['edition_dates'] = {}
        for edition in ed_info.findAll('edition', {'type': 'current'}):
            for attr in ['whenSubmitted', 'whenModified', 'whenReleased', 'whenProduced']:
                if edition.find('date', {'type': 'whenProduced'}):
                    self.info['edition_dates'][attr] = edition.find('date', {'type': 'whenProduced'}).text

    def tsv_format(self):
        list_infos = [self.idhal, escape(self.title.strip()), ";".join(self.authors),
                      escape(self.venue.strip()), self.text_lang,
                      self.info['edition_dates']['whenProduced'],
                      self.pub_type, self.hal_cat.strip()]
        # add abstracts for each possible language
        for lang in self.abstract:
            if lang not in langcodes:
                os.sys.stderr.write(lang + '\n')
        #assert all(lang in langcodes for lang in self.abstract)
        for lang in langcodes:
            if lang in self.abstract:
                list_infos.append(self.abstract[lang])
            else:
                list_infos.append('')
        list_infos.extend([self.url, self.file_url])
        return list_infos

def json_format(pub):
    list_infos = [pub.idhal, escape(pub.title.strip()), ";".join(pub.authors),
                      escape(pub.venue.strip()), pub.text_lang,
                      pub.info['edition_dates']['whenProduced'],
                      pub.pub_type, pub.hal_cat.strip(),
                      pub.abstract.get('en', ''), pub.abstract.get('fr', '')]
    headers = ['idhal', 'title', 'authors', 'venue', 'lang', 'date', 'pubtype', 'cat', 'abstract_en', 'abstract_fr']
    jsonl = {}
    for h, header in enumerate(headers):
        jsonl[header] = list_infos[h]
    return jsonl

def escape(text):
    for charbefore, charafter in [('\n', r'\\n'), ('\t', r'\\t')]:
        text = text.replace(charbefore, charafter)
    return text

def get_web_page(address):
    '''
    Return the content of a webpage, handling common errors.
    '''
    def append_log(message):
        os.sys.stderr.write(message+'\n')
    try:
        user_agent = 'Mozilla/4.0 (compatible; MSIE 5.5; Windows NT)'
        headers = { 'User-Agent' : user_agent }
        request = urllib.Request(address, None, headers)
        response = urllib.urlopen(request, timeout=30)
        try:
            return response.read()
        finally:
            response.close()
    except urllib.URLError as e:
        append_log('URL Error: ' + str(e.reason) + ': ' + address)
    except Exception as e:
        append_log('Unknown Error: ' + str(e) + address)

ids_written = []
def get_list_pubs(link, id2pub = {}, output_format='json', cache_file=None):
    '''
    Get the publications present in the specified link.
    '''
    # get publications from online
    os.sys.stderr.write('Getting online publications\n')
    print(link)
    downloaded = json.loads(get_web_page(link + "&wt=" + output_format).decode("utf-8"))
    docs = downloaded["response"]["docs"]

    for doc in docs:
        docid = doc['docid']
        url = doc['uri_s']
        os.sys.stderr.write(url + '\n')
        if docid not in id2pub:
            tei = get_web_page(url + "/tei")
            if tei is None:
                continue
            try:
                id2pub[docid] = Publication(tei, url, docid)
            except AttributeError:
                continue
        if docid not in ids_written:
            #print('\t'.join([str(x) for x in id2pub[docid].tsv_format()]))
            print(json.dumps(json_format(id2pub[docid])))
            ids_written.append(docid)
            os.sys.stderr.write('Length of ids_written: ' + str(len(ids_written)) + '\n')
        else:
            os.sys.stderr.write('repeated entry\n')
    return id2pub, len(docs), "&cursorMark=" + downloaded['nextCursorMark']

parser = argparse.ArgumentParser()
parser.add_argument('HAL_category', choices=('info', 'cs.CL', 'sde', 'sdu'))
parser.add_argument('cache_output_file', help='.dill format')
args = parser.parse_args()

cache = args.cache_output_file
if os.path.exists(cache):
    id2pub = dill.load(open(cache, 'rb'))
else:
    id2pub ={}

# go through domains and keywords

keyword=args.HAL_category
page_start = 1
finished = False
cursor_mark = '&cursorMark=*'
while not finished:

    len_before=len(id2pub)

    # unfortunately, this filter on the lab structure does not seem to do anything at all... (need to process this down the line)
    #labs = "&labStructName_t=*" + parse.quote("géosciences") +"~+OR+" + parse.quote("géophysique") + "~+OR+" + parse.quote("géoscience") + "~+OR+" + parse.quote("géophysiques")

    if keyword == 'sdu':
        domain="level0_domain_s:sdu"
    else:
        domain="domain_t:" + keyword
    
    # go through page per page (250 results at a time)
    link = "https://api.archives-ouvertes.fr/search/?q=" + domain + "&rows=500&sort=docid%20asc" + cursor_mark
    id2pub, num_downloaded, cursor_mark = get_list_pubs(link, id2pub=id2pub)
    page_start += 1
    if num_downloaded == 0:
        finished = True
    os.sys.stderr.write('Newly downloaded: ' + str(num_downloaded) + '\n')
    os.sys.stderr.write('Total number in cache: ' + str(len(id2pub)) + '\n')
    if len_before != len(id2pub):
        dill.dump(id2pub, open(cache, 'wb'))
    else:
        os.sys.stderr.write('No new ids added\n')
