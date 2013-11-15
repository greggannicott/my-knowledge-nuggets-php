<?php

// Candidate for a helper class:
function extract_page_title($url) {
    if (!isset($url)) {throw new Exception("Unable to extract page title. No URL provided");}
    
    $fp = file_get_contents($url);
    
    if (!$fp) {return null;}

    $res = preg_match("/<title>(.*?)<\/title>/sim", $fp, $title_matches);
    if (!$res) {return null;} 

    $title = $title_matches[1];
    return ($title);
}

// Perform a web_hook post using CURL
function web_hook_post($url, $fields) {
   // url-ify the data for the POST
   foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
   rtrim($fields_string,'&');

   //open connection
   $ch = curl_init();

   //set the url, number of POST vars, POST data
   curl_setopt($ch,CURLOPT_URL,$url);
   curl_setopt($ch,CURLOPT_POST,count($fields));
   curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);

   //execute post
   $result = curl_exec($ch);

   //close connection
   curl_close($ch);

   return ($result);
}

// Strip http(s) from address
function remove_http($url) {
   return ereg_replace("(https?)://", "", $url);
}

?>
