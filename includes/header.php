<div id="header_outter">
   <div id="header_inner">
      <form name="search" action="/search.php" method="get" id="search_form">
         <input type="text" id="q" name="q" <?php print ($query != '' ? 'value="'.stripslashes($query).'"' : null); ?> > <input type="submit" value="search">
         <input type="hidden" name="user" value="<?=$user_id;?>">
      </form>
      <div id="page_title"><h1><?=$SITE['site_name'];?></h1></div>
      <div id="top_menu"><?php include("includes/top_menu.php"); ?></div>
   </div>
</div>