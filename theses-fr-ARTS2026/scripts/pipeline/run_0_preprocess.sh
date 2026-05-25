#!/bin/bash

#SBATCH --job-name=segment-align-2025-12-16
#SBATCH --account=almanach
#SBATCH --partition=gpu
#SBATCH --gres=gpu:rtx8000:1     # GPU nodes are only available in gpu partition
#SBATCH --mem=32G
#SBATCH --cpus-per-gpu=8          # number of OpenMP threads

#SBATCH --time=20:00:00
#SBATCH --output=%x-%j.out
nvidia-smi -L
echo --------------------------------------

# almanach
source ~/.bashrc
source activate /home/zpeng/miniconda3/envs/resumeTHE
cd /home/zpeng/scratch/MaTOS/resumeAllTHE/
pwd


current_date_time="`date "+%Y-%m-%d %H:%M:%S"`";
echo $current_date_time;
echo "sbatch run_0_preprocess.sh"

######### preprocess #########


pq_path=local_data/THE1/theses.fr.combined.parquet
STORE_DIR=local_data/THE1/resumeAllTHE_lid176

# pq_path=local_data/THE/tmp/sample1000.theses.fr.combined.parquet
# STORE_DIR=local_data/THE/tmp1

mkdir -p $STORE_DIR
lid_pq_path=$STORE_DIR/theses.fr.combined_lid.parquet
trash_path=$STORE_DIR/trash_empty_data.theses.fr.combined.parquet
# python scripts/pipeline/preprocess.py $pq_path $lid_pq_path $trash_path
lang1=fr
lang2=en
seg_store_path=$STORE_DIR/to_segment.theses.fr.$lang1-$lang2.parquet
overlap_path=$STORE_DIR/trash_overlap.id.txt
echo $seg_store_path

# python scripts/pipeline/preprocess.py $pq_path $lid_pq_path $trash_path $lang1 $lang2 $seg_store_path
python scripts/pipeline/preprocess.py --pq_path $pq_path --lid_store_path $lid_pq_path --seg_store_path $seg_store_path --trash_path $trash_path --lang1 $lang1 --lang2 $lang2 --overlap_path $overlap_path
