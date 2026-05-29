# ParaEPS 

This repository contains about 13k parallel abstracts and 29 parallel articles (fr / en) of scientific documents in the Earth and Planetary Sciences domain. 
The data resources, data collection and construction are presented in detail at 

> Ziqian Peng, Lichao Zhu, Rachel Bawden, Maud Bénard, Éric de la Clergerie, et al.. Parallel Corpora of Scholarly Documents for English-French Machine Translation. BUCC 2026 - 19th Workshop on Building and Using Comparable Corpora, LREC2026, May 2026, Palma de Mallorca, Spain. ⟨hal-05608444⟩

Here is the folder structure. It contains
- `tsv_raw` : tsv files with raw texts of parallel abstracts by collection, their docid, and other meta-data (authors, citations, etc.) if available.
For `CanMin` and `CJES`, we only release the docid and citations as the restricted copyright of these abstracts.
-  `txt` : plain texts of the test set, including four subsets of parallel abstracts (`txt/test`), and two subsets of parallel articles (`txt/test-long`). We preserve sentence-level and document-level boundaries. Using the `BSGF` subset as an example, `txt/test/BSGF/auxiliary_files/EPS_BSGF_doc_sep.en` contains one BSGF abstract at each line, with `<sep>` indicating the sentence boundary. In addition, we provide `txt/test/BSGF/auxiliary_files/EPS_BSGF_sent.idx`, an index file indicating the correspondence between documents and sentences in the document-level version.
-  `xml_collections`: it comprise
   - parallel abstracts in tmx format by collection, including abstracts from BSGF, CRAS, CRG, ISTEX and THE. 
`xml_collections/auxiliary_files` comprise the list of docid for training set, validation set, and test sets of paraEPS. Please check more details in our paper. 
To reproduce the training set and validation set, you are invited to extract, process and align the raw texts of `CanMin` and `CJES` as we cannot redistribute these documents. We will soon release the scripts for this reproduction.
    - parallel articles (`MERSENNE` and `STUDENT`) in tmx format and their resources. For `MERSENNE`, we preserve both sentence boundaries and paragrph boundaries. `xml_collections/test-long/MERSENNE/auxiliary_files` includes also the section titles and their seg_id in the sentence-aligned articles.



```
├── README.md
├── tsv_raw
│   ├── BSGF
│   ├── CJES_docid
│   ├── CRAS
│   ├── CRG
│   ├── CanMin_docid
│   ├── ISTEX
│   └── THE
├── txt
│   ├── test
│   └── test-long
├── xml2dataset.py
└── xml_collections
    ├── BSGF
    ├── CRAS
    ├── CRG
    ├── ISTEX
    ├── README.md
    ├── THE
    ├── auxiliary_files
    └── test-long
```

The scripts for data processing and dataset construction will be available soon.

<!-- ## Example of tmx file 

 Here is an example of the tmx files for sentences aligned in a document pairs. We only show the first two sentence pairs for simplicity. -->


- [ ] `<SEC0>` in section title files marks ... 
<!-- - [ ] add statistics  -->
- [ ] add an example of our data in tmx format

<!-- - `scripts` gives an example to extract parallel texts from the given xml files and to reconstruct the datasets. --> -->
<!-- 
- THE : 100 documents, 1295 phrases
- CRAS : 100 documents, 676 phrases
- CRG : 59 documents, 368 phrases
- BSGF : 132 documents, 1308 phrases
avec chacun une liste des doc_id `*_test.list.txt`. L'order de ces doc_id indique l'ordre des textes dans les jeux de test (sauf pour MERSENNE, l'ordre est l'ID des documents, i.e. mersenne0, mersenne1, ... ). -->