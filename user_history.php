<?php require_once("includes/config.php"); ?>
<?php

$order_by = $_GET['order_by'];
$order = $_GET['order'];

   // <editor-fold defaultstate="collapsed" desc="Generate Nugget List">

   $scope_params = null;

   // Set the number of nuggets to display per page...
   $nuggets_per_page = $SITE['search']['results_per_page'];

   // If we don't have a starting nugget number, set one
   if ($_GET['page_number'] != '') {
      $page_number = $_GET['page_number'];
   } else {
      $page_number = 1;
   }

   // Decide the page title
   $page_title = 'Your Recently Viewed Nuggets';

   // Set the sort criteria
   if ($order_by != '' AND $order != '') {
      switch ($order_by) {
         case 'bydatevisited':
            $order_by_converted = "max(history.dt_created)";
            break;
      }
      // Now create the array
      $sorting = array (
        order_by => $order_by_converted,
        order => $order
      );
   // No values given. Set a default:
   } else {
      $sorting = array (
        order_by => 'max(history.dt_created)',
        order => 'desc'
      );
   }

   // Pull it all together to create the params we're going to pass in
   $query_params = array(
        pagination => array (
           starting_point => (($page_number - 1) * $nuggets_per_page),
           number_of_results => $nuggets_per_page
        ),
        sorting => $sorting
      );

   // Now pull back the results
   $results = $current_user->return_viewed_nugget_history($query_params);

   // </editor-fold>
?>
<html>
	<head>
        <title><?=$SITE['site_name'];?> - History</title>
        <link rel="stylesheet" type="text/css" href="/styles/generic.css" />
        <link rel="search" type="application/opensearchdescription+xml" title="My Knowledge Nuggets" href="http://mkn.gannicott.co.uk/opensearch_all.xml" />
         <script type="text/javascript" src="/javascript/jquery-1.3.2.js"></script>
         <script type="text/javascript" src="/javascript/jquery.timeago.js"></script>
         <script type="text/javascript" src="/javascript/jquery.hotkeys-0.7.9.min.js"></script>
         <!-- Include global keyboard shortcuts -->
         <?php require_once('includes/global_keyboard_shortcuts.php');?>
         <script type="text/javascript" charset="utf-8">

            // Used to track which nugget is active in terms of the keyboard shortcuts display
            search_result_container_active = 0;

            <?php
            // Determine the max results per page so we can prevent keyboard shortcut navigation going beyond that point
            print 'total_nuggets_onscreen = '.count($results['entries']).';';
            ?>

            $(document).ready(function(){
               // Apply the friendly dates
               jQuery("abbr.timeago").timeago();
            });

            // Keyboard bindings
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
               nugget_url = $("#search_results_container_" + search_result_container_active).children("span").children("a").attr("href");
               window.location.href = nugget_url;
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
                     <fieldset>
                        <legend>Sort Order</legend>
                        <ul class="right_menu_items">
                           <?php
                           if (($order_by == 'bydatevisited' && $order == 'desc') || ($order_by == '' && $order == '')) {print '<li><b>By Date Visited (New to Old)</b></li>';} else {print '<li><a href="/history/?order_by=bydatevisited&order=desc">By Date Visited (New to Old)</a></li>';}
                           if ($order_by == 'bydatevisited' && $order == 'asc') {print '<li><b>By Date Visited (Old to New)</b></li>';} else {print '<li><a href="/history/?order_by=bydatevisited&order=asc">By Date Visited (Old to New)</a></li>';}
                           ?>
                        </ul>
                     </fieldset>
                  </div>
               </div>
               <div id="center_column">
                  <div id="center_column_inner">
                     <?php

                           $search_results_container_count = 0;   // Used to handle keyboard shortcut navigation of results

                              // List the user's recent history
                              if (count($results['entries']) > 0) {
                                foreach ($results['entries'] as $entry) {
                                   $search_results_container_count++;
                                   $nugget = new Nugget($entry['id'],$db);

                                   // Decide whether we need limiting dots
                                   if (strlen($nugget->getBody()) > $SITE['search']['max_chars_displayed']) {
                                      $trailing_dots = '...';
                                   } else {
                                      $trailing_dots = '';
                                   }
                                   print '<span class="search_results_container" id="search_results_container_'.$search_results_container_count.'">';
                                      // Title
                                      print '<span class="search_results_title"><a href="'.$nugget->return_permalink().'">'.$nugget->getTitle().'</a></span>';
                                      // Date
                                      print '<span class="search_results_date">Visited <abbr class="timeago" title="'.date("c",$entry['dt_visited']).'">'.date("l dS \of F, o @ G:i",$entry['dt_visited']).'</abbr></span>';
                                      // Sample of Nugget
                                      print '<span class="search_results_description">'.trim(substr(strip_tags($nugget->getBody()),0,$SITE['search']['max_chars_displayed'])).' <b>'.$trailing_dots.'</b></span>';
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
                                   $first = '<a href="/history/1/?order_by='.$order_by.'&order='.$order.'">First</a> | ';
                                } else {
                                   $first = 'First | ';
                                }

                                // Decide on whether there is a 'prev'
                                if ($page_number > 1) {
                                   $prev = '<a href="/history/'.($page_number - 1).'/?order_by='.$order_by.'&order='.$order.'">Prev</a> | ';
                                } else {
                                   $prev = 'Prev | ';
                                }

                                // Decide on whether there is a 'next'
                                if ($page_number < $number_of_pages) {
                                   $next = '<a href="/history/'.($page_number + 1).'/?order_by='.$order_by.'&order='.$order.'">Next</a> | ';
                                } else {
                                   $next = 'Next | ';
                                }

                                // Decide on whether there is a 'last' link
                                if ($page_number < $last_page_number) {
                                   $last = '<a href="/history/'.($last_page_number).'/?order_by='.$order_by.'&order='.$order.'">Last</a>';
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
                                      print '<a href="/history/'.$i.'/?order_by='.$order_by.'&order='.$order.'">'.$i.'</a> | ';
                                   } else {
                                      print '<b>'.$i.'</b> | ';
                                   }
                                }
                                print $next.$last.'</p>';
                              } else {
                                print '<p>Sorry, there are no nuggets to display.</p>';
                              }
                           // </editor-fold>

                     ?>
                  </div>
               </div>
            </div>
         </div>
         <?php require_once("includes/footer.php");?>
      </div>
	</body>
</html>