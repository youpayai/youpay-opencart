<?php
/**
 * Usage: Update the $path to your callback script and set it to receive $_GET or $_REQUEST params
 *
 * This is a redirect script used when callbacks from API integrations don't allow query data back in the url
 * It works by converting all &_REQUEST vars to $_GET and pass them back to the real callback script internally
 * This is also often needed when callback is stopped due to disallowing of server redirect.
 * Usually in that case, a javascript form redirect needs to be created to fool their system.
*/
$host  = $_SERVER['HTTP_HOST'];
$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$path  = 'index.php?route=/extension/payment/youpay/callback';
$query = "";

if (isset($_REQUEST) && $_REQUEST) {
	foreach($_REQUEST as $key => $value) {
		$query .= '&' . $key . '=' . urlencode($value);
	}
	$query = rtrim($query, '&');
	//die('http://' . $host . $uri . '/' . $path . $query . '');
	//header("Location: http://$host$uri/$path$query");
	echo '<script language="Javascript">window.location="https://' . $host . $uri . '/' . $path . $query . '"</script>';	
} else { // Assume URLFail if there are no GET vars
	$path  = 'index.php?route=checkout/error';
	$_SESSION['error'] = 'Callback Jump Script Failed. No $_REQUEST vars found.';
	header("Location: http://$host$uri/$path");
}
//die('This file cannot be accessed directly');
?>