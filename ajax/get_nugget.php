<?php
require_once("../includes/config.php");
// Disable warnings - they aren't caught by exceptions, but they do prevent the ajax response from working
// This happens when an invalid URL is passed in.
error_reporting(E_ERROR | E_PARSE);
try {
    // Get the nugget's details
   $nugget = new Nugget($_GET['id'],$db);
   //$nugget_tags = new Nugget_Tag_List($db);
   //$nugget_tags->init($nugget->getId());
   //$tags = $nugget_tags->return_tags();
   $output = array(
      type=>'success',
      nugget_details=> array(
         id=>$nugget->getId(),
         title=>$nugget->getTitle(),
         body=>$nugget->getBody(),
         user_id=>$nugget->getUser_id(),
         tags=>$nugget->getTags(),
         dt_last_mod=>date("c",$nugget->getDt_last_mod()),
         dt_created=>date("c",$nugget->getDt_created())
      )
   );
} catch (Exception $e) {
	// Log the error
	$logger->log($e->getMessage(),$e->getCode());
	// Generate some json to return
	$output = array(
	                type=>'error',
	                message=>$e->getMessage()
	               );
	$output = json_encode($output);
  die($output);
}

$output = json_encode($output);
print $output;

?>