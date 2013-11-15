<?php

	// Include pair classes
	require_once("Log.php");

    // Declare the root_dir
    $root_dir = $_SERVER["DOCUMENT_ROOT"];   

    // Include helper functions
   require_once($root_dir."/includes/functions.php");

   // Include in-house classes

    require_once($root_dir."/classes/Nugget.php");
    require_once($root_dir."/classes/Nugget_Related_Links.php");
    require_once($root_dir."/classes/Nugget_Store.php");
    require_once($root_dir."/classes/User.php");
    require_once($root_dir."/classes/UserLogin.php");
    require_once($root_dir."/classes/Search.php");

    // Site Wide Variables
    $SITE['site_name'] = 'Knowledge Base';
    $SITE['search']['results_per_page'] = 10;
    $SITE['search']['max_chars_displayed'] = 200;
    $SITE['search']['pagination']['pages_to_display'] = 5; // Number of links to other pages to display
    $SITE['search']['tag_multiplier'] = 1000; // used to give additional weight to tag matches
    $SITE['search']['search_results_container_hint_text'] = "Press the 'enter' key to view nugget.";

    // Pages that require a login to view
    $secure_pages = array(
       'add.php'
       , 'add_redirect_error.php'
       , 'add_redirect_success.php'
       , 'edit.php'
       , 'user_history.php'
       , 'settings.php'
       , 'settings_profile.php'
       , 'settings_web_hooks.php'
       , 'settings_delete.php'
       , 'settings_password.php'
    );

	// Setup the logging
	# Browser output
	$conf = array('error_prepend' => '<font color="#ff0000"><b>Ooops!</b></font><br><font color="#666666">','error_append'  => '</font>');
	$log_browser = &Log::singleton('display', '', '', $conf, PEAR_LOG_INFO);
   # File output
   $conf = array('lineFormat' => '%{timestamp} [%{priority}]  %{message}');   # http://www.indelible.org/php/Log/guide.html#log-line-format
   $log_file = &Log::singleton('file', 'logs/debug_'.date('y_m_d').'.log', '', $conf, PEAR_LOG_DEBUG);
	# Combine logs together
	$logger = &Log::singleton('composite');
	$logger->addChild($log_browser);
	$logger->addChild($log_file);

   // Prepare the ERRORS array.
   // This will be used to display errors that have take place prior to the HTML
   // being printed.
   $ERRORS = array();

	// Establish a MySQL Connection
	$db = new mysqli('localhost','greg','','knowledge_base');

   // Check whether the user needs to be logged in to view the current page
   // If they do, see whether they are logged in and redirect accordingly
   // Otherwise, note there user_id if they have a cookie
   $current_page = basename($_SERVER['PHP_SELF']);
   $login_required = false;
   $user_id = null;
   foreach ($secure_pages as $page) {
      if ($current_page == $page) {$login_required = true; break;}
   }
   if ($login_required == true) {
      if (UserLogin::perform_login_check()) {
         # If we've got this far, we know the user really exists.
         try {
            $user_id = $_COOKIE['user_id'];  // Really the below object should be used but this is here whilst it is phased out
            $current_user = new User($user_id,$db);
         } catch (Exception $e) {
            print '<p class="pageHeadingError">Ooops... Error!</p>';
            print $e->getMessage();
         }
         // For logging purposes, note whether the user is logged in
         $tmp_user_logged_in = "true";
      } else {
         // For logging purposes, note whether the user is logged in
         $tmp_user_logged_in = "true";
      }
   } else {
      // Login not required, but if the user does have a cookie, lets know it's id
      if (isset($_COOKIE['user_id'])) {
         $user_id = $_COOKIE['user_id'];  // Really the below object should be used but this is here whilst it is phased out
         $current_user = new User($user_id,$db);
      }
   }

   // Create the header of the log entry for this session:
   $logger->log("===============================================================",PEAR_LOG_DEBUG);
   $logger->log("Script File Name: ".$_SERVER["SCRIPT_FILENAME"],PEAR_LOG_DEBUG);
   $logger->log("Query String: ".$_SERVER['QUERY_STRING'],PEAR_LOG_DEBUG);
   $logger->log("Request Method: ".$_SERVER['REQUEST_METHOD'],PEAR_LOG_DEBUG);
   $logger->log("HTTP Cookie: ".$_SERVER['HTTP_COOKIE'],PEAR_LOG_DEBUG);
   $logger->log("User Logged In: ".$tmp_user_logged_in,PEAR_LOG_DEBUG);
   $logger->log("---------------------------------------------------------------",PEAR_LOG_DEBUG);
?>
