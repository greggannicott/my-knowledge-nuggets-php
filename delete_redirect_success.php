<?php
require_once("includes/config.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
   <head>
     <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
     <title><?=$SITE['site_name'];?> - Delete Nugget</title>
     <link rel="search" type="application/opensearchdescription+xml" title="My Knowledge Nuggets" href="http://mkn.gannicott.co.uk/opensearch_all.xml" />
     <link rel="stylesheet" type="text/css" href="styles/generic.css" />
     <script type="text/javascript" src="/javascript/jquery-1.3.2.js"></script>
     <script type="text/javascript" src="/javascript/jquery.hotkeys-0.7.9.min.js"></script>
     <!-- Include global keyboard shortcuts -->
     <?php require_once('includes/global_keyboard_shortcuts.php');?>
   </head>
   <body>
      <div id="container">
         <?php include("includes/header.php"); ?>
         <div id="main_content_outter">
            <div id="main_content_inner">
                 <h1>Delete a New Nugget</h1>
                 <h2 class="success">Yay... Success!</h2>
                 <p>The Nugget has been deleted.</p>
                 <h2>Now what would you like to do?</h2>
                 <ul>
                     <li><a href="/users/<?=$current_user->getUsername();?>/nuggets/1/">View My Nuggets</a></li>
                     <li><a href="index.php">Go to the Homepage</a></li>
                     <li><a href="add.php">Create a Brand New Nugget</a></li>
                 </ul>
            </div>
         </div>
         <?php require_once("includes/footer.php");?>
      </div>
   </body>
</html>