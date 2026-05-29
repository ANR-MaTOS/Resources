# Resources for the MaTOS (Machine Translation for Open Science) ANR project

This repository contains publicly released resources created in the context of the [MaTOS ANR project](https://anr-matos.github.io).

## Content
  - [1. Post-editing data for the NLP domain (EAMT 2024)](#1-post-editing-data-for-the-nlp-domain-eamt-2024)
  - [2. Parallel corpus of EN-FR abstracts in the NLP domain (TALN 2024)](#2-parallel-corpus-of-en-fr-abstracts-in-the-nlp-domain-taln-2024)
  - [3. Parallel(EN-FR) Articles published in Compte-rendus de l'Académie des sciences, (GEOS and CHIM), prepared by the Mersenne Center (WMT 2025)](#3-mersenne-en-fr-parallel-articles-in-geos-and-chim-wmt-2025)
  - [4. Parallel Abstracts and Parallel Articles in NLP and EPS (BUCC 2026)](#4-parallel-abstracts-and-parallel-articles-in-nlp-and-eps-bucc-2026)
    - [ParaEPS](#paraeps)
    - [ParaNLP](#paranlp)
  - [5. Parallel abstracts from all French PhD Thesis Library (theses.fr) (ARTS2026)](#5-parallel-abstracts-from-all-thesesfr-arts2026)

----------------

<a id="eamt2024-postedition"></a>
## 1. Post-editing data for the NLP domain (EAMT 2024)
The data in [NLP-corpus] is described in the following paper, published at the EAMT Conference 2024: 

>Rachel Bawden, Ziqian Peng, Maud Bénard, Éric Villemonte de la Clergerie, Raphaël Esamotunu, Mathilde Huguin, Natalie Kübler, Alexandra Mestivier, Mona Michelot, Laurent Romary, Lichao Zhu and François Yvon. 2024. Translate your Own: a Post-Edition Experiment in the NLP domain. In *Proceedings of the 25th Annual Conference of the European Association for Machine Translation*. Sheffield, UK. European Association for Machine Translation. To appear. 

Please cite the following:
```
@inproceedings{bawden-etal-2024-translate,
    title = "{Translate your Own: a Post-Edition Experiment in the NLP domain}",
    author = "Rachel Bawden and Ziqian Peng and Maud Bénard and Éric Villemonte de la Clergerie and Raphaël Esamotunu and Mathilde Huguin and Natalie Kübler and Alexandra Mestivier and Mona Michelot and Laurent Romary and Lichao Zhu and François Yvon",
    booktitle = "Proceedings of the 25th Annual Conference of the European Association for Machine Translation",
    month = jun,
    year = "2024",
    address = "Sheffield, UK",
    publisher = "European Association for Machine Translation"
}
```

Resources available:
- Corpus:
    - The initial NLP corpus extracted from HAL (English article titles and abstracts): `NLP-corpus/nlp_corpus.jsonl`, a subsection of recent articles (2020 onwards): `nlp_corpus.2020-present.jsonl`, and translations by the 3 MT systems: `nlp_corpus.2020-present-{systran,deepl,etranslation}.jsonl`
    - NLP keywords and list of venues for the filtering of HAL articles: `nlp_keywords_dedupl.txt` and `nlp_venues.txt`
    - Creation scripts found in `corpus-creation-scripts/`. See `corpus-creation-scripts/README.md` for details.
    - Postedits from the NLP community and translators: `NLP-corpus/postedits-NLP-community.csv` and `NLP-corpus/postedits-translators.csv`, and the user metadata: `NLP-corpus/postedits-user-metadata.csv`
- Post-editing interface:
  - The post-editing interface is still live and you can still contribute here: [https://postedition.anr-matos.fr](https://postedition.anr-matos.fr).
  - Code for the post-editing interface: `postediting-interface/tal/`

<a id="taln2024-NLP-abstracts"></a>
## 2. Parallel corpus of EN-FR abstracts in the NLP domain (TALN 2024)

The data in [NLP-abstracts-TALN2024] correspond to approximately 2,000 abstracts of PhD Theses and journal abstracts in the NLP domain in French associated with their English translation (or vice-versa). These texts have been downloaded from public sources, manually curated, and aligned at the sentence level. It is redistributed under the terms of the [CC-BY Licence](https://creativecommons.org/licenses/by/4.0/).

This data has been used in the following paper, published at the 2024 edition of the TALN conference: 

>Ziqian Peng, Rachel Bawden, and François Yvon. 2024. À propos des difficultés de traduire automatiquement de longs documents. In Actes de la 31ème Conférence sur le Traitement Automatique des Langues Naturelles, volume 1 : articles longs et prises de position, pages 2–21, Toulouse, France. ATALA and AFPC.

If you would like to use this data, please use the following citation:

```
@inproceedings{peng-etal-2024-propos,
    title = "{{\`A}} propos des difficult{\'e}s de traduire automatiquement de longs documents",
    author = "Peng, Ziqian  and
      Bawden, Rachel  and
      Yvon, Fran{\c{c}}ois",
    editor = "Balaguer, Mathieu  and
      Bendahman, Nihed  and
      Ho-dac, Lydia-Mai  and
      Mauclair, Julie  and
      G Moreno, Jose  and
      Pinquier, Julien",
    booktitle = "Actes de la 31{\`e}me Conf{\'e}rence sur le Traitement Automatique des Langues Naturelles, volume 1 : articles longs et prises de position",
    month = "7",
    year = "2024",
    address = "Toulouse, France",
    publisher = "ATALA and AFPC",
    url = "https://aclanthology.org/2024.jeptalnrecital-taln.1",
    pages = "2--21",
    abstract = "Les nouvelles architectures de traduction automatique sont capables de traiter des segments longs et de surpasser la traduction de phrases isol{\'e}es, laissant entrevoir la possibilit{\'e} de traduire des documents complets. Pour y parvenir, il est n{\'e}cessaire de surmonter un certain nombre de difficult{\'e}s li{\'e}es {\`a} la longueur des documents {\`a} traduire. Dans cette {\'e}tude, nous discutons de la traduction des documents sous l{'}angle de l{'}{\'e}valuation, en essayant de r{\'e}pondre {\`a} une question simple: comment mesurer s{'}il existe une d{\'e}gradation des performances de traduction avec la longueur des documents ? Nos analyses, qui {\'e}valuent des syst{\`e}mes encodeur-d{\'e}codeur et un grand mod{\`e}le de langue {\`a} l{'}aune de plusieurs m{\'e}triques sur une t{\^a}che de traduction de documents scientifiques sugg{\`e}rent que traduire les documents longs d{'}un bloc reste un probl{\`e}me difficile.",
    language = "French",
}
```

<a id="wmt2025-mersenne-articles"></a>
## 3. MERSENNE EN-FR Parallel Articles in GEOS and CHIM (WMT 2025)

The data in [MERSENNE-articles-WMT2025] correspond to 23 EN-FR parallel articles. 19 of them are published in Comptes Rendus Géoscience and 4 of them are published in Comptes Rendus Chimie, in partnership with the Mersenne Centre for Open Scientific Publishing based on a diamond open access policy. These journal articles and their translations distributed under a CC-BY 4.0 licence. The translations are provided by the Mersenne Center and publically accessible online.

We manually curated the raw textes, segmented and aligned them at the sentence level. We also preserve the paragraph boundary of the aligned sentences, and identified the section titles and their position in the article.

This data has been used in the following paper, published at WMT 2025, with more details about the data construction in Appendix A:

> Ziqian Peng, Rachel Bawden, and François Yvon. 2025. Self-Retrieval from Distant Contexts for Document-Level Machine Translation. In Proceedings of the Tenth Conference on Machine Translation, pages 220–240, Suzhou, China. Association for Computational Linguistics.

If you would like to use this data, please use the following citation:

```
@inproceedings{peng-etal-2025-self,
    title = "Self-Retrieval from Distant Contexts for Document-Level Machine Translation",
    author = "Peng, Ziqian  and
      Bawden, Rachel  and
      Yvon, Fran{\c{c}}ois",
    editor = "Haddow, Barry  and
      Kocmi, Tom  and
      Koehn, Philipp  and
      Monz, Christof",
    booktitle = "Proceedings of the Tenth Conference on Machine Translation",
    month = nov,
    year = "2025",
    address = "Suzhou, China",
    publisher = "Association for Computational Linguistics",
    url = "https://aclanthology.org/2025.wmt-1.13/",
    doi = "10.18653/v1/2025.wmt-1.13",
    pages = "220--240",
    ISBN = "979-8-89176-341-8",
    abstract = "Document-level machine translation is a challenging task, as it requires modeling both short-range and long-range dependencies to maintain the coherence and cohesion of the generated translation. However, these dependencies are sparse, and most context-augmented translation systems resort to two equally unsatisfactory options: either to include maximally long contexts, hoping that the useful dependencies are not lost in the noise; or to use limited local contexts, at the risk of missing relevant information. In this work, we study a self-retrieval-augmented machine translation framework (Self-RAMT), aimed at informing translation decisions with informative local and global contexts dynamically extracted from the source and target texts. We examine the effectiveness of this method using three large language models, considering three criteria for context selection. We carry out experiments on TED talks as well as parallel scientific articles, considering three translation directions. Our results show that integrating distant contexts with Self-RAMT improves translation quality as measured by reference-based scores and consistency metrics."
}
```

<a id="bucc2026-paraNLP-paraEPS"></a>
## 4. Parallel Abstracts and Parallel Articles in NLP and EPS (BUCC 2026)


The data and scripts in [MaTOS-corpus-BUCC2026] correspond to two corpora `ParaEPS` and `ParaNLP` introduced in the following paper, published at BUCC 2026:

> Ziqian Peng, Lichao Zhu, Rachel Bawden, Maud Bénard, Éric de la Clergerie, et al.. Parallel Corpora of Scholarly Documents for English-French Machine Translation. BUCC 2026 - 19th Workshop on Building and Using Comparable Corpora, LREC2026, May 2026, Palma de Mallorca, Spain. ⟨hal-05608444⟩

### ParaEPS
ParaEPS consists of around 14k parallel articles and 29 parallel articles in Earth and Planetary Sciences (EPS) domain. Detailed data resource are reported in Table 2 of our paper.
For parallel abstracts, we release the test sets, consisting of four subsets.

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

<!-- omit in toc -->
### Citations

```
@inproceedings{peng-etal-2026-parallel,
  TITLE = {{Parallel Corpora of Scholarly Documents for English-French Machine Translation}},
  AUTHOR = {Peng, Ziqian and Zhu, Lichao and Bawden, Rachel and Bénard, Maud and de la Clergerie, Éric and Huguin, Mathilde and Kübler, Natalie and Lerner, Paul and Mestivier, Alexandra and Yvon, François},
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

<a id="arts2026-these-abstracts"></a>
## 5. Parallel abstracts from all theses.fr (ARTS2026)

The folder [theses-fr-ARTS2026] will soon release a parallel corpus of PhD thesis abstracts(294k), titles(333k), and keywords(~400k) from theses.fr, with the scripts for data collection, processing and construction. This work will be presented at ARTS @ CORIA-TALN 2026.

