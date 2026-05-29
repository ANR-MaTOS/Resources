# ParaNLP

This repository contains about 3k parallel abstracts and 72 parallel articles (fr / en) of scientific documents in the NLP domain. 
The data resources, data collection and construction are presented in detail at 

> Ziqian Peng, Lichao Zhu, Rachel Bawden, Maud Bénard, Éric de la Clergerie, et al.. Parallel Corpora of Scholarly Documents for English-French Machine Translation. BUCC 2026 - 19th Workshop on Building and Using Comparable Corpora, LREC2026, May 2026, Palma de Mallorca, Spain. ⟨hal-05608444⟩

Here is the folder structure. It contains

-  `txt` : plain texts of the test set, including four subsets of parallel abstracts (`txt/test`) and parallel articles (`txt/test-long`)
<!-- -  , and two subsets of parallel articles (`txt/test-long`). We preserve sentence-level and document-level boundaries. Using the `BSGF` subset as an example, `txt/test/BSGF/auxiliary_files/EPS_BSGF_doc_sep.en` contains one BSGF abstract at each line, with `<sep>` indicating the sentence boundary. In addition, we provide `txt/test/BSGF/auxiliary_files/EPS_BSGF_sent.idx`, an index file indicating the correspondence between documents and sentences in the document-level version. -->
-  `xml_collections`: it comprise
   - parallel abstracts in tmx format by collection, 
   - parallel articles (`NLPsilver` in EN--FR and FR--EN) in tmx format with meta data such as titles and urls.

```
├── README.md
├── txt
│   ├── dev
│   │   ├── dev.en
│   │   └── dev.fr
│   ├── long-test
│   │   └── NLPsilver-bucc
│   ├── testset
│   │   ├── THE
│   │   └── rTAL
│   └── train
│       ├── train.en
│       └── train.fr
├── xml-collections
│   ├── ISTEX
│   │   ├── ISTEX_tmx.zip
│   │   └── auxiliary_files
│   ├── THE
│   │   ├── THE_tmx.zip
│   │   └── auxiliary_files
│   ├── long-test
│   │   └── NLPsilver
│   └── rTAL
│       ├── auxiliary_files
│       └── rTAL_tmx.zip
└── xml2dataset.py
```

<!-- 
- [ ] add statistics -->
- [ ] add an example of our data in tmx format

<!-- We indicate the DOCID in the meta data. Using this DOCID, people can find the original source for each abstract from **THE** in [theses.fr](https://theses.fr/), and for all abstracts in **rTAL** later than 2006 in https://aclanthology.org/venues/tal/. Regarding the abstracts of **rTAL** published before 2006, only their abstracts are open-source as the meta-data. -->