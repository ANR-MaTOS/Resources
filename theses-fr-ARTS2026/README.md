# resumeAllTHE: Collect & Align abstracts from [theses.fr](https://theses.fr/)

This is the repository that contains the scripts and any other materials to collect all abstracts from theses.fr, within the [MaTOS](https://anr-matos.github.io/) projects.

The aligned abstracts and keywords are in [to-add-the-link-of-resource] [add-readme-with-stat-with-the-resource]


## Codebase organisation
This section briefly describes the codebase in the `scripts` folder and explains how to run it.

### scraping
more details in scripts/scraping/README.md

### pipeline
- **preprocess**
```
sbatch scripts/pipeline/run_0_preprocess.sh
```
- LID
- detect empty content, overlaps of English content in French abstracts (and vice versa)
- collect abstract pairs to segment


- **segmentation & alignment** (abstracts and keywords)

For abstracts, we apply Trankit for segmentation and Bertalign for alignment (see instructions for **Installation** in the next section)
```
sbatch --array=0-3 scripts/pipeline/run_1_segment_align.sh
```
For keywords, we compute the cosine similarity according to the LaBSE embeddings

```
sbatch scripts/pipeline/run_1_keywords.sh
```

### filtering
- evaluate (abstracts and titles)
```
sbatch --array=0-3 scripts/filtering/run_2_eval.sh
```

## Installation (Trankit & Bertalign)


This section presents the installation instruction for both trankit and bertalign, to run the scripts in `pipeline`, 

### 1. Trankit (for segmentation)
1. create environment then install trankit
  
  with numpy==1.26.4, adapters==1.1.1, transformers<4.50,

  pytorch is installed with pip to avoid mismatch between recent version of MKL/OpenMP (2024/2025) and previous pytorch versions

```
conda create --name resumeTHE python=3.10
conda activate resumeTHE
pip install "numpy<2.0"
pip install adapters==1.1.1 transformers==4.48.3
pip install torch==2.0.1 torchvision==0.15.2 torchaudio==2.0.2 --index-url https://download.pytorch.org/whl/cu118

python -m pip install trankit
```

2. to address (the issue of trankit server to download models)[https://github.com/nlp-uoregon/trankit/issues/88], replace the url in base_utils.py:

```
vim /home/zpeng/miniconda3/envs/resumeTHE/lib/python3.10/site-packages/trankit/utils/base_utils.py
```
as

```
def download(cache_dir, language, saved_model_version, embedding_name):  # put a try-catch here
    lang_dir = os.path.join(cache_dir, embedding_name, language)
    save_fpath = os.path.join(lang_dir, '{}.zip'.format(language))

    if not os.path.exists(os.path.join(lang_dir, '{}.downloaded'.format(language))):
        # url = "http://nlp.uoregon.edu/download/trankit/{}/{}/{}.zip".format(saved_model_version, embedding_name,
        url = "https://huggingface.co/uonlp/trankit/resolve/main/models/{}/{}/{}.zip".format(saved_model_version, embedding_name,language)
        print(url)
```
save the file with `!wq`.


###  2. bertalign (for alignment)

- clone bertalign, the forked version of ANR-MaTOS, install it in development mode (from source)
```
git clone https://github.com/ANR-MaTOS/bertalign.git
cd bertalign
python -m pip install -r requirements.txt
pip install -e .
```

- specify the model path in `bertalign/models.py` for `model` and `lid_model`

for the embedding model, you can set `model_name = "LaBSE"` or  `model_name = ` a specific location

for lid_model applied for the language detection, see below:

  - If you need language detection
  > download the fasttext model here `https://fasttext.cc/docs/en/language-identification.html` 
  > register its path to `bertalign/utils.py`

  - if you do not need language detection,  
  > you can comment out `langcode` and `fasttexts` from `models.py`, `requirements.txt` and `bertalign/utils.py`
  make sure that you specify well the `src_lang` and `tgt_lang` when initilising Bertalign, 

  and replace the function `detect_lang` of bertalign/utils.py as
  ```
  def detect_lang(text):
      raise NotImplementedError('Please provide the source language and the target language')
  ```



## References

- [ ] add our ARTS paper

```
@inproceedings{nguyen-etal-2021-trankit,
    title = "Trankit: A Light-Weight Transformer-based Toolkit for Multilingual Natural Language Processing",
    author = "Nguyen, Minh Van  and
      Lai, Viet Dac  and
      Pouran Ben Veyseh, Amir  and
      Nguyen, Thien Huu",
    editor = "Gkatzia, Dimitra  and
      Seddah, Djam{\'e}",
    booktitle = "Proceedings of the 16th Conference of the European Chapter of the Association for Computational Linguistics: System Demonstrations",
    month = apr,
    year = "2021",
    address = "Online",
    publisher = "Association for Computational Linguistics",
    url = "https://aclanthology.org/2021.eacl-demos.10",
    doi = "10.18653/v1/2021.eacl-demos.10",
    pages = "80--90",
    abstract = "We introduce Trankit, a light-weight Transformer-based Toolkit for multilingual Natural Language Processing (NLP). It provides a trainable pipeline for fundamental NLP tasks over 100 languages, and 90 pretrained pipelines for 56 languages. Built on a state-of-the-art pretrained language model, Trankit significantly outperforms prior multilingual NLP pipelines over sentence segmentation, part-of-speech tagging, morphological feature tagging, and dependency parsing while maintaining competitive performance for tokenization, multi-word token expansion, and lemmatization over 90 Universal Dependencies treebanks. Despite the use of a large pretrained transformer, our toolkit is still efficient in memory usage and speed. This is achieved by our novel plug-and-play mechanism with Adapters where a multilingual pretrained transformer is shared across pipelines for different languages. Our toolkit along with pretrained models and code are publicly available at: \url{https://github.com/nlp-uoregon/trankit}. A demo website for our toolkit is also available at: \url{http://nlp.uoregon.edu/trankit}. Finally, we create a demo video for Trankit at: \url{https://youtu.be/q0KGP3zGjGc}.",
}


@article{liu-tal-2022-bertalign,
    author = {Liu, Lei and Zhu, Min},
    title = "{Bertalign: Improved word embedding-based sentence alignment for Chinese–English parallel corpora of literary texts}",
    journal = {Digital Scholarship in the Humanities},
    volume = {38},
    number = {2},
    pages = {621-634},
    year = {2022},
    month = {12},
    abstract = "{Bertalign is designed to improve sentence alignment accuracy for Chinese–English parallel corpora of literary texts. Aligning bilingual literary texts is not trivial, since most of the translation is interpretative and not based on 1-to-1 mappings between source and target sentences. Existing alignment methods highlight 1-to-1 links while having difficulty coping with 1-to-many and many-to-many alignments that are common in literary texts. To overcome the weaknesses of current approaches, we propose a novel two-step algorithm for bilingual sentence alignment. The first step finds the optimal paths for 1-to-1 alignments based on the top-k most semantically similar target sentences for each source sentence using the bidirectional encoder representations from transformer-based cross-lingual word embeddings. The second step relies on search paths found in the previous step to recover all valid alignments with more than one sentence on each side of the bilingual text. A comprehensive experiment was conducted on a newly built Chinese–English literary parallel corpus and a large-scale publicly available bilingual corpus of the Bible to compare the performance of Bertalign with five baseline systems: Gale-Church, Hunalign, Bleualign, Bleurtalign, and Vecalign. The results show that Bertalign achieves the highest accuracy in terms of F1 score on the two evaluation datasets than previous methods.}",
    issn = {2055-7671},
    doi = {10.1093/llc/fqac089},
    url = {https://doi.org/10.1093/llc/fqac089},
    eprint = {https://academic.oup.com/dsh/article-pdf/38/2/621/50488021/fqac089.pdf},
}
```




