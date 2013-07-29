<?php
//Part 1
if (isset($_GET['weburl']))
{
  	
	//http://www.dcs.bbk.ac.uk/~martin/sewn/ls3/testpage.html
	$weburl = htmlentities(trim($_GET['weburl']));
	
	//Get the root.
	$rootdir = dirname($weburl);
	
	// Create DOM from URL or file
	$html = file_get_contents($weburl);
	
	/*** a new dom object ***/
    $dom = new domDocument;
	
	/*** load the html into the object ***/
    $dom->loadHTML($html);
	
	/*** discard white space ***/
    $dom->preserveWhiteSpace = false;
		
	
	$logFile = "log.txt";
	$fp = fopen($logFile, 'w') or die("can't open file");
	
	// Find all links
	foreach ($dom->getElementsByTagName('a') as $node)
	{
		//echo $node->nodeValue.': '.$node->getAttribute("href")."\n";
	   $url= $node->getAttribute("href");
	   echo $url . '<br>'; 
	   fwrite($fp, "$url\r\n");
	}

	//Close the file
	fclose($fp);
    
}


?>
<html>
	<head>
	</head>
		<body>
			<form>
				<input type="text" name="weburl" value="http://www.dcs.bbk.ac.uk/~martin/sewn/ls3/testpage.html"><br />
				<input type="submit">
			</form>
		</body>
</html>