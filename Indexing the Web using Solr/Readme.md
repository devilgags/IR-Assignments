In this assignment, Apache Solr software is used to import a pre-selected lot of web pages, 
which is then used to investigate different ranking strategies.

The basic two strategies referred here are Lucene and Page Rank.

Solr uses Lucene to facilitate ranking. Lucene uses a combination of the Vector Space Model and the
Boolean model to determine how relevant a given document is to a user's query. The vector space model
is based on term frequency. The Boolean model is first used to narrow down the documents that need to
be scored based on the use of Boolean logic in the query specification.

For page rank -
A graph is needed with the outgoing node count and direction. For this NetworkX library is used in python. 
After generating the page rank scores from the graph, appropriate changes are made to manage schema 

<fieldType name="external" keyField="id" defVal="0" class="solr.ExternalFileField" valType="pFloat"/>
<field name="pageRankFile" type="external" stored="false" indexed="false" />

Relevant statistics were calculated on both the ranking strategies and 
a comparison was drawn on the relevance and overlap produced in the queries.
