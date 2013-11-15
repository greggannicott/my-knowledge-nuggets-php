<?php

	// Include pair classes
	require_once("Log.php");

    // Declare the root_dir
    $root_dir = $_SERVER["DOCUMENT_ROOT"];   // in work:  DOCUMENT_ROOT = C:/xampp/xampp/htdocs/mkn/www/
                                             // on homer: DOCUMENT ROOT = /home/gannicott.co.uk/mkn/www/

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
    $SITE['paths']['includes'] = '/includes_new'; // Location of the 'includes' directory


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
	# Combine logs together
	$logger = &Log::singleton('composite');
	$logger->addChild($log_browser);

   // Prepare the ERRORS array.
   // This will be used to display errors that have take place prior to the HTML
   // being printed.
   $ERRORS = array();

	// Establish a MySQL Connection
	$db = new mysqli('localhost','greg','wooky711','knowledge_base');

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
      }
   } else {
      // Login not required, but if the user does have a cookie, lets know it's id
      if (isset($_COOKIE['user_id'])) {
         $user_id = $_COOKIE['user_id'];  // Really the below object should be used but this is here whilst it is phased out
         $current_user = new User($user_id,$db);
      }
   }
?>