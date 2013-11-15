<?php require_once("includes/config.php"); ?>
<?php
// If the user has submitted data, add it and redirect the user to the appropriate page
if (isset($_POST['submit'])) {
    try {
        if (!isset($current_user)) {
           throw new Exception("Unable to update profile. You're not logged in.",PEAR_LOG_ERR);
        }
        $user = new User($current_user->getId(), $db);
        $user->update_password($_POST['existing_password'], $_POST['new_password'], $_POST['new_password_confirmation']);
        // Redirect the user to the confirmation page
        $host  = $_SERVER['HTTP_HOST'];
        $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $extra = 'settings_password_redirect_success.php';
        header("Location: http://$host$uri/$extra");
        exit;
    } catch (Exception $e) {
        // Redirect the user to the error page
        $host  = $_SERVER['HTTP_HOST'];
        $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $extra = 'settings_password_redirect_error.php?error_message='.$e->getMessage();
        header("Location: http://$host$uri/$extra");
        exit;
    }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?=$SITE['site_name'];?> - Settings - Change Password</title>
        <link rel="search" type="application/opensearchdescription+xml" title="My Knowledge Nuggets" href="http://mkn.gannicott.co.uk/opensearch_all.xml" />
        <link rel="stylesheet" type="text/css" href="/styles/generic.css" />
        <script type="text/javascript" src="/javascript/jquery-1.3.2.js"></script>
        <script type="text/javascript" src="/javascript/jquery.form.js"></script>
        <script type="text/javascript" src="/javascript/jquery.hotkeys-0.7.9.min.js"></script>
        <!-- Include global keyboard shortcuts -->
        <?php require_once('includes/global_keyboard_shortcuts.php');?>
    </head>
	<body>
      <div id="container">
         <?php include("includes/header.php"); ?>
         <div id="main_content_outter">
            <div id="main_content_inner">
               <h1>Change Password</h1>
               <form id="change_password" name="change_password" action="/settings_password.php" method="post">
                 <p><label for="existing_password">Existing Password</label></p>
                 <p><input type="password" name="existing_password" size="20"></p>
                 <p><label for="new_password">New Password</label></p>
                 <p><input type="password" name="new_password" size="20"></p>
                 <p><label for="new_password_confirmation">Confirm New Password</label></p>
                 <p><input type="password" name="new_password_confirmation" size="20"></p>
                 <p><input type="submit" name="submit" value="Update"></p>
               </form>
            </div>
         </div>
         <?php require_once("includes/footer.php");?>
      </div>
    </body>
</html>
