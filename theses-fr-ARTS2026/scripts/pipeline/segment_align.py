import os, sys
from pathlib import Path
from tqdm import tqdm
import unicodedata
import pandas as pd

from trankit import Pipeline
from bertalign import Bertalign
import argparse

import time


# https://trankit.readthedocs.io/en/latest/pkgnames.html#pretrained-languages-their-code-names
LANG_DICT = {'en': 'english', 'fr': 'french', 'es': 'spanish', 'it': 'italian', 'pt':'portuguese', 'de': 'german' }

MAX_N_LANG = 8


def segment_texts(txt, pipeline):
    """
    segment documents to sentences with Trankit pipeline, assume that the texts are already normalized to NFC format 
    e.g. using unicodedata.normalize('NFC', doc_txt)
    """
    sent = [ s['text'] for s in pipeline.ssplit(txt)['sentences']]
    return sent


def get_segment_lang(txt, pipeline, lang1 = 'fr', lang2 = 'en'):
    """
    segmentations
    return '<sep>'.join(segments of lang1) + '\n\n' + '<sep>'.join(segments of lang2)
    """    
    txt1 = unicodedata.normalize('NFC', txt.loc[lang1])
    txt2 = unicodedata.normalize('NFC', txt.loc[lang2])
    
    seg1 = segment_texts(txt1, pipeline)
    seg2 = segment_texts(txt2, pipeline)
    res = '\n\n'.join( [ '<sep>'.join(seg1), '<sep>'.join(seg2) ])    
    return res


# to store scores and alignes
def get_bertalign(
    item,
    lang1 = 'fr',
    lang2 = 'en',
    skip = -0.001, 
    win= 10, 
    len_slack = 0.15,
):
    """
    get the alignments produced by bertalign
    """
    docid = item['id']
    txt = item['segment']

    seg_l1, seg_l2 = txt.split('\n\n') 
    
    seg_l1 = '\n'.join([ l.strip() for l in seg_l1.split('<sep>') if l.strip()])
    seg_l2 = '\n'.join([ l.strip() for l in seg_l2.split('<sep>') if l.strip()])
    
    aligner = Bertalign(
        seg_l1, seg_l2, is_split = True, src_lang = lang1, tgt_lang = lang2, 
        skip =skip, win= win, len_slack = len_slack
        )
    aligner.align_sents()

    sents1, sents2 = aligner.get_align_sents()

    res = {'id': docid, 'l1': sents1, 'l2': sents2 }
    res['has_empty_align'] = '' in sents1 or '' in sents2
    
    res |= aligner.get_align_score()

    res['segment'] = txt
    res['nb_sent'] = len(sents1)
    return res

    
def get_segment_align_df(
        input_df,
        to_store_path,
        pipeline,
        lang1 = 'fr',
        lang2='en',
        skip = -0.001, 
        win= 10, 
        len_slack = 0.15,
        is_segmented = False,
        ):
    """
    return res_df of fileds [ id, l1, l2, has_empty_align, columns of scores(bertalign score, length ratio, cos), segmentation of lang1\n\nlang2]
    """
    # extract potential abstract pairs for fr and en (to segment)
    # input_df = find_abstracts_to_segment(df_loaded, lang1, lang2)
    
    if not is_segmented:
        # segmentation
        seg_df = input_df.apply(lambda x : get_segment_lang(x, pipeline, lang1, lang2), axis = 1)
        seg_df = pd.concat( [input_df['id'], seg_df.rename('segment')], axis = 1)
    else:
        seg_df = input_df
    
    # align
    res_df = seg_df.apply(
        lambda x: get_bertalign(x, lang1, lang2, skip, win, len_slack), 
        axis = 1) 
    res_df = res_df.apply(pd.Series)
    
    res_df.to_parquet(to_store_path)
    return res_df


def get_to_store_path(to_store_dir, begin_id, step, i):
    return  f"{to_store_dir}/aligned{begin_id}-window{step}.chunk{i}-{i+1}.theses.fr.parquet"   
    
def gather_results(
    df_loaded, 
    store_dir,  
    step = 2000, 
    parallel_size = 8000,
):
    """
    # parallel_size is multiple of step
    # run segmentation and alignement, store the result for each 2k (the value of step) pairs of parallel abstracts 
    # detect whether all documents are aligned, if so combine them all
    """
    
    to_compute = False
    to_store_path = f"{store_dir}/aligned.theses.fr.parquet"  
    if os.path.exists(to_store_path):
        print(f'Alignments are already stored at {to_store_path}')
        return to_compute

    res_ls = []
    for b_id in range((len(df_loaded)//parallel_size)+1):    
        for i in range( parallel_size//step):
            pq_path = get_to_store_path(store_dir, b_id, step, i)
                        
            if os.path.exists(pq_path):
                df_chunk = pd.read_parquet(pq_path)
                res_ls.append(df_chunk)
            else:
                id0 = b_id*parallel_size + i*step
                id1 = b_id*parallel_size + (i+1)*step
                # print(id0, id1, os.path.exists(pq_path), (b_id+i)>0, id0 > len(df_loaded) )
                # print(pq_path)
                if (b_id+i)>0 and id0 > len(df_loaded):
                    print('All alignments are collected')
                    break
                else:
                    print('to compute:', len(df_loaded), b_id,  parallel_size, id0, id1)
                    to_compute = True
                    return to_compute 

    if not to_compute:
        print(f'Storing gathered alignments to {to_store_path} ')
        res_df = pd.concat(res_ls) 
        res_df.to_parquet(to_store_path)
    return to_compute


def get_alignments(
    df_loaded, 
    store_dir,  
    begin_id , 
    step = 2000, 
    parallel_size = 8000,
    lang1 = 'fr',
    lang2 = 'en',   
    skip = -0.001, 
    win= 10, 
    len_slack = 0.15,
    is_segmented = False
):
    """
    # parallel_size is multiple of step
    # run segmentation and alignement, store the result for each 2k (the value of step) pairs of parallel abstracts 
    # detect whether all documents are aligned, if so combine them all
    """
    
    to_compute = gather_results( df_loaded,  store_dir,  step = step,  parallel_size = parallel_size )
    if not to_compute:
        return
    print(f'Segment and align the portion {begin_id}')
    
    # init pipeline
    p_multi = Pipeline(LANG_DICT[lang1])
    p_multi.add(LANG_DICT[lang2])
    p_multi.set_auto(True)    

    Path(store_dir).mkdir(parents=True, exist_ok=True)
    
    max_chunk_id = parallel_size // step
    if begin_id == 'all':
        begin_id = 0
    
    for i in tqdm(range(0, max_chunk_id)):
        pq_path = get_to_store_path(store_dir, begin_id, step, i)
        if not os.path.exists(pq_path):    
            id0 = begin_id*parallel_size + i*step
            id1 = begin_id*parallel_size + (i+1)*step

            if id0 > len(df_loaded):
                print('All documents in the current portion are aligned')
                break
            else:
                print(id0, id1)
                begin_t = time.time()
                df_chunk = get_segment_align_df(
                    df_loaded[id0:id1], 
                    pq_path,
                    pipeline = p_multi,
                    lang1 = lang1,
                    lang2= lang2,
                    skip = skip, 
                    win= win, 
                    len_slack = len_slack,
                    is_segmented = is_segmented,
                )
                taken_t = time.time() -  begin_t
                print(f"segment and align {step} documents from the document {begin_id}x{parallel_size}+{i*step}), taken {taken_t} seconds")
              

if __name__ == "__main__":
    # if len(sys.argv) < 8 :
    #     print("Usage: python segment_align.py to_segment_align_pq_path lang1 lang2 store_dir portion_id step parallel_size")
    #     print("Notes: bertalign take lang1 as the source language, lang2 as target language; parallel_size is 0 if take the full input parquet, or it should be multiple of step, for example 8000 for step == 2000")
        
    #     sys.exit(-1)

    parser = argparse.ArgumentParser()
    parser.add_argument("--to_segment_align_pq_path", type=str, required=True, help="Path to input parquet file, with texts to segment")
    parser.add_argument("--store_dir", type=str, required=True, help="path to store the segmented sentences and the alignments")
    parser.add_argument("--l1", type=str, default='en', help="source language")
    parser.add_argument("--l2", type=str, default='fr', help="target language")
    parser.add_argument("--portion_id", type=int,  help="portion id when the job is launched with array")
    parser.add_argument("--step", type=int, default=4000, help="to store the results after processing how many number of input texts")
    parser.add_argument("--parallel_size", type=int, default= 80000, help="number of texts to process in one subjob when the slurm job is launched with array")

    args = parser.parse_args()
            
    # pq_path = "../local_data/THE/theses.fr.combined_lid.parquet"
    pq_path = args.to_segment_align_pq_path # sys.argv[1]

    lang1 = args.l1
    lang2 = args.l2

    store_dir  = args.store_dir
    step  = args.portion_id

    portion_id  = args.portion_id
    portion_step  = args.portion_id

    parallel_size = args.parallel_size
  
    
    # store_dir = sys.argv[4]
    # portion_id = int(sys.argv[5])
    # step = int(sys.argv[6])
    # parallel_size  = int(sys.argv[7])
    
    assert(parallel_size%step == 0)
    
    # id titleFR titleEN year discipline keywords abstract 
    to_segment_df_all = pd.read_parquet(pq_path)
    print(f'loaded{len(to_segment_df_all)} documents, to compute the portion {portion_id}')
                
    Path(store_dir ).mkdir(parents=True, exist_ok=True)
    get_alignments(
        to_segment_df_all,
        store_dir,  
        portion_id , 
        step = step, 
        parallel_size = parallel_size,
        lang1 = lang1,
        lang2 = lang2,   
        skip = -0.001, 
        win= 10, 
        len_slack = 0.15,
        is_segmented = True,
    )

