// This plugin represents a command to apply the 'pre' element to selected text.

(function(){
 var a= {
  exec:function(editor){
   var format = {
    element : 'pre'
   };
   var style = new CKEDITOR.style(format);
   style.apply(editor.document);
  }
 },

 b="button-pre";
 CKEDITOR.plugins.add(b,{
  init:function(editor){
   editor.addCommand(b,a);
  }
 });
})();