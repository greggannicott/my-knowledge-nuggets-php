// This plugin represents a command to apply the 'H2' element to selected text.

(function(){
 var a= {
  exec:function(editor){
   var format = {
    element : 'h2',
    attributes : { 'class' : 'item_body_heading_2' }
   };
   var style = new CKEDITOR.style(format);
   style.apply(editor.document);
  }
 },

 b="button-h2";
 CKEDITOR.plugins.add(b,{
  init:function(editor){
   editor.addCommand(b,a);
  }
 });
})();