<?php
    //	make	sure	browsers	see	this	page	as	utf-8	encoded	HTML
    header('Content-Type:	text/html;	charset=utf-8');
    ini_set('memory_limit', '1000M');

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
    
            

    if ($query)
    {
        //	The	Apache	Solr	Client	library	should	be	on	the	include	path
        //	which	is	usually	most	easily	accomplished	by	placing	in	the
        //	same	directory	as	this	script	(	.	or	current	directory	is	a	default
        //	php	include	path	entry	in	the	php.ini)

        // $query = $query.'&facet.field=og_url&facet.minCount=10';

        // &facet.field=og_url&facet.minCount=10
        
        require_once('solr-php-client-master/Apache/Solr/Service.php');

        require_once('SpellCorrector.php');
        

        //	create	a	new	solr	service	instance	- host, port,	and	corename
        //	path	(all	defaults	in	this	example)
        
        $solr	= new Apache_Solr_Service('localhost',	8983,	'/solr/IR_Assignment3_Core/');

        $corrector_arr = explode(" ", $query);

        

        $iterater = 0;

        // echo "SIZE OF ARR : ".count($corrector_arr);
        // echo "content [0] : ".$corrector_arr[0];
        // echo "content [1] : ".$corrector_arr[1];

        if ( count($corrector_arr) > 1 ) {
            while ( $iterater < count($corrector_arr) ) {
                // echo "Before : ".$corrector_arr[$iterater];
                $corrector_arr[$iterater] = SpellCorrector::correct($corrector_arr[$iterater]);
                // echo "After  : ".$corrector_arr[$iterater];
                if( $iterater == 0 ) {
                    $corrector = $corrector.$corrector_arr[$iterater];
                }
                else {
                    $corrector = $corrector." ".$corrector_arr[$iterater];    
                }
                
                $iterater++;
            }
        }
        else {
            $corrector = SpellCorrector::correct($corrector_arr[0]);
            // echo "corrector is : ".$corrector;
        }

        

        // if ($corrector == $query) {
        //     $corrector = "";
        // }
        
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
                $results = $solr->search($corrector, 0, $limit, $additionalParameters);

                // echo "RESULTS TIME : ".$corrector;
                
            }
            else {
                // $results	= $solr->search($query,	0,	$limit);
                $additionalParameters = array('fq'=>'og_url:[* TO *]');
                $results = $solr->search($corrector, 0, $limit, $additionalParameters);
                // echo "RESULTS TIME : ".$corrector;
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
        
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <!-- Optional theme -->
        <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous"> -->

        <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
        


        
        <style>

            #magnify {

                font-size: 16px;

            }

            #put-them-inline {
                display: inline;
            }
            
            .container {
                /*display: table;*/

                /*vertical-align: middle;*/
            }
            
            #form_container {
                /*display: table-cell;*/
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
                width: 80%;
                display: inline;
                text-align: center;
            }
            
            #legend_id {
                font-size: 1.2em;
                text-align: center;
            }
        
        </style>
        
    </head>
    <body>
        
        <h1>CSCI 572 : Information Retrieval and Web Seach Engines</h1>
        
        <h3>Assignment #4 : Enhancing Your Search Engine</h3>
        
        <div class="container">
        
        <form accept-charset="utf-8" method="get" id="form_container">
                        
            <div class="form-group col-md-5 col-md-offset-4" id="search_container">
                <!-- <label for="q" id="searchlabel">Search : </label> -->
                <div id="put-them-inline">
                    <input type="text" name="q" class="form-control" id="q" aria-describedby="SearchFieldHelp" placeholder="Search Term" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>">
                    <button type="submit" class="btn btn-primary"> <span class="glyphicon glyphicon-search" id="magnify"></span> </button>
                </div>
                </br>
                <small id="SearchFieldHelp" class="form-text text-muted">Enter what you want to search here.</small>

                </br>
                <?php if ($corrector != $query): ?>
                <div class="col-md-12 col-md-offset-0">
                    <p>Do you want to search: <a href="http://localhost/IR_trial.php?q=<?php echo $corrector; ?>"><?php echo $corrector; ?></a></p>
                </div>
                <?php endif; ?>






            </div>

            <br>
            
            
            <fieldset class="form-group col-md-8 col-md-offset-3">
                <legend id="legend_id" class="col-md-9">Type of Ranking Algorithm</legend>
                <div class="form-check col-md-8 col-md-offset-3">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="pageRankType" id="optionsRadios1" value="lucene" <?php echo isset($_GET['pageRankType']) && $_GET['pageRankType'] == "pageRankAlgo" ? "" : "checked"; ?> > Lucene 
                    </label>
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="pageRankType" id="optionsRadios2" value="pageRankAlgo" <?php echo isset($_GET['pageRankType']) && $_GET['pageRankType'] == "pageRankAlgo" ? "checked" : ""; ?> > PageRank 
                    </label>
                </div>
            </fieldset>
            
            
            
            
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
        <div id="results" class="col-md-9 col-md-offset-2">

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
                            
                        }

                        if ( htmlspecialchars($field, ENT_NOQUOTES, 'utf-8') == "id" ) {

                            $line = explode("/", $value);
                            $fil = $line[6];

                            $file_for_description = $value;

                            if(array_key_exists($fil, $filetourl)) {
                                $url = $filetourl[$fil];
                                $urlbol = 1;
                            }


                            /* open file in file_for_description and get a line ! */



                            if ($corrector != $query) {
                                $searchfor = $corrector;
                            }
                            else {
                                $searchfor = $query;
                            }

                            /*

                            $file_descp = fopen($file_for_description, "r");

                            
                            $matches = array();

                            if ( $file_descp ) {
                                while( !feof($file_descp) ) {
                                    $buffer = fgets($file_descp);
                                    
                                    
                                    if(strpos($buffer, $searchfor) !== FALSE) {
                                        
                                        $matches[] = $buffer;
                                        echo $buffer;
                                        ?><br><?php    
                                        
                                        
                                    }
                                        
                                }
                                fclose( $file_descp );
                            }

                            echo "MATCHES ARE : \n";
                            print_r($matches);

                            
                            */







                            // $contents = file_get_contents($file_for_description);

                            // if (preg_match('/(?:<body[^>]*>)(.*)<\/body>/isU', $contents, $match)) {
                            //     $body = $match[0];
                            // }



                            
                            // $pattern = preg_quote($searchfor, '');
                            
                            // $pattern = "/[^.]*.[a-zA-Z0-9]*.$pattern.*\./";

                            // if(preg_match($pattern, $body, $matches)){
                            //    echo "Found matches:\n";
                            //    echo $matches[0][0];
                            // }
                            // else{
                            //    echo "No matches found";
                            // }
                               













                            $term = $searchfor;
                            $snippet="null";
                            // $content_html =  file_get_html($file_for_description);

                            $myvar1 = file_get_contents($file_for_description);
                            $dom = new DOMDocument();
                            libxml_use_internal_errors( 1 );      // <-- add this line to avoid DOM errors
                            $dom->loadHTML( $myvar1 );

                            // $elements = $dom->getElementsByTagName('title');

                            // var_dump($elements);

                            // foreach ($elements as $row) {
                            // //Loop through each child (cell) of the row
                            // foreach ($row->childNodes as $cell) {
                            //     echo $cell->nodeValue; // Display the contents of each cell - this is the value you want to extract
                            // }
                        // }

                            $content_array = array();
                            foreach($dom->getElementsByTagName('body') as $head)
                            {
                                foreach ($head->childNodes as $cell) {
                                    $content_array[] = $cell->nodeValue; // Display the contents of each cell - this is the value you want to extract
                                }
                            }

                            // echo $content_array[0];

                            $regex_html = '/[^\\>"\/#]{70,100}('.$term.')[^\\>"\/<#]{70,156}/i'; 
                            for ($i=0;$i<sizeof($content_array);$i++) {

                                if(preg_match($regex_html, $content_array[$i], $html_match)==1) {
                                    $snippet = html_entity_decode($html_match[0], ENT_QUOTES | ENT_HTML5, 'UTF-8'); 
                                }
                                else {
                                    if(strpos($term, ' ')>=0) {
                                        $parts = preg_split("/[\s]+/", $term);
                                        foreach($parts as $str) {
                                            $term = $str;
                                            $regex_html = '/[^\\>"\/#]{70,100}('.$term.')[^\\>"\/<#]{70,156}/i';
                                            if(preg_match($regex_html, $content_array[$i], $html_match)==1) {
                                                $snippet = html_entity_decode($html_match[0], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                break;
                                            }
                                        }
                                    }
                                }
                                if ($snippet!="null") {
                                    // echo $snippet;
                                    break;
                                }
                            }

                        

                        }


                        if ($snippet=="null" || $snippet==""  || $snippet==" ") {
                            if($link_description=="null" || $link_description==""  || $link_description==" ") {
                                $snippet = $hreftitle;
                            }
                            else
                                $snippet = $link_description;
                        }
                        
                        
                        
                        $snippet1 = "...".$snippet."...";
                        


                    }
                            
        ?>
            
            <!-- <span>TITLE &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp: </span> --><a href="<?php  echo $url  ?>"><?php echo $hreftitle ?> </a> <br>
            <!-- <div class="subtext"><span>FILE &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp: </span><span><?php echo htmlspecialchars($fil, ENT_NOQUOTES, 'utf-8'); ?></span></div> -->
            <!-- <span>URL &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp: </span> --><a class="innerhtml" href=" <?php  echo $url  ?>">  <?php echo $url ?> </a> <br>
            <!-- <span>DESCRIPTION : </span> --><div class="subtext"><span><?php echo $snippet1    ?> </span></div>
            <br>
            
            

        </div>
        <?php 
                }
            }
        ?>
        
        </div>


        <script src="http://code.jquery.com/jquery-1.10.2.js"></script>
        <script src="http://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>


        <script>
        $(function() {
            var URL_PREFIX = "http://localhost:8983/solr/IR_Assignment3_Core/suggest?q=";
            var URL_SUFFIX = "&wt=json";
            $("#q").autocomplete({
                source : function(request, response) {
                    var lastword = $("#q").val().toLowerCase().split(" ").pop(-1);
                    var URL = URL_PREFIX + lastword + URL_SUFFIX;
                    var slicevalue=10;
                    $.ajax({
                        url : URL,
                        success : function(data) {
                            var lastword = $("#q").val().toLowerCase().split(" ").pop(-1);
                            var len = $("#q").val().length;
                            var suggestions = data.suggest.suggest[lastword].suggestions;
                            suggestions = $.map(suggestions, function (value, index) {

                                if (len==1) {
                                    slicevalue = 10;
                                }
                                if (len==2) {
                                    slicevalue = 7;
                                }
                                if (len>=3) {
                                    slicevalue = 5;
                                }

                                var prefix = "";
                                var query = $("#q").val();
                                var queries = query.split(" ");
                                if (queries.length > 1) {
                                    var lastIndex = query.lastIndexOf(" ");
                                    prefix = query.substring(0, lastIndex + 1).toLowerCase();
                                }
                                if (prefix == "" && isStopWord(value.term)) {
                                    return null;
                                }
                                if (!/^[0-9a-zA-Z]+$/.test(value.term)) {
                                    return null;
                                }
                                return prefix + value.term;
                            });
                            response(suggestions.slice(0, slicevalue));
                        },
                        dataType : 'jsonp',
                        jsonp : 'json.wrf'
                    });
                },
                minLength : 1
            });
        });
        function isStopWord(word)
        {
            var regex = new RegExp("\\b"+word+"\\b","i");
            return stopWords.search(regex) < 0 ? false : true;
        }

        var stopWords = "a,able,about,above,abst,accordance,according,accordingly,across,act,actually,added,adj,\
        affected,affecting,affects,after,afterwards,again,against,ah,all,almost,alone,along,already,also,although,\
        always,am,among,amongst,an,and,announce,another,any,anybody,anyhow,anymore,anyone,anything,anyway,anyways,\
        anywhere,apparently,approximately,are,aren,arent,arise,around,as,aside,ask,asking,at,auth,available,away,awfully,\
        b,back,be,became,because,become,becomes,becoming,been,before,beforehand,begin,beginning,beginnings,begins,behind,\
        being,believe,below,beside,besides,between,beyond,biol,both,brief,briefly,but,by,c,ca,came,can,cannot,can't,cause,causes,\
        certain,certainly,co,com,come,comes,contain,containing,contains,could,couldnt,d,date,did,didn't,different,do,does,doesn't,\
        doing,done,don't,down,downwards,due,during,e,each,ed,edu,effect,eg,eight,eighty,either,else,elsewhere,end,ending,enough,\
        especially,et,et-al,etc,even,ever,every,everybody,everyone,everything,everywhere,ex,except,f,far,few,ff,fifth,first,five,fix,\
        followed,following,follows,for,former,formerly,forth,found,four,from,further,furthermore,g,gave,get,gets,getting,give,given,gives,\
        giving,go,goes,gone,got,gotten,h,had,happens,hardly,has,hasn't,have,haven't,having,he,hed,hence,her,here,hereafter,hereby,herein,\
        heres,hereupon,hers,herself,hes,hi,hid,him,himself,his,hither,home,how,howbeit,however,hundred,i,id,ie,if,i'll,im,immediate,\
        immediately,importance,important,in,inc,indeed,index,information,instead,into,invention,inward,is,isn't,it,itd,it'll,its,itself,\
        i've,j,just,k,keep,keeps,kept,kg,km,know,known,knows,l,largely,last,lately,later,latter,latterly,least,less,lest,let,lets,like,\
        liked,likely,line,little,'ll,look,looking,looks,ltd,m,made,mainly,make,makes,many,may,maybe,me,mean,means,meantime,meanwhile,\
        merely,mg,might,million,miss,ml,more,moreover,most,mostly,mr,mrs,much,mug,must,my,myself,n,na,name,namely,nay,nd,near,nearly,\
        necessarily,necessary,need,needs,neither,never,nevertheless,new,next,nine,ninety,no,nobody,non,none,nonetheless,noone,nor,\
        normally,nos,not,noted,nothing,now,nowhere,o,obtain,obtained,obviously,of,off,often,oh,ok,okay,old,omitted,on,once,one,ones,\
        only,onto,or,ord,other,others,otherwise,ought,our,ours,ourselves,out,outside,over,overall,owing,own,p,page,pages,part,\
        particular,particularly,past,per,perhaps,placed,please,plus,poorly,possible,possibly,potentially,pp,predominantly,present,\
        previously,primarily,probably,promptly,proud,provides,put,q,que,quickly,quite,qv,r,ran,rather,rd,re,readily,really,recent,\
        recently,ref,refs,regarding,regardless,regards,related,relatively,research,respectively,resulted,resulting,results,right,run,s,\
        said,same,saw,say,saying,says,sec,section,see,seeing,seem,seemed,seeming,seems,seen,self,selves,sent,seven,several,shall,she,shed,\
        she'll,shes,should,shouldn't,show,showed,shown,showns,shows,significant,significantly,similar,similarly,since,six,slightly,so,\
        some,somebody,somehow,someone,somethan,something,sometime,sometimes,somewhat,somewhere,soon,sorry,specifically,specified,specify,\
        specifying,still,stop,strongly,sub,substantially,successfully,such,sufficiently,suggest,sup,sure,t,take,taken,taking,tell,tends,\
        th,than,thank,thanks,thanx,that,that'll,thats,that've,the,their,theirs,them,themselves,then,thence,there,thereafter,thereby,\
        thered,therefore,therein,there'll,thereof,therere,theres,thereto,thereupon,there've,these,they,theyd,they'll,theyre,they've,\
        think,this,those,thou,though,thoughh,thousand,throug,through,throughout,thru,thus,til,tip,to,together,too,took,toward,towards,\
        tried,tries,truly,try,trying,ts,twice,two,u,un,under,unfortunately,unless,unlike,unlikely,until,unto,up,upon,ups,us,use,used,\
        useful,usefully,usefulness,uses,using,usually,v,value,various,'ve,very,via,viz,vol,vols,vs,w,want,wants,was,wasn't,way,we,wed,\
        welcome,we'll,went,were,weren't,we've,what,whatever,what'll,whats,when,whence,whenever,where,whereafter,whereas,whereby,wherein,\
        wheres,whereupon,wherever,whether,which,while,whim,whither,who,whod,whoever,whole,who'll,whom,whomever,whos,whose,why,widely,\
        willing,wish,with,within,without,won't,words,world,would,wouldn't,www,x,y,yes,yet,you,youd,you'll,your,youre,yours,yourself,\
        yourselves,you've,z,zero";
    </script>
        
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.5/js/bootstrap.min.js" integrity="sha384-BLiI7JTZm+JWlgKa0M0kGRpJbF2J8q+qreVrKBC47e3K6BW78kGLrCkeRX6I9RoK" crossorigin="anonymous"></script>
        
    </body>
</html>