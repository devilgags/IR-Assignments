<?php
    //	make	sure	browsers	see	this	page	as	utf-8	encoded	HTML
    header('Content-Type:	text/html;	charset=utf-8');
    error_reporting(0);
    $limit	= 10;
    $query	= isset($_REQUEST['q'])	?	$_REQUEST['q']	:	false;
    $results	= false;

$file1 = fopen("map_nbcnews.csv", "r");
$file2 = fopen("map_wsj.csv", "r");
$filetourl = array();
while(!feof($file1))
{
    $line = fgets($file1);
        $tokens = explode(",", $line);
    $filetourl[$tokens[0]] = $tokens[1];
}
while(!feof($file2))
{
    $line = fgets($file2);
    $tokens = explode(",", $line);
    $filetourl[$tokens[0]] = $tokens[1];
}
    
            
                // 'q'=>'&facet.field:og_url&facet.numTerms:true&facet.minCount:10'

            



    if ($query)
    {
        //	The	Apache	Solr	Client	library	should	be	on	the	include	path
        //	which	is	usually	most	easily	accomplished	by	placing	in	the
        //	same	directory	as	this	script	(	.	or	current	directory	is	a	default
        //	php	include	path	entry	in	the	php.ini)

        // $query = $query.'&facet.field=og_url&facet.minCount=10';

        // &facet.field=og_url&facet.minCount=10
        
        require_once('solr-php-client-master/Apache/Solr/Service.php');
        
        //	create	a	new	solr	service	instance	- host, port,	and	corename
        //	path	(all	defaults	in	this	example)
        
        $solr	= new Apache_Solr_Service('localhost',	8983,	'/solr/IR_Assignment3_Core/');
        
        //	if	magic	quotes	is	enabled	then	stripslashes	will	be	needed
        if (get_magic_quotes_gpc()	== 1)
        {
            $query	= stripslashes($query);
        }
        
        //	in	production	code	you'll	always	want	to	use	a	try	/catch	for	any
        //	possible	exceptions	emitted		by	searching	(i.e.	connection
        //	problems	or	a	query	parsing	error)
        
        try
        {
            
            
            if($_GET['pageRankType'] == "pageRankAlgo") {
                $additionalParameters = array('fq'=>'og_url:[* TO *]','sort'=>'pageRankFile desc');
                // $additionalParameters = array('sort'=>'pageRankFile desc');
                $results = $solr->search($query, 0, $limit, $additionalParameters);
                
            }
            else {
                // $results	= $solr->search($query,	0,	$limit);
                $additionalParameters = array('fq'=>'og_url:[* TO *]');
                $results = $solr->search($query, 0, $limit, $additionalParameters);
            }
            
            
        }
        catch (Exception $e)
        {
            //	in	production	you'd	probably	log	or	email	this	error	to	an	admin
            //	and	then	show	a	special	message	to	the	user	but	for	this	example
            //	we're	going	to	show	the	full	exception
            die("<html><head><title>SEARCH	EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
        }
    }
?>
<html>
    <head>
        <title>PHP Solr Form</title>
        
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.5/css/bootstrap.min.css" integrity="sha384-AysaV+vQoT3kOAXZkl02PThvDr8HYKPZhNT5h/CXfBThSRXQ6jW5DO2ekP5ViFdi" crossorigin="anonymous">

        
        <style>
            
            .container {
                display: table;
                vertical-align: middle;
            }
            
            #form_container {
                display: table-cell;
                /*vertical-align: middle;*/
            }
        
            a, u {
                text-decoration: none;
            }
            
            a:hover {
                text-decoration: underline;
            } 
            a:visited {
                text-decoration: none;
            }

            
            .innerhtml {
                text-decoration: none;
                color: green;
            }
            .innerhtml:visited {
                color: green;
            }
            .innerhtml:hover {
                
                text-decoration: none;
                color: green;
            }
            
            h1,h3 {
                text-align: center;
            }
            
            #searchlabel {
                width: auto;
                display: inline;
            }
            #q {
                width: auto;
                display: inline;
            }
            
            #legend_id {
                font-size: 1.2em;
            }
        
        </style>
        
    </head>
    <body>
        
        <h1>CSCI 572 : Information Retrieval and Web Seach Engines</h1>
        
        <h3>Assignment #3 : Indexing the Web Using Solr</h3>
        
        <div class="container">
        
        <form accept-charset="utf-8" method="get" id="form_container">
                        
            <div class="form-group" id="search_container">
                <label for="q" id="searchlabel">Search : </label>
                <input type="text" name="q" class="form-control" id="q" aria-describedby="SearchFieldHelp" placeholder="Search Term" value="<?php echo isset($_GET['q']) ? $_GET['q'] : '' ?>">
                <small id="SearchFieldHelp" class="form-text text-muted">Enter what you want to search here.</small>
            </div>
            
            
            <fieldset class="form-group">
                <legend id="legend_id">Type of Ranking Algorithm</legend>
                <div class="form-check">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="pageRankType" id="optionsRadios1" value="lucene" <?php echo isset($_GET['pageRankType']) && $_GET['pageRankType'] == "pageRankAlgo" ? "" : "checked"; ?> > Lucene 
                    </label>
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="pageRankType" id="optionsRadios2" value="pageRankAlgo" <?php echo isset($_GET['pageRankType']) && $_GET['pageRankType'] == "pageRankAlgo" ? "checked" : ""; ?> > PageRank 
                    </label>
                </div>
            </fieldset>
            
            
            <button type="submit" class="btn btn-primary">Submit</button>
            
        </form>
        
        <?php
            //	display	results
            if ($results)
            {
                $total	= (int)	$results->response->numFound;
                $start	= min(1,	$total);
                $end	= min($limit,	$total);
        ?>
        
        <?php

            //	iterate	result	documents
                foreach ($results->response->docs	as $doc)
                {

        ?>
        <div id="results">

        <?php
                    //	iterate	document	fields	/	values
                    foreach ($doc	as $field	=> $value)
                    {
                
                        if ( htmlspecialchars($field, ENT_NOQUOTES, 'utf-8') == "title" ) {

                            $hreftitle = htmlspecialchars($value,ENT_NOQUOTES,'utf-8');
                            $titlebol = 1;
                        }
                        

                        // if( htmlspecialchars($field, ENT_NOQUOTES, 'utf-8') == "og_url" ) {

                        //     $hreflink = htmlspecialchars($value,ENT_NOQUOTES,'utf-8');

                        //     // $shorturl = substr($hreflink, 0, 45) . " ...";
                        // }

                        if( htmlspecialchars($field, ENT_NOQUOTES, 'utf-8') == "og_description" ) {

                            $link_description = htmlspecialchars($value,ENT_NOQUOTES,'utf-8');
                            $descbol = 1;
                        }

                        if ( htmlspecialchars($field, ENT_NOQUOTES, 'utf-8') == "id" ) {

                            $line = explode("/", $value);
                            $fil = $line[6];

                            if(array_key_exists($fil, $filetourl)) {
                                $url = $filetourl[$fil];
                                $urlbol = 1;
                            }
                                

                        }
                        


                    }
                            
        ?>

            <span>TITLE &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp: </span><a href="<?php  echo $url  ?>"><?php echo $hreftitle ?> </a> <br>
            <div class="subtext"><span>FILE &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp: </span><span><?php echo htmlspecialchars($fil, ENT_NOQUOTES, 'utf-8'); ?></span></div>
            <span>URL &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp: </span><a class="innerhtml" href=" <?php  echo $url  ?>">  <?php echo $url ?> </a> <br>
            <span>DESCRIPTION : </span><div class="subtext"><span><?php echo $link_description    ?> </span></div>
            <br>
            
            

        </div>
        <?php 
                }
            }
        ?>
        
        </div>
        
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.5/js/bootstrap.min.js" integrity="sha384-BLiI7JTZm+JWlgKa0M0kGRpJbF2J8q+qreVrKBC47e3K6BW78kGLrCkeRX6I9RoK" crossorigin="anonymous"></script>
        
    </body>
</html>