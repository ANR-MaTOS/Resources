import torch.nn.functional as F
import torch
import pandas as pd
import unicodedata
import time 
import os
from scipy.optimize import linear_sum_assignment
from pathlib import Path
import argparse

# embeddings
from sentence_transformers import SentenceTransformer

# LID
import fasttext
LID_MODEL_PATH = '/home/zpeng/scratch/MaTOS/resumeAllTHE/scripts/pipeline/lid_model/lid.176.ftz'



def get_key_df(df_loaded):
    """extract keywords to align"""
    key_df = df_loaded[['id', 'keywords_fr', 'keywords_en']].dropna()
    year_df = df_loaded[['year']][df_loaded.index.isin(key_df.index)]
    assert(len( year_df.dropna()) == len(year_df))

    key_df = pd.concat([key_df, year_df.astype(int)], axis = 1)
    return key_df


def detect_lang_keywords(txt, lid_model):
    """
    detect the language of the input text
    """
    predlang = lid_model.predict(txt, threshold = 0.2)
    lang = 'UNKOWN'
    prob_lid = -1
    if len(predlang[0]): 
        prob_lid = predlang[1][0]
        lang = predlang[0][0].split("__label__")[1]
        if lang.startswith('zh'):
            lang = 'zh'
        
    return lang, prob_lid

# lang id
def lid_keywords(item, lid_model):
    """
    detect the language of the input text
    """
        
    keywords_fr = unicodedata.normalize('NFC', item['keywords_fr'])
    keywords_en = unicodedata.normalize('NFC', item['keywords_en'])
    # if len(item['keywords_fr'].split('\n')) > 1 or  len(item['keywords_en'].split('\n')) > 1 :
    #     print(item['id'])
    #     print(keywords_fr)
    #     print(keywords_en)
        
    keywords_fr = [ l.strip() for l in keywords_fr.split('///') if l.strip() ]
    keywords_en = [ l.strip() for l in keywords_en.split('///') if l.strip() ]
    
    to_lid_fr = ' '.join(keywords_fr).lower()
    to_lid_en = ' '.join(keywords_en).lower()

    lid_lang_fr, prob0 = detect_lang_keywords(to_lid_fr, lid_model)
    lid_lang_en, prob1 = detect_lang_keywords(to_lid_en, lid_model)

    res_dict = {}
    res_dict = {
        'id': item['id'],
        'keywords_fr': '///'.join(keywords_fr),
        'keywords_en': '///'.join(keywords_en),
        'lid_keywords_fr': lid_lang_fr,
        'lid_keywords_en': lid_lang_en,
        'prob_lid_keywords_fr': prob0,
        'prob_lid_keywords_en': prob1,
        'year': item['year'],
    }
    
    return pd.Series(res_dict)

def run_lid_keywords( key_df, lid_store_fpath, lid_model):
    lid_key_df = key_df.apply(lambda x: lid_keywords(x, lid_model), axis = 1)
    # "../local_data/THE1/resumeAllTHE_lid176/theses.fr.keywords_bilingue_nfc_lid.parquet"
    lid_key_df.to_parquet(lid_store_fpath)
    return lid_key_df


def align_keywords(item, encoder_model, add_year = False, thred = 0.5):
    """compute cosine similarity between keywords"""
    keywords_fr = unicodedata.normalize('NFC', item['keywords_fr'])
    keywords_en = unicodedata.normalize('NFC', item['keywords_en'])
    keywords_fr = [ l.strip() for l in keywords_fr.split('///') if l.strip() ]
    keywords_en = [ l.strip() for l in keywords_en.split('///') if l.strip() ]

    # compute cosine similarity
    uncased_keywords_fr = [l.lower() for l in keywords_fr ]
    uncased_keywords_en = [l.lower() for l in keywords_en ]
    
    sent_vecs0 = encoder_model.encode(uncased_keywords_fr)
    sent_vecs1 = encoder_model.encode(uncased_keywords_en)
    # Expand A → (m, 1, dim)
    # Expand B → (1, n, dim)
    A = torch.Tensor(sent_vecs0).unsqueeze(1)
    B = torch.Tensor(sent_vecs1).unsqueeze(0)
        
    cos_sim = F.cosine_similarity(A, B, dim = -1)
    # Use of the Jonker–Volgenant algorithm to find the optimal assignment
    fr_idx, en_idx = linear_sum_assignment(-cos_sim)

    match_fr = [keywords_fr[i] for i in fr_idx]
    match_en = [keywords_en[i] for i in en_idx]
    scores = cos_sim[fr_idx, en_idx].tolist()

    match_fr = [v for i, v in enumerate(match_fr) if scores[i] > thred]
    match_en = [v for i, v in enumerate(match_en) if scores[i] > thred]
    scores = [v for v in scores if v > thred]

    res_dict = {
        'Name': [item.name]*len(match_fr), 
        'id': [item['id']]*len(match_fr),
        'en': match_en, 
        'fr': match_fr, 
        'cos-uncased': scores, 
    }

    if add_year:
        res_dict['year'] = [item['year']]*len(res_dict['id'])
    return pd.Series(res_dict)
        
    
def run_align_keywords(encoder_model, key_df, to_store_fpath, thred = 0.5):
    """compute cosine similarity between keywords"""
    begin_t = time.time()

    key_align_df = key_df.apply(lambda x: align_keywords(x, encoder_model, add_year=True, thred = thred), axis = 1)
    res_df = key_align_df.explode(["Name", "id", "en", "fr","cos-uncased"])
    res_df.to_parquet(to_store_fpath)

    taken_t = time.time() - begin_t
    print(f'Taken {taken_t} s in total for {len(key_df)} items, {round(taken_t/len(key_align_df), 2)} s for each')


if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("--pq_path", type=str, required=True, help="Path to input parquet file, which is the output of preprocess.py, or lid_store_path which stores the output of run_lid_keywords")
    parser.add_argument("--lid_store_path", type=str, required=True, help="path to store the LID results ")
    parser.add_argument("--store_path", type=str, required=True, help="path to store the aligned keywords and their cosine similarities")
    parser.add_argument("--align_thred", type=float, default=0.5, help="thredshold of cosine similarity to match a pair of keywords")

    args = parser.parse_args()
        
    # pq_path = "../local_data/THE/theses.fr.combined_lid.parquet"
    pq_path = args.pq_path
    store_fpath = args.store_path
    Path( os.path.dirname(store_fpath) ).mkdir(parents=True, exist_ok=True)
    # store_fpath = f"{store_dir}/theses.fr.en-fr-keywords.parquet"
    # lid_store_fpath ="{store_dir}/theses.fr.keywords_bilingue_nfc_lid.parquet"
    lid_store_fpath = args.lid_store_path
    align_thred = args.align_thred

    if pq_path  != lid_store_fpath:
        Path( os.path.dirname(lid_store_fpath) ).mkdir(parents=True, exist_ok=True)
        # id titleFR titleEN year discipline keywords abstract 
        df_loaded_all = pd.read_parquet(pq_path)

        # id keywords_fr keywords_en year
        key_df = get_key_df(df_loaded_all)
        lid_model = fasttext.load_model(LID_MODEL_PATH)
        lid_key_df = run_lid_keywords( key_df, lid_store_fpath, lid_model)
    else:
        lid_key_df = pd.read_parquet(pq_path)
    
    print(f'To align keywords from {len(lid_key_df)} documents')
    model_name = "LaBSE"
    encoder_model = SentenceTransformer(model_name)
    run_align_keywords(encoder_model, lid_key_df, store_fpath, thred = align_thred)

    