<?php require_once("includes/config.php"); ?>
<?php
// If the user has submitted data, add it and redirect the user to the appropriate page
if (isset($_POST['submit'])) {
    try {
        if (!isset($current_user)) {
           throw new Exception("Unable to update profile. You're not logged in.",PEAR_LOG_ERR);
        }
        $user = new User($current_user->getId(), $db);
        $user->setEmail($_POST['email']);
        $user->setFirst_name($_POST['first_name']);
        $user->setSurname($_POST['surname']);
        $user->setDescription($_POST['description']);
        $user->setHomepage($_POST['homepage']);
        $user->update();
        // Redirect the user to the confirmation page
        $host  = $_SERVER['HTTP_HOST'];
        $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $extra = 'settings_profile_redirect_success.php';
        header("Location: http://$host$uri/$extra");
        exit;
    } catch (Exception $e) {
        // Redirect the user to the error page
        $host  = $_SERVER['HTTP_HOST'];
        $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $extra = 'settings_profile_redirect_error.php?error_message='.$e->getMessage();
        header("Location: http://$host$uri/$extra");
        exit;
    }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?=$SITE['site_name'];?> - Settings - Edit My Profile</title>
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
               <h1>Edit My Profile</h1>
               <form id="manage_profile" name="manage_profile" action="/settings_profile.php" method="post">
                 <p><label for="first_name">First Name / Surname</label></p>
                 <p><input type="text" name="first_name" value="<?=$current_user->getFirst_name();?>" size="20"> / <input type="text" name="surname" value="<?=$current_user->getSurname();?>" size="30"></p>
                 <p><label for="email">Email Address</label></p>
                 <p><input type="email" name="email"  value="<?=$current_user->getEmail();?>" size="30"></p>
                 <p><label for="description">Describe yourself:</label></p>
                 <p><textarea style="width: 520px" name="description"><?=$current_user->getDescription();?></textarea></p>
                 <p><label for="homepage">Homepage</label></p>
                 <p><span style="font-size: 18px">http://</span> <input type="url" name="homepage" value="<?=$current_user->getHomepage();?>" size="30"></p>
                 <p><input type="submit" name="submit" value="Update"></p>
               </form>
            </div>
         </div>
         <?php require_once("includes/footer.php");?>
      </div>
    </body>
</html>
