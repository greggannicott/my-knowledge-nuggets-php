// This plugin represents a command to apply the 'H1' element to selected text.

(function(){
 var a= {
  exec:function(editor){
   var format = {
    element : 'h1',
    attributes : { 'class' : 'item_body_heading_1' }
   };
   var style = new CKEDITOR.style(format);
   style.apply(editor.document);
  }
 },

 b="button-h1";
 CKEDITOR.plugins.add(b,{
  init:function(editor){
   editor.addCommand(b,a);
  }
 });
})();