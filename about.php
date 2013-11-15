<?php require_once("includes/config.php"); ?>
<html>
	<head>
        <title><?=$SITE['site_name'];?> - About</title>
        <link rel="search" type="application/opensearchdescription+xml" title="My Knowledge Nuggets" href="http://mkn.gannicott.co.uk/opensearch_all.xml" />
        <link rel="stylesheet" type="text/css" href="/styles/generic.css" />
        <script type="text/javascript" src="/javascript/jquery-1.3.2.js"></script>
        <script type="text/javascript" src="/javascript/jquery.hotkeys-0.7.9.min.js"></script>
        <!-- Include global keyboard shortcuts -->
        <?php require_once('includes/global_keyboard_shortcuts.php');?>
	</head>
	<body>
      <div id="container">
         <?php include("includes/header.php"); ?>
         <div id="main_content_outter">
            <div id="main_content_inner">
               <h1>About</h1>
               <p><i>This obviously needs further work. Consider a table of contents, and some screen shots would be good also...</i></p>
               <h2>What is <?=$SITE['site_name'];?>?</h2>
               <p><?=$SITE['site_name'];?> is a place for you to store all your nuggets of knowledge in one easy to maintain location, resulting in your own personal knowledge base.</p>
               <h2>Sharing the Knowledge</h2>
               <p>The primary use of the site is to offer you a home for all your knowledge, no matter how big or small. But by optionally making those nuggets public, as well as your own knowledge base Nuggets, you and others will be able to tap into the collection of all our knowledge bases.</p>
               <h2>What Would you Store?</h2>
               <p>Using the rich-text editor to create your content, among other items, you can store:</p>
               <ul>
                  <li>This</li>
                  <li>That</li>
                  <li>And Tother</li>
               </ul>
               <h2>Similar Solutions</h2>
               <p>We created this site based on a need for such a service. However, before creating the site we did use other services to achieve the same goal. For us, they weren't quite what we were looking for. However, they might be just right for you, so we thought it would be useful to mention them here. To an extent, these acted as an inspiration for <?=$SITE['site_name'];?>:</p>
               <ul>
                  <li><a href="http://evernote.com">Evernote</a></li>
                  <li>Notepad (my very first knowledge base was manually maintained within Notepad)</li>
                  <li>A Wiki (eg. Mindtouch)</li>
                  <li>Microsoft Excel</li>
                  <li><a href="http://projects.gnome.org/tomboy/">Tomboy</a></li>
               </ul>
               <h2>Software Used</h2>
               <p>This site was created with the help of the following software:</p>
               <p><i>Hyperlink these, and perhaps offer some detail as to what they do. Use Definition Lists if you do.</i></p>
               <ul>
                  <li>Apache</li>
                  <li>PHP</li>
                  <li>MySQL</li>
                  <li>CKEditor</li>
                  <li>jQuery</li>
                  <li>jQuery Timeago Plugin</li>
                  <li>jQuery AutoSuggest Plugin</li>
                  <li>jQuery HotKeys Plugin</li>
               </ul>
               <h2>Open Standards Supported</h2>
               <p><?=$SITE['site_name'];?> supports the open standards listed below. If there is a standard you'd like to see supported, please let us know and we'll see what we can do.</p>
               <p><i>Requires formatting:</i></p>
               <dl>
                  <dt><a href="http://www.opensearch.org">Open Search</a></dt>
                  <dd>Enables you to easily add the ability to search your Nuggets to your browser.</dd>
                  <dt><a href="https://wiki.mozilla.org/Labs/Weave/Identity/Account_Manager/Spec/Latest">Mozilla Account Manager</a></dt>
                  <dd>Enables you to manage your identity using the browser rather than site.</dd>
                  <dt><a href="http://www.webhooks.org/">Web Hooks</a></dt>
                  <dd>Empowers you to run your own hosted scripts when certain actions are performed on <?=$SITE['site_name'];?>.</dd>
               </dl>
            </div>
         </div>
         <?php require_once("includes/footer.php");?>
      </div>
	</body>
</html>
