<form method="post">
Crawl site: <input type="text" name="weburl" value="http://www.dcs.bbk.ac.uk/~martin/sewn/ls3/" style="width:300px">
<input value="Crawl Me" type="submit">
</form>


<?php

/****************************************
Web Crawler by Sachith Dassanayake
http://www.dcs.bbk.ac.uk/~mdassa02/
sachithd@gmail.com
****************************************/




if (isset($_POST['weburl']) && !empty($_POST['weburl'])) 
{

//ini_set( "display_errors", 0);

set_time_limit (0); 


/***********************************************
Function to remove line break, carraige return from a string
**********************************************/

function removeEmptyLines($string)
{
	return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $string);
}


/*****************************************************
Function to return absolute URLs from the relative URLs
Refered: http://www.web-max.ca/PHP/misc_24.php
*****************************************************/

function InternetCombineUrl($absolute, $relative) {
        
		
		$p = parse_url($relative);
		
		
        if(isset($p["scheme"]))return $relative;
        
		
        extract(parse_url($absolute));
        
        $path = dirname($path); 
		//echo "path is :" . $path . "<br>";
		//echo  "relative is :" . $relative . "<br>";
		
        if($relative{0} == '/') {
            $cparts = array_filter(explode("/", $relative));
        }
        else {
            $aparts = array_filter(explode("/", $path));
            $rparts = array_filter(explode("/", $relative));
            $cparts = array_merge($aparts, $rparts);
            foreach($cparts as $i => $part) {
                if($part == '.') {
                    $cparts[$i] = null;
                }
                if($part == '..') {
                    $cparts[$i - 1] = null;
                    $cparts[$i] = null;
                }
            }
            $cparts = array_filter($cparts);
        }
        $path = implode("/", $cparts);
        $url = "";
        if($scheme) {
            $url = "$scheme://";
        }
        if(isset($user)) {
            $url .= "$user";
            if($pass) {
                $url .= ":$pass";
            }
            $url .= "@";
        }
        if($host) {
            $url .= "$host/";
        }
        $url .= $path;
        return $url;
}




/************************************************************************
Function to read robot.txt file and returns the disallowed URLs in an arry
Refered http://www.sphider.eu/forum/read.php?3,2740
************************************************************************/
function readRobottxt($rurl)
{	
	//Variable declaration
	$permissionHash = array();
	$currentAgent = ''; 		
	$disallows = array();
	
	$robotstxt=False;
	
	//check if robot.txt file exists
    $handle = @fopen($rurl."/robots.txt",'r');
    
	if($handle !== false)
	{
		$robotstxt=True;
    }
    else
	{
		$robotstxt=False;
    }

	if ($robotstxt) //If robot txt exisits
	{
		//Read the robots.txt
		$robotshtml = file_get_contents($rurl."/robots.txt");
		$robot = explode("\n", $robotshtml); //create array separate by new line
	
		
		//Loop through the robot.txt file
		foreach ($robot as $line)
		{	
			$newLine = removeEmptyLines($line);
			$currentAgent='*';
			
			if (!empty($newLine))
			{
			
			
			//Ignoring the user agents for this course work. Reading only the instructions provided for any user agent (*)
			
			/**
			 * If the user agent is initialized, and it's not * or our
			 * user agent then we'll ignore it.
			 */
			/*if(!($currentAgent === '' || $currentAgent === '*' 
				|| $currentAgent === $user_agent)) {	
					continue;
			}*/
	
			/* Ignore any commented lines. */
			if(strpos(trim($line), '#') === 0) 
			{
				continue;
			} else 
			{
				/* Check for embedded comments, throw them out as well. */
				$commentSeparationArray = explode('#', $line);
				$line = $commentSeparationArray[0];
			}
			
	
			/* Extract key value pair from each line. */
			list($key, $value) = explode(':', $line);
			
			
			
			/* If we have a user agent line, then we can change the current agent. */
			/*if(strtolower($key) == 'user-agent') {
				$currentAgent = trim($value);
				$permissionHash[$currentAgent]['allow'] = array();
				$permissionHash[$currentAgent]['disallow'] = array();
			
			/*
			/* If we have an allow directive, push it onto permission hash. */
			/*} else */
			
			if(strtolower($key) == 'allow') 
			{
				if(trim($value) != '')
					$permissionHash[$currentAgent]['allow'][] = rtrim($rurl, '/').trim($value);
			
			/* If we have a disallow directive, push it onto permission hash. */
			} else if (strtolower($key) == 'disallow') 
			{
				if(trim($value) != '')
					$permissionHash[$currentAgent]['disallow'][] = rtrim($rurl, '/').trim($value);
			}
				
		}
				
		}//End of for each
		$disallows = $permissionHash['*']['disallow']; //Returns the disallowed (disallowed list for any user agent)
	}	
		/* Return the disallows */
		return $disallows;	
}


/******************************************
Function to retreive the content of a URL
*******************************************/

function get_url_contents($url)
{
	$crl = curl_init();
	$timeout = 5;
	curl_setopt ($crl, CURLOPT_URL,$url);
	curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
	$ret = curl_exec($crl);
	curl_close($crl);
	return $ret;
}

/********************************************************************************
A function to detect full/absolute URLs

**********************************************************************************/

function is_absoluteurl($path) {
    // Check if it begins with a protocol specification:
    if (preg_match('|^[a-zA-Z]+://|', $path)) {
        return true;
    } else {
        return false;
    }
}



/*************************************
Function to detect the root directory
*************************************/

function rootdir($weburl)
{

  	$rootdir = "";
	//http://www.dcs.bbk.ac.uk/~martin/sewn/ls3/testpage.html
	$weburl = htmlentities($weburl);
	
	//Get the root directory (As required by the course work)
	$temp = explode("/", $weburl);
	for($i = 0; $i < sizeof($temp) - 1; $i++) {
		$rootdir .=  $temp[$i] . "/";
	}
	return $rootdir ;

}

/****************************************
Function to write a string to a give file
****************************************/

function writeFile($stringData, $fh)
{
	fwrite($fh, $stringData);
}


/****************************************
Function to open a file
****************************************/

function openFile($fileName)
{
	$fh = fopen($fileName, 'a') or die("can't open file");
	return $fh;
}

/*********************************************
Function to close a file
********************************************/

function closeFile($fileHandler)
{
	fclose($fileHandler);
}




/*********************************************
Function to open a file. Starts from scratch
********************************************/
function initializeFile($myFile)
{
	$fh = fopen($myFile, 'w') or die("can't open file");
	fclose($fh);
}




/*****************************************************
Function to returns the hyperlinks on a HTML document
*****************************************************/

function extractLinks($html)
{
	$tempLinks = array();
	$dom = new DOMDocument;
	@$dom->loadHTML($html); //Suspress pages with mark up errors
	
	foreach ($dom->getElementsByTagName('a') as $node)
	{
	 // echo $node->nodeValue.': '.$node->getAttribute("href")."\n";
	  $tempLinks[] = trim($node->getAttribute("href"));
	}
	
	//Remove empty and null URLs
	foreach ($tempLinks as $key => $value) {
      if (is_null($value) || $value=="") {
        unset($tempLinks[$key]);
      }
    }
	
	
	return $tempLinks;
}





/**************************************************************
Function to compare the URL with the robot.txt disallowed list
*************************************************************/
function checkForDisallows($url,$rootURL)
{
	
	$disallowed = false;
	$robotDisallowedLst = readRobottxt($rootURL);
		
	foreach ($robotDisallowedLst as $value) 
	{
		//echo "Value: $value<br />\n";
		if(contains($value,$url))
		{
			$disallowed = True;
			break;
		}
		
	}	
	
	return $disallowed;
}

/**************************************************************
Function to compare the URL with invalid file extentions
*************************************************************/
function checkForDisallowedFileTypes($url)
{
	
	$disallowed = false;
	$filesDisallowedList = array(".css");
		
	foreach ($filesDisallowedList as $value) 
	{
		//echo "Value: $value<br />\n";
		if(contains($value,$url))
		{
			$disallowed = True;
			break;
		}
		
	}	

	
	
	return $disallowed;
}



/*****************************************************************************
This function accept a URL 
crawls the page and returns the URLs in an array (Returns only the valid URLs)
*****************************************************************************/
function returnLinks($linkstodig)
{

	global $page_data;
	$urlList = array();

	$url=$linkstodig;
	
	$rootURL=$_POST['weburl'];
	
	$crawlFileHandler = openFile("crawl.txt");
	$invalidFileHandler = openFile("invalid.txt");
	$blockedFileHandler = openFile("robotblocked.txt");
	$notcrawledFileHandler = openFile("notcrawled.txt");
	

	if(contains($rootURL,$url)) 							//Check if the root exists in the URL else do not crawl
	{
		if(!(checkForDisallows($url,$rootURL))) 			//Check if the URL is disallowed in the robots.txt file (Only crawl allowed URLs)
		{
			if(url_exists($url)) 							//Check if the URL is a valid URL
			{
				if(!(checkForDisallowedFileTypes($url))) 	// Check for file types if we need to block certain file extentions
				{
					writeFile("<Visited " . $url . ">\r\n",$crawlFileHandler); //Starts to write the file with validated URLs
					$page_data=get_url_contents($url);
					

					$datac = extractLinks($page_data); 		//Call the function to extract links from a page
						
					$urlModified = $url . "sachith.html"; 	//Add a dummy path to make 
					//echo "modified URL is : " . $urlModified . "<br>";
					

					foreach($datac AS $dfile) 
					{	
						
						/*if(contains("../",$dfile))
						{
							$extention = InternetCombineUrl($urlModified,$dfile);
						}
						else if(contains("./",$dfile))
						{
							$extention = str_replace("./", '',$dfile); // hidden folders
							$extention = InternetCombineUrl($urlModified,$extention);
							echo $extention;
						}
						else
						{
							
						}*/
					//	echo "debug: " . $urlModified ."\t" . $dfile . "<br>";
						
						$extention = InternetCombineUrl($urlModified,$dfile);
						
						//echo "dfile: " . $dfile . "<br>";
						
							// resolve relative URLs							
						//echo  $extention . "   "  . $urlModified . "<br>";
						//$extention = str_replace("../", '', $dfile);
						
						
						if(!(contains("mailto:",$extention)||contains("#",$extention))) //Remove email address and urls with bookmarks
						{			
						
						/*	if(!(is_absoluteurl($dfile))) //If its a relative URL
								{
									
									$newURL = ltrim($extention, '/');	//Remove links starts with / at the begining
									$newURL = ltrim($newURL , '.');	//Remove links starts with . at the begining
									$newURL = rootdir($url).$newURL;
									
								}
							else
								{
									$newURL = $extention;
								}*/
							$newURL = $extention; //Commented the above after implementing InternetCombineUrl
							writeFile("\t<Link " . $newURL . ">\r\n",$crawlFileHandler);	//write to the file links on the visited page
							$urlList[]=$newURL;
						}

					}
					writeFile("\r\n",$crawlFileHandler);
				}
			}
			else
			{
				writeFile("$url\r\n",$invalidFileHandler); //links coudn't open
			}
		
			unset($datac); //Clear the variables
			return $urlList;
		}
		else
		{
			writeFile("$url\r\n",$blockedFileHandler); //links blocked by Robots
		}
	}
	else
	{	
		writeFile("$url\r\n",$notcrawledFileHandler); //links not crawled
	}
	
	closeFile($crawlFileHandler);
	closeFile($invalidFileHandler);
	closeFile($blockedFileHandler);
	closeFile($notcrawledFileHandler);
	
	
	
	
	
}


/********************************
Check if the URL is a valid URL
*********************************/ 

function url_exists($durl)
{
		$handle   = curl_init($durl);
		if (false === $handle)
		{
			return false;
		}
		curl_setopt($handle, CURLOPT_HEADER, true);
		curl_setopt($handle, CURLOPT_FAILONERROR, true);  // this works
		curl_setopt($handle, CURLOPT_HTTPHEADER, Array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15") );
		curl_setopt($handle, CURLOPT_NOBODY, true);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
		$connectable = curl_exec($handle);
		curl_close($handle);  
        if (stripos(substr_replace($connectable,'',30),'200 OK')) 
		{
            return true;
        } 
		else 
		{
			return false;
        }
}

/***************************************************
Function to check if a string contains a substring
***************************************************/

function contains($substring, $string)
{
	$pos = strpos($string, $substring);
	if($pos === false) {
			return false;
	}
	else {
			return true;
	}
}
 

/**********************************
Outputs the URLs  
**********************************/
function generateURLs($url) 
{
    echo $url.'<br>';
	global $page_data; //html content 
	// html can be used in future to develop 
	unset( $page_data);
}
 
 
 /***************************
 Search engine starts here
 ****************************/
 

	//Initial Link
	$rootdir = $_POST['weburl'];

	//Start a new file
	initializeFile("crawl.txt"); 		//Crawled URLs with links
	initializeFile("notcrawled.txt"); 	//Not crawled outside the domain
	initializeFile("robotblocked.txt");		//URLs blocked by robot.txt
	initializeFile("invalid.txt");		//Couldn't open the URLs
	
	
	$sites = array();
	$myLinks = array();
	
	$sites[]=stripslashes($rootdir);
	for ($i=0;isset($sites[$i]);$i++) 
	{
		$myLinks = returnLinks(stripslashes($sites[$i]));	
		if(isset($myLinks))
		{
			foreach ($myLinks AS $val) 
			{
				if (!isset($sites[$val])) 
				{
					$sites[]=$val;
					//echo $val;
					$sites[$val]=true;
				}
			} 
			unset($val);
			
			if (url_exists($sites[$i])) 
			{
				generateURLs($sites[$i]);
				flush();
			}
		}
	}

} //End of posted variable check
?>