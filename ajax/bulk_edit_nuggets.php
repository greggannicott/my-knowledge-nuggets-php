<?php
require_once("../includes/config.php");
// Disable warnings - they aren't caught by exceptions, but they do prevent the ajax response from working
// This happens when an invalid URL is passed in.
//error_reporting(E_ERROR | E_PARSE);
try {

   $action = $_POST['action'];
   $nuggets = $_POST['nuggets'];
   if (isset($_POST['bulk_edit_add_tags'])) {$tags = $_POST['bulk_edit_add_tags'];}
   $message = '';

   // Do some validation
   if (count($nuggets) == 0) {
      throw new Exception("Unable to perform bulk action. No Nuggets have been selected.");
   }
   if ($action == 'add_tag' && isset($tags) == null) {
      throw new Exception("Unable to add tags as a tag has not been provided.");
   }

   // Run through a list of nuggets and perform the desired actions
   foreach ($nuggets as $nugget) {
      $nugget = new Nugget($nugget,$db);
      switch($action) {
         case 'delete':
            $nugget->delete();
            $message = "Your selected nuggets have been deleted.";
            break;
         case 'make_public':
            $nugget->setPublic(1);
            $nugget->update();
            $message = "Your selected nuggets have been made public.";
            break;
         case 'make_private':
            $nugget->setPublic(0);
            $nugget->update();
            $message = "Your selected nuggets have been hidden from the public.";
            break;
         case 'add_tag':
            $nugget->add_tags($tags);
            $nugget->update();
            $message = "The tags have been added to your selected nuggets.";
            break;
         default:
            throw new Exception("Unable to apply bulk edit to ".$nugget.": Invalid action provided.");
      }
      $nugget = null;
   }
   $output = array(
      type=>'success',
      action=>$action,
      message=>$message,
      nuggets=>$nuggets
   );
} catch (Exception $e) {
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