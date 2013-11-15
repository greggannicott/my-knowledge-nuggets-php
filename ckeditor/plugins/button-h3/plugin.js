// This plugin represents a command to apply the 'H3' element to selected text.

(function(){
 var a= {
  exec:function(editor){
   var format = {
    element : 'h3',
    attributes : { 'class' : 'item_body_heading_3' }
   };
   var style = new CKEDITOR.style(format);
   style.apply(editor.document);
  }
 },

 b="button-h3";
 CKEDITOR.plugins.add(b,{
  init:function(editor){
   editor.addCommand(b,a);
  }
 });
})();