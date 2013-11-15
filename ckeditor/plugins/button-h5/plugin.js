// This plugin represents a command to apply the 'H5' element to selected text.

(function(){
 var a= {
  exec:function(editor){
   var format = {
    element : 'h5',
    attributes : { 'class' : 'item_body_heading_5' }
   };
   var style = new CKEDITOR.style(format);
   style.apply(editor.document);
  }
 },

 b="button-h5";
 CKEDITOR.plugins.add(b,{
  init:function(editor){
   editor.addCommand(b,a);
  }
 });
})();