$(document).bind('keyup',{combi:'/',disableInInput:true},focus_search);

function focus_search() {
   $("#q").focus();
   // Return false to prevent any default behavour by the browser
   return false;
}