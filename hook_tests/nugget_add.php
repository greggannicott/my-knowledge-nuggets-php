<?php

// Read in the post data and write out to a file
$myFile = "nugget_add.txt";
$fh = fopen($myFile, 'w') or die("can't open file");

$stringData = "Hook Action:\n----------------\n";
$stringData .= $_POST['hook_action']."\n\n";

$stringData .= "ID:\n----------------\n";
$stringData .= $_POST['id']."\n\n";

$stringData .= "Title:\n----------------\n";
$stringData .= $_POST['title']."\n\n";

$stringData .= "Body:\n----------------\n";
$stringData .= $_POST['body']."\n\n";

$stringData .= "Tags:\n----------------\n";
$stringData .= $_POST['tags']."\n\n";

$stringData .= "Public:\n----------------\n";
$stringData .= $_POST['public']."\n\n";

$stringData .= "Draft:\n----------------\n";
$stringData .= $_POST['draft']."\n\n";

$stringData .= "Hits by owner:\n----------------\n";
$stringData .= $_POST['hits_by_owner']."\n\n";

$stringData .= "Hits by others:\n----------------\n";
$stringData .= $_POST['hits_by_others']."\n\n";

$stringData .= "URL:\n----------------\n";
$stringData .= $_POST['url']."\n\n";

fwrite($fh, $stringData);
fclose($fh);

?>
