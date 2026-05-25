import pandas as pd
import numpy as np

#############################
# transform the document-level data row (where each row corresponds to an abstract) to sentence level (where each row correspond to a sentence)

DATA_DIR = 'DIV/corpora/raw/resumeAllTHE'
align_path = f'{DATA_DIR}/aligned.theses.fr.with-cometkiwi.parquet'
align_loaded_df = pd.read_parquet(align_path)



doc2sent_align_df = align_loaded_df.explode(['l1', 'l2', 'bertalign', 'length_ratio', 'cos']).reset_index().drop(['level_0', 'cometkiwi22'], axis = 1)



comet_list = []
for tmp_id in range(4):
    score_fpath = f"{DATA_DIR}/cometkiwi22/aligned{tmp_id}.theses.fr.with-cometkiwi.parquet.cometkiwi.tsv"
    tmp_comet_list = pd.read_csv(score_fpath, sep = '\t', index_col = 0).T.values[0]
    comet_list.append(tmp_comet_list)




sent_id_list = []
for _, item in align_loaded_df.iterrows():
    sent_id_list += list(range(item['nb_sent']))

sent_info_dict = {
    'sent_id': sent_id_list,
    'cometkiwi22': np.concatenate(comet_list)
}



sent_info_df = pd.DataFrame(sent_info_dict)



sent_align_df = pd.concat( [doc2sent_align_df, sent_info_df ], axis = 1)

sent_align_path = f'{DATA_DIR}/aligned-by-sents.theses.fr.with-cometkiwi.parquet'
# sent_align_df.to_parquet( sent_align_path )
sent_align_df