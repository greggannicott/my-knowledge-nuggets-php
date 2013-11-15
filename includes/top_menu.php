<?php

   // is user logged in?
   if (isset($user_id)) {
      print '<a href="/add/" style="float: right; margin-right: 0px; padding: 0px" class="menu_button" title="create a brand new nugget (a)">Add New Nugget</a><a href="/index.php" class="menu_button" title="take me to the front page of '.$SITE['site_name'].'">Home</a><a href="/users/'.$current_user->getUsername().'/nuggets/1/" class="menu_button" title="view a list of nuggets i have created">My Nuggets</a><a href="/history/" class="menu_button" title="view a history of nuggets i have recently viewed">History</a><a href="/settings/" class="menu_button" title="view and change my settings for '.$SITE['site_name'].'">Settings</a><a href="/logout.php" class="menu_button" title="log out of '.$SITE['site_name'].'">Log Out</a>';
   } else {
      print '<a href="/index.php" class="menu_button" title="take me to the front page of '.$SITE['site_name'].'">Home</a><a href="/login/" class="menu_button" title="log in to '.$SITE['site_name'].'">Login</a><a href="/join/" class="menu_button" title="register to setup your own knowledge base - it\'s free">Join</a><a href="/about/" class="menu_button" title="tell me more about '.$SITE['site_name'].'">About</a>';
   }

?>