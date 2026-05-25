import json
import urllib.request as urllib2
import xml.etree.ElementTree as ET
from tqdm import tqdm
from multiprocessing import Pool


base_url = "https://theses.fr"
json_path = "../../data/theses.fr.json"

output_json_path = "../../data/theses.fr.abstract.json"
MAX_N = None
# MAX_N = 1


def _fetch_abstracts(tid):
    try:
        xml_url = f"{base_url}/api/v1/export/xml/{tid}"
        data = urllib2.urlopen(xml_url, timeout=30).read()
        root = ET.fromstring(data)

        namespaces = {
            'dcterms': 'http://purl.org/dc/terms/',
            'dc': 'http://purl.org/dc/elements/1.1/',
            'rdf': 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
            'foaf': 'http://xmlns.com/foaf/0.1/',
            'xml': 'http://www.w3.org/XML/1998/namespace',
        }
        abstracts = root.findall(".//dcterms:abstract", namespaces)
        # keywords = root.findall(".//dc:subject", namespaces)

        out = {"tid": tid}
        out["abstracts"] = {}
        for abstract in abstracts:
            lang = abstract.attrib.get('{http://www.w3.org/XML/1998/namespace}lang', 'Unknown')
            out["abstracts"][lang] = abstract.text
        out["keywords"] = {}
        out["controlled_keywords"] = {}

        elements = list(root.iter())
        for i, el in enumerate(elements):
            if el.tag == "{http://purl.org/dc/elements/1.1/}subject":
                keyword = (el.text or "").strip()
                if keyword == "":
                    continue

                # Assume uncontrolled unless proven otherwise
                is_controlled = False

                # Look at next element in document
                if i + 1 < len(elements):
                    nxt = elements[i + 1]

                    if nxt.tag == "{http://purl.org/dc/terms/}subject":
                        # Does it have rdf:resource?
                        if any(attr.endswith('resource') for attr in nxt.attrib):
                            is_controlled = True
                lang = el.attrib.get('{http://www.w3.org/XML/1998/namespace}lang', 'Unknown')
                if lang == "Unknown":
                    continue
                if is_controlled:
                    if lang not in out["controlled_keywords"]:
                        out["controlled_keywords"][lang] = []
                    out["controlled_keywords"][lang].append(keyword)
                else:
                    if lang not in out["keywords"]:
                        out["keywords"][lang] = []
                    out["keywords"][lang].append(keyword)

        return out
    except Exception:
        # on any failure return an empty dict to keep indexing stable
        return {}

if __name__ == "__main__":
    output_json = list()

    with open(json_path) as f:
        all_theses_json = json.load(f)

    if MAX_N is None:
        n = all_theses_json["totalHits"]
    else:
        n = min(MAX_N, all_theses_json["totalHits"])

    tids = [all_theses_json["theses"][i]["id"] for i in range(n)]

    # Load existing checkpoint if available
    try:
        with open(output_json_path) as f:
            output_json = json.load(f)
        start_idx = len(output_json)
        print(f"Resuming from checkpoint: {start_idx}/{len(tids)}")
    except FileNotFoundError:
        output_json = []
        start_idx = 0

    # Parallel fetch with a process pool, preserving order
    with Pool() as pool:
        for i, result in enumerate(tqdm(pool.imap(_fetch_abstracts, tids[start_idx:]), 
                        total=len(tids), 
                        initial=start_idx),
                        start=start_idx):
            output_json.append(result)
            
            # Save checkpoint every 100 items
            if (i + 1) % 10000 == 0:
                with open(output_json_path, "w", encoding="utf-8") as f:
                    json.dump(output_json, f, ensure_ascii=False, indent=2)

    # Final save
    with open(output_json_path, "w", encoding="utf-8") as f:
        json.dump(output_json, f, ensure_ascii=False, indent=2)
