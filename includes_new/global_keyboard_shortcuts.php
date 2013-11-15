<?php
print '<script type="text/javascript" src="/javascript/global_keyboard_shortcuts_all.js"></script>';
if (isset($current_user)) {
   print '<script type="text/javascript" src="/javascript/global_keyboard_shortcuts_logged_in.js"></script>';
}
?>