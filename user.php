<?php require_once("includes/config.php"); ?>
<?php

$what = $_GET['what'];
$order_by = $_GET['order_by'];
$order = $_GET['order'];
$privacy_level = $_GET['privacy_level'];
$draft = $_GET['draft'];

// Set default 'order_by' and 'order' if they don't already exist
if ($order_by == '') {$order_by = 'bydatecreated';}
if ($order == '') {$order = 'desc';}

// Create a user object
if ($_GET['username'] != '') {
   $user = new User(User::return_user_id($_GET['username'], $db), $db);
} else {
   print "No username provided.";
   die();
}

if ($_GET['what'] == 'nuggets') {
   // <editor-fold defaultstate="collapsed" desc="Generate Nugget List">

   $scope_params = null;

   // Set the number of nuggets to display per page... (this needs to be placed into site config or somewhere)
   $nuggets_per_page = $SITE['search']['results_per_page'];

   // If we don't have a starting nugget number, set one
   if (isset($_GET['page_number']) && $_GET['page_number'] != '') {
      $page_number = $_GET['page_number'];
   } else {
      $page_number = 1;
   }

   // Create the scope params
   // These can be overriden later on
   $scope_params = array (
      type => 'user',
      user_id => $user->getId(),
      privacy_level => $privacy_level
   );

   // Determing whether draft nuggets should be included
   if ($draft) {
      $draft_param = $draft;
   } else {
      $draft_param = 'exclude';  // exclude drafts
   }

   // Decide the page title
   if ($user->getId() == $user_id) {
      $page_title = 'My Nuggets';
   } else {
      $page_title = User::return_display_name($user->getId(), $user_id, $db).'\'s Nuggets';
   }


   // Set the sort criteria
   if ($order_by != '' AND $order != '') {
      // Convert the friendly url into a column name...
      switch ($order_by) {
         case 'bydatecreated':
            $order_by_converted[] = "results.dt_created";
            $order_converted[] = $order;
            break;
         case 'bydatemodified':
            $order_by_converted[] = "results.dt_last_mod";
            $order_converted[] = $order;
            break;
         case 'bytitle':
            $order_by_converted[] = "results.title";
            $order_converted[] = $order;
            break;
      }
      // Now create the array
      $sorting = array (
        order_by => $order_by_converted,
        order => $order_converted
      );
   } else {
      $sorting = array (
        order_by => array('results.dt_created'),
        order => array('desc')
      );
   }

   // Pull it all together to create the params we're going to pass in
   $query_params = array(
        scope => $scope_params,
        pagination => array (
           starting_point => (($page_number - 1) * $nuggets_per_page),
           number_of_results => $nuggets_per_page
        ),
        sorting => $sorting,
        search => $query,
        draft => $draft_param
      );

   // Now pull back the results
   $kb = new Nugget_Store($db);
   $results = $kb->query($query_params);

   // </editor-fold>
}
?>
<html>
	<head>
      <title><?=$SITE['site_name'].' - '.$user->getFirst_name().' '.$user->getSurname();?></title>
        <link rel="stylesheet" type="text/css" href="/styles/generic.css" />
        <link rel="stylesheet" type="text/css" href="/styles/jquery-ui-1.8.5.custom.css" />
        <style>
            /* For this page only - we don't have it in the generic as it screws up the add/edit tags */
            /* This style adds padding to the 'add tag' box in bulk edit */
            #as-selections-tags {
                margin-top: 10px;
                margin-bottom: 10px;
            }
        </style>
         <link rel="search" type="application/opensearchdescription+xml" title="My Knowledge Nuggets" href="http://mkn.gannicott.co.uk/opensearch_all.xml" />
         <script type="text/javascript" src="/javascript/jquery-1.3.2.js"></script>
         <script type="text/javascript" src="/javascript/jquery.timeago.js"></script>
         <script type="text/javascript" src="/javascript/jquery.form.js"></script>
         <script type="text/javascript" src="/javascript/jquery.hotkeys-0.7.9.min.js"></script>
         <script type="text/javascript" src="/javascript/jquery-ui-1.8.5.custom.min.js"></script>
         <!-- Include global keyboard shortcuts -->
         <?php require_once('includes/global_keyboard_shortcuts.php');?>
         
         <script type="text/javascript" charset="utf-8">

            // Used to track which nugget is active in terms of the keyboard shortcuts display
            search_result_container_active = 0;

            <?php
            // Determine the max results per page so we can prevent keyboard shortcut navigation going beyond that point
            print 'total_nuggets_onscreen = '.(count($results['entries'])).';';
            
            ?>

            $(document).ready(function(){
               // Apply the friendly dates
               jQuery("abbr.timeago").timeago();

            });

            // Auto Suggest
            $(function() {

               function split( val ) {
                  return val.split( /\s/ );
               }

               function extractLast( term ) {
                  return split( term ).pop();
               }

               $( "#bulk_edit_add_tags" ).autocomplete({
                  source: function( request, response ) {
                     $.getJSON( "/ajax/get_tag_suggestions.php", {
                        term: extractLast( request.term )
                     }, response );
                  },
                  search: function() {
                     // custom minLength
                     var term = extractLast( this.value );
                     if ( term.length < 2 ) {
                        return false;
                     }
                  },
                  focus: function() {
                     // prevent value inserted on focus
                     return false;
                  },
                  select: function( event, ui ) {
                     var terms = split( this.value );
                     // remove the current input
                     terms.pop();
                     // add the selected item
                     terms.push( ui.item.value );
                     // add placeholder to get the space at the end
                     terms.push( "" );
                     this.value = terms.join( " " );
                     return false;
                  }

               });

            });

            function submit_bulk_edit(action) {
               // Handle the bulk edit of nuggets
               $('#bulk_edit_nuggets').ajaxSubmit({
                  dataType: 'json',
                  data: {action:action},
                  success: process_bulk_edit_nuggets_response
               });
            }

            // Toggles the display of bulk_edit items
            function toggle_bulk_edit() {
               $("#bulk_edit").toggle("fast");
               $(".bulk_edit_checkbox").toggle();
            }
            
            function process_bulk_edit_nuggets_response(json) {
               if (json.type == 'success') {
                  switch(json.action) {
                     case 'delete':
                        // Remove deleted nuggets from the interface
                        $.each(json.nuggets,function(i,item) {
                           $("#nugget_" + item).remove();
                        });
                        // Change the confirmation box
                        $("#bulk_edit_confirmation_success p").html(json.message + ' <span onclick="bulk_edit_remove_confirmation_success()" class="bulk_edit_nugget_confirmation yes">OK</span>');
                        $("#bulk_edit_confirmation_success").show();
                        break;
                     case 'make_public':
                        // Change the confirmation box
                        $("#bulk_edit_confirmation_success p").html(json.message + ' <span onclick="bulk_edit_remove_confirmation_success()" class="bulk_edit_nugget_confirmation yes">OK</span>');
                        $("#bulk_edit_confirmation_success").show();
                        break;
                     case 'make_private':
                        // Change the confirmation box
                        $("#bulk_edit_confirmation_success p").html(json.message + ' <span onclick="bulk_edit_remove_confirmation_success()" class="bulk_edit_nugget_confirmation yes">OK</span>');
                        $("#bulk_edit_confirmation_success").show();
                        break;
                     case 'add_tag':
                        $("#bulk_edit_add_tags").val("");
                        $("#bulk_edit_confirmation_success p").html(json.message + ' <span onclick="bulk_edit_remove_confirmation_success()" class="bulk_edit_nugget_confirmation yes">OK</span>');
                        $("#bulk_edit_confirmation_success").show();
                        break;
                  }
               } else {
                  $("#bulk_edit_confirmation_error p").html(json.message + ' <span onclick="bulk_edit_remove_confirmation_error()" class="bulk_edit_nugget_confirmation yes">OK</span>');
                  $("#bulk_edit_confirmation_error").show();
               }
            }

            function bulk_edit_delete(confirmation) {
               switch (confirmation) {
                  case true:
                     $("#bulk_edit_delete_confirmation").hide();
                     submit_bulk_edit('delete');
                     break;
                  case false:
                     $("#bulk_edit_delete_confirmation").hide();
                     $("#bulk_edit_choices").toggle();
                     break;
                  default:
                     $("#bulk_edit_choices").toggle();
                     $("#bulk_edit_delete_confirmation").toggle();
                     break;
               }
            }

            function bulk_edit_make_public() {
               $("#bulk_edit_choices").toggle();
               submit_bulk_edit('make_public');
            }

            function bulk_edit_make_private() {
               $("#bulk_edit_choices").toggle();
               submit_bulk_edit('make_private');
            }

            function bulk_edit_add_tag(input) {
                // 'input' is used to perform a cancel

                // User cancels:
                if (input == false) {
                    $("#bulk_edit_choices").toggle();
                    $("#bulk_edit_add_tag_prompt").hide();
                    $("#bulk_edit").height(40);
                    return false;
                }

                // User needs to select tags:
                if ($("#bulk_edit_add_tag_prompt").css('display') == 'none') {
                    $("#bulk_edit_choices").toggle();
                    $("#bulk_edit_add_tag_prompt").show();
                    $("#bulk_edit").height(90);
                // User has entered tags:
                } else {
                    $("#bulk_edit_add_tag_prompt").hide();
                    $("#bulk_edit").height(40);
                    submit_bulk_edit('add_tag');
                }
            }

            function bulk_edit_remove_confirmation_success() {
               $("#bulk_edit_confirmation_success").hide();
               $("#bulk_edit_choices").show();
            }

            function bulk_edit_remove_confirmation_error() {
               $("#bulk_edit_choices").show();
               $("#bulk_edit_confirmation_error").hide();
            }

            // Keyboard Shortcuts

            $(document).bind('keydown',{combi:'j',disableInInput:true},next_result);
            $(document).bind('keydown',{combi:'down',disableInInput:true},next_result);
            $(document).bind('keydown',{combi:'k',disableInInput:true},prev_result);
            $(document).bind('keydown',{combi:'up',disableInInput:true},prev_result);
            $(document).bind('keydown',{combi:'v',disableInInput:true},view_result);
            $(document).bind('keydown',{combi:'enter',disableInInput:true},view_result);
            $(document).bind('keydown',{combi:'return',disableInInput:true},view_result);
            $(document).bind('keydown',{combi:'esc',disableInInput:true},clear_keyboard_shortcut_display);

            // Functions used to action keyboard shortcuts

            function next_result() {

               // Check to see whether we have hit the last item or can move on to next item
               if (search_result_container_active != total_nuggets_onscreen) {
                  search_result_container_active++;
               }

               // Remove focus from old and move to new
               $(".search_results_container").removeClass("focus");
               $("#search_results_container_" + search_result_container_active).addClass("focus");
               $(".search_results_container_hint").removeClass("focus");
               $("#search_results_container_hint_" + search_result_container_active).addClass("focus");

               // Make sure the highlighted element is in view
               adjust_viewport_to_selected_nugget();

               // Return false to prevent any default behavour by the browser
               return false;

            }

            function prev_result() {
               
               // We don't want the cursor to go to far up, so prevent counter
               // from going above 1 (eg. 0).
               if (search_result_container_active >= 2) {
                  search_result_container_active--;
               }

               // Remove focus from old and move to new
               $(".search_results_container").removeClass("focus");
               $("#search_results_container_" + search_result_container_active).addClass("focus");
               $(".search_results_container_hint").removeClass("focus");
               $("#search_results_container_hint_" + search_result_container_active).addClass("focus");

               // Make sure the highlighted element is in view
               adjust_viewport_to_selected_nugget();

               // Return false to prevent any default behavour by the browser
               return false;

            }

            function view_result() {
               nugget_url = $("#search_results_container_" + search_result_container_active).children("span").children("span").children("a").attr("href");
               window.location.href = nugget_url;
            }

            // NO KEYBOARD SHORTCUT BOUND
            function view_all_results() {
               target_url = "<?='/search.php?q='.urlencode($query).'&scope=notuser&user='.$user->getId();?>";
               window.location.href = target_url;
            }

            // NO KEYBOARD SHORTCUT BOUND
            function view_my_results() {
               target_url = "<?='/search.php?q='.urlencode($query).'&scope=user&user='.$user->getId();?>";
               window.location.href = target_url;
            }

            function clear_keyboard_shortcut_display() {
               $(".search_results_container").removeClass("focus");
               $(".search_results_container_hint").removeClass("focus");
            }

            // Util functions

            // Adjusts the viewport to make sure the selected nugget is
            // always on screen when it's highlighted via a keyboard shortcut
            function adjust_viewport_to_selected_nugget() {

               offset = $("#search_results_container_" + search_result_container_active).offset();
               container_height = $("#search_results_container_" + search_result_container_active).height();
               hint_height = $("#search_results_container_hint_" + search_result_container_active).height();
               height = container_height + hint_height + 20;   // 20 added to cope with an unknown factor

               // Check it doesn't go off the bottom:
               // ------------------

               // Find out the absolute bottom of the highlighted item
               offset_bottom = offset.top + height;

               // Check whether that is off screen - if so, move screen accordingly
               // The 50's adjust for something - not entirely sure what tbh
               if (offset_bottom > ($(window).scrollTop() + $(window).height())) {
                  // Determine what the scroll top should be
                  scrollTop = offset_bottom - $(window).height();
                  $(window).scrollTop(scrollTop);
               }

               // Check it doesn't go off the top:
               // ------------------

               // Check whether that is off screen - if so, move screen accordingly
               if (offset.top < $(window).scrollTop()) {
                  $(window).scrollTop((offset.top - 20));
               }
               
            }

         </script>
	</head>
	<body>
      <div id="container">
         <?php include("includes/header.php"); ?>
         <div id="main_content_outter">
            <div id="main_content_inner">
               <?php
               // Display the results title
               print '<span class="search_results_section_heading_container">';
                  print '<span class="search_results_section_heading">'.$page_title.'</span>';
                  print '<span class="search_results_section_heading_results_count">'.$results['statistics']['rows_found'].' Nuggets(s)</span>';
               print '</span>';
               ?>
               <div id="right_column">
                  <div id="right_column_inner">
                     <?php
                     // Only display the user box if it's not the current user
                     if ($user->getId() != $user_id) {
                        print '<fieldset>';
                           print '<legend>'.$user->getFirst_name().' '.$user->getSurname().'</legend>';

                           if ($user->getDescription() != '') {
                              print '<p id="bio">"'.$user->getDescription().'"</p>';
                           }

                           print '<ul>';
                              print '<li><u>Joined</u>: <abbr class="timeago" title="'.date("c",$user->getDt_created()).'">'.date("l dS \of F, o @ G:i",$user->getDt_created()).'</abbr></li>';
                              if ($user->return_number_of_nuggets() > 0) {
                                 print '<li><u>Last Nugget</u>: <abbr class="timeago" title="'.date("c",$user->return_last_nugget_dt()).'">'.date("l dS \of F, o @ G:i",$user->return_last_nugget_dt()).'></abbr></li>';
                              } else {
                                 print '<li><u>Last Nugget</u>: N/A</li>';
                              }
                              print '<li><u>Number of Nuggets</u>: '.$user->return_number_of_nuggets().'</li>';
                              if ($user->getHomepage() != '') {
                                 print '<li><u>Homepage</u>: <a href="http://'.$user->getHomepage().'" title="'.$user->getHomepage().'" target="_blank">'.substr($user->getHomepage(),0,18).'..'.'</a></li>';
                              }
                           print '</ul>';

                        print '</fieldset>';
                     }

                     if ($user->getId() == $user_id) {
                     ?>
                        <fieldset>
                           <legend>Actions</legend>
                           <ul class="right_menu_items">
                              <li><span style="color: #1462C1; cursor: pointer;" onclick="toggle_bulk_edit()">Perform Bulk Actions on Nuggets</span></li>
                           </ul>
                        </fieldset>
                     <?
                     }
                     ?>
                     <fieldset>
                        <legend>Sort Order</legend>
                        <ul class="right_menu_items">
                           <?php
                           if ($order_by == 'bydatecreated' && $order == 'desc') {print '<li><b>Newest to Oldest</b></li>';} else {print '<li><a href="/users/'.$user->getUsername().'/nuggets/1/order_by=bydatecreated&order=desc&privacy_level='.$privacy_level.'&draft='.$draft_param.'">Newest to Oldest</a></li>';}
                           if ($order_by == 'bydatecreated' && $order == 'asc') {print '<li><b>Oldest to Newest</b></li>';} else {print '<li><a href="/users/'.$user->getUsername().'/nuggets/1/order_by=bydatecreated&order=asc&privacy_level='.$privacy_level.'&draft='.$draft_param.'">Oldest to Newest</a></li>';}
                           if ($order_by == 'bytitle' && $order == 'asc') {print '<li><b>By Title</b></li>';} else {print '<li><a href="/users/'.$user->getUsername().'/nuggets/1/order_by=bytitle&order=asc&privacy_level='.$privacy_level.'&draft='.$draft_param.'">By Title</a></li>';}
                           ?>
                        </ul>
                     </fieldset>
                     <fieldset>
                        <legend>Filters</legend>
                        <?php
                        // Check that the user is visiting his own page, otherwise don't display these filters
                        if ($user->getId() == $user_id) {

                           // privacy
                           print '<ul class="right_menu_items">';
                              if ($privacy_level == 'all' || $privacy_level == '') {print '<li><b>All Nuggets</b></li>';} else {print '<li><a href="/users/'.$user->getUsername().'/nuggets/1/order_by='.$order_by.'&order='.$order.'&privacy_level=all&draft='.$draft_param.'" title="display all my nuggets">All My Nuggets</a></li>';}
                              if ($privacy_level == 'public') {print '<li><b>Public Nuggets Only</b></li>';} else {print '<li><a href="/users/'.$user->getUsername().'/nuggets/1/order_by='.$order_by.'&order='.$order.'&privacy_level=public&draft='.$draft_param.'" title="only display my public nuggets">Public Nuggets Only</a></li>';}
                              if ($privacy_level == 'private') {print '<li><b>Private Nuggets Only</b></li>';} else {print '<li><a href="/users/'.$user->getUsername().'/nuggets/1/order_by='.$order_by.'&order='.$order.'&privacy_level=private&draft='.$draft_param.'" title="only display my private nuggets">Private Nuggets Only</a></li>';}
                           print '</ul>';

                           // Draft
                           print '<ul class="right_menu_items">';
                              if ($draft_param == 'exclude') {print '<li><b>Exclude Draft Nuggets</b></li>';} else {print '<li><a href="/users/'.$user->getUsername().'/nuggets/1/order_by='.$order_by.'&order='.$order.'&privacy_level='.$privacy_level.'&draft=exclude" title="exclude draft nuggets">Exclude Draft Nuggets</a></li>';}
                              if ($draft_param == 'include') {print '<li><b>Include Draft Nuggets</b></li>';} else {print '<li><a href="/users/'.$user->getUsername().'/nuggets/1/order_by='.$order_by.'&order='.$order.'&privacy_level='.$privacy_level.'&draft=include" title="include draft nuggets">Include Draft Nuggets</a></li>';}
                              if ($draft_param == 'only') {print '<li><b>Only Draft Nuggets</b></li>';} else {print '<li><a href="/users/'.$user->getUsername().'/nuggets/1/order_by='.$order_by.'&order='.$order.'&privacy_level='.$privacy_level.'&draft=only" title="only draft nuggets">Only Draft Nuggets</a></li>';}
                           print '</ul>';

                        } else {
                           print '<ul class="right_menu_items">';
                              print '<li><i>No filters available</i></li>';
                           print '</ul>';
                        }
                        ?>
                     </fieldset>
                  </div>
               </div>
               <div id="center_column">
                  <div id="center_column_inner">

                     <form action="/ajax/bulk_edit_nuggets.php" method="post" id="bulk_edit_nuggets">

                     <!-- Display the bulk edit options. These should be hidden at first -->
                     <fieldset id="bulk_edit" style="display: none">
                        <legend>Bulk Edit</legend>
                        <!-- Choices -->
                        <span id="bulk_edit_choices">
                           <ul class="bulk_edit_menu">
                              <li class="first" onclick="bulk_edit_make_public();">Make Public</li>
                              <li onclick="bulk_edit_make_private();">Make Private</li>
                              <li onclick="bulk_edit_delete();">Delete</li>
                              <li onclick="bulk_edit_add_tag();">Add Tag</li>
                           </ul>
                           <span class="bulk_edit_close_cross" onclick="toggle_bulk_edit();">close</span>
                        </span>
                        <!-- Delete Confirmation (HIDDEN) -->
                        <span id="bulk_edit_delete_confirmation" style="display: none">
                           <p class="bulk_edit_menu_text">Are you sure you wish to delete the selected Nuggets? <span onclick="bulk_edit_delete(true)" class="bulk_edit_nugget_confirmation yes">Yes</span><span onclick="bulk_edit_delete(false)" class="bulk_edit_nugget_confirmation no">No</span></p>
                        </span>
                        <!-- Add Tag Prompt (HIDDEN) -->
                        <span id="bulk_edit_add_tag_prompt" style="display: none">
                           <p class="bulk_edit_menu_text">Enter tags below. <span onclick="bulk_edit_add_tag()" class="bulk_edit_nugget_confirmation yes">Add</span><span onclick="bulk_edit_add_tag(false)" class="bulk_edit_nugget_confirmation no">Cancel</span></p>
                           <p><input type="text" name="bulk_edit_add_tags" style="font-size: 12px; width: 100%; margin-top: 5px;" id="bulk_edit_add_tags"></p>
                        </span>
                        <!-- Confirmation of outcome success (HIDDEN) -->
                        <span id="bulk_edit_confirmation_success" style="display: none">
                           <p class="bulk_edit_menu_text"></p>
                        </span>
                        <!-- Confirmation of outcome error (HIDDEN) -->
                        <span id="bulk_edit_confirmation_error" style="display: none">
                           <p class="bulk_edit_menu_text"></p>
                        </span>

                     </fieldset>

                     <?php

                        if ($what == 'nuggets') {
                           // ** List the user's nuggets
                              if (count($results['entries']) > 0) {
                                $search_results_container_count = 0;   // Used to handle keyboard shortcut navigation of results
                                foreach ($results['entries'] as $entry) {
                                   $search_results_container_count++;
                                   $nugget = new Nugget($entry['id'],$db);

                                   // Decide whether we need limiting dots
                                   if (strlen($nugget->getBody()) > $SITE['search']['max_chars_displayed']) {
                                      $trailing_dots = '...';
                                   } else {
                                      $trailing_dots = '';
                                   }
                                   print '<span class="search_results_container" id="search_results_container_'.$search_results_container_count.'">'; // Used for keyboard shortcut display
                                      print '<span id="nugget_'.$nugget->getId().'">'; // Used for 'bulk edit' functions
                                         // Title
                                         print '<span class="search_results_title"><input name="nuggets['.$nugget->getId().']" value="'.$nugget->getId().'" type="checkbox" style="display: none" class="bulk_edit_checkbox"> <a href="'.$nugget->return_permalink().'">'.$nugget->getTitle().'</a></span>';
                                         // Date
                                         print '<span class="search_results_date">Created <abbr class="timeago" title="'.date("c",$nugget->getDt_created()).'">'.date("l dS \of F, o @ G:i",$nugget->getDt_created()).'</abbr></span>';
                                         // Sample of Nugget
                                         print '<span class="search_results_description">'.trim(substr(strip_tags($nugget->getBody()),0,$SITE['search']['max_chars_displayed'])).' <b>'.$trailing_dots.'</b></span>';
                                      print '</span>';
                                   print '</span>';
                                   // Print hints of what can be done with this nugget using keyboard shortcuts
                                   print '<span class="search_results_container_hint" id="search_results_container_hint_'.$search_results_container_count.'">'.$SITE['search']['search_results_container_hint_text'].'</span>';

                                }

                                // ** PAGINATION

                                // Find out how many pages we need
                                $number_of_pages = ceil($results['statistics']['rows_found'] / $nuggets_per_page);

                                // Determine what the last page number would be...
                                $last_page_number = ceil($results['statistics']['rows_found'] / $nuggets_per_page);

                                // Decide on whether there is a 'first' link
                                if ($page_number > 1) {
                                   $first = '<a href="/users/'.$user->getUsername().'/nuggets/1/order_by='.$order_by.'&order='.$order.'&privacy_level='.$privacy_level.'&draft='.$draft_param.'">First</a> | ';
                                } else {
                                   $first = 'First | ';
                                }

                                // Decide on whether there is a 'prev'
                                if ($page_number > 1) {
                                   $prev = '<a href="/users/'.$user->getUsername().'/nuggets/'.($page_number - 1).'/order_by='.$order_by.'&order='.$order.'&privacy_level='.$privacy_level.'&draft='.$draft_param.'">Prev</a> | ';
                                } else {
                                   $prev = 'Prev | ';
                                }

                                // Decide on whether there is a 'next'
                                if ($page_number < $number_of_pages) {
                                   $next = '<a href="/users/'.$user->getUsername().'/nuggets/'.($page_number + 1).'/order_by='.$order_by.'&order='.$order.'&privacy_level='.$privacy_level.'&draft='.$draft_param.'">Next</a> | ';
                                } else {
                                   $next = 'Next | ';
                                }

                                // Decide on whether there is a 'last' link
                                if ($page_number < $last_page_number) {
                                   $last = '<a href="/users/'.$user->getUsername().'/nuggets/'.($last_page_number).'/order_by='.$order_by.'&order='.$order.'&privacy_level='.$privacy_level.'&draft='.$draft_param.'">Last</a>';
                                } else {
                                   $last = 'Last';
                                }

                                // WARNING: The following code is something that didn't make any sense seconds after writing it. Best of luck

                                // Work out what the first and last page number we want to diplay is
                                $pages_to_display = $SITE['search']['pagination']['pages_to_display'];
                                // First we need to determine the number of adjecents
                                $adjacents = ceil(($pages_to_display - 1) / 2);  // We -1 to account for the current page
                                if ($page_number <= ($pages_to_display - $adjacents)) {
                                   $start_page = 1;
                                   // The following line checks to see if the number of pages that can be displayed is greater than the number of pages in the result set
                                   // If so, it simply outputs the number of pages in the result set
                                   $last_page_number_displayed = (($pages_to_display > $last_page_number)?$last_page_number:$pages_to_display);
                                } elseif (($page_number > ($pages_to_display - $adjacents)) AND ($page_number <= ($last_page_number - $adjacents))) {
                                   // The following checks to see whether it will print out a page 0. If it is going to, it sets the start page to 1.
                                   $start_page = ((($page_number - $adjacents) > 0)?($page_number - $adjacents):1);
                                   $last_page_number_displayed = $page_number + $adjacents;
                                } elseif ($page_number > ($last_page_number - $adjacents)) {
                                   // The following checks to see whether it will print out a page 0. If it is going to, it sets the start page to 1.
                                   $start_page = (($last_page_number - $pages_to_display + 1 > 0)?$last_page_number - $pages_to_display + 1:1);
                                   $last_page_number_displayed = $last_page_number;
                                }

                                // Display the possible pages
                                print '<p>'.$first.$prev;
                                for ($i = $start_page; $i <= $last_page_number_displayed; $i++) {
                                   // Is it the current page?
                                   if ($i != $page_number) {
                                      print '<a href="/users/'.$user->getUsername().'/nuggets/'.$i.'/order_by='.$order_by.'&order='.$order.'&privacy_level='.$privacy_level.'&draft='.$draft_param.'">'.$i.'</a> | ';
                                   } else {
                                      print '<b>'.$i.'</b> | ';
                                   }
                                }
                                print $next.$last.'</p>';
                              } else {
                                print '<p>Sorry, there are no nuggets to display.</p>';
                              }
                           // </editor-fold>
                        }

                     ?>
                     </form>
                  </div>
               </div>
            </div>
         </div>
         <?php require_once("includes/footer.php");?>
      </div>
	</body>
</html>
