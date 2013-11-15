<div id="footer_outter">
   <div id="footer_inner">
      <p>
         <a href="/" title="take me to the front page of <?=$SITE['site_name'];?>">Home</a> |
         <a href="/about/" title="tell me more about <?=$SITE['site_name'];?>">About</a>
      </p>
      <?php
         // Check to see if the user is logged in. If they are, print a message to state who they're logged in as
         if (isset($user_id)) {
            print '<p>Currently logged in as: <b>'.$current_user->getUsername().'</b></p>';
         }
      ?>
      <p>Copyright 2010, <?=$SITE['site_name'];?></p>
   </div>
</div>