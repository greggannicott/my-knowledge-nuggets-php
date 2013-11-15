<?php
require_once("includes/config.php");
// If the user has submitted data, add it and redirect the user to the appropriate page
if (isset($_POST['submit'])) {
    try {
        $store = new Nugget_Store($db);
        // Combine the related links (at present they are in two seperate variables)
        $related_links = Nugget_Related_Links::combine_form_data($_POST['related_link_url'], $_POST['related_link_title']);
        // Add the entry to the database
        $new_nugget_id = $store->add($_POST['title'],$_POST['body'],$_POST['public'],$_POST['tags'],$related_links,$user_id,$_POST['draft']);
        // Create an instance of the nugget so we can get it's url
        $nugget = new Nugget($new_nugget_id, $db);
        // Redirect the user to the url of this nugget
        header("Location: ".$nugget->return_permalink());
        exit;
    } catch (Exception $e) {
        // Redirect the user to the error page
        $host  = $_SERVER['HTTP_HOST'];
        $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $extra = 'add_redirect_error.php?error_message='.$e->getMessage();
        header("Location: http://$host$uri/$extra");
        exit;
    }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?=$SITE['site_name'];?> - Add New Nugget</title>
        <link rel="search" type="application/opensearchdescription+xml" title="My Knowledge Nuggets" href="http://mkn.gannicott.co.uk/opensearch_all.xml" />
        <link rel="stylesheet" type="text/css" href="/styles/generic.css" />
        <link rel="stylesheet" type="text/css" href="/styles/knowledge_item_body.css" />
        <link rel="stylesheet" type="text/css" href="/styles/jquery-ui-1.8.5.custom.css" />
        <script type="text/javascript" src="/javascript/jquery-1.4.2.min.js"></script>
        <script type="text/javascript" src="/javascript/jquery.form.js"></script>
        <script type="text/javascript" src="/javascript/jquery.blockUI.js"></script>
        <script type="text/javascript" src="/javascript/jquery.simplemodal-1.3.3.min"></script>
        <script type="text/javascript" src="/ckeditor/ckeditor.js"></script>
        <script type="text/javascript" src="/javascript/jquery.hotkeys-0.7.9.min.js"></script>
        <script type="text/javascript" src="/javascript/jquery-ui-1.8.5.custom.min.js"></script>
        <!-- Include global keyboard shortcuts -->
        <?php require_once('includes/global_keyboard_shortcuts.php');?>
        <!-- include some css to override site css that's affecting ckeditor -->
        <style>
           .cke_skin_kama input {font-size: 12px}
        </style>
        <style>
           .ui-autocomplete-loading { background: white url('/images/ui-anim_basic_16x16.gif') right center no-repeat;
        </style>
        <script type="text/javascript">

           // Variable used to track whether it's safe to exit
           var safe_to_leave = false;

           // Tell the browser to call warn_on_exit before changing
           window.onbeforeunload = warn_on_exit;
           
            // wait for the DOM to be loaded
            $(document).ready(function() {
                // Enable the CKEditor:
                CKEDITOR.replace('body',{customConfig : '/javascript/ckeditor_config.js'});
                // Give the title focus
                $("#title").focus().select();

            });

            // Prompt user to confirm they are sure they wish to exit without saving changes
            function go_back() {
               history.go(-1);
            }

            // Prompts the user to see if they really want to leave
            function warn_on_exit() {
               // If user is submitting data, it won't prompt them.
               if (!safe_to_leave) {
                  return "Are you sure you want to exit without saving?"
               }
            }

            // Auto Suggest
            $(function() {

               function split( val ) {
                  return val.split( /\s/ );
               }

               function extractLast( term ) {
                  return split( term ).pop();
               }

               $( "#tags" ).autocomplete({
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

        </script>
    </head>
	<body>
      <div id="container">
         <?php include("includes/header.php"); ?>
         <div id="main_content_outter">
            <div id="main_content_inner">
               <h1>Add a New Nugget</h1>
               <form id="manage_nugget" name="manage_nugget" action="/add.php" method="post">
                  <div id="nugget_form_right">
                     <fieldset>
                        <legend>Properties</legend>
                           <!-- Visibility -->
                           <p>
                              <?php
                              // Set the scope status based on the user's selected default
                              if ($current_user->getDefault_scope() == 'public') {
                                 $default_scope_checked = 'checked';
                              } else {
                                 $default_scope_checked = '';
                              }
                                 ?>
                              <span class="properties"><input type="checkbox" name="public" id="public" value="1" TABINDEX=4 <?=$default_scope_checked;?>> <label for="public">Public (visible to all users)</label></span>
                              <span class="properties"><input type="checkbox" name="draft" id="draft" value="1"  TABINDEX=5> <label for="draft">Draft</label></span>
                           </p>
                     </fieldset>
                     <fieldset>
                        <legend>Tags</legend>
                           <!-- Tags -->
                           <p>
                              <div class="ui-widget search_box_container">
                                 <label for="tags">Tags</label><br>
                                 <input id="tags" name="tags" type="text" TABINDEX=5 autocomplete="off">
                              </div>
                     </p>
                     </fieldset>
                     <!-- buttons -->
                     <p><button type="button" name="cancel" id="cancel" TABINDEX=8 onClick="go_back();">Cancel</button> <button type="submit" name="submit" onclick="safe_to_leave = true;" TABINDEX=7>Add Nugget</button></p>
                  </div>
                  <div id="nugget_form_left">
                     <fieldset>
                        <legend>Your Nugget</legend>
                        <!-- Title -->
                        <p>
                           <input id="title" name="title" type="text" TABINDEX=1 placeholder="The title of your Nugget" value="Nugget Title" required autofocus>
                        </p>
                        <!-- Nugget Body -->
                        <p>
                           <textarea id="body" name="body" TABINDEX=3></textarea>
                        </p>
                     </fieldset>
                  </div>
               </form>
            </div>
         </div>
         <?php require_once("includes/footer.php");?>
      </div>
    </body>
</html>
<!-- The 'add url' form -->
<div id="manage_related_link" style="display: none">
   <h1>Add Related Link</h1>
   <form name="related_link">
      <span>URL</span>
      <input type="text" name="related_link_url" id="related_link_url"> <input type="button" name="get_title" id="get_title" value="Get Title">
      <span>Title</span>
      <input type="text" name="related_link_title" id="related_link_title">
      <input type="button" name="add_link_button" id="add_link_button" onClick="add_link();" value="Add Link">
   </form>

</div>