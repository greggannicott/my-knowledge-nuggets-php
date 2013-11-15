<?php require_once("includes/config.php"); ?>
<?php
// If the user has submitted data, add it and redirect the user to the appropriate page
if (isset($_POST['submit'])) {
    try {
        if ($_POST['id'] == '') {
           throw new Exception("Unable to update entry. No ID provided.",PEAR_LOG_ERR);
        }

        // Convert the tags to an array
        $tags = split(" ",trim($_POST['tags']));

        $item = new Nugget($_POST['id'], $db);
        $item->setTitle($_POST['title']);
        $item->setBody($_POST['body']);
        $item->setTags($tags);
        $item->setPublic($_POST['public']);
        $item->setDraft($_POST['draft']);
        $item->setRelated_links(Nugget_Related_Links::combine_form_data($_POST['related_link_url'], $_POST['related_link_title']));
        $item->update();
        header("Location: ".$item->return_permalink());
        exit;
    } catch (Exception $e) {
        // Redirect the user to the error page
        $host  = $_SERVER['HTTP_HOST'];
        $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        $extra = 'edit_redirect_error.php?error_message='.$e->getMessage();
        header("Location: http://$host$uri/$extra");
        exit;
    }
}

// Instantiate our nugget
if ($_GET['id'] != '') {
   $item = new Nugget($_GET['id'], $db);
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?=$SITE['site_name'];?> - Edit Nugget</title>
        <link rel="search" type="application/opensearchdescription+xml" title="My Knowledge Nuggets" href="http://mkn.gannicott.co.uk/opensearch_all.xml" />
        <link rel="stylesheet" type="text/css" href="styles/generic.css" />
        <link rel="stylesheet" type="text/css" href="/styles/jquery-ui-1.8.5.custom.css" />
        <script type="text/javascript" src="/javascript/jquery-1.4.2.min.js"></script>
        <script type="text/javascript" src="javascript/jquery.form.js"></script>
        <link rel="stylesheet" type="text/css" href="styles/knowledge_item_body.css" />
        <script type="text/javascript" src="javascript/jquery-1.3.2.js"></script>
        <script type="text/javascript" src="javascript/jquery.form.js"></script>
        <script type="text/javascript" src="javascript/jquery.blockUI.js"></script>
        <script type="text/javascript" src="ckeditor/ckeditor.js"></script>
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
                $("#title").focus();

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
               <?php
               if ($_GET['id'] != '') {
                  if ($item->getUser_id() == $current_user->getId()) {
                  ?>
                     <h1>Edit a Nugget</h1>
                     <form id="manage_nugget" name="manage_nugget" action="edit.php" method="post">
                        <div id="nugget_form_right">
                           <fieldset>
                              <legend>Properties</legend>
                                 <p>
                                    <!-- Visibility -->
                                    <?php
                                       if ($item->getPublic() == 1) {
                                          $checked = "checked";
                                       } else {
                                          $checked = "";
                                       }
                                    ?>
                                    <span class="properties"><input type="checkbox" name="public" id="public" TABINDEX=4 value="1" <?=$checked;?>> <label for="public">Public (visible to all users)</label></span>
                                    <!-- Draft -->
                                    <?php
                                       if ($item->getDraft() == 1) {
                                          $checked = "checked";
                                       } else {
                                          $checked = "";
                                       }
                                    ?>
                                    <span class="properties"><input type="checkbox" name="draft" id="draft" TABINDEX=5 value="1" <?=$checked;?>> <label for="draft">Draft</label></span>
                                 </p>
                           </fieldset>
                           <fieldset>
                              <legend>Tags</legend>
                                 <!-- Tags -->
                                 <div class="ui-widget search_box_container">
                                 <p>
                                    <label for="tags">Tags</label><br>
                                    <input id="tags" name="tags" type="text" value="<?php print $item->get_tags_string();?>" TABINDEX=5>
                                 </p>
                                 </div>
                           </fieldset>
                           <!-- buttons -->
                           <p><button type="button" name="cancel" id="cancel" TABINDEX=8 onClick="go_back();">Cancel</button> <button type="submit" name="submit" onclick="safe_to_leave = true;" TABINDEX=7>Save</button></p>
                        </div>
                        <div id="nugget_form_left">
                           <fieldset>
                              <legend>Your Nugget</legend>
                              <!-- Title -->
                              <p>
                                 <input id="title" name="title" type="text" TABINDEX=1 value="<?=$item->getTitle();?>" required autofocus>
                              </p>
                              <!-- Nugget Body -->
                              <p>
                                 <textarea id="body" name="body" TABINDEX=3><?=HTMLSPECIALCHARS($item->getBody());?></textarea>
                              </p>
                           </fieldset>
                        </div>
                        <input type="hidden" name="id" value="<?= $item->getId();?>">
                     </form>
                    <?
                  } else {
                     $logger->log("Unable to edit item. This isn't your Nugget to edit.", PEAR_LOG_ERR);
                  }
               } else {
                 $logger->log("Unable to edit item. No ID provided.",PEAR_LOG_ERR);
               }
               ?>
            </div>
         </div>
         <?php require_once("includes/footer.php");?>
      </div>
    </body>
</html>
