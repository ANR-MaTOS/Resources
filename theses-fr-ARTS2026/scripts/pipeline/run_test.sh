#!/bin/bash

#SBATCH --job-name=segment-align-2025-12-01
#SBATCH --account=almanach
#SBATCH --partition=gpu
#SBATCH --gres=gpu:rtx8000:1     # GPU nodes are only available in gpu partition
#SBATCH --mem=16G
#SBATCH --cpus-per-gpu=8          # number of OpenMP threads

#SBATCH --time=16:00:00
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
echo "sbatch --array=0-1 ../run_test.sh"
echo "Running task $SLURM_ARRAY_TASK_ID"

######### preprocess #########

pq_path=local_data/THE/tmp/sample1000.theses.fr.combined.parquet

# ##### STORE_DIR=local_data/THE/resumeAllTHE
STORE_DIR=local_data/THE/tmp1
mkdir -p $STORE_DIR
lid_pq_path=$STORE_DIR/theses.fr.combined_lid.parquet
trash_path=$STORE_DIR/trash_empty_data.theses.fr.combined.parquet
# if [ "$SLURM_ARRAY_TASK_ID" -eq 0 ]; then
# fi
# python scripts/pipeline/preprocess.py $pq_path $lid_pq_path $trash_path


# ##### segment_align
pq_path=$lid_pq_path
lang1='fr'
lang2='en'
portion_id=$SLURM_ARRAY_TASK_ID
step=250
parallel_size=500
# python scripts/pipeline/segment_align.py $lid_pq_path $lang1 $lang2 $STORE_DIR $portion_id $step $parallel_size

source ~/.bashrc
source activate /home/zpeng/miniconda3/envs/py311
cd /home/zpeng/scratch/MaTOS/resumeAllTHE/

# ##### evaluate 
align_pq_path=$STORE_DIR/aligned.theses.fr.parquet
store_fname=aligned.theses.fr.with-cometkiwi.parquet
batch_size=16
# python scripts/filtering/eval_alignments.py $align_pq_path $STORE_DIR $store_fname $portion_id $batch_size $parallel_size
# python scripts/filtering/eval_alignments.py $align_pq_path $STORE_DIR $store_fname $portion_id $batch_size $parallel_size



store_fpath=$STORE_DIR/theses.fr.titles.with-cometkiwi.parquet
python scripts/filtering/eval_alignments.py $lid_pq_path $store_fpath $batch_size


