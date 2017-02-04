import networkx as nx
import os

G=nx.read_edgelist('edgeList.txt')
pr = nx.pagerank(G, alpha=0.85, personalization=None, max_iter=30, tol=1e-06, nstart=None, weight='weight', dangling=None)
f = open('external_pageRankFile.txt', 'w+')
for u in G:
    f.write('/Users/NamanAvasthi/Desktop/crawl_data_example/'+u+'='+str(pr[u])+'\n')