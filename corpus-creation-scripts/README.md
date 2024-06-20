# Corpus creation scripts

## Requirements

This has currently been tested with python 3.9.16.
```
conda create --name pymatos python=3
conda active pymatos
pip install -r requirements.txt
```

## NLP corpus

### Resources

*must be created/downloaded (i.e. not already in this repository)

Publications:

- *`../NLP-corpus/acl-publication-info.74k.parquet`: ACL publications from [ACL Anthology corpus](https://github.com/shauryr/ACL-anthology-corpus). Download from [here](https://drive.google.com/file/d/1CFCzNGlTls0H-Zcaem4Hg_ETj4ebhcDO/view?usp=sharing)
- *`../NLP-corpus/pubs-info.dill`: HAL computer science publications. Create by calling `python scripts/download_from_hal.py info pubs-info.dill`
- *`../NLP-corpus/pubs-cl.dill`: HAL cs.CL publications. Create by calling `python scripts/download_from_hal.py cs.CL pubs-cl.dill`

Keywords:

- `../NLP-corpus/nlp_keywords_dedupl.txt`: keywords for computational linguistics (i)
  manually extracted (and manually filtered) from HAL cs.CL
  publications and (ii) added manually. To get the entire (pre-filtered) keyword
  list from HAL publications: `python scripts/get_hal_cl_keywords.py pubs-cs-cl.dill`

Venues:

- `../NLP-corpus/nlp_venues.txt`: list of NLP venues, compiled from a list of ACL venues (using some heuristics to extract variants of the venue names), filtered manually with some extra venues added. To get the list of ACL venues and their variants: `python scripts/get_acl_venues.py > acl_venues_pp.txt`

### Create NLP corpus

The corpus is composed of English HAL articles associated with the "computer science" category that are either associated with an identified NLP venue or contains in the keywords, title or abstract an identified NLP-specific keyword. Only those articles with an English abstract and no French abstract are included.

```
python scripts/get_corpus.py ../NLP-corpus/pubs-info.dill ../NLP-corpus/nlp_venues.txt ../NLP-corpus/nlp_keywords_dedupl.txt > ../NLP-corpus/nlp_corpus.jsonl
```

## STEP corpus

### Resources
*must be created/downloaded (i.e. not already in this repository)

- `../STEP-corpus/pubs-sde.dill`: HAL "Environmental Sciences" publications. Create by calling `python scripts/download_from_hal-step.py sde pubs-sde.dill`
- `../STEP-corpus/pubs-sdu.dill`: HAL "Sciences of the Universe" publications. Create by calling `python scripts/download_from_hal-step.py sdu ../STEP-corpuspubs-sdu.dill`
- `../STEP-corpus/step-up-structures.list`: List of research structures to be used as a filter for the final corpus

### Create STEP corpus

The corpus is composed of English HAL articles associated with the sde/sdu categories that are associated with one of the research structures listed. Only those articles with an English abstract and no French abstract are included.

```
python scripts/get_corpus-step.py [--structures STRUCTURES] [--after_year AFTER_YEAR] [--open_licences_only] pubs
```

More specifically, here are some examples:

```
python scripts/get_corpus-step.py ../STEP-corpuspubs-sde.dill > ../STEP-corpussde-corpus.jsonl
python scripts/get_corpus-step.py ../STEP-corpuspubs-sdu.dill > ../STEP-corpussdu-corpus.jsonl
python scripts/get_corpus-step.py --structures ../STEP-corpusstep-up-structures.list ../STEP-corpuspubs-sde.dill > ../STEP-corpussde-corpus.step-up.jsonl
python scripts/get_corpus-step.py --structures ../STEP-corpusstep-up-structures.list ../STEP-corpuspubs-sdu.dill > ../STEP-corpussdu-corpus.step-up.jsonl
python scripts/get_corpus-step.py --structures ../STEP-corpusstep-up-structures.list ../STEP-corpuspubs-sde.dill --after_year 2019 > ../STEP-corpussde-corpus.step-up.2020-present.jsonl
python scripts/get_corpus-step.py --structures ../STEP-corpusstep-up-structures.list ../STEP-corpuspubs-sdu.dill --after_year 2019 > ../STEP-corpussdu-corpus.step-up.2020-present.jsonl
```

To merge two corpora (removing any duplicates):

```
python scripts/merged-jsonl.py json_file1 json_file2
```
