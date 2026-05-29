# Merged TEDtalks
# - each document contains the first n sentences of a EN--FR TED talk (try n = 10, 20, 30, check first bm25?) 
# - we randomly take two documents as $X^1$  and $X^2$, all the pairs of ($X^1$, $X^2$) are unique
# - merge them 
# - [ ] store them separately
# - merge each pair of docs (in total 66 pairs when n = 12 ) (first try once, then repeat 10 times)
# - compute scores
# - compute error rate for the last sentence

import re, os
import pandas as pd

from pathlib import Path
import warnings
import unicodedata

import matplotlib.pyplot as plt
import numpy as np
import json

from metrics.eval_utils import get_stat


############################################################
DATA_ROOT_PATH = "MaTOS/data"

# raw
def get_ted_doc_path(lang, src_lang, tgt_lang):
    fpath = f'{DATA_ROOT_PATH}/TAL/corpora/dataset/IWSLT2017/{src_lang}-{tgt_lang}/TED_doc/TED_doc_sep.{lang}'
    return fpath


def get_store_dir(n, sample_id):
    return f"{DATA_ROOT_PATH}/TAL/retrieval_mt/merged_doc/TED{n}/sample{sample_id}"

def get_store_path( store_dir, lang,  with_sep = True ):
    tmp = '_sep' if with_sep else ''
    res = f"{store_dir}/merged_doc{tmp}.{lang}"
    return res

def get_merged_doc_idx_path(store_dir):
    return f'{store_dir}/merged_doc_idx.json'


############################################################

def read_tsv(fpath):
    return pd.read_csv(fpath, sep = '\t', index_col = 0)

def get_file_lines(fpath):
    res = [l.strip() for l in open(fpath ,'rt').read().split('\n') if l.strip()]
    return res


def get_talk_chunks(src_path, tgt_path, n ):
    # get talks
    src_txt = get_file_lines(src_path)
    src_talk_dict = {}
    for i, d in enumerate(src_txt):
        sents = [l.strip() for l in d.split('<sep>')][:n]
        assert(len(sents) == n)
        src_talk_dict[i] = sents
    
    tgt_txt = get_file_lines(tgt_path)
    assert(len(src_txt) == len(tgt_txt))
    tgt_talk_dict = {}
    for i, d in enumerate(tgt_txt):
        sents = [l.strip() for l in d.split('<sep>')][:n]
        assert(len(sents) == n)
        tgt_talk_dict[i] = sents
        
    return src_talk_dict, tgt_talk_dict


def get_merged_doc_idx_single(n:int):
    doc_len = 2*n
    idx_list = np.zeros(doc_len, dtype=int )
    
    rng = np.random.default_rng()
    idx_d2 = rng.choice(range(doc_len), n , replace=False)
    assert(len(set(idx_d2)) == n )
    idx_list[idx_d2] = 1
    return idx_d2, idx_list

############################################################
def make_merged_doc(src_path, tgt_path, n, sample_id, src_lang, tgt_lang):
    # get talks
    src_talk_dict, tgt_talk_dict = get_talk_chunks(src_path, tgt_path, n )
    
    merged_doc_idx_list = []
    
    src_merged_doc_list = []
    tgt_merged_doc_list = []
    src_merged_doc_sep_list = []
    tgt_merged_doc_sep_list = []
    
    for d1 in range(1, len(src_talk_dict)):
        for d2 in range(d1):  
            src_merged_doc, tgt_merged_doc, idx_list = merge_doc_v1(
                src_talk_dict[d1].copy(), src_talk_dict[d2].copy(), tgt_talk_dict[d1].copy(), tgt_talk_dict[d2].copy(), n
            )
            merged_doc_idx_list.append( idx_list  )
    
            src_merged_doc_list.append( ' '.join(src_merged_doc))
            src_merged_doc_sep_list.append( '<sep> '.join(src_merged_doc) )
    
            tgt_merged_doc_list.append( ' '.join(tgt_merged_doc))
            tgt_merged_doc_sep_list.append( '<sep> '.join(tgt_merged_doc) )
                
    
    # store
    store_dir = get_store_dir(n, sample_id)
    Path( store_dir ).mkdir(parents=True, exist_ok=True)
    
    src_store_path = get_store_path( store_dir,  src_lang,  with_sep = True )
    tgt_store_path = get_store_path( store_dir, tgt_lang,  with_sep = True )
    idx_store_path = get_merged_doc_idx_path(store_dir)
    
    with open(idx_store_path, "wt") as f:
        json.dump(merged_doc_idx_list, f)
            
    with open(src_store_path, 'wt') as f:
        f.write('\n'.join(src_merged_doc_sep_list))
    
    with open(tgt_store_path, 'wt') as f:
        f.write('\n'.join(tgt_merged_doc_sep_list))

def main(): 
    src_lang = 'en'
    tgt_lang = 'fr'
    
    src_path =  get_ted_doc_path(src_lang, src_lang, tgt_lang) 
    tgt_path = get_ted_doc_path(tgt_lang, src_lang, tgt_lang)
    
    for n in [10, 20, 30]:
        for sample_id in range(100):
            make_merged_doc(src_path, tgt_path, n, sample_id, src_lang, tgt_lang)

if __name__ == "__main__":
    main()
    
