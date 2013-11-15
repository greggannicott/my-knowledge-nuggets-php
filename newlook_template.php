<?php require_once("includes_new/config.php"); ?>
<?php
   // The follow code would prevent the main content beind displayed and would
   // instead present an error message:
   //$ERRORS[0]['message'] = "Whoops! Something went wrong.";
?>
<!DOCTYPE html>
<html>
   <head>

      <!-- site / page title -->
      <title><?=$SITE['site_name'];?></title>

      <!-- Include style sheets -->
      <link rel="stylesheet" type="text/css" href="/styles/generic_newlook.css" />
      <link rel="stylesheet" type="text/css" href="/styles/jquery-ui-1.8.5.custom.css" />

      <!-- Include support for the opensearch protocol -->
      <link rel="search" type="application/opensearchdescription+xml" title="My Knowledge Nuggets" href="http://mkn.gannicott.co.uk/opensearch_all.xml" />

      <!-- Include jquery and it's plugins -->
      <script type="text/javascript" src="/javascript/jquery-1.3.2.js"></script>
      <script type="text/javascript" src="/javascript/jquery.timeago.js"></script>
      <script type="text/javascript" src="/javascript/jquery.form.js"></script>
      <script type="text/javascript" src="/javascript/jquery.hotkeys-0.7.9.min.js"></script>
      <script type="text/javascript" src="/javascript/jquery-ui-1.8.5.custom.min.js"></script>

      <!-- Include global keyboard shortcuts -->
      <?php require_once($SITE['paths']['includes'].'/global_keyboard_shortcuts.php');?>

      <!-- Javascript specific to this page -->
      <script type="text/javascript" charset="utf-8">
      </script>

   </head>
	<body>
      <div id="page-container">

         <div id="main-content">

            <!-- HEADER -->
            <?php require_once($SITE['paths']['includes'].'/header.php');?>

            <!-- LEFT SIDE BAR -->
            <div id="left" class="structure-outer">
               <span class="structure-inner">

                  <!-- MAIN MENU -->
                  <?php require_once($SITE['paths']['includes'].'/main_menu.php');?>

                  <!-- Additional Side Items -->
                  <fieldset class="filters">
                     <legend>Results Filter</legend>
                     <ul>
                        <li class="selected">All Nuggets</li>
                        <li class="unselected"><a href="#">Public Nuggets Only</a></li>
                        <li class="unselected"><a href="#">Private Nuggets Only</a></li>
                     </ul>
                     <ul>
                        <li class="selected">Exclude Draft Nuggets</li>
                        <li class="unselected"><a href="#">Include Draft Nuggets</a></li>
                        <li class="unselected"><a href="#">Only Draft Nuggets</a></li>
                     </ul>
                  </fieldset>
               </span>
            </div>

            <!-- MAIN CONTENT -->
            <div id="main" class="structure-outer">
               <span class="structure-inner">
                  <?php
                     // Check to see whether there were any errors. If there were, display the error rather than the
                     // the page content.
                     if (count($ERRORS) > 0) {
                        print '<h1 class="error">Error Encountered</h1>';
                        print '<p class="error">'.$ERRORS[0]['message'].'</p>';
                     } else {
                  ?>
                  <h1>Some pointers</h1>
                  <p>Here be a regular paragraph that's in place for testing purposes. Here be a regular paragraph that's in place for testing purposes. Here be a regular paragraph that's in place for testing purposes. Here be a regular paragraph that's in place for testing purposes. Here be a regular paragraph that's in place for testing purposes. Here be a regular paragraph that's in place for testing purposes. Here be a regular paragraph that's in place for testing purposes. Here be a regular paragraph that's in place for testing purposes. </p>
                  <ul>
                     <li><a href="/index.php">Determine</a> some structure (ie. what will feature on the top menu, side menu etc. Try to have as few items on the top menu as possible, with more options available as a result of the side menu.</li>
                     <li>Determine where the filters, attributes etc. will go. Maybe a top menu, with a sub menu directly below it.</li>
                     <li>Keep the site looking as plain as possible. Shades of grey with the occasional bit of colour. Use MKS as a inspiration.</li>
                  </ul>
                  <p>Here be a regular paragraph that's in place for testing purposes. Here be a regular paragraph that's in place for testing purposes. Here be a regular paragraph that's in place for testing purposes. Here be a regular paragraph that's in place for testing purposes. Here be a regular paragraph that's in place for testing purposes. Here be a regular paragraph that's in place for testing purposes. Here be a regular paragraph that's in place for testing purposes. Here be a regular paragraph that's in place for testing purposes. </p>
                  <h1>Heading 1</h1>
                  <h2>Heading 2</h2>
                  <h3>Heading 3</h3>
                  <h4>Heading 4</h4>
                  <h5>Heading 5</h5>
                  <h6>Heading 6</h6>
                  <?
                     // Close off the error if/else statement
                     }
                  ?>
               </span>

            </div>

         </div>

         <!-- FOOTER -->
         <?php require_once($SITE['paths']['includes']."/footer.php");?>

      </div>
	</body>
</html>
