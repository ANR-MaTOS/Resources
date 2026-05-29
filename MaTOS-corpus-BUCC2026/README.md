# Parallel Corpora of Scholarly Documents for English-French Machine Translation


This repository release two corpora `ParaEPS` and `ParaNLP` introduced in the following paper, published at BUCC 2026:

> Ziqian Peng, Lichao Zhu, Rachel Bawden, Maud Bénard, Éric de la Clergerie, et al.. Parallel Corpora of Scholarly Documents for English-French Machine Translation. BUCC 2026 - 19th Workshop on Building and Using Comparable Corpora, LREC2026, May 2026, Palma de Mallorca, Spain. ⟨hal-05608444⟩

### ParaEPS
ParaEPS consists of around 14k parallel articles and 29 parallel articles in Earth and Planetary Sciences (EPS) domain. Detailed data resource are reported in Table 2 of our paper.
For parallel abstracts, we release the test sets, consisting of four subsets (BSGF, CRAS, CRG and THE).

For the training set and the validation set, as the status of redistributing abstracts from CanMin and CJES are restricted, we provide the docid of the abstracts and the citations of the corresponding publications in the folder [MaTOS-corpus-BUCC2026/paraEPS/tsv_raw]. We will soon release the scripts for data construction from html files for these abstracts.
Then, we release other abstracts by collection in tmx format, with the list of docid for trainining set, validation set and test set.
BSGF, CRAS and CRG articles are released under a permissive CC BY 4.0 license, and abstracts from theses.fr are considered as metadata so that not copyrighted.


The parallel articles from ParaEPS are organised as two subset:

- MERSENNE: 19 parallel articles published in Comptes Rendus Géoscience, which is part of the MERSENNE parallel articles in the folder [MERSENNE-articles-WMT2025]
- STUDENT: 10 parallel articles consisting of proof-readed human-translations or post-edition by master’ students from a specialised translation course at Université Paris Cité.


### ParaNLP
ParaNLP consists of around 3k parallel abstracts and 76 parallel articles in the NLP domain. 
The parallel abstracts are extracted from the metadata of published documents in [theses.fr](https://theses.fr) (THE), [ISTEX](https://www.istex.fr/)., and [revue TAL](https://www.atala.org/revuetal) (rTAL), which are therefore not copy-righted.

Regarding the parallel articles, we release 72 of them, with 36 EN--FR parallel article and 36 FR--EN articles, constructed using 36 comparable articles published in NLP domain. These parallel articles are refer to as NLPsilver in our paper. 
We cannot release 4 human translated articles due to the copyright issue.


## Script
The scripts for data collection, processing and construction will be relesed soon.

<!-- ### Structures
- preprocess and postprocess
- segment-align 

### Installation & how to run -->



<!-- ### Ethic statement and licences -->


## How to cite
```text
@inproceedings{peng:hal-05608444,
  TITLE = {{Parallel Corpora of Scholarly Documents for English-French Machine Translation}},
  AUTHOR = {Peng, Ziqian and Zhu, Lichao and Bawden, Rachel and B{\'e}nard, Maud and de la Clergerie, {\'E}ric and Huguin, Mathilde and K{\"u}bler, Natalie and Lerner, Paul and Mestivier, Alexandra and Yvon, Fran{\c c}ois},
  URL = {https://hal.science/hal-05608444},
  BOOKTITLE = {{Proceedings of the 19th Workshop on Building and Using Comparable Corpora (BUCC)}},
  ADDRESS = {Palma de Mallorca, Spain},
  ORGANIZATION = {{LREC2026}},
  YEAR = {2026},
  MONTH = May,
  KEYWORDS = {Large language models ; Long-context Modelling ; Scientific Documents ; Parallel Corpus ; Machine Translation},
  PDF = {https://hal.science/hal-05608444v1/file/BUCC_MaTOS_corpus_camera_ready.pdf},
  HAL_ID = {hal-05608444},
  HAL_VERSION = {v1},
}
```