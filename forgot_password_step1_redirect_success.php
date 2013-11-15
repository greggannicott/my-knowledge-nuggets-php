<?php
require_once("includes/config.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
   <head>
     <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
     <title><?=$SITE['site_name'];?></title>
     <?php if (isset($user_id)) {print '<link rel="search" type="application/opensearchdescription+xml" title="My Knowledge Nuggets (My Nuggets)" href="http://mkn.gannicott.co.uk/opensearch_my.xml" />';}?>
     <link rel="search" type="application/opensearchdescription+xml" title="My Knowledge Nuggets (All Nuggets)" href="http://mkn.gannicott.co.uk/opensearch_all.xml" />
     <link rel="stylesheet" type="text/css" href="styles/generic.css" />
   </head>
	<body>
      <div id="container">
         <?php include("includes/header.php"); ?>
         <div id="main_content_outter">
            <div id="main_content_inner">
               <h1>Forgotten Password</h1>
               <h2 class="success">The Emails Away!</h2>
               <p>Check your inbox. You should see an email containing the next step required to reset your password.</p>
               <ul>
                  <li><a href="/">Return to Front Page</a></li>
               </ul>
            </div>
         </div>
         <?php require_once("includes/footer.php");?>
      </div>
   </body>
</html>