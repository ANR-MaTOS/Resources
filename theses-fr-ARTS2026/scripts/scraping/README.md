# Scraping step

## How to run

Require `python` and its `tqdm` library beforehand.

* Run in terminal:

```bash run.sh```

## Results

The script has two steps:

1. The download of the descriptive json file of all the **finished** thesis. Unfortunately, it does not contain the abstract, but it can be used to retrieve the abstracts from the unique url ids.
2. The scraping of the theses.fr website of all the abstracts one by one. The script is slow as for now.

The outputs are two json files in the `./data` directory:
1. ̀`theses.fr.json`: see scraper.py for hints of how to access it
2. `theses.fr.abstract.json`: a list of dictionaries of language to corresponding abstract and keywords

The indexing of both files is the same.

## TODO:

* [ ] merge the two json files in one with only the useful data
* [ ] discuss about it
