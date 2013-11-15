<?php require_once("includes/config.php"); ?>
<html>
	<head>
        <title><?=$SITE['site_name'];?></title>
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
               <h1>Heading 1</h1>
               <p>Kiln Test...</p>
               <h2>Heading 2</h2>
               <p>Paragraph</p>
               <h3>Heading 3</h3>
               <p>Paragraph</p>
               <h4>Heading 4</h4>
               <p>Paragraph</p>
               <h5>Heading 5</h5>
               <p>Paragraph</p>
            </div>
         </div>
         <?php require_once("includes/footer.php");?>
      </div>
	</body>
</html>
