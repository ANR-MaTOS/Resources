import sys
import pandas as pd
import unicodedata
import time 
import re
import argparse
# convert language code for the output of GlotLID models
from iso639 import Lang
# to speed up dataframe.apply
from pandarallel import pandarallel

import fasttext
lid_model = fasttext.load_model('/home/zpeng/scratch/MaTOS/resumeAllTHE/scripts/scripts/pipeline/lid_model/lid.176.ftz')


############
# empty data
def detect_empty_data(df_loaded):
    """detect collected abstracts that have neither an abstract nor keywords."""
    to_check_list = ['keywords_fr', 'keywords_en', 'abstract_1' ]
    
    k = to_check_list[0]
    tmp_df = df_loaded[df_loaded[k] != df_loaded[k]]
    
    for k in to_check_list[1:]:
        tmp_df = tmp_df[tmp_df[k] != tmp_df[k]]
    
    return tmp_df


def convert_langID(lang_code):
    """
    convert language code for the LID output, if using the GlotLID models https://github.com/cisnlp/GlotLID
    """
    try:
        iso3 = lang_code.split('_')[0]
        lg = Lang(iso3)
        # Return the 2-letter code (pt1)
        return lg.pt1 if lg.pt1 else iso3
    except:
        return lang_code

# lang id
def detect_lang(text, lid_model):
    """
    detect the language of the input text
    """
    max_len = 1000
    first, *others = text.splitlines()
    chunk = first[0 : min(max_len, len(first))]
    chunk = re.sub('\xa0 *', ' ', chunk)
    chunk = unicodedata.normalize('NFC', chunk).lower()
    predlang = lid_model.predict(chunk, threshold = 0.5)

    lang = 'UNKOWN'
    if len(predlang[0]): 
        lang = predlang[0][0].split("__label__")[1]
        lang = convert_langID(lang)
        if lang.startswith('zh'):
            lang = 'zh'
        
    return lang
    

def detect_lang_map(x): 
    """sub fonction for LID"""
    txt = x.values[0]
    # if len(txt) < 100, usually there is no content for the abstract, so we disregard it.
    # to consider if needed in your case that if len(txt) < 200, the abstract has only one or two sentences (e.g. 2024LYO10249).
    if txt is None or len(txt) < 100:
        return None
    return detect_lang(txt, lid_model)



def extract_abstract_pairs(item, lang1 = 'fr', lang2 = 'en', to_drop_list = ["abstract", "Résumé", "Résumé :",]):
    """
    in the entire corpus, take all EN--FR which may be at lang_2 and lang_3 etc.
    return res_dict = {'id': docid, lang1: abstract_lang1, lang2: abstract_lang2 }
    """    

    all_lang = [item[f'lid_lang_{i}'] for i in range(1, MAX_N_LANG)]
        
    res_dict = {'id': item['id'] }
    if lang1 in all_lang and lang2 in all_lang:
        for i in range(1, MAX_N_LANG):
            for la in [lang1, lang2]:
                if item[f'lid_lang_{i}'] == la:
                    txt = re.sub('\xa0 *', ' ', item[f'abstract_{i}'] )
                    res_dict[la] = unicodedata.normalize('NFC', txt )
                    continue
        # if the raw data of one side contains a copy of another side
        txt1 = res_dict.get(lang1)
        txt2 = res_dict.get(lang2)
        if txt1 in txt2:
            res_dict[lang2] = re.sub( txt1, '', txt2).strip()
        elif txt2 in txt1:
            res_dict[lang1] = re.sub( txt2, '', txt1).strip()
        else:
            if txt1[:200] in txt2 and txt2[:200] in txt1:
                res_dict['abstract_overlap'] = True
             
        # if the raw data begin with a certain words like Abstract
        for la in [lang1, lang2]:
            for dp in to_drop_list: 
                if dp.lower() == res_dict[la][:len(dp)].lower():
                    res_dict[la] = res_dict[la][len(dp):]          
                
                if dp.lower() == res_dict[la][-len(dp):].lower():
                    res_dict[la] = res_dict[la][:-len(dp)]          
            
    return res_dict


def find_abstracts_to_segment(df_loaded, lang1, lang2):
    """in the entire corpus, take all EN--FR which may be at lang_2 and lang_3 etc."""  
    # res = df_loaded[['lid_lang_1', 'lid_lang_2']].dropna().index 
    
    tmp_df = df_loaded[ ['id']+ [f'abstract_{i}' for i in range(1, MAX_N_LANG)] + [f'lid_lang_{i}' for i in range(1, MAX_N_LANG)]]
    to_segment_df = tmp_df.T.parallel_apply( lambda x: extract_abstract_pairs(x, lang1, lang2)).apply(pd.Series)
    to_segment_df = to_segment_df.loc[to_segment_df[[lang1, lang2]].dropna().index]
    
    return to_segment_df

def remove_overlaps(to_segment_df, overlap_path):
    """remove docid for which there is an English (i.e. French) abstract appears at the beginning of French (i.e. English) abstracts """
    overlap_df = to_segment_df[['id','abstract_overlap']].dropna()
    to_segment_df  = to_segment_df[~to_segment_df.index.isin(overlap_df.index)].drop('abstract_overlap', axis = 1)
    print(f'remove {len(overlap_df)} pairs of abstracts with overlaps of more than 200 characters at the beginings.')

    with open(overlap_path, 'wt') as f:
        f.write( '\n'.join(overlap_df.id.values))
    return to_segment_df
    

def get_lid_titles(title_pq_path):
    """LID for titles"""
    def detect_lang_map(x):    
        txt = x.values[0]
        if txt is None:
            return None
        return detect_lang(txt, lid_model)
            
    # title_pq_path = f"{STORE_DIR}/theses.fr.titles.with-cometkiwi.parquet"
    title_df = pd.read_parquet(title_pq_path)
    
    lid_fr_df =  title_df[['title_fr']].parallel_apply(detect_lang_map, axis = 1).rename('lid_fr')
    lid_en_df =  title_df[['title_en']].parallel_apply(detect_lang_map, axis = 1).rename('lid_en')
    title_df = pd.concat( [ title_df, lid_fr_df, lid_en_df], axis =1)
    # title_df.to_parquet(title_pq_path)
    return title_df




if __name__ == "__main__":
    # if len(sys.argv) < 7 :
    #     print("Usage: python preprocess.py pq_path lid_store_path trash_path lang1 lang2 seg_store_path overlap_path")
        
    #     sys.exit(-1)
    parser = argparse.ArgumentParser()
    parser.add_argument("--pq_path", type=str, required=True, help="Path to the collected raw data in parquet file, which is the output of scraping/run.sh")
    parser.add_argument("--lid_store_path", type=str,  help="Path to store lid results with the input data")
    parser.add_argument("--seg_store_path", type=str, help="Path to store the identified abstract to segment")
    parser.add_argument("--trash_path", type=str, help="Path to store docid of abstracts to drop")
    parser.add_argument("--lang1", type=str, default='en', help="first language to extract")
    parser.add_argument("--lang2", type=str, default='fr', help="second language to extract")
    parser.add_argument("--overlap_path", type=str,  help="Path to store docid of the potential EN (i.e. FR) abstracts which begin with its version in FR (i.e. EN).")
    args = parser.parse_args()
    
    pandarallel.initialize(progress_bar=True, nb_workers= 16) 

    pq_path = args.pq_path
    # pq_path = "../local_data/THE/theses.fr.combined.parquet"
    store_path = args.store_path #sys.argv[2]
     #"../local_data/THE/theses.fr.combined_lid.parquet"
    trash_path = args.trash_path
    # "../local_data/THE/trash0.theses.fr.combined.parquet"
    lang1 = args.lang1
    lang2 = args.lang2
    seg_path = args.seg_store_path
    overlap_path = args.overlap_path
    MAX_N_LANG = 8
    
    begin_t = time.time()
    # id titleFR titleEN year discipline keywords abstract 
    df_loaded = pd.read_parquet(pq_path)
    
    tmp_df = detect_empty_data(df_loaded)
    tmp_df.to_parquet(trash_path)
    print(f"remove {len(tmp_df)} documents without parallel abstracts or parallel keywords")
    
    df_loaded = df_loaded[~df_loaded.index.isin(tmp_df.index)]
    for la in range(1, MAX_N_LANG):    
        df_loaded[f'lid_lang_{la}'] = df_loaded[[f'abstract_{la}']].parallel_apply(detect_lang_map, axis = 1)
    df_loaded.to_parquet(store_path)
    print(f'Taken {time.time() - begin_t} seconds for LID')
    
    # df_loaded = pd.read_parquet(store_path)
    print(f'Extract bilingual abstracts for {lang1} and {lang2} ')
    to_segment_df = find_abstracts_to_segment(df_loaded, lang1, lang2)
    # check overlaps
    remove_overlaps(to_segment_df, overlap_path)
    
    # store
    to_segment_df.to_parquet(seg_path)
    print(f'Total time {time.time() - begin_t} seconds') # seconds
    

    
    
        