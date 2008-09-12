<?php
/*
	Amberdms Billing System
	(c) Copyright 2008 Amberdms Ltd

	www.amberdms.com
	Licenced under the GNU GPL version 2 only.
*/

// includes
include("include/config.php");
include("include/amberphplib/main.php");


// get the page to display
$page = $_GET["page"];
if ($page == "")
	$page = "home.php";

	
// perform security checks on the page
// security_localphp prevents any nasties, and then we check the the page exists.
$page_valid = 0;
if (!security_localphp($page))
{
	$_SESSION["error"]["message"][] = "Sorry, the requested page could not be found - please check your URL.";
}
else
{
	if (!@file_exists($page))
	{
		$_SESSION["error"]["message"][] = "Sorry, the requested page could not be found - please check your URL.";
	}
	else
        {
		$page_valid = 1;
	}
}



// set default page state
if (!$_SESSION["error"]["pagestate"])
	$_SESSION["error"]["pagestate"] = 1;



?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Strict//EN" "http://www.w3.org/TR/REC-html40/strict.dtd">
<html>
<head>
	<title>Amberdms Billing System</title>
	<meta name="copyright" content="(C)Copyright 2008 Amberdms Ltd.">


<script type="text/javascript">

function obj_hide(obj)
{
	document.getElementById(obj).style.display = 'none';
}
function obj_show(obj)
{
	document.getElementById(obj).style.display = '';
}

</script>
	
</head>

<style type="text/css">
@import url("include/style.css");
</style>


<body>


<!-- Main Structure Table -->
<table width="1000" cellspacing="5" cellpadding="0" align="center">



<!-- Header -->
<tr>
	<td bgcolor="#ffbf00" style="border: 1px #747474 dashed;">
		<table width="100%">
		<tr>
			<td width="50%" align="left"><img src="images/amberdms-billing-system-logo.png" alt="Amberdms Billing System"></td>
			<td width="50%" align="right" valign="top">
			<?php

			if ($username = user_information("username"))
			{
				print "<p style=\"font-size: 10px;\"><b>You are logged on as $username | <a href=\"index.php?page=user/logout.php\">logout</a></b></p>";


			}

			?>
			</td>
		</tr>
		</table>
	</td>
</tr>

<tr>
	<td width="100%">

	<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<?php
	/*
		Here we draw the menu. All pages have the top menu displayed, then differing numbers of sub menus, depending
		on the page currently open.

		In future, this should be replaced with a more fancy javascript solution with roll over features, etc.
	*/

	if (user_online())
	{

		if ($page_valid == 1)
		{
			// page is valid and exists
			// this means that the $page value has already been checked to prevent
			// SQL injection or other unwanted problems.

			$parents = array();

			// get the menu item for this page
			$mysql_string		= "SELECT parent FROM `menu` WHERE link='$page' ORDER BY priority DESC";
			$mysql_menu_result	= mysql_query($mysql_string);
			$mysql_menu_num_rows	= mysql_num_rows($mysql_menu_result);

			if ($mysql_menu_num_rows)
			{
				$mysql_menu_data = mysql_fetch_array($mysql_menu_result);
				$parents[]	= $mysql_menu_data["parent"];
				$lastparent	= $mysql_menu_data["parent"];

				// now get the all the other parents
				$mysql_menu_num_rows = 1;
				while ($mysql_menu_num_rows == 1)
				{
					$mysql_string		= "SELECT parent FROM `menu` WHERE topic='$lastparent' ORDER BY priority DESC";
					$mysql_menu_result	= mysql_query($mysql_string);
					$mysql_menu_num_rows	= mysql_num_rows($mysql_menu_result);

					if ($mysql_menu_num_rows)
					{
						$mysql_menu_data = mysql_fetch_array($mysql_menu_result);
						$parents[]	= $mysql_menu_data["parent"];
						$lastparent	= $mysql_menu_data["parent"];
					}
				}

				// we now sort the array, and end up with all the menu
				// levels in order
				$parents = array_reverse($parents);
			}
		}


		// if we have no sub-menu information, just display the top menu.
		if (!$parents)
		{
			$parents[] = "top";
		}

		/*
			Now we display all the menus.

			Note that the "top" menu has the addition of a second column to the right
			for the user perferences/administration box.
		*/
		for ($i = 0; $i <= count($parents); $i++)
		{
			print "<tr>";
			print "<td width=\"100%\" cellpadding=\"0\" cellborder=\"0\" cellspacing=\"0\">";
			print "<ul id=\"menu\">";

			// get an array of all the permissions this user had
			$user_permissions	= array();
			$userid			= user_information("id");
			$mysql_string		= "SELECT permid FROM `users_permissions` WHERE userid='$userid'";
			$mysql_result		= mysql_query($mysql_string);

			while ($mysql_data = mysql_fetch_array($mysql_result))
			{
				$user_permissions[] = $mysql_data["permid"];
			}

			
			// get the data for this menu
			$mysql_string		= "SELECT link, topic, permid FROM `menu` WHERE parent='$parents[$i]' ORDER BY priority";
			$mysql_menu_result	= mysql_query($mysql_string);
			$mysql_menu_num_rows	= mysql_num_rows($mysql_menu_result);
				
			while ($mysql_menu_data = mysql_fetch_array($mysql_menu_result))
			{
				// check that the user has permissions to display this link
				if (in_array($mysql_menu_data["permid"], $user_permissions) || $mysql_menu_data["permid"] == 0)
				{
					// if this entry has no topic, it only exists for the purpose of getting a parent
					// link highlighted. In this case, ignore the current entry.

					if ($mysql_menu_data["topic"])
					{
						// highlight the entry, if it's the parent of the next sub menu, or if this is a sub menu.
						if ($parents[$i + 1] == $mysql_menu_data["topic"] || $mysql_menu_data["link"] == $page)
						{
							print "<li><a style=\"background-color: #7e7e7e;\" href=\"index.php?page=". $mysql_menu_data["link"] ."\" title=". $mysql_menu_data["topic"] .">". $mysql_menu_data["topic"] ."</a></li>";
						}
						else
						{
							print "<li><a href=\"index.php?page=". $mysql_menu_data["link"] ."\" title=". $mysql_menu_data["topic"] .">". $mysql_menu_data["topic"] ."</a></li>";
						}
					}
				}

			}

			print "</ul>";
			print "</td>";
			print "</tr>";
		}

	} // end if user is online
	?>

	
	</table>
	
	</td></tr>

	<?php

	// display section.
        //      - display navigation menu
        //      - display any errors & notifications
        //      - display the page.


	// LOAD THE PAGE AND PROCESS HEADER CODE
	if ($page_valid == 1)
	{
		include($page);
	}


	/*
		DRAW NAVIGATION MENU

		The main application menu is built from the configuration in the MySQL database. However, it is often desirable
		to be able to create a custom menu for the page currently running, for uses such as spliting large pages into
		multiple sections (simular to tabs).
	        
		Usage (example):

			(The following code should go after user permissions verification, but before the page_render function)

			To enable the nav menu on the page:
			> $_SESSION["nav"]["active"] = 1;

			For each menu entry you wish to have, use the following syntax:
			> $_SESSION["nav"]["query"][] = "page=home/home.php";
			> $_SESSION["nav"]["title"][] = "Return to Home";

			To choose which one will be high-lighted when the menu is drawn, specify which
			page URL should be made current:			
			> $_SESSION["nav"]["current"] = "page=home/home.php"

		
	*/	
	if ($_SESSION["nav"]["active"])
	{
		print "<tr>";
		print "<td width=\"100%\" cellpadding=\"0\" cellborder=\"0\" cellspacing=\"0\">";

		print "<ul id=\"navmenu\">";

		        $j = count($_SESSION["nav"]["query"]);
		        for ($i=0; $i < $j; $i++)
		        {
				// are we viewing the current page?
				if ($_SESSION["nav"]["current"] == $_SESSION["nav"]["query"][$i])
				{
					print "<li><a style=\"background-color: #60ae62;\" href=\"index.php?". $_SESSION["nav"]["query"][$i] ."\" title=\"". $_SESSION["nav"]["title"][$i] ."\">". $_SESSION["nav"]["title"][$i] ."</a></li>";
				}
				else
				{
					print "<li><a href=\"index.php?". $_SESSION["nav"]["query"][$i] ."\" title=\"". $_SESSION["nav"]["title"][$i] ."\">". $_SESSION["nav"]["title"][$i] ."</a></li>";
				}
			}

		
		print "</ul>";

		print "</td>";
		print "</tr>";
	}
	
	

        // DRAW ERROR/NOTIFCATION MESSAGES
        if ($_SESSION["error"]["message"])
        {
                print "<tr><td bgcolor=\"#ffeda4\" style=\"border: 1px dashed #dc6d00; padding: 3px;\">";
                print "<p><b>Error:</b><br><br>";

		foreach ($_SESSION["error"]["message"] as $errormsg)
		{
			print "$errormsg<br>";
		}
		
		print "</p>";
                print "</td></tr>";

        }
        elseif ($_SESSION["notification"]["message"])
        {
                print "<tr><td bgcolor=\"#c7e8ed\" style=\"border: 1px dashed #374893; padding: 3px;\">";
                print "<p><b>Notification:</b><br><br>";
		
		foreach ($_SESSION["notification"]["message"] as $notificationmsg)
		{
			print "$notificationmsg<br>";
		}

		print "</p>";
                print "</td></tr>";
        }


	// CENTER DATA
	print "<tr><td bgcolor=\"#ffffff\" style=\"border: 1px #000000 dashed; padding: 5px;\">";
	print "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">";
	

        // DISPLAY THE PAGE (PROVIDING THAT ONE WAS LOADED)
        if ($_SESSION["error"]["pagestate"] && $page_valid)
        {

		// display the page
		print "<td valign=\"top\" style=\"padding: 5px;\">";
                page_render();
                print "<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br></td>";

                // save query string, so the user can return here if they login. (providing none of the pages are in the user/ folder, as that will break some stuff otherwise.)
                if (!preg_match('/^user/', $page))
                {
                        $_SESSION["login"]["previouspage"] = $_SERVER["QUERY_STRING"];
                }
        }
	else
	{
		// draw the content page table column to keep everything neat
                print "<td><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br></td>";
	}

	?>

	</tr>
	</table>

	</td>
	</tr>


<!-- Page Footer -->
<tr>
	<td bgcolor="#ffbf00" style="border: 1px #747474 dashed;">

	<table width="100%">
	<tr>
		<td align="left">
		<p style="font-size: 10px">(c) Copyright 2008 <a href="http://www.amberdms.com">Amberdms Ltd</a>.</p>
		</td>
	</tr>
	</table>
	
	</td>
</tr>

<?php

if ($_SESSION["user"]["log_debug"])
{
	print "<tr>";
	print "<td bgcolor=\"#ffffff\" style=\"border: 1px #000000 dashed;\">";
	print "<p><b>Debug Output:</b></p>";
	
	foreach ($_SESSION["user"]["log_debug"] as $log)
	{
		print "$log<br>";
	}

	print "</td>";
	print "</tr>";
}

?>


</table>

<br><br><br><br><br>
<br><br><br><br><br>
<br><br><br><br><br>
<br><br><br><br><br>
<br><br><br><br><br>
<br><br><br><br><br>

</body></html>


<?php

// erase error and notification arrays
$_SESSION["user"]["log_debug"] = array();
$_SESSION["error"] = array();
$_SESSION["notification"] = array();
$_SESSION["nav"] = array();

?>