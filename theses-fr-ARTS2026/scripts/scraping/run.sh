#!/bin/bash

######### download json file with all metadata but no abstract
# first, get number of available theses by downloading a single result
mkdir -p ../../data
curl -L --compressed \
  -H "Accept: application/json" \
  -o ../../data/theses-1.fr.json \
  "https://theses.fr/api/v1/theses/recherche/?q=*&nombre=1&tri=dateDesc&filtres=%5BStatut=%22soutenue%22%5D"


NUM=$(grep -Eo "totalHits\":[0-9]*," < ../../data/theses-1.fr.json | sed -ne "s/totalHits\":\(.*\),/\1/p")
echo "Number of thesis available: $NUM"

# download in pages to avoid too-large single request
PAGE_SIZE=10000
OUTDIR=../../data/pages
mkdir -p "$OUTDIR"


# OFFSET=0
# echo "Downloading ${NUM} results in pages of ${PAGE_SIZE}..."
# while [ "$OFFSET" -lt "$NUM" ]; do
#   echo "Fetching offset ${OFFSET}..."
#   curl -s -L --compressed \
#     -H "Accept: application/json" \
#     -o "${OUTDIR}/theses.${OFFSET}.json" \
#     "https://theses.fr/api/v1/theses/recherche/?q=*&nombre=${PAGE_SIZE}&debut=${OFFSET}&tri=dateDesc&filtres=%5BStatut=%22soutenue%22%5D"
#   OFFSET=$((OFFSET + PAGE_SIZE))
#   sleep 0.2
# done


OFFSET=0
echo "Downloading ${NUM} results in pages of ${PAGE_SIZE}..."
while [ "$OFFSET" -lt "$NUM" ]; do
  echo "Fetching offset ${OFFSET}..."
  tmp="${OUTDIR}/tmp.theses.${OFFSET}.json"
  final="${OUTDIR}/theses.${OFFSET}.json"

  curl -sS -L --compressed \
      --fail \
      --retry 5 \
      --retry-delay 2 \
      --retry-connrefused \
      -H "Accept: application/json" \
      -o "$tmp" \
      "https://theses.fr/api/v1/theses/recherche/?q=*&nombre=${PAGE_SIZE}&debut=${OFFSET}&tri=dateDesc&filtres=%5BStatut=%22soutenue%22%5D"
    
  echo "Checking: $f"
    if jq empty "$tmp"; then
          mv "$tmp" "$final"
      else
          echo "Invalid JSON: $f"
      fi

  OFFSET=$((OFFSET + PAGE_SIZE))
  sleep 0.2
done

echo "Pages saved to ${OUTDIR}."


# jq empty ../../data/pages/theses.*.json
######### merge pages into a single file (requires jq)
jq -s 'map(.theses) | {theses: add, totalHits: '"$NUM"'}' ../../data/pages/theses.*.json > ../../data/theses.fr.json

du -h ../../data/theses.fr.json

######### retrieve the abstract with scrapping using the url ids in the json file
python scraper.py

du -h ../../data/theses.fr.abstract.json

python combine_all.py
