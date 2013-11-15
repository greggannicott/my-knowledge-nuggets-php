<?php
require_once("includes/config.php");
// Check that we have an ID
if (isset($_GET['submit'])) {
   try {
      if ($_GET['id'] != '') {
         $nugget = new Nugget($_GET['id'],$db);
         // Check that the user attempting to delete this nugget owns the nugget
         if ($nugget->getUser_id() == $current_user->getId()) {
         $nugget->delete();
         // Redirect the user to the confirmation page
         $host  = $_SERVER['HTTP_HOST'];
         $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
         $extra = 'delete_redirect_success.php';
         header("Location: http://$host$uri/$extra");
         exit;
         } else {
            throw new Exception ("Unable to delete the Nugget. This isn't your Nugget to delete.");
         }
      } else {
         throw new Exception ("Unable to delete the Nugget. No ID provided.");
      }
   } catch (Exception $e) {
      // Redirect the user to the error page
      $host  = $_SERVER['HTTP_HOST'];
      $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
      $extra = 'delete_redirect_error.php?error_message='.$e->getMessage();
      header("Location: http://$host$uri/$extra");
      exit;
   }
}
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
                 <h1>Delete Nugget</h1>
                 <p>Are you sure you want to delete this Nugget?</p>
                 <p><a href="delete.php?id=<?=$_GET['id'];?>&submit=submit">Yes</a> / <a href="nugget.php?id=<?=$_GET['id'];?>">No</a></p>
            </div>
         </div>
         <?php require_once("includes/footer.php");?>
      </div>
   </body>
</html>