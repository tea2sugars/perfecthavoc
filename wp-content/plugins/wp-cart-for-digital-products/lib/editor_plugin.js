(function() {
tinymce.create('tinymce.plugins.WPeStore', {
init : function(ed, url) {
// Register commands, mceWPeStore is name of command to be executed.
ed.addCommand('mceWPeStore', function() {
ed.windowManager.open({
file : url + '/eStore_shortcode_insert_window.php',
height : 180 + parseInt(ed.getLang('wpEstore.delta_height', 0)),
width : 420,
inline : 1
}, {
plugin_url : url
});
});

// Register buttons,this is the button will be displayed on wordpress rich editor
ed.addButton('wpEstoreButton', {title : 'WP eStore Shortcodes', cmd : 'mceWPeStore', image:url + '/lib_images/eStore-tiny-mce-button.png'});
},

getInfo : function() {
return {
longname : 'WP eStore',
author : 'Ruhul Amin',
authorurl : 'http://www.tipsandtricks-hq.com',
infourl : 'http://www.tipsandtricks-hq.com',
version : tinymce.majorVersion + "." + tinymce.minorVersion
};
}
});

// Register plugin
tinymce.PluginManager.add('wpEstore', tinymce.plugins.WPeStore);
})();