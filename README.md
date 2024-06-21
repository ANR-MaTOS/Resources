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
