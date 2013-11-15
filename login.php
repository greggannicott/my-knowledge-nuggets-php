<?php require_once("includes/config.php"); ?>
<?php
   # Is the user attempting to login? If so, try to log them in
   if (isset($_POST['username']) && isset($_POST['password'])) {
      try {
         // Grab the id for this client.
         // This will check that the username and password is correct
         $user_id = UserLogin::get_user_id($_POST['username'],$_POST['password'],$db);
         // Now plant the cookie
         UserLogin::login($user_id);
         // Redirect the user to the confirmation page
         $host  = $_SERVER['HTTP_HOST'];
         $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
         $extra = 'login_redirect_success.php';
         // Send out a header so Mozilla Account Manager knows what to do
         header('X-Account-Management-Status: active; name="'.$_POST['username'].'"');
         // Now send out a standard redirect
         header("Location: http://$host$uri/$extra");
         exit;
      } catch (Exception $e) {
        // Redirect the user to the error page
        $host  = $_SERVER['HTTP_HOST'];
        $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $extra = 'login_redirect_error.php?error_message='.$e->getMessage();
         // Send out a header so Mozilla Account Manager knows what to do
         header('X-Account-Management-Status: none;');
         // Now send out a standard redirect
        header("Location: http://$host$uri/$extra");
        exit;
      }
   }
?>
<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
   <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
         <title><?=$SITE['site_name'];?> - Login</title>
         <link rel="search" type="application/opensearchdescription+xml" title="My Knowledge Nuggets" href="http://mkn.gannicott.co.uk/opensearch_all.xml" />
         <link rel="stylesheet" type="text/css" href="/styles/generic.css" />
         <script type="text/javascript" src="/javascript/jquery-1.3.2.js"></script>
         <script type="text/javascript" src="/javascript/jquery.hotkeys-0.7.9.min.js"></script>
         <!-- Include global keyboard shortcuts -->
         <?php require_once('includes/global_keyboard_shortcuts.php');?>
         <script type="text/javascript">
           // wait for the DOM to be loaded
           $(document).ready(function() {
              // Give the 'Firstname' field focus:
              $("#username").focus();
           });
        </script>
   </head>
	<body>
      <div id="container">
         <?php include("includes/header.php"); ?>
         <div id="main_content_outter">
            <div id="main_content_inner">
               <?php
               print '<form action="/login.php" method="post" name="loginForm">';
                  print '<h1>Login to My Knowledge Nuggets</h1>';

                  print '<p><label for="username">Username</label></p>';
                  print '<p><input type="text" name="username" id="username"></p>';

                  print '<p><label for="password">Password</label></p>';
                  print '<p><input type="password" name="password" id="password"></p>';

                  print '<p><input type="submit" value="Login"></p>';
               print '</form>';
               ?>
            </div>
         </div>
         <?php require_once("includes/footer.php");?>
      </div>
   </body>
</html>