#!/bin/bash

#SBATCH --job-name=segment-align-2026-03-16
#SBATCH --account=almanach
#SBATCH --partition=gpu
#SBATCH --gres=gpu:rtx8000:1     # GPU nodes are only available in gpu partition
#SBATCH --mem=32G
#SBATCH --cpus-per-gpu=8          # number of OpenMP threads

#SBATCH --time=24:00:00
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
echo "sbatch --array=0-3 ../run_1_segment_align.sh"
echo "Running task $SLURM_ARRAY_TASK_ID"

######### preprocess #########
# pq_path=local_data/THE/tmp/sample1000.theses.fr.combined.parquet
# STORE_DIR=local_data/THE1/tmp1
# step=100
# parallel_size=400

STORE_DIR=local_data/THE1/resumeAllTHE_lid176_realign

mkdir -p $STORE_DIR


# segment_align
lang1='fr'
lang2='en'
# seg_pq_path=local_data/THE1/resumeAllTHE/to_segment.theses.fr.$lang1-$lang2.parquet
# seg_pq_path=local_data/THE1/resumeAllTHE/to_segment_new_diff.theses.fr.$lang1-$lang2.parquet
seg_pq_path=local_data/THE1/resumeAllTHE_lid176/to_align.theses.fr.$lang1-$lang2.parquet

portion_id=$SLURM_ARRAY_TASK_ID
step=10000
parallel_size=80000
python scripts/pipeline/segment_align.py --to_segment_align_pq_path $seg_pq_path --store_dir $STORE_DIR --l1 $lang1 --l2 $lang2 --portion_id $portion_id --step $step --parallel_size $parallel_size

python scripts/pipeline/segment_align.py --to_segment_align_pq_path $seg_pq_path --store_dir $STORE_DIR --l1 $lang1 --l2 $lang2 --portion_id $portion_id --step $step --parallel_size $parallel_size


# python scripts/pipeline/segment_align.py $seg_pq_path $lang1 $lang2 $STORE_DIR $portion_id $step $parallel_size
# python scripts/pipeline/segment_align.py $seg_pq_path $lang1 $lang2 $STORE_DIR $portion_id $step $parallel_size

