#!/bin/bash

#SBATCH --job-name=segment-align-2026-03-16
#SBATCH --account=almanach
#SBATCH --partition=almanach
#SBATCH --gres=gpu:rtx8000:1     # GPU nodes are only available in gpu partition
#SBATCH --mem=32G
#SBATCH --cpus-per-gpu=8          # number of OpenMP threads

#SBATCH --time=20:00:00
#SBATCH --output=%x-%j.out
nvidia-smi -L
echo --------------------------------------

# almanach
source ~/.bashrc
source activate /home/zpeng/miniconda3/envs/py311
cd /home/zpeng/scratch/MaTOS/resumeAllTHE/
pwd


current_date_time="`date "+%Y-%m-%d %H:%M:%S"`";
echo $current_date_time;
echo "sbatch --array=0-3 run_2_eval.sh"
echo "Running task $SLURM_ARRAY_TASK_ID"




# STORE_DIR=local_data/THE1/resumeAllTHE_lid176
STORE_DIR=local_data/THE1/resumeAllTHE_lid176_realign

mkdir -p $STORE_DIR

##### evaluate abstracts #####
# ../../local_data/THE1/resumeAllTHE/aligned.theses.fr.parquet
align_pq_path=$STORE_DIR/aligned.theses.fr.parquet
store_fname=aligned.theses.fr.with-cometkiwi.parquet

portion_id=$SLURM_ARRAY_TASK_ID
parallel_size=80000
batch_size=16

python scripts/filtering/eval_alignments.py doc \
        --align_pq_path $align_pq_path --store_dir $STORE_DIR --store_fname $store_fname --portion_id $portion_id --bsz $batch_size -- parallel_size $parallel_size

python scripts/filtering/eval_alignments.py doc \
        --align_pq_path $align_pq_path --store_dir $STORE_DIR --store_fname $store_fname --portion_id $portion_id --bsz $batch_size -- parallel_size $parallel_size


##### evaluate titles #####
lid_pq_path=$STORE_DIR/theses.fr.combined_lid.parquet
title_store_fpath=$STORE_DIR/theses.fr.titles.with-cometkiwi.parquet
python scripts/filtering/eval_alignments.py -- title_pq_path $lid_pq_path --store_fpath $title_store_fpath --bsz $batch_size
