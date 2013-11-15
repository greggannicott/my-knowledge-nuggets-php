<fieldset class="menus">
<legend>Site Menu</legend>
<?php
   if (isset($user_id)) {
      print '<ul>';
         print '<li><a href="/">Home</a></li>';
         print '<li><a href="/add/">Add New Nugget</a></li>';
         print '<li><a href="/users/'.$current_user->getUsername().'/nuggets/1/">My Nuggets</a></li>';
         print '<ul>';
            print '<li><a href="#">Published</a></li>';
            print '<li><a href="#">Drafts</a></li>';
         print '</ul>';
         print '<li><a href="/history/">History</a></li>';
         print '<li><a href="/settings/">Settings</a></li>';
         print '<li><a href="/logout/">Log Out</a></li>';
      print '</ul>';
   } else {
      print '<ul>';
         print '<li><a href="/">Home</a></li>';
         print '<li><a href="/login/">Login</a></li>';
         print '<li><a href="/join/">Join</a></li>';
         print '<li><a href="/about/">About</a></li>';
      print '</ul>';
   }
?>
</fieldset>