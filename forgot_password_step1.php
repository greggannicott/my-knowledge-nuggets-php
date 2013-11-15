<?php require_once("includes/config.php"); ?>
<?php
// If the user has submitted data, add it and redirect the user to the appropriate page
if (isset($_POST['submit'])) {
    try {
        // Create a user object off the back of the username provided.
        $user_id = User::return_user_id($_POST['username'], $db);
        // Generate a unique ID that we can include in the email sent out to the user
        $user = new User($user_id, $db);
        $uid = $user->generate_forgotten_password_uid();       
        // Send the email out
        // EMAIL WOULD BE SENT AT THIS POINT, BUT HOMER CAN'T SEND EMAIL!! :-(
        // Redirect the user to the confirmation page
        $host  = $_SERVER['HTTP_HOST'];
        $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $extra = 'forgot_password_step1_redirect_success.php';
        header("Location: http://$host$uri/$extra");
        exit;
    } catch (Exception $e) {
        // Redirect the user to the error page
        $host  = $_SERVER['HTTP_HOST'];
        $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $extra = 'forgot_password_step1_redirect_error.php?error_message='.$e->getMessage();
        header("Location: http://$host$uri/$extra");
        exit;
    }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?=$SITE['site_name'];?></title>
        <?php if (isset($user_id)) {print '<link rel="search" type="application/opensearchdescription+xml" title="My Knowledge Nuggets (My Nuggets)" href="http://mkn.gannicott.co.uk/opensearch_my.xml" />';}?>
        <link rel="search" type="application/opensearchdescription+xml" title="My Knowledge Nuggets (All Nuggets)" href="http://mkn.gannicott.co.uk/opensearch_all.xml" />
        <link rel="stylesheet" type="text/css" href="/styles/generic.css" />
        <script type="text/javascript" src="/javascript/jquery-1.3.2.js"></script>
        <script type="text/javascript" src="/javascript/jquery.form.js"></script>
    </head>
	<body>
      <div id="container">
         <?php include("includes/header.php"); ?>
         <div id="main_content_outter">
            <div id="main_content_inner">
               <h1>Forgotten Password</h1>
               <form id="forgot_password" name="forgot_password" action="/forgot_password.php" method="post">
                 <p>To begin the process of resetting your password, please enter your username below.</p>
                 <p>This will result in an email being sent to the email address you've provided us. Please following the instructions contained within that email.</p>
                 <p><label for="username">Username</label></p>
                 <p><input type="text" name="username" size="20"></p>
                 <p><input type="submit" name="submit" value="Send Email"></p>
               </form>
            </div>
         </div>
         <?php require_once("includes/footer.php");?>
      </div>
    </body>
</html>
