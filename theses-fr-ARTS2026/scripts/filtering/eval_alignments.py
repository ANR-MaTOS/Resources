from comet import download_model, load_from_checkpoint
import pandas as pd
import sys
import os
import time
from pathlib import Path
import unicodedata
import argparse


def get_qe_inputs(aligned_df):
    """prepare input for cometkiwi"""
    to_eval_list = []
    for doc in aligned_df.iterrows():
        assert(len(doc)==2)

        sent_pair = [ { 'src': txt1, 'mt' : txt2 } for txt1, txt2 in zip(doc[1]['l1'], doc[1]['l2']) ]
        
        to_eval_list +=sent_pair
    return to_eval_list


def eval_comet_qe(model, df_loaded, store_fpath, batch_size = 16):
    """evaluate and store cometkiwi scores"""
    assert(store_fpath.endswith('parquet'))
    
    if os.path.exists(store_fpath):
        print(f'comet-qe scores with alignments are already stored at {store_fpath}')
        return

    qe_inputs = get_qe_inputs(df_loaded)

    model_output = model.predict(qe_inputs, batch_size=batch_size, gpus=1)
    qe_store_fpath = f"{store_fpath}.cometkiwi.tsv"
    pd.DataFrame( model_output.scores).to_csv(qe_store_fpath, sep = '\t')

    # read scores
    comet_qe_df = pd.read_csv(qe_store_fpath, sep = '\t', index_col = 0)
    doc_bound = [0] + df_loaded['nb_sent'].cumsum().values.tolist()

    segment_sums = [
        comet_qe_df.iloc[doc_bound[i]:doc_bound[i+1]].mean().iloc[0]
        for i in range(len(df_loaded))
    ]
    
    doc_comet_qe_df = pd.DataFrame(segment_sums).rename(columns = {0:'cometkiwi22'}).set_index(df_loaded.index)
    res_df = pd.concat( [df_loaded, doc_comet_qe_df ], axis = 1)

    res_df.to_parquet(store_fpath)

    
def get_store_fpath(store_dir, portion_id):
    res = f"{store_dir}/aligned{portion_id}.theses.fr.with-cometkiwi.parquet"
    return res

    
def gather_results( store_dir, store_fname, nb_portions ):
    """collect and combine results"""
    assert(store_fname.endswith('parquet'))
    to_compute = False
    to_store_fpath = f"{store_dir}/{store_fname}"
    if os.path.exists(to_store_fpath):
        print(f'Alignments with scores are are already stored at {to_store_fpath}')
        return to_compute
        
    res_ls = []
    for i in range(nb_portions):
        pq_path = get_store_fpath(store_dir, i)
        print(pq_path)
        print(os.path.exists(pq_path))
        if os.path.exists(pq_path):
            tmp_df = pd.read_parquet(pq_path)
            res_ls.append(tmp_df) 
        else:
            to_compute = True
            return to_compute
    
    print(f'Storing gathered scores and alignemnts to {to_store_fpath} ')
    res_df = pd.concat(res_ls) 
    res_df.to_parquet(to_store_fpath)
    
    return to_compute

def eval_alignments_comet_qe(
    df_loaded, 
    store_dir, 
    store_fname, 
    portion_id, 
    batch_size = 16,
    parallel_size = 8000,
):
    """evaluate the quality estimation scores in cometkiwi, gather results if the evaluation of all portions are done."""
    nb_portions = (len(df_loaded)//parallel_size) + int(len(df_loaded)%parallel_size !=0)
    to_compute = gather_results( store_dir, store_fname, nb_portions )
    if not to_compute:
        return

    # Load the cometkiwi model:
    model_path = download_model("Unbabel/wmt22-cometkiwi-da")
    model = load_from_checkpoint(model_path)

    store_fpath = get_store_fpath(store_dir, portion_id)
    id0 = portion_id*parallel_size
    id1 = (portion_id+1)*parallel_size
    
    eval_comet_qe(
        model, 
        df_loaded[id0 : id1], 
        store_fpath = store_fpath , 
        batch_size = batch_size
    )


def _normalise(item):
    """NFC normalisation"""
    item['title_fr'] = unicodedata.normalize('NFC', item['title_fr'])
    item['title_en'] = unicodedata.normalize('NFC', item['title_en'])
    return item


def eval_qe_titles( title_df, store_fpath, batch_size = 16):
    """
    title_df: dataframe with columns 'id', 'title_fr', 'title_en'
    to evaluate the cometkiwi score between titles in fr and in en
    """
    assert(store_fpath.endswith('parquet'))
    if os.path.exists(store_fpath):
        print(f'comet-qe scores with titles are already stored at {store_fpath}')
        return

    # load model
    model_path = download_model("Unbabel/wmt22-cometkiwi-da")
    model = load_from_checkpoint(model_path)

    # load and normalise titles
    title_df = title_df.apply(_normalise, axis = 1 )
    qe_inputs = [ { 'src': doc['title_fr'], 'mt' : doc['title_en'] }  for _, doc in title_df.iterrows() ]

    # compute cometkiwi
    model_output = model.predict(qe_inputs, batch_size=batch_size, gpus=1)
    qe_df = pd.DataFrame( model_output.scores)

    # store
    qe_store_fpath = f"{store_fpath}.cometkiwi.tsv"
    qe_df.to_csv(qe_store_fpath, sep = '\t')
    res_df = pd.concat(
        [title_df.reset_index()[['id', 'title_fr', 'title_en']],  qe_df.rename(columns = {0: 'cometkiwi22'})], 
        axis = 1
    )
    res_df.to_parquet(store_fpath)
    print(res_df.describe().T)


if __name__ == "__main__":
    # if len(sys.argv) not in [4, 7] :
    #     print("Usage: python eval_alignments.py align_pq_path store_dir store_fname portion_id batch_size parallel_size")
    
    #     print("Usage: python eval_alignments.py title_pq_path store_fpath batch_size")
    #     sys.exit(-1)
    
    parser = argparse.ArgumentParser()

    subparsers = parser.add_subparsers(dest="mode", required=True)

    # Mode 1
    parser_doc = subparsers.add_parser("doc")
    parser_doc.add_argument("--align_pq_path", type=str, required=True, help="Path to input parquet file, with texts to evaluate")
    parser_doc.add_argument("--store_dir", type=str, required=True, help="Path to store the cometkiwi scores along with the input texts")
    parser_doc.add_argument("--store_fname", type=str, required=True, help="File name to store the results under the given store_dir")
    parser_doc.add_argument("--portion_id", type=int, default=0, help="portion id when the job is launched with array")
    parser_doc.add_argument("--bsz", type=int, default=16, help="batch size for the cometkiwi model")
    parser_doc.add_argument("--parallel_size", type=int, default= 80000, help="number of texts to process in one subjob when the slurm job is launched with array")

    # Mode 2
    parser_title = subparsers.add_parser("title")
    parser_title.add_argument("title_pq_path", type=str, required=True, help="Path to input parquet file, with titles to evaluate")
    parser_title.add_argument("store_fpath", type=str, required=True, help="Path to store the cometkiwi scores along with the input texts")
    parser_title.add_argument("--bsz", type=int, default=16, help="batch size for the cometkiwi model")

    args = parser.parse_args()


    if args.mode == "doc":
        print("evaluate sentence alignments")
        align_pq_path = args.align_pq_path

        df_loaded_all = pd.read_parquet(align_pq_path)
        print(f'loaded{len(df_loaded_all)} documents, to compute the portion {args.portion_id}')
        
        eval_alignments_comet_qe(
            df_loaded_all, 
            args.store_dir, 
            args.store_fname, 
            args.portion_id, 
            batch_size = args.bsz,
            parallel_size = args.parallel_size,
        )
    elif args.mode == "title":    
        print("evaluate sentence alignments for titles")
        title_pq_path = args.title_pq_path
    
        df_loaded_all = pd.read_parquet(title_pq_path)
        title_df = df_loaded_all[['id', 'title_fr', 'title_en']].dropna()
        print(f'loaded {len(title_df)} pairs of titles')
        eval_qe_titles( 
            title_df, 
            args.store_fpath, 
            batch_size = args.bsz
        )
    else:
        print("Unknown mode. Choose mode between doc and title to run evaluation on abstracts (doc) or titles (title)")
    

