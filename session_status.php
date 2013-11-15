<?php

   // Used to determine if user is logged in.
   // Written initially for Mozilla's Account Manager software
   $session_status = null;

	require_once("includes/config.php");
	if (isset($current_user)) {
      header('X-Account-Management-Status: active; name="'.$current_user->getUsername().'"');
   } else {
      header('X-Account-Management-Status: none;');
   }

?>