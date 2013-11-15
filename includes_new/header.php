<?php
   // If an error has occured, we want the header to change color
   // By including the 'errors' class, the CSS takes care of this.
   if (count($ERRORS) == 0) {
      $errors_style = '';
   } else {
      $errors_style = 'errors';
   }
?>
<div id="header" class="structure-outer <?=$errors_style;?>">
   <span class="structure-inner">
      <span id="logo">
         <?php echo $SITE['site_name'];?>
      </span>
      <span>
         <form name="search" action="/search.php" method="get" id="search-form">
            <input type="text" id="q" name="q" <?php print ($query != '' ? 'value="'.stripslashes($query).'"' : null); ?> ><input type="submit" value="search">
            <input type="hidden" name="user" value="<?=$user_id;?>">
         </form>
      </span>
   </span>
</div>