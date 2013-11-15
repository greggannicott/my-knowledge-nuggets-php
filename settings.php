<?php require_once("includes/config.php");?>
<html>
	<head>
		<title><?=$SITE['site_name'];?> - Settings</title>
        <link rel="search" type="application/opensearchdescription+xml" title="My Knowledge Nuggets" href="http://mkn.gannicott.co.uk/opensearch_all.xml" />
        <link rel="stylesheet" type="text/css" href="/styles/generic.css" />
        <script type="text/javascript" src="/javascript/jquery-1.3.2.js"></script>
        <script type="text/javascript" src="/javascript/jquery.hotkeys-0.7.9.min.js"></script>
        <!-- Include global keyboard shortcuts -->
        <?php require_once('includes/global_keyboard_shortcuts.php');?>
        <script type="text/javascript">
        </script>
	</head>
	<body>
      <div id="container">
         <?php include("includes/header.php"); ?>
         <div id="main_content_outter">
            <div id="main_content_inner">
               <span id="settings">
                  <h1>Settings</h1>
                  <h2>Account</h2>
                  <dl>
                     <dt><a href="/settings/profile/">Edit My Profile</a></dt><dd>Manage the details associated with your account.</dd>
                     <dt><a href="/settings/configuration/">Edit My Site Configuration</a></dt><dd>Manage the way this site works for you.</dd>
                     <dt><a href="/settings/password/">Change My Password</a></dt><dd>Give your MKN account a new password.</dd>
                     <dt><a href="/settings/delete/">Delete My Account</a></dt><dd>Leave My Knowledge Nuggets and remove all content associated with your account.</dd>
                  </dl>
                  <h2>Advanced</h2>
                  <dl>
                     <dt><a href="/settings/web_hooks/">Manage Your Web Hooks</a></dt><dd>Call external scripts when particular actions are performed on MKN. (coming soon)</dd>
                  </dl>
               </span>
            </div>
         </div>
         <?php require_once("includes/footer.php");?>
      </div>
	</body>
</html>
