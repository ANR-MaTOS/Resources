#!/bin/bash

#SBATCH --job-name=segment-align-2025-12-16
#SBATCH --account=almanach
#SBATCH --partition=gpu
#SBATCH --gres=gpu:rtx8000:1     # GPU nodes are only available in gpu partition
#SBATCH --mem=32G
#SBATCH --cpus-per-gpu=8          # number of OpenMP threads

#SBATCH --time=24:00:00
#SBATCH --output=%x-%j.out
nvidia-smi -L
echo --------------------------------------

source ~/.bashrc
source activate /home/zpeng/miniconda3/envs/resumeTHE
cd /home/zpeng/scratch/MaTOS/resumeAllTHE/
pwd

current_date_time="`date "+%Y-%m-%d %H:%M:%S"`";
echo $current_date_time;
echo "sbatch ../run_1_keywords.sh"

# pq_path=local_data/THE/tmp/sample1000.theses.fr.combined.parquet
# STORE_DIR=local_data/THE/tmp1

STORE_DIR=local_data/THE1/resumeAllTHE_lid176
mkdir -p $STORE_DIR
# lid_pq_path=$STORE_DIR/theses.fr.combined_lid.parquet
lid_store_path=$STORE_DIR/theses.fr.keywords_bilingual_nfc_lid.parquet
store_path=$STORE_DIR/theses.fr.en-fr-keywords.parquet


######### align keywords ######### 
# python scripts/pipeline/align_keywords.py --pq_path $lid_pq_path --lid_store_path $lid_store_path  --store_path $store_path 
python scripts/pipeline/align_keywords.py --pq_path $lid_store_path --lid_store_path $lid_store_path  --store_path $store_path 

