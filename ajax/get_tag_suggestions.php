<?php
require_once("../includes/config.php");
// Disable warnings - they aren't caught by exceptions, but they do prevent the ajax response from working
// This happens when an invalid URL is passed in.
error_reporting(E_ERROR | E_PARSE);
$json = array();
$data = array();
$suggestions = Nugget_Store::suggest_tags($_GET['term'], $current_user->getId(), $db);
print json_encode($suggestions);

?>