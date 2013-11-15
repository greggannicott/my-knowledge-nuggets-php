<?php
require_once("../includes/config.php");
require_once("../includes/functions.php");
// Disable warnings - they aren't caught by exceptions, but they do prevent the ajax response from working
// This happens when an invalid URL is passed in.
error_reporting(E_ERROR | E_PARSE); 
try {
    // Make sure the URL has http:// or https:// prefixed:
    $url = Nugget_Related_Links::prefix_http_protocol($_GET['url']);
    $page_title = extract_page_title($url);
    if ($page_title != null) {
        $output = array(
                        type=>'success',
                        title=>$page_title
                       );
    } else {
        $output = array(
                        type=>'error',
                        message=>'Unable to determine page title.'
                       );
    }
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