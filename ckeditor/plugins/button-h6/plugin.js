// This plugin represents a command to apply the 'H6' element to selected text.

(function(){
 var a= {
  exec:function(editor){
   var format = {
    element : 'h6',
    attributes : { 'class' : 'item_body_heading_6' }
   };
   var style = new CKEDITOR.style(format);
   style.apply(editor.document);
  }
 },

 b="button-h6";
 CKEDITOR.plugins.add(b,{
  init:function(editor){
   editor.addCommand(b,a);
  }
 });
})();