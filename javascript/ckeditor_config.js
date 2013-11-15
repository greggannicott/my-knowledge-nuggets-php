CKEDITOR.editorConfig = function( config )
{
    config.uiColor = '#F0F0FF';
    config.width = '630px';
    config.height = '430px';
    config.resize_enabled = false;
    config.stylesCombo_stylesSet = 'mkn:/styles/ckeditor_styles.js';
    config.contentsCss = '/styles/knowledge_item_body.css';
    CKEDITOR.config.removeFormatTags = 'b,big,code,del,dfn,em,font,i,ins,kbd,q,samp,small,span,strike,strong,sub,sup,tt,u,var,h1,h2,h3,h4,h5,h6';
    config.toolbar = 'Standard';
    config.toolbar_Standard =
    [
       ['Source','Maximize','-','SpellChecker', 'Scayt'],
       ['Undo','Redo','-','Find','Replace','-','PasteText','RemoveFormat'],
       ['Bold','Italic','Underline','Strike'],
       ['NumberedList','BulletedList'],['-','Outdent','Indent','Blockquote'],
       ['Link','Unlink','Anchor'],
       ['Image','Table','HorizontalRule','SpecialChar'],['TextColor','BGColor'],
       ['Styles','Font','FontSize'],['Code'],['About']
    ];
    config.extraPlugins = 'syntaxhighlight,tableresize,button-h1,button-h2,button-h3,button-h4,button-h5,button-h6,button-pre';
    config.keystrokes = [
       [ CKEDITOR.CTRL + 49 /*1*/, 'button-h1' ],
       [ CKEDITOR.CTRL + 50 /*2*/, 'button-h2' ],
       [ CKEDITOR.CTRL + 51 /*3*/, 'button-h3' ],
       [ CKEDITOR.CTRL + 52 /*4*/, 'button-h4' ],
       [ CKEDITOR.CTRL + 53 /*5*/, 'button-h5' ],
       [ CKEDITOR.CTRL + 54 /*6*/, 'button-h6' ],
       [ CKEDITOR.CTRL + CKEDITOR.SHIFT + 70 /*F*/, 'button-pre' ],
    ];
};