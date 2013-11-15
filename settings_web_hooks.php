<?php require_once("includes/config.php"); ?>
<?php
// If the user has submitted data, add it and redirect the user to the appropriate page
if (isset($_POST['submit'])) {
    try {
        if (!isset($current_user)) {
           throw new Exception("Unable to update profile. You're not logged in.",PEAR_LOG_ERR);
        }
        $user = new User($current_user->getId(), $db);
        $user->setWeb_hook_url_nugget_add($_POST['nugget_added']);
        $user->setWeb_hook_url_nugget_update($_POST['nugget_updated']);
        $user->setWeb_hook_url_nugget_delete($_POST['nugget_deleted']);
        $user->update();
        // Redirect the user to the confirmation page
        $host  = $_SERVER['HTTP_HOST'];
        $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $extra = 'settings_web_hooks_redirect_success.php';
        header("Location: http://$host$uri/$extra");
        exit;
    } catch (Exception $e) {
        // Redirect the user to the error page
        $host  = $_SERVER['HTTP_HOST'];
        $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $extra = 'settings_web_hooks_redirect_error.php?error_message='.$e->getMessage();
        header("Location: http://$host$uri/$extra");
        exit;
    }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?=$SITE['site_name'];?> - Settings - Web Hooks</title>
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
               <span id="web_hooks">
               <h1>Manage Your Web Hooks</h1>
               <p>Explain what Web Hooks are here...</p>
               <form id="manage_web_hooks" name="manage_web_hooks" action="/settings_web_hooks.php" method="post">
                 <h2>Nuggets</h2>
                 <p>The following web hooks are called when the said actions are performed against Nuggets</p>
                 <p><label for="nugget_added">When you <b>add</b> a Nugget</label></p>
                 <p>http:// <input type="text" name="nugget_added" value="<?=$current_user->getWeb_hook_url_nugget_add();?>" size="60"></p>
                 <p><label for="nugget_edited">When you <b>update</b> changes to a Nugget</label></p>
                 <p>http:// <input type="text" name="nugget_updated" value="<?=$current_user->getWeb_hook_url_nugget_update();?>" size="60"></p>
                 <p><label for="nugget_deleted">When you <b>delete</b> a Nugget</label></p>
                 <p>http:// <input type="text" name="nugget_deleted" value="<?=$current_user->getWeb_hook_url_nugget_delete();?>" size="60"></p>
                 <p><input type="submit" name="submit" value="Update"></p>
               </form>
               </span>
            </div>
         </div>
         <?php require_once("includes/footer.php");?>
      </div>
    </body>
</html>
