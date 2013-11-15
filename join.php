<?php require_once("includes/config.php");
// If the user has submitted data, add it and redirect the user to the appropriate page
if (isset($_POST['submit'])) {
    try {
        // Create the account
        $id = User::create($_POST['username'], $_POST['password'], $_POST['email'], $_POST['first_name'], $_POST['surname'], $db);
        // Log the user in
        UserLogin::login($id);
        // Redirect the user to the confirmation page
        $host  = $_SERVER['HTTP_HOST'];
        $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $extra = 'join_redirect_success.php';
        header("Location: http://$host$uri/$extra");
        exit;
    } catch (Exception $e) {
        // Redirect the user to the error page
        $host  = $_SERVER['HTTP_HOST'];
        $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $extra = 'join_redirect_error.php?error_message='.$e->getMessage();
        header("Location: http://$host$uri/$extra");
        exit;
    }
}
?>
<html>
	<head>
		<title><?=$SITE['site_name'];?> - Join</title>
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
              $("#first_name").focus();
           });
        </script>
	</head>
	<body>
      <div id="container">
         <?php include("includes/header.php"); ?>
         <div id="main_content_outter">
            <div id="main_content_inner">
               <h1>Join!</h1>
               <form name="join" method="post" action="/join.php">
                 <p><label for="first_name">First Name / Surname</label></p>
                 <p><input type="text" name="first_name" id="first_name" size="20"> / <input type="text" name="surname" size="30"></p>
                 <p><label for="username">Username</label></p>
                 <p><input type="text" name="username" size="30"></p>
                 <p><label for="password">Password</label></p>
                 <p><input type="password" name="password" size="30"></p>
                 <p><label for="email">Email Address</label></p>
                 <p><input type="email" name="email" size="30"></p>
                 <p><input type="submit" name="submit" value="Join"></p>
               </form>
            </div>
         </div>
         <?php require_once("includes/footer.php");?>
      </div>
	</body>
</html>
