# The MERSENNE Parallel Articles

This repository releases the MERSENNE parallel article dataset evaluated in the following paper:

>Ziqian Peng, Rachel Bawden, and François Yvon. 2025. Self-Retrieval from Distant Contexts for Document-Level Machine Translation. In Proceedings of the Tenth Conference on Machine Translation, pages 220–240, Suzhou, China. Association for Computational Linguistics.

It contains 
- `MERSENNE`: plain text of 23 parallel articles, at sentence-level, paragraph-level and sentence-level. In `MERSENNE/auxiliary_files`, we mark sentence boundaries using the `<sep>` tag for the paragraph-level and article-level version, with length statistics in `MERSENNE/auxiliary_files/stat`.
- `resource_october_2024.tsv`: The meta data of MERSENNE articles, with URLs of raw texts.
- `MERSENNE_tmx`: tmx files of the 23 parallel articles, an example is displayed in the next section.
- `auxiliary_files`: equations and tables, section titles in English at level h2 and h3 with their sentence ID in the aligned articles.
- `scripts`: 
  - `scripts/get_merged_ted.py` generates the mergedTED test set used for evaluating extraction error rate and coverage rate in Tables 2 and 3 of the paper.
  - `scripts/data-scrawl-process-mersenne-WMT2025.ipynb`, applied to collect and process the parallel articles, and to prepare the test set in tmx and txt format after the alignment.

Other scripts for data construction, and for the self-RAMT framework will be provided soon.


## Example of tmx file & ressources of raw data

Here is an example of the tmx files for sentences aligned in a pair of FR--EN article. We only show a part of the aligned sentences for simplicity. 

In the meta data, we indicate the URL of the source article (url_src), the URL of the translation (url_tgt), authors, titles in both languages, volume, translator and the source language.

We provide both sentence-level and paragraph-level boundaries, with `tuid` indicating the sentence ID, and `<prop type="paragraph">1</prop>` indicating the paragraph ID.

<!-- - [ ] add scripts for tmx2dataset. -->

```
<?xml version='1.0' encoding='utf-8'?>
<tmx version="1.4b">
    <header creationtool="xml.etree.ElementTree" creationtoolversion="1.3.0" datatype="PlainText" segtype="sentence" adminlang="en" srclang="FR" tgtlang="EN" o-tmf="XML" creationdate="2024-11-15" creationid="MaTOS">
        <note>This is the sentence alignement file for MERSENNE-mersenne0. segId begin by 1, tuid = segId</note>
        <docid>mersenne0</docid>
        <url_src>https://comptes-rendus.academie-sciences.fr/geoscience/articles/fr/10.5802/crgeos.31/</url_src>
        <url_tgt>https://comptes-rendus.academie-sciences.fr/geoscience/articles/en/10.5802/crgeos.31/</url_tgt>
        <author>Etienne Ghys, Ghislain de Marsily</author>
        <title_en>Science is not Fuzzy...</title_en>
        <title_fr>La Science n’est pas Floue ...</title_fr>
        <volume>CRGEOS, 2020, Volume 352, Facing climate change, the range of possibilities</volume>
        <translator>Romain Dziegielinski</translator>
        <elem type="sourceLanguage">FR</elem>
        <elem type="targetLanguage">EN</elem>
    </header>
    <body>
        <tu tuid="1">
            <prop type="paragraph">1</prop>
            <tuv xml:lang="FR">
                <seg>La Science n’est pas Floue ...</seg>
            </tuv>
            <tuv xml:lang="EN">
                <seg>Science is not Fuzzy...</seg>
            </tuv>
        </tu>
        <tu tuid="2">
            <prop type="paragraph">2</prop>
            <tuv xml:lang="FR">
                <seg>Un article récent du journal Libération titrait «Les savants flous».</seg>
            </tuv>
            <tuv xml:lang="EN">
                <seg>A recent article in the newspaper Libération headlined « Les savants flous ».</seg>
            </tuv>
        </tu>
        <tu tuid="3">
            <prop type="paragraph">2</prop>
            <tuv xml:lang="FR">
                <seg>L’image de la science dans le public a été considérablement perturbée par l’épidémie de Covid.</seg>
            </tuv>
            <tuv xml:lang="EN">
                <seg>The public image of science has been considerably disrupted by the Covid epidemic.</seg>
            </tuv>
        </tu>
        ...
        ...
        <tu tuid="101">
            <prop type="paragraph">40</prop>
            <tuv xml:lang="FR">
                <seg>²https://www.academie-sciences.fr/fr/Colloques-conferences-et-debats/changement-climatique.html</seg>
            </tuv>
            <tuv xml:lang="EN">
                <seg>²https://www.academie-sciences.fr/fr/Colloques-conferences-et-debats/changement-climatique.html</seg>
            </tuv>
        </tu>
    </body>
</tmx>
```


## How to cite

This data is described in the following paper (Appendix A), published at WMT 2026: 

<!-- - [  ] todo, check whether we need to mention BUCC2026 paper here -->

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