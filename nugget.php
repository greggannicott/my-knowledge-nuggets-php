<?php require_once("includes/config.php"); ?>
<?php

// Get the details of the nugget
$nugget = new Nugget($_GET['id'],$db);

// Note that the user has visited this Nugget (for the purpose of the /history)
if (isset($current_user)) {
   $current_user->add_viewed_nugget_history($_GET['id']);
}

// Increment the hits for this nugget
if (isset($current_user) && ($nugget->getUser_id() == $current_user->getId())) {
   $nugget->increment_hits('owner');
} else {
   $nugget->increment_hits('others');
}

?>
<html>
	<head>
      <title><?=$nugget->getTitle().' - '.$SITE['site_name'];?></title>
      <link rel="search" type="application/opensearchdescription+xml" title="My Knowledge Nuggets" href="http://mkn.gannicott.co.uk/opensearch_all.xml" />
      <link rel="stylesheet" type="text/css" href="/styles/generic.css" />
      <link rel="stylesheet" type="text/css" href="/styles/knowledge_item_body.css" />
      <!-- Include required JS for syntax highlighting files -->
      <script type="text/javascript" src="/syntaxhighlighter/src/shCore.js"></script>
      <script type="text/javascript" src="/syntaxhighlighter/scripts/shBrushBash.js"></script>
      <script type="text/javascript" src="/syntaxhighlighter/scripts/shBrushCSharp.js"></script>
      <script type="text/javascript" src="/syntaxhighlighter/scripts/shBrushCpp.js"></script>
      <script type="text/javascript" src="/syntaxhighlighter/scripts/shBrushCss.js"></script>
      <script type="text/javascript" src="/syntaxhighlighter/scripts/shBrushJScript.js"></script>
      <script type="text/javascript" src="/syntaxhighlighter/scripts/shBrushJava.js"></script>
      <script type="text/javascript" src="/syntaxhighlighter/scripts/shBrushPerl.js"></script>
      <script type="text/javascript" src="/syntaxhighlighter/scripts/shBrushPhp.js"></script>
      <script type="text/javascript" src="/syntaxhighlighter/scripts/shBrushPlain.js"></script>
      <script type="text/javascript" src="/syntaxhighlighter/scripts/shBrushPython.js"></script>
      <script type="text/javascript" src="/syntaxhighlighter/scripts/shBrushRuby.js"></script>
      <script type="text/javascript" src="/syntaxhighlighter/scripts/shBrushSql.js"></script>
      <script type="text/javascript" src="/syntaxhighlighter/scripts/shBrushVb.js"></script>
      <script type="text/javascript" src="/syntaxhighlighter/scripts/shBrushXml.js"></script>
      <!-- Include *at least* the core style and default theme -->
      <!-- get shCore.css from our own styles directory as it contains modifications -->
      <link href="/styles/shCore.css" rel="stylesheet" type="text/css" />
      <link href="/syntaxhighlighter/styles/shThemeDefault.css" rel="stylesheet" type="text/css" />
      <script type="text/javascript" src="/javascript/jquery-1.3.2.js"></script>
      <script type="text/javascript" src="/javascript/jquery.timeago.js"></script>
      <script type="text/javascript" src="/javascript/jquery.hotkeys-0.7.9.min.js"></script>
      <!-- Include global keyboard shortcuts -->
      <?php require_once('includes/global_keyboard_shortcuts.php');?>
      
		<script type="text/javascript" charset="utf-8">
			$(document).ready(function(){
            // Convert all time-ago instances to friendly dates
            jQuery("abbr.timeago").timeago();
            // Apply the syntax highlighting
            SyntaxHighlighter.all();
            // Apply the friendly dates
            jQuery("abbr.timeago").timeago();
         });
         
         // Bind required keyboard shortcuts
         $(document).bind('keydown',{combi:'e',disableInInput:true},goto_edit);
         $(document).bind('keydown',{combi:'d',disableInInput:true},goto_delete);
         $(document).bind('keydown',{combi:'del',disableInInput:true},goto_delete);

         function goto_edit() {
            window.location.href = "/edit.php?id=<?php print $nugget->getId();?>";
         }

         function goto_delete() {
            window.location = "/delete.php?id=<?php print $nugget->getId();?>";
         }
      </script>
	</head>
	<body>
      <div id="container">
         <?php include("includes/header.php"); ?>
         <div id="main_content_outter">
            <div id="main_content_inner">
               <?php

               // If this Nugget is private, prevent other users from viewing it.
               // -------------------------
               // The following IF statement refuses entry if:
               // 1)
               // - the nugget is private
               // - the user is logged in
               // - the user that is logged in is different to the owner
               // OR 2)
               // - the nugget is private
               // - the user is not logged in
               
               if (($nugget->getPublic == false && isset($current_user) && ($nugget->getUser_id() != $current_user->getId())) OR ($nugget->getPublic == false && !isset($current_user))) {
                  
                  // Display a message to the user
                  print '<h1>Forbidden</h1>';
                  print '<p>Sorry, this Nugget is only viewable by the owner of the Nugget.</p>';
                  
                  // And if the user is not logged in, suggest they login
                  if (!isset($current_user)) {
                     print '<p>If you are the owner of this Nugget, you\'ve probably been blocked as you are not logged in. Please <a href="/login/">login</a> and try again. Thanks.</p>';
                  }

               } else {
               ?>
               <h1><?=$nugget->getTitle();?></h1>
               <div id="right_column">
                  <div id="right_column_inner">
                     <fieldset>
                        <legend>Table of Contents</legend>
                        <?php
                        if (count($nugget->getHeadings()) > 0 ) {
                           foreach($nugget->getHeadings() as $heading) {
                              if ($heading['size'] <= 3) {
                                 print '<span class="nugget_toc_heading'.$heading['size'].'"><a href="#'.$heading['text'].'">'.$heading['text'].'</a></span>';
                              }
                           }
                        } else {
                           print '<p><i>Sorry, this Nugget does not contain a table of contents.</i></p>';
                        }
                        ?>
                     </fieldset>
                     <fieldset>
                        <legend>Actions</legend>
                        <ul class="right_menu_items">
                           <?php
                           // We only want to display the following actions if the user owns the nugget...
                           if (isset($current_user) && ($nugget->getUser_id() == $current_user->getId())) {
                              print '<li><a href="/edit.php?id='.$nugget->getId().'" title="edit this Nugget (e)">Edit Nugget</a></li>';
                              print '<li><a href="/delete.php?id='.$nugget->getId().'" title="delete this Nugget (d, delete)">Delete Nugget</a></li>';
                           } else {
                              print '<li>No Actions Available</li>';
                           }
                           ?>
                        </ul>
                     </fieldset>
                     <fieldset>
                        <legend>Attributes</legend>
                        <?php
                        $user = new User($nugget->getUser_id(), $db);
                        ?>
                        <span class="nugget_attribute"><span class="attr_name">Author: </span><span class="attr_val"><a href="/users/<?=$user->getUsername();?>"><?=user::return_display_name($nugget->getUser_id(), $user_id, $db);?></a></span></span>
                        <span class="nugget_attribute"><span class="attr_name">Created: </span><abbr class="attr_val timeago" title="<?=date("c",$nugget->getDt_created());?>"><?=date("l dS \of F, o @ G:i",$nugget->getDt_created());?></abbr></span>
                        <span class="nugget_attribute"><span class="attr_name">Last Modified: </span><abbr class="attr_val timeago" title="<?=date("c",$nugget->getDt_last_mod());?>"><?=date("l dS \of F, o @ G:i",$nugget->getDt_last_mod());?></abbr></span>
                        <?php
                        // Output whether it's a private or public nugget
                        // Should only be displayed if the nugget belongs to the user - what's the point otherwise?
                        if (isset($current_user) && ($nugget->getUser_id() == $current_user->getId())) {
                           switch ($nugget->getPublic()) {
                              case 1:
                                 $scope_val = "Public";
                                 break;
                              case 0:
                                 $scope_val = "Private";
                                 break;
                           }
                           print '<span class="nugget_attribute"><span class="attr_name">Scope: </span><span class="attr_val">'.$scope_val.'</span></span>';
                        }
                        ?>
                     </fieldset>
                     <fieldset>
                        <legend>Tags</legend>
                        <?php
                        if (count($nugget->getTags()) > 0) {
                        print '<ul class="tags">';
                           foreach ($nugget->getTags() as $tag) {
                              print '<li><a href="/search.php?q='.$tag.'">'.$tag.'</a></li>';
                           }
                        print '</ul>';
                        } else {
                           print '<p>None</p>';
                        }
                        ?>
                     </fieldset>
                  </div>
               </div>
               <div id="center_column">
                  <div id="center_column_inner">
                     <div id="nugget_body">
                        <?php
                           print $nugget::inject_anchors_into_headings($nugget->getBody());
                        ?>
                     </div>
                  </div>
               </div>
               <?php
               }  // Closes off the 'is this user allowed to view this?' if statement
               ?>
            </div>
         </div>
         <?php require_once("includes/footer.php");?>
      </div>
	</body>
</html>