# see more details at https://github.com/Maxwell1447/Auto-Threshold-For-Data-Filtering
import sys
import numpy as np
from sklearn.mixture import GaussianMixture
from scipy import stats
import matplotlib.pyplot as plt
import argparse
import pandas as pd

def g(x, args):
    if x < args.a:
        return 0
    if x > args.b:
        return 1
    return (x - args.a) ** args.p / (args.b - args.a) ** args.p

def weigthed_normals(xs, weights, means, vars, args, good=True):
    out = np.zeros(len(xs))
    if good:
        f = (lambda x: g(x, args))
    else:
        f = (lambda x: 1 - g(x, args))
    for w, mu, var in zip(weights, means, vars):
        out = out + f(mu) * w * stats.norm.pdf(xs, mu, np.sqrt(var))

    return out


def find_threshold(scores, args):

    y = scores[:args.n]
    gm = GaussianMixture(n_components=4)
    gm.fit(y.reshape(-1, 1))

    # lims = (0, 1)
    lims = (args.a, args.b)
    xs = np.linspace(lims[0], lims[1], 100).reshape(100, 1)

    
    f_plus = weigthed_normals(xs[:, 0], gm.weights_, gm.means_[:, 0], gm.covariances_[:, 0, 0], args, good=True)
    f_minus = weigthed_normals(xs[:, 0], gm.weights_, gm.means_[:, 0], gm.covariances_[:, 0, 0], args, good=False)
    print(f_plus)
    ratios = f_plus / (f_plus + f_minus)
    
    num_inf = (ratios < args.t).sum()
    threshold = xs[num_inf, 0]
    
    return threshold


if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("--input_scores", type=str, required=True, help="Path to input scores parquet file")
    parser.add_argument("--n", type=int, default=5000, help="Number of samples to use for threshold estimation")
    parser.add_argument("--t", type=float, default=0.7, help="Threshold on the probability f_plus / (f_plus + f_minus)")
    parser.add_argument("--a", type=float, default=0.4, help="Bad scores cutoff for the g function")
    parser.add_argument("--b", type=float, default=0.85, help="Good scores cutoff for the g function")
    parser.add_argument("--p", type=float, default=1.0, help="Parameter p for the g function")
    args = parser.parse_args()

    # for format of scores file
    # here we assume it's a parquet file from which we can extract the cometkiwi score as a 1D array of scores
    # but it can be changed as needed (to adapt)


    # align_path = f'{DATA_DIR}/aligned.theses.fr.with-cometkiwi.parquet'
    align_df = pd.read_parquet(args.input_scores)
    # remove duplicates
    tmp_df = align_df[align_df['id'].duplicated()]
    align_df = align_df.drop(index = tmp_df.index)
    # remove abstracts with empty alignments
    no_empty_df = align_df[~align_df['has_empty_align']]

    scores = no_empty_df['cometkiwi22'].values

    # scores = np.load(args.input_scores)
    threshold = find_threshold(scores, args)

    print(threshold)