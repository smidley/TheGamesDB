<?php	## Interface that allows clients to add series to their favorites
	## Parameters:
	##   $_REQUEST["accountid"]
	##   $_REQUEST["seriesid"]
	##   $_REQUEST["type"] (add|remove|null)
	##
	## Returns:
	##   Success

	## Include functions, db connection, etc
	include("include.php");
?>
<?php
	## Prepare the search string
	$accountid		= $_REQUEST["accountid"];
	$seriesid		= $_REQUEST["seriesid"];
	$type			= $_REQUEST["type"];
	if ($accountid == "")  {
		print "<Error>accountid is required</Error>\n";
		exit;
	}
	elseif (($type == "add" || $type == "remove") && $seriesid == "")  {
		print "<Error>seriesid is required for adding and deleting</Error>\n";
		exit;
	}
	else  {
		print "<Favorites>\n";
	}


	## Run the query
	$query = "SELECT id, favorites FROM users WHERE uniqueid='$accountid' LIMIT 1";
	$result	= mysql_query($query) or die('Query failed: ' . mysql_error());
	$db = mysql_fetch_object($result);


	## If we've found a valid user
	if ($db->id)  {
		## Split their current favorites into an array, add new item,
		## and rejoin list
		$favorites = array();
		if ($type == "remove")  {
			$temp = explode(",", $db->favorites);
			foreach ($temp AS $id)  {
				if ($id != $seriesid)  {
					array_push($favorites, $id);
				}
			}
			$favorites = array_unique($favorites);
			$favoriteslist = implode(",", $favorites);

			## Insert replacement value into database
			$query	= "UPDATE users SET favorites='$favoriteslist' WHERE id=$db->id";
			$result	= mysql_query($query) or die('Query failed: ' . mysql_error());

			## Print new favorites list
			foreach ($favorites AS $id)  {
				print "<Series>$id</Series>\n";
			}
		}
		elseif ($type == "add")  {
			$favorites = explode(",", $db->favorites);
			array_push($favorites, $seriesid);
			$favorites = array_unique($favorites);
			$favoriteslist = implode(",", $favorites);

			## Insert replacement value into database
			$query	= "UPDATE users SET favorites='$favoriteslist' WHERE id=$db->id";
			$result	= mysql_query($query) or die('Query failed: ' . mysql_error());

			## Print new favorites list
			foreach ($favorites AS $id)  {
				print "<Series>$id</Series>\n";
			}
		}
		else  {
			## Print new favorites list
			$temp = explode(",", $db->favorites);
			foreach ($temp AS $id)  {
				print "<Series>$id</Series>\n";
			}
		}

	}
?>
</Favorites>
