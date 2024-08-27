This repository contains about 2000 abstracts (fr / en) of scientific documents in the Natural Language Processing domain. It has been collected from the following sources:
- Abstracts of PhD theses from the [these.fr](https://these.fr), selected based on keywords. These are located in the subdirectory THE_abstracts.
- Abstracts of articles published in the [TAL journal](https://www.atala.org/revuetal). These are located in the subdirectory rTAL_abstracts. 

Each repository contains the complete set of abstracts in XLIFF format, in xml the subdirectory; as well as a text version of the abstracts used for training, developing, or testing our models. The detailed content is as follows: 

- `rTAL_abstracts`
  - `xml` : containing a folder for each document pair, with the aligned sentences for the current document in an xml file
  - `drop.lst`: a plain text file that lists at each line the DOCID of documents to discard among the xml files in `xml` folder
  - `rTAL_doc.idx`: a tsv file indicating the index of each document in `rTAL`
  - `txt_test`: the test set in plain text used in our experiments, it consists of 
    - the document-level test set without sentence boundaries (e.g. `rTAL_doc.fr`), 
    - the document-level test set with sentence boundaries in the sub-folder `doc_with_sep`, 
    - the sentence level test set in the sub-folder `sents`, 
    with `rTAL_sent.idx` an index file indicating the correspondence between documents and sentences in the document-level version.
    For example, `D0.3` represents the third sentence in the first document of `rTAL` test set.

- `THE_abstracts`
  - The content is similar to `rTAL` for the test set `THE`
  - `test.lst` contains the DOCID of documents in `THE`, with the same order as the positions of these documents.
  - `txt_dev` contains the document-level validation set used to train our document-level models. We also release the version with sentence boundaries in the sub-folder `doc_with_sep`
  - `dev.lst` contains the DOCID of documents in the validation set.

**The Resources currently available** are the test sets **THE** and **rTAL**, the validation set in **TAL**. The complete training set will also be published set soon.
  
## Example of xml file & ressources of raw data ##

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

This data is described in the following paper, published at the 2024 edition of the TALN conference: 

Ziqian Peng, Rachel Bawden, and François Yvon. 2024. À propos des difficultés de traduire automatiquement de longs documents. In Actes de la 31ème Conférence sur le Traitement Automatique des Langues Naturelles, volume 1 : articles longs et prises de position, pages 2–21, Toulouse, France. ATALA and AFPC.
https://aclanthology.org/2024.jeptalnrecital-taln.1/





