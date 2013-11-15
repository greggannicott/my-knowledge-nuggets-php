<?php require_once("includes/config.php"); ?>
<?php

   // before we go any further, lets establish some things...

   // <editor-fold defaultstate="collapsed" desc="Generate Required Search Results">

   // We only want to perform a search if we have a query
   $query = $_GET['q'];
   $privacy_level = $_GET['privacy_level'];
   $draft = $_GET['draft'];
   $order_by = $_GET['order_by'];
   $order = $_GET['order'];

   if ($query != '') {

      // <editor-fold defaultstate="collapsed" desc="Establish Generic Search Criteria">
      // Some of these are overriden later on depending on the search type
      $scope = null;
      $scope_params = null;
      $order_by_converted = null;
      $order_converted = null;

      // Create a user object
      if ($_GET['user_id'] != '') {
         // If a user ID has been provided, use that
         $user = new User($_GET['user_id'],$db);
      } else {
         // Otherwise, go with the default (ie. the cookie)
         $user = new User($user_id,$db);
      }

      // Set the number of nuggets to display per page... (this needs to be placed into site config or somewhere)
      $nuggets_per_page = $SITE['search']['results_per_page'];

      // If we don't have a starting nugget number, set one
      if (isset($_GET['page_number'])) {
         $page_number = $_GET['page_number'];
      } else {
         $page_number = 1;
      }

      // Determine the scope
      // If the user is not logged in, offer up a non user search
      if (!isset($user_id)) {
         $scope = 'notuser';
      // If a scope has been provided AND user is logged in go with scope provided
      } elseif (isset($_GET['scope']) && isset($user_id)) {
         $scope = $_GET['scope'];
      // Otherwise go with the default (which is dependent on whether user is logged in or not)
      } else {
         if (isset($user_id)) {
            $scope = 'user';
         } else {
            $scope = 'notuser';
         }
      }

      // Create the scope params
      // These can be overriden later on
      $scope_params = array (
         type => $scope,
         user_id => $user->getId(),
         privacy_level => $privacy_level
      );

      // Generate the sort parameters
      // Set a default if values do not exist..
      if ($order_by == '' || $order == '') {
         $order_by = 'byrelevance';
         $order = 'desc';
      }

      // order_by: Convert the URL to something SQL can understand
      // Note: they're inserted into arrays to enable multiple order criteria
      switch ($order_by) {
         case 'bydatecreated':
            $order_by_converted[] = "results.dt_created";
            $order_converted[] = $order;
            break;
         case 'bydatemodified':
            $order_by_converted[] = "results.dt_last_mod";
            $order_converted[] = $order;
            break;
         case 'byrelevance':
            print "byrelevance";
            // Primary order method is tag score
            $order_by_converted[] = 'results.tag_score';
            $order_converted[] = 'desc';  // Overide any order given... it should be desc for relevance
            // Secondary order method is popularity
            $order_by_converted[] = 'results.hits';
            $order_converted[] = 'desc';  // Overide any order given... it should be desc for popularity
            break;
      }

      // </editor-fold>

      // <editor-fold defaultstate="collapsed" desc="If scope = 'user'">
      // If the scope is equal to 'user', we simply want to generate paginated results
      if ($scope == 'user') {

         $page_title = 'My Nuggets';

         // Determing whether draft nuggets should be included
         if ($draft) {
            $draft_param = $draft;
         } else {
            $draft_param = 'exclude';  // exclude drafts
         }

         // Configure the sort
         $sorting = array (
              order_by => $order_by_converted,
              order => $order_converted
            );

         // Pull it all together to create the params we're going to pass in
         $query_params = array(
              scope => $scope_params,
              pagination => array (
                 starting_point => (($page_number - 1) * $nuggets_per_page),
                 number_of_results => $nuggets_per_page
              ),
              sorting => $sorting,
              search => array(
                 term => $query,
                 tag_multiplier => $SITE['search']['tag_multiplier']
              ),
              draft => $draft_param  // Exclude draft nuggets
            );

         // Now pull back the results
         $kb = new Nugget_Store($db);
         $results = $kb->query($query_params);
      }
      // </editor-fold>

      // <editor-fold defaultstate="collapsed" desc="If scope = 'notuser'">
      // If the scope is equal to notuser, we simply want to generate paginated results
      if ($scope == 'notuser') {

         $page_title = 'Other People\'s Nuggets';

         $sorting = array (
           order_by => $order_by_converted,
           order => $order_converted
         );

         // If the user isn't logged in, we need to override the user id
         // When the query is run it needs to know to not query the id
         if (!isset($user_id)) {
            $scope_params['user_id'] = null;
         }

         // Pull it all together to create the params we're going to pass in
         $query_params = array(
              scope => $scope_params,
              pagination => array (
                 starting_point => (($page_number - 1) * $nuggets_per_page),
                 number_of_results => $nuggets_per_page
              ),
              sorting => $sorting,
              search => array(
                 term => $query,
                 tag_multiplier => $SITE['search']['tag_multiplier']
              ),
              draft => 'exclude'  // Exclude draft nuggets
            );

         // Now pull back the results
         $kb = new Nugget_Store($db);
         $results = $kb->query($query_params);
      }
      // </editor-fold>

   }

   // </editor-fold>

?>
<html>
	<head>
         <title><?=$SITE['site_name'];?> - Search Results</title>
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
            if ($scope == 'user') {
               print 'total_nuggets_onscreen = '.count($results['entries']).';';
            } else if ($scope == 'notuser') {
               print 'total_nuggets_onscreen = '.count($results['entries']).';';
            }
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
               <h1>Search Results</h1>
               <div id="right_column">
                  <div id="right_column_inner">
                     <!-- Needs to be coded.. here more to help with layout for now -->
                     <fieldset>
                        <legend>Sort Order</legend>
                        <ul class="right_menu_items">
                           <?php
                           // Relevance
                           if ($order_by == 'byrelevance') {print '<li><b>Relevance</b></li>';} else {print '<li><a href="search.php?q='.urlencode($query).'&scope='.$scope.'&user='.$user->getId().'&privacy_level='.$privacy_level.'&draft='.$draft_param.'&order_by=byrelevance&order=desc" title="order results by relevance">Relevance</a></li>';}
                           // Created
                           if ($order_by == 'bydatecreated' && $order == 'desc') {print '<li>Created: <b>Newest</b> | <a href="search.php?q='.urlencode($query).'&scope='.$scope.'&user='.$user->getId().'&privacy_level='.$privacy_level.'&draft='.$draft_param.'&order_by=bydatecreated&order=asc" title="order results by oldest to newest">Oldest</a></li>';}
                           if ($order_by == 'bydatecreated' && $order == 'asc') {print '<li>Created: <a href="search.php?q='.urlencode($query).'&scope='.$scope.'&user='.$user->getId().'&privacy_level='.$privacy_level.'&draft='.$draft_param.'&order_by=bydatecreated&order=desc" title="order results by newest to oldest">Newest</a> | <b>Oldest</b></li>';}
                           if ($order_by != 'bydatecreated') {print '<li>Created: <a href="search.php?q='.urlencode($query).'&scope='.$scope.'&user='.$user->getId().'&privacy_level='.$privacy_level.'&draft='.$draft_param.'&order_by=bydatecreated&order=desc" title="order results by newest to oldest">Newest</a> | <a href="search.php?q='.urlencode($query).'&scope='.$scope.'&user='.$user->getId().'&privacy_level='.$privacy_level.'&draft='.$draft_param.'&order_by=bydatecreated&order=asc" title="order results by oldest to newest">Oldest</a></li>';}
                           // Modified
                           if ($order_by == 'bydatemodified' && $order == 'desc') {print '<li>Last Modified: <b>Latest</b> | <a href="search.php?q='.urlencode($query).'&scope='.$scope.'&user='.$user->getId().'&privacy_level='.$privacy_level.'&draft='.$draft_param.'&order_by=bydatemodified&order=asc" title="order results by oldest to newest">Oldest</a></li>';}
                           if ($order_by == 'bydatemodified' && $order == 'asc') {print '<li>Last Modified: <a href="search.php?q='.urlencode($query).'&scope='.$scope.'&user='.$user->getId().'&privacy_level='.$privacy_level.'&draft='.$draft_param.'&order_by=bydatemodified&order=desc" title="order results by newest to oldest">Latest</a> | <b>Oldest</b></li>';}
                           if ($order_by != 'bydatemodified') {print '<li>Last Modified: <a href="search.php?q='.urlencode($query).'&scope='.$scope.'&user='.$user->getId().'&privacy_level='.$privacy_level.'&draft='.$draft_param.'&order_by=bydatemodified&order=desc" title="order results by newest to oldest">Latest</a> | <a href="search.php?q='.urlencode($query).'&scope='.$scope.'&user='.$user->getId().'&privacy_level='.$privacy_level.'&draft='.$draft_param.'&order_by=bydatemodified&order=asc" title="order results by oldest to newest">Oldest</a></li>';}
                           ?>
                        </ul>
                     </fieldset>
                     <?php
                     // For now we only want filtering when a search is taking place
                     if ($query != '') {
                        print '<fieldset>';
                           print '<legend>Filters</legend>';
                           if ($user->getId() != NULL) {

                              // Who's nuggets
                              print '<ul class="right_menu_items">';
                                 if ($scope == 'user') {print '<li><b>My Nuggets</b></li>';} else {print '<li><a href="search.php?q='.urlencode($query).'&scope=user&user='.$user->getId().'&draft='.$draft_param.'&order_by='.$order_by.'&order='.$order.'">My Nuggets</a></li>';}
                                 if ($scope == 'notuser') {print '<li><b>Other People\'s Nuggets</b></li>';} else {print '<li><a href="search.php?q='.urlencode($query).'&scope=notuser&user='.$user->getId().'&draft='.$draft_param.'&order_by='.$order_by.'&order='.$order.'">Other People\'s Nuggets</a></li>';}
                              print '</ul>';

                              if ($scope == 'user') {

                                 // Scope
                                 print '<ul class="right_menu_items">';
                                    if ($privacy_level == 'all' || $privacy_level == '') {print '<li><b>All Nuggets</b></li>';} else {print '<li><a href="search.php?q='.urlencode($query).'&scope='.$scope.'&user='.$user->getId().'&privacy_level=all&draft='.$draft_param.'&order_by='.$order_by.'&order='.$order.'" title="display all my nuggets">All Nuggets</a></li>';}
                                    if ($privacy_level == 'public') {print '<li><b>My Public Nuggets</b></li>';} else {print '<li><a href="search.php?q='.urlencode($query).'&scope='.$scope.'&user='.$user->getId().'&privacy_level=public&draft='.$draft_param.'&order_by='.$order_by.'&order='.$order.'" title="only display my public nuggets">My Public Nuggets</a></li>';}
                                    if ($privacy_level == 'private') {print '<li><b>My Private Nuggets</b></li>';} else {print '<li><a href="search.php?q='.urlencode($query).'&scope='.$scope.'&user='.$user->getId().'&privacy_level=private&draft='.$draft_param.'&order_by='.$order_by.'&order='.$order.'" title="only display my private nuggets">My Private Nuggets</a></li>';}
                                 print '</ul>';

                                 // Draft Status
                                 print '<ul class="right_menu_items">';
                                    if ($draft_param == 'exclude') {print '<li><b>Exclude Draft Nuggets</b></li>';} else {print '<li><a href="search.php?q='.urlencode($query).'&scope='.$scope.'&user='.$user->getId().'&privacy_level='.$privacy_level.'&draft=exclude&order_by='.$order_by.'&order='.$order.'" title="exclude draft nuggets">Exclude Draft Nuggets</a></li>';}
                                    if ($draft_param == 'include') {print '<li><b>Include Draft Nuggets</b></li>';} else {print '<li><a href="search.php?q='.urlencode($query).'&scope='.$scope.'&user='.$user->getId().'&privacy_level='.$privacy_level.'&draft=include&order_by='.$order_by.'&order='.$order.'" title="include draft nuggets along side published nuggets">Include Draft Nuggets</a></li>';}
                                    if ($draft_param == 'only') {print '<li><b>Only Draft Nuggets</b></li>';} else {print '<li><a href="search.php?q='.urlencode($query).'&scope='.$scope.'&user='.$user->getId().'&privacy_level='.$privacy_level.'&draft=only&order_by='.$order_by.'&order='.$order.'" title="display only draft nuggets">Only Draft Nuggets</a></li>';}
                                 print '</ul>';

                              }

                           } else {
                              print '<li><i>No filters available to guests. Please <a href="/login/"  title="log in to '.$SITE['site_name'].'">login</a> or <a href="/join/" title="register to setup your own knowledge base - it\'s free">register</a></i>.</li>';
                           }
                        print '</fieldset>';
                     }
                     ?>
                  </div>
               </div>
               <div id="center_column">
                  <div id="center_column_inner">
                     <?php

                        // ** SEARCH RESULTS

                        if ($query != '') {

                           // <editor-fold defaultstate="collapsed" desc="Search Results: where scope == 'user' or 'notuser'">
                           // If we have results, display them
                           if ($scope == 'user' OR $scope == 'notuser') {

                              // Display the results title
                              print '<span class="search_results_section_heading_container">';
                                 print '<span class="search_results_section_sub_heading">'.$page_title.'</span>';
                                 print '<span class="search_results_section_heading_results_count">'.$results['statistics']['rows_found'].' Result(s)</span>';
                              print '</span>';

                              $search_results_container_count = 0;   // Used to handle keyboard shortcut navigation of results

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
                                      print '<span class="search_results_date">Created <abbr class="timeago" title="'.date("c",$nugget->getDt_created()).'">'.date("l dS \of F, o @ G:i",$nugget->getDt_created()).'</abbr></span>';
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
                                   $first = '<a href="/search.php?q='.urlencode($query).'&scope='.$scope.'&user='.$user->getId().'&page_number=1&privacy_level='.$privacy_level.'&draft='.$draft_param.'&order_by='.$order_by.'&order='.$order.'">First</a> | ';
                                } else {
                                   $first = 'First | ';
                                }

                                // Decide on whether there is a 'prev'
                                if ($page_number > 1) {
                                   $prev = '<a href="/search.php?q='.urlencode($query).'&scope='.$scope.'&user='.$user->getId().'&page_number='.($page_number - 1).'&privacy_level='.$privacy_level.'&draft='.$draft_param.'&order_by='.$order_by.'&order='.$order.'">Prev</a> | ';
                                } else {
                                   $prev = 'Prev | ';
                                }

                                // Decide on whether there is a 'next'
                                if ($page_number < $number_of_pages) {
                                   $next = '<a href="/search.php?q='.urlencode($query).'&scope='.$scope.'&user='.$user->getId().'&page_number='.($page_number + 1).'&privacy_level='.$privacy_level.'&draft='.$draft_param.'&order_by='.$order_by.'&order='.$order.'">Next</a> | ';
                                } else {
                                   $next = 'Next | ';
                                }

                                // Decide on whether there is a 'last' link
                                if ($page_number < $last_page_number) {
                                   $last = '<a href="/search.php?q='.urlencode($query).'&scope='.$scope.'&user='.$user->getId().'&page_number='.($last_page_number).'&privacy_level='.$privacy_level.'&draft='.$draft_param.'&order_by='.$order_by.'&order='.$order.'">Last</a>';
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
                                      print '<a href="/search.php?q='.urlencode($query).'&scope='.$scope.'&user='.$user->getId().'&page_number='.$i.'&privacy_level='.$privacy_level.'&draft='.$draft_param.'&order_by='.$order_by.'&order='.$order.'">'.$i.'</a> | ';
                                   } else {
                                      print '<b>'.$i.'</b> | ';
                                   }
                                }
                                print $next.$last.'</p>';
                              } else {
                                print '<p>Sorry, your query did not return any results. Please refine your search terms.</p>';
                              }
                           }
                           // </editor-fold>

                        } else {
                           print '<span class="search_results_section_heading_container">';
                              print '<span class="search_results_section_sub_heading">Invalid Search</span>';
                              print '<span class="search_results_section_heading_results_count">0 Result(s)</span>';
                           print '</span>';
                           print '<p>Sorry we\'re unable to perform a search as you have not provided a search query.';
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