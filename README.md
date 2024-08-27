# Resources for the MaTOS (Machine Translation for Open Science) ANR project

This repository contains publicly released resources created in the context of the [MaTOS ANR project](https://anr-matos.github.io).

## 1. Post-editing data for the NLP domain

This data is described in the following paper, published at the EAMT conference 2024: 

Rachel Bawden, Ziqian Peng, Maud Bénard, Éric Villemonte de la Clergerie, Raphaël Esamotunu, Mathilde Huguin, Natalie Kübler, Alexandra Mestivier, Mona Michelot, Laurent Romary, Lichao Zhu and François Yvon. 2024. Translate your Own: a Post-Edition Experiment in the NLP domain. In *Proceedings of the 25th Annual Conference of the European Association for Machine Translation*. Sheffield, UK. European Association for Machine Translation. To appear. 

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

## 2. Parallel corpus of EN-FR abstracts in the NLP domain

This data is described in the following paper, published at the TALN conference 2024: 

Ziqian Peng, Rachel Bawden, and François Yvon. 2024. À propos des difficultés de traduire automatiquement de longs documents. In Actes de la 31ème Conférence sur le Traitement Automatique des Langues Naturelles, volume 1 : articles longs et prises de position, pages 2–21, Toulouse, France. ATALA and AFPC.

Please cite the following:

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

**Resources available** are the test sets **THE** and **rTAL**, the validation set in **TAL**, 
and we will release our complete training set soon.
The corpus is in the folder `NLP-abstract`, including

- `rTAL_abstracts_TALN2024`
  - `xml` : containing a folder for each document pair, with the aligned sentences for the current document in an xml file
  - `drop.lst` : a plain text file that lists at each line the DOCID of documents to discard among the xml files in `xml` folder
  - `rTAL_doc.idx` : an tsv file indicating the position of each document in `rTAL`
  - `txt_test` : the test set in plain text used in our experiments, it consists of 
    - the test set at document level without sentence boundaries (e.g. `rTAL_doc.fr`), 
    - the test set at document level with sentence boundaries in the sub-folder `doc_with_sep` , 
    - the test set at sentence level in the sub-folder `sents`, 
    with `rTAL_sent.idx` an index file indicating the correspondance of each sentence with the document-level version.
    For example, `D0.3` represents the third sentence in the first document of `rTAL` test set.

- `THE_abstracts_TALN2024`
  - the same as `rTAL` for the test set `THE` except the index file in tsv
  - `test.lst` indicating the DOCID of documents in `THE`, with the same order as the positions of these documents.
  - `txt_dev` contains the document-level validation set used to train our document-level models. We also release the version with sentence boundaries in the sub-folder `doc_with_sep`
  - `dev.lst` indicating the DOCID of documents in the validation set, with the same order as the positions of these documents.
  
**Example of xml file & ressources of raw data**

Here is an example of the xml files for sentences aligned in a document pairs. We only show the first two sentence pairs for simplicity. 

We indicate the DOCID in the meta data. Using this DOCID, people can find the original source for each abstract from **THE** in [theses.fr](https://theses.fr/), and for all abstracts in **rTAL** later than 2006 in https://aclanthology.org/venues/tal/. Regarding the abstracts of **rTAL** published before 2006, only their abstracts are open-source as the meta-data.

```xml
<?xml version='1.0' encoding='utf-8'?>
<tmx version="1.4b">
    <header creationtool="xml.etree.ElementTree" creationtoolversion="1.3.0" datatype="PlainText" segtype="sentence" adminlang="en-us" srclang="FR" o-tmf="XML" creationdate="2023-04-28" creationid="MaTOS">
        <note>This is the sentence alignement file for THE-theses.fr-2016ISAT0016. segId begin by 1, tuid = segId</note>
        <docid>2016ISAT0016</docid>
        <elem type="sourceLanguage">FR</elem>
        <elem type="targetLanguage">EN</elem>
    </header>
    <body>
        <tu tuid="1">
            <tuv xml:lang="FR">
                <seg>Dans cette thèse, nous proposons une méthodologie basée sur les modèles pour gérer la complexité de la conception des systèmes autonomiques cognitifs intégrant des objets connectés.</seg>
            </tuv>
            <tuv xml:lang="EN">
                <seg>In this thesis, we propose a collaborative model driven methodology for designing Autonomic Cognitive IoT systems to deal with IoT design complexity.</seg>
            </tuv>
        </tu>
        <tu tuid="2">
            <tuv xml:lang="FR">
                <seg>Cette méthodologie englobe un ensemble de patrons de conception dont nous avons défini pour modéliser la coordination dynamique des processus autonomiques pour gérer l’évolution des besoins du système, et pour enrichir les systèmes avec des propriétés cognitives qui permettent de comprendre les données et de générer des nouvelles connaissances.</seg>
            </tuv>
            <tuv xml:lang="EN">
                <seg>We defined within this methodology a set of autonomic cognitive design patterns that aim at (1) delineating the dynamic coordination of the autonomic processes to deal with the system's context changeability and requirements evolution at run-time, and (2) adding cognitive abilities to IoT systems to understand big data and generate new insights.</seg>
            </tuv>
        </tu>
    </body>
</tmx>

```





