<?php

	###=============###
	###PREREQUISITES###
	###--------------------------###
	
	## Include base functions, db connection, etc
	include("include.php");
	
	## Get requested game id from api call
	$requestedID = $_REQUEST['id'];
	
	if (empty($id) || !is_numeric($id)) {
    print "<Error>An integer formatted id is required</Error>\n";
    exit;
	}
	
	###==============###
	###VITAL FUNCTIONS###
	###----------------------------###
	
	## Function to generate a fanart thumb image if does not already exist
	function makeFanartThumb($sourcefile, $targetfile) {

        ## Get the image sizes and read it into an image object
        $sourcefile_id  = imagecreatefromjpeg($sourcefile);
        $width          = imageSX($sourcefile_id);
        $height         = imageSY($sourcefile_id);

        ## Settings
        //$scale          = 0.1;
		$destWidth = 300;
		$destHeight = 169;

        ## Create a new destination image object - for scale resize replace $destWidth, $destHeight with: $width * $scale, $height * $scale
        $result_id      = imagecreatetruecolor($destWidth, $destHeight);

        ## Copy our source image resized into the destination object - for scale resize replace $destWidth, $destHeight with: $width * $scale, $height * $scale
        imagecopyresampled($result_id, $sourcefile_id, 0, 0, 0, 0, $destWidth, $destHeight, $width, $height);

        ## Return the JPG
        imagejpeg ($result_id, $targetfile, 90);

        ## Wrap it up
        imagedestroy($sourcefile_id);
        imagedestroy($result_id);
	}
	
	
	## Function to process all fanart for the requested game id
	function processFanart($gameID)
	{
		## Select all fanart rows for the requested game id
		$faResult = mysql_query(" SELECT filename FROM banners WHERE keyvalue = $gameID AND keytype = 'fanart' ORDER BY filename ASC ");
		
		## Process each fanart row incrementally
		while($faRow = mysql_fetch_assoc($faResult))
		{
			## Construct file names
			$faOriginal = $faRow['filename'];
			$faVignette = str_replace("original", "vignette", $faRow['filename']);
			$faThumb = str_replace("original", "thumb", $faRow['filename']);
		
			## Check to see if the original fanart file actually exists before attempting to process 
			if(file_exists("../banners/$faOriginal"))
			{			
				## Check if thumb already exists
				if(!file_exists("../banners/$faThumb"))
				{					
					## If thumb is non-existant then create it
					makeFanartThumb("../banners/$faOriginal", "../banners/$faThumb");
				}
				
				## Get Fanart Image Dimensions
				list($image_width, $image_height, $image_type, $image_attr) = getimagesize("../banners/$faOriginal");
				$faWidth = $image_width;
				$faHeight = $image_height;
				
				## Output Fanart XML Branch
				print "<fanart>\n";
					print "<original width=\"$faWidth\" height=\"$faHeight\">$faOriginal</original>\n";
					print "<vignette width=\"$faWidth\" height=\"$faHeight\">$faVignette</vignette>\n";
					print "<thumb>$faThumb</thumb>\n";
				print "</fanart>\n";
			}
		}
	}
	
	function processBoxart($gameID)
	{
		## Select all boxart rows for the requested game id
		$baResult = mysql_query(" SELECT filename FROM banners WHERE keyvalue = $gameID AND keytype = 'boxart' ORDER BY filename ASC ");
		
		## Process each boxart row incrementally
		while($baRow = mysql_fetch_assoc($baResult))
		{
			## Construct file names
			$baOriginal = $baRow['filename'];
			
			$type  = (preg_match('/front/', $baOriginal)) ? 'front' : 'back';
		
			## Check to see if the original boxart file actually exists before attempting to process 
			if(file_exists("../banners/$baOriginal"))
			{
				## Get boxart image dimensions
				list($image_width, $image_height, $image_type, $image_attr) = getimagesize("../banners/$baOriginal");
				$baWidth = $image_width;
				$baHeight = $image_height;
				
				## Output Boxart XML Branch
				echo "<boxart side=\"$type\" width=\"$baWidth\" height=\"$baHeight\">$baOriginal</boxart>\n";
			}
		}
	}
	
	function processBanner($gameID)
	{
		## Select all boxart rows for the requested game id
		$banResult = mysql_query(" SELECT filename FROM banners WHERE keyvalue = $gameID AND keytype = 'series' ORDER BY filename ASC ");
		
		## Process each boxart row incrementally
		while($banRow = mysql_fetch_assoc($banResult))
		{
			## Construct file names
			$banOriginal = $banRow['filename'];
		
			## Check to see if the original boxart file actually exists before attempting to process 
			if(file_exists("../banners/$banOriginal"))
			{			
				## Output Boxart XML Branch
				echo "<banner width=\"760\" height=\"140\">$banOriginal</banner>";
			}
		}
	}
	
	
	
	###===============###
	###MAIN XML OUTPUT###
	###-----------------------------###
	
	
	print "<Data>\n";
	
	
	print "<baseImgUrl>http://thegamesdb.net/banners/</baseImgUrl>\n";
	
	## Open Images XML Branch
	print "<Images>\n";
	
	processFanart($requestedID);
	processBoxart($requestedID);
	processBanner($requestedID);
	
	## Close Images XML Branch
	print "</Images>\n";
	
	
	print "</Data>";
?>