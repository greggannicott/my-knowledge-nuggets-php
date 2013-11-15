<?php require_once("includes/config.php"); ?>
<html>
	<head>
		<title><?=$SITE['site_name'];?></title>
      <link rel="search" type="application/opensearchdescription+xml" title="My Knowledge Nuggets" href="http://mkn.gannicott.co.uk/opensearch_all.xml" />
      <link rel="stylesheet" type="text/css" href="styles/generic.css" />
      <link rel="stylesheet" type="text/css" href="styles/knowledge_item_body.css" />
      <!-- Include required JS files -->
      <script type="text/javascript" src="syntaxhighlighter/src/shCore.js"></script>
      <!-- At least one brush, here we choose JS. You need to include a brush for every language you want to highlight -->
      <script type="text/javascript" src="syntaxhighlighter/scripts/shBrushBash.js"></script>
      <script type="text/javascript" src="syntaxhighlighter/scripts/shBrushCSharp.js"></script>
      <script type="text/javascript" src="syntaxhighlighter/scripts/shBrushCpp.js"></script>
      <script type="text/javascript" src="syntaxhighlighter/scripts/shBrushCss.js"></script>
      <script type="text/javascript" src="syntaxhighlighter/scripts/shBrushJScript.js"></script>
      <script type="text/javascript" src="syntaxhighlighter/scripts/shBrushJava.js"></script>
      <script type="text/javascript" src="syntaxhighlighter/scripts/shBrushPerl.js"></script>
      <script type="text/javascript" src="syntaxhighlighter/scripts/shBrushPhp.js"></script>
      <script type="text/javascript" src="syntaxhighlighter/scripts/shBrushPlain.js"></script>
      <script type="text/javascript" src="syntaxhighlighter/scripts/shBrushPython.js"></script>
      <script type="text/javascript" src="syntaxhighlighter/scripts/shBrushRuby.js"></script>
      <script type="text/javascript" src="syntaxhighlighter/scripts/shBrushSql.js"></script>
      <script type="text/javascript" src="syntaxhighlighter/scripts/shBrushVb.js"></script>
      <script type="text/javascript" src="syntaxhighlighter/scripts/shBrushXml.js"></script>
      <script type="text/javascript" src="/javascript/jquery.hotkeys-0.7.9.min.js"></script>
      <!-- Include global keyboard shortcuts -->
      <?php require_once('includes/global_keyboard_shortcuts.php');?>
      
      <!-- Include *at least* the core style and default theme -->
      <!-- get shCore.css from our own styles directory as it contains modifications -->
      <link href="styles/shCore.css" rel="stylesheet" type="text/css" />
      <link href="syntaxhighlighter/styles/shThemeDefault.css" rel="stylesheet" type="text/css" />
      <script type="text/javascript" src="javascript/jquery-1.3.2.js"></script>
      <script type="text/javascript" src="javascript/jquery.timeago.js"></script>
		<script type="text/javascript" charset="utf-8">
			$(document).ready(function(){
				// Resize the relevant heights
				resizeWindow();

				// If the User resizes the window, adjust the relevant heights
				$(window).bind("resize", resizeWindow);

				function resizeWindow( e ) {
					var windowHeight = $(window).height();
					// take away the header
					newWindowHeight = (windowHeight - $("#header_outter").height() - 20); // the minus 4 is take get rid of the size bar
					$("#item_content_outter").css("height", newWindowHeight );
					$("#item_content_inner").css("height", (newWindowHeight - 33));
               $("#item_content_body_outer").css("height", ((newWindowHeight - 33) - $("#item_content_summary").height()));
               
					$("#items_list_outter").css("height", newWindowHeight - 42);
					$("#items_list_inner").css("height", (newWindowHeight - 23 - 42));
				}

			});

         // Displays the contents of a nugget in the content panel
         function display_nugget(id) {
            // Display the 'loading' sign
            $("#notification_message").text("Grabbing the Nugget for you...");
            $("#notification").fadeIn();
            $.ajax({
              type: "GET",
              url: "ajax/get_nugget.php",
              data: "id="+id,
              dataType: "json",
              success: function(json) {
                  if (json.type == 'success') {
                     // Take away the highlighting from the previously selected item
                     $("span.items_list_box.selected").removeClass("selected");
                     // Highlight the currently selected item
                     $("#list_item_"+json.nugget_details.id).addClass("selected");
                     // Now populate the content panel
                     $("#item_content_title").text(json.nugget_details.title);
                     $("#item_content_body_content").html(json.nugget_details.body);
                     $("#item_content_attributes_title").text("Show Attributes");
                     $("#item_content_attributes_title").addClass("item_content_summary_buttons");
                     $("#item_content_attributes_title").bind("click", toggle_attributes);
                     $("#item_content_attributes_dt_created").html('<abbr id="item_content_attributes_dt_created" class="item_content_attributes_value timeago" title="' + json.nugget_details.dt_created + '">' + json.nugget_details.dt_created + '</abbr>');
                     $("#item_content_attributes_dt_last_mod").html('<abbr id="item_content_attributes_dt_last_mod" class="item_content_attributes_value timeago" title="' + json.nugget_details.dt_last_mod + '">' + json.nugget_details.dt_last_mod + '</abbr>');
                     $("#item_content_links").html('<a href="delete.php?id='+json.nugget_details.id+'" class="item_content_summary_buttons" id="item_content_delete_button">Delete</a><a href="edit.php?id='+json.nugget_details.id+'" class="item_content_summary_buttons">Edit</a>');
                     // Build up the list of tags and create HTML to handle those tags
                     var tags_html = '';
                     if (json.nugget_details.tags.length > 0 && json.nugget_details.tags[0] != '') {
                        $.each(json.nugget_details.tags, function(intIndex, objValue) {
                                 tags_html += '<li>' + objValue + '</li>';
                              })
                     } else {
                        tags_html = '<li>None</li>';
                     }
                     $("#item_content_tags").html(tags_html);
                     // Convert all time-ago instances to friendly dates
                     jQuery("abbr.timeago").timeago();
                     // Apply the syntax highlighting
                     SyntaxHighlighter.highlight();
                     // Remove the notification
                     $("#notification").fadeOut(1000);
                  } else {
                      alert("Error: " + json.message);
                  }
              }
            })
         }
         function toggle_attributes() {
         $("#item_content_attributes_box").slideToggle(300);
         }
		</script>
	</head>
	<body>
      <div id="container">
         <?php include("includes/header.php"); ?>
         <div id="main_content_outter">
            <div id="main_content_inner">
               <?php
                  if (!isset($_GET['q'])) {
                     $results_description = "My Nuggets";
                  } else {
                     $results_description = "Search results for: \"".$_GET['q']."\"";
                  }
               ?>
               <div id="results_description"><?= $results_description; ?></div>
               <div id="item_content_outter">
                  <div id="item_content_inner">
                     <!-- the title is outside of the 'inner_inner' so it touches the border of the parent box -->
                     <div id="item_content_summary">
                        <span id="item_content_title">&nbsp;</span>
                        <span id="item_content_top_bar">
                           <span id="item_content_attributes_title">&nbsp;</span>
                           <span id="item_content_links">&nbsp;</span>
                        </span>
                        <span id="item_content_attributes_box" style="display: none">
                           <span id="item_content_attributes_rightbox">
                              <span class="item_content_attributes_heading">Added: </span><span id="item_content_attributes_dt_created"></span><br>
                              <span class="item_content_attributes_heading">Last Modified: </span><span id="item_content_attributes_dt_last_mod"></span><br>
                           </span>
                           <span id="item_content_attributes_leftbox">
                             <span class="item_content_attributes_tags">Tags: </span>
                              <ul id="item_content_tags">
                              </ul>
                           </span>
                        </span>
                     </div>
                     <!-- 'inner_inner' is used to prevent padding from affecting screen layout -->
                     <div id="item_content_body_outer">
                        <div id="item_content_body_content">

                        </div>
                     </div>
                  </div>
               </div>
               <div id="items_list_outter">
                  <span id="items_list_heading">&nbsp;</span>
                  <div id="items_list_inner">
                     <?php
                        // If this is a search we want to sort by relevance, otherwise use dt_created
                        if ($_GET['q'] != '') {
                           $sorting = array (
                             order_by => 'score',
                             order => 'desc'
                           );
                        } else {
                           $sorting = array (
                             order_by => 'nuggets.dt_created',
                             order => 'desc'
                           );
                        }
                        // Now start to build the parameters of the query
                        $kb = new Nugget_Store($db);
                        $query_params = array(
                          users => array (
                             type=>'individual',   // Could be multiple - still to be developed
                             user_id=>$user_id
                          ),
                          pagination => array (
                             starting_point => '0',
                             number_of_results => '1000'
                          ),
                          sorting => $sorting,
                          search => $_GET['q']
                        );
                        $entries = $kb->query($query_params);
                        if (count($entries) > 0) {
                          $count = 0;  // used to determine whether to display odd or even colour
                          $oddeven_class = null;
                          foreach ($entries as $entry) {
                             $count++;
                             // Work out whether to display odd or even
                             if ($count % 2) {
                                $oddeven_class = "odd";
                             } else {
                                $oddeven_class = "even";
                             }
                             $nugget = new Nugget($entry['id'],$db);
                             print '<span id="list_item_'.$nugget->getId().'" class="items_list_box '.$oddeven_class.'" onclick="display_nugget('.$nugget->getId().');">';
                                // Prepare trailing dots in case we trim the title
                                if (strlen($nugget->getTitle()) > 60) {
                                   $trailing_dots = '...';
                                } else {
                                   $trailing_dots = '';
                                }
                                print '<span class="items_list_title">'.substr($nugget->getTitle(),0,60).$trailing_dots.'</span>';
                             print '</span>';
                          }
                        } else {
                          print '<span class="items_list_box_empty"><span class="items_list_title empty">There are no Nuggets in your collection... <a href="add.php">Why not add your first one</a>?</span></span>';
                        }
                     ?>
                  </div>
               </div>
            </div>
         </div>
         <?php require_once("includes/footer.php");?>
      </div>
	</body>
</html>
