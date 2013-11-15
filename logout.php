<?php

	require_once("includes/config.php");
	try {
		# Log the user out
		UserLogin::logout();
		# Now redirect them to the clients index.
		$host  = $_SERVER['HTTP_HOST'];
		$uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
		$extra = 'index.php';
      // Send out a header so Mozilla Account Manager knows what to do
      header('X-Account-Management-Status: none;');
      // Now send out a standard redirect
		header("Location: http://$host$uri/$extra");
	} catch (Exception $e) {
		print '<h1>Ooops! Error :-(</h1>';
		print $e->getMessage();
	}

?>