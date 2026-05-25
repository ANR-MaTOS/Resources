import json
import pyarrow.parquet as pq
from tqdm import tqdm
import pandas as pd
import re



def clean_text(text):
    if type(text) != str:
        return text
    text = text.replace("\n", " ").replace("\r", " ").strip()
    text = re.sub(r"\s+", " ", text)
    return text

metadata_path = "../../data/theses.fr.json"
data_path = "../../data/theses.fr.abstract.json"


with open(metadata_path) as f:
    metadata = json.load(f)

with open(data_path) as f:
    data = json.load(f)

combined_data = list()
MAX_NUM_LANG = 8  # can reach up to 8 languages, but only once

for i in tqdm(range(int(metadata["totalHits"]))):
    # only consider matching ids
    if "tid" not in data[i] or "id" not in metadata["theses"][i]:
        continue

    id_meta = metadata["theses"][i]["id"]
    id_data = data[i]["tid"]
    if id_meta != id_data:
        continue

    combined = dict()

    # metadata
    combined["id"] = id_meta
    if "dateSoutenance" in metadata["theses"][i] and type(metadata["theses"][i]["dateSoutenance"]) == str and len(metadata["theses"][i]["dateSoutenance"]) > 0:
        date = metadata["theses"][i]["dateSoutenance"].split("/")[-1]
        combined["year"] = int(date)
    else:
        combined["year"] = None
    if "titrePrincipal" in metadata["theses"][i] and type(metadata["theses"][i]["titrePrincipal"]) == str and len(metadata["theses"][i]["titrePrincipal"]) > 0:
        combined["title_fr"] = metadata["theses"][i]["titrePrincipal"]
    if "titreEN" in metadata["theses"][i] and type(metadata["theses"][i]["titreEN"]) == str and len(metadata["theses"][i]["titreEN"]) > 0:
        combined["title_en"] = metadata["theses"][i]["titreEN"]
    if "discipline" in metadata["theses"][i] and type(metadata["theses"][i]["discipline"]) == str and len(metadata["theses"][i]["discipline"]) > 0:
        combined["discipline"] = metadata["theses"][i]["discipline"]
    else:
        combined["discipline"] = None

    # keywords
    if (not "controlled_keywords" in data[i]) or len(data[i]["controlled_keywords"]) == 0 or "fr" not in data[i]["controlled_keywords"]:
        combined["controlled_keywords"] = None
    else:
        combined["controlled_keywords"] = "///".join([clean_text(e) for e in data[i]["controlled_keywords"]["fr"]])

    if (not "keywords" in data[i]) or len(data[i]["keywords"]) == 0:
        combined["keywords_en"] = None
        combined["keywords_fr"] = None
    else:
        if len([e for e in data[i]["keywords"]]) and "fr" in data[i]["keywords"]:
            combined["keywords_fr"] = "///".join([clean_text(e) for e in data[i]["keywords"]["fr"]])
        else:
            combined["keywords_fr"] = None
        if len([e for e in data[i]["keywords"]]) and "en" in data[i]["keywords"]:
            combined["keywords_en"] = "///".join([clean_text(e) for e in data[i]["keywords"]["en"]])
        else:
            combined["keywords_en"] = None
    
    # abstract
    keys = list(data[i]["abstracts"])
    for j in range(MAX_NUM_LANG):
        if j < len(keys):
            combined[f"lang_{j+1}"] = keys[j]
            combined[f"abstract_{j+1}"] = data[i]["abstracts"][keys[j]]
        else:
            combined[f"lang_{j+1}"] = None
            combined[f"abstract_{j+1}"] = None


    combined_data.append(combined)



# id titleFR titleEN year discipline keywords abstract

df = pd.DataFrame(combined_data)
df.to_parquet("../../data/theses.fr.combined.parquet")

# load
df_loaded = pd.read_parquet("../../data/theses.fr.combined.parquet")
print(df_loaded.head())
print(df_loaded.info())
print(f"Shape: {df_loaded.shape}")