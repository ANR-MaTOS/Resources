# The MERSENNE Parallel Articles

This repository releases the MERSENNE parallel article dataset evaluated in the paper Self-Retrieval from Distant Contexts for Document-Level Machine Translation.

The raw texts were collected from the source URLs listed in resource-Octobre-2024.csv.


The scripts, additional details and documentation will be provided soon.



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