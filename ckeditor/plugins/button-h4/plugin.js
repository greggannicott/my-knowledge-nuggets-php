// This plugin represents a command to apply the 'H4' element to selected text.

(function(){
 var a= {
  exec:function(editor){
   var format = {
    element : 'h4',
    attributes : { 'class' : 'item_body_heading_4' }
   };
   var style = new CKEDITOR.style(format);
   style.apply(editor.document);
  }
 },

 b="button-h4";
 CKEDITOR.plugins.add(b,{
  init:function(editor){
   editor.addCommand(b,a);
  }
 });
})();