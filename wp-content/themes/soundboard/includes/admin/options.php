<?php

function optionsframework_option_name() {
        
        // This gets the theme name from the stylesheet (lowercase and without spaces)    
        if (function_exists('wp_get_theme')){
                $theme_data = wp_get_theme('theme-name');
                $themename = $theme_data->Name;
        } else {
                $theme_data = get_theme_data(STYLESHEETPATH . '/style.css');
                $themename = $theme_data['Name'];
        }    
        $themename = preg_replace("/\W/", "", strtolower($themename) );
        
        $optionsframework_settings = get_option('optionsframework');
        $optionsframework_settings['id'] = $themename;
        update_option('optionsframework', $optionsframework_settings);
}

function optionsframework_options() {
   
    // Pull all the categories into an array
    $options_categories = array();
    $options_categories_obj = get_categories();
    foreach ($options_categories_obj as $category) {
        $options_categories[$category->cat_ID] = $category->cat_name;
    }

    // Pull all the pages into an array
    $options_pages = array();
    $options_pages_obj = get_pages('sort_column=post_parent,menu_order');
    $options_pages[''] = 'Select a page:';
    foreach ($options_pages_obj as $page) {
        $options_pages[$page->ID] = $page->post_title;
    }

    // If using image radio buttons, define a directory path
    $imagepath =  get_bloginfo('stylesheet_directory') . '/includes/admin/images/';
    
    
    // VARIABLES        
    $shortname = "gg";
    
    $skin = array("light" => __('light','gxg_textdomain'), "dark" => __('dark','gxg_textdomain'),);
    
    $fonts = array("Francois One" => "default (Francois One)",
                   "Oleo Script" => "Oleo Script",
                    "Sancreek" => "Sancreek",
                    "Shojumaru" => "Shojumaru",
                    "Lilita One" => "Lilita One",
                    "Annie Use Your Telescope" => "Annie Use Your Telescope",
                    "Love Ya Like A Sister" => "Love Ya Like A Sister",
                    "Luckiest Guy" => "Luckiest Guy",
                    "Anonymous Pro" => "Anonymous Pro",
                    "Special Elite" => "Special Elite",
                    "Leckerli One" => "Leckerli One",
                    "Sansita One" => "Sansita One",
                    "Rokkitt" => "Rokkitt",
                    "Just Another Hand" => "Just Another Hand",
                    "Londrina Solid" => "Londrina Solid",
                    "Vibur" => "Vibur",
                    "Concert One" => "Concert One",
                    "Patrick Hand" => "Patrick Hand",
                    "Bevan" => "Bevan",
                    "Rancho" => "Rancho",
                    "Permanent Marker" => "Permanent Marker",
                    "Lemon" => "Lemon",
                    "Knewave" => "Knewave" );
    $trans = array("none" => "none", "uppercase" => "uppercase");
   

    // Pull all the slider posts into an array
    $args = array("numberposts" => -1 , "orderby" => "post_date" , "post_type" => "slider"); 
    $options_slides = array();
    $options_slides_obj = get_posts($args);
    $options_slides[''] = 'Select a slider:';
    foreach ($options_slides_obj as $page) {
        $options_slides[$page->ID] = $page->post_title;
    }


    // JW Player Versions
    $options_jwversion = array("default" => "select your JW Player version:", "version6" => "Version 6", "version5" => "Version 5 and older");
    
    // Past Tour Dates
    $options_pastdatesorder = array("DESC" => "new dates first", "ASC" => "old dates first");
    
    // Home Page excerpt or full post
    $options_homeposts = array("excerpt" => "excerpt", "full" => "full post");
    
    // Main menu font size
    $options_navifontsize = array(
                                  "12px" => "12px",
                                  "13px" => "13px",
                                  "14px" => "14px",
                                  "15px" => "15px",
                                  "16px" => "16px",
                                  "17px" => "17px",
                                  "18px" => "18px"
                                  );
    
    
    
    // OPTIONS    
    $options = array();        

//------------------------------------------------------------------------------
// GENERAL
//------------------------------------------------------------------------------

        $options[] = array( "name" => __('GENERAL','gxg_textdomain'),
                                                "type" => "heading",
                                                "img" => "/includes/admin/images/g.png");

        $options[] = array( "name" => __('Configure the general setup of your theme. Upload a logo and insert a google tracking code.','gxg_textdomain'),
        "type" => "info");



        $options[] = array( "name" => __('Activate JW Player','gxg_textdomain'),  
                                                "desc" => __('Check this box after you have downloaded the JW Player files and placed them in the jwplayer folder.','gxg_textdomain'),        
                                                "id" => $shortname."_jwplayer",
                                                "std" => "",
                                                "type" => "checkbox");
        
        $options[] = array( "name" => __('JW Player version','gxg_textdomain'),                                                
                                                "id" => $shortname."_jwversion",
                                                "type" => "select",
                                                "std" => "default",
                                                "options" => $options_jwversion);
        
        $options[] = array( "name" => __('Load WooCommerce stylesheets','gxg_textdomain'),  
                                                "desc" => __('Check this box after you have installed the WooCommerce plugin.','gxg_textdomain'),        
                                                "id" => $shortname."_woo",
                                                "std" => "",
                                                "type" => "checkbox");
        

        $options[] = array( "name" => __('Logo','gxg_textdomain'),
                                                "desc" => __('Upload your Logo','gxg_textdomain'),
                                                "id" => $shortname."_logo_image",
                                                "type" => "upload");
        
        $options[] = array( "name" => __('Center your Logo','gxg_textdomain'),
                                                "desc" => __('If you would like to center your logo, enter your logo\'s width here. If your logo has a width of 200px, enter: 200.','gxg_textdomain'),
                                                "id" => $shortname."_logo_center",
                                                "type" => "text");

        $options[] = array( "name" => __('Logo for Login page','gxg_textdomain'),
                                                "desc" => __('Upload a logo to replace the Wordpress logo on the login page','gxg_textdomain'),
                                                "id" => $shortname."_login_image",
                                                "type" => "upload");
        
        $options[] = array( "name" => __('Custom CSS','gxg_textdomain'),
                                                "desc" => __('Want to add any custom CSS code? Put it in here, and the rest is taken care of. This overrides any other stylesheets.','gxg_textdomain'),
                                                "id" => $shortname."_custom_css",
                                                "std" => "",
                                                "type" => "textarea");

        $options[] = array( "name" => __('Favicon','gxg_textdomain'),
                                                "desc" => __('Upload a 16 x 16 favicon','gxg_textdomain'),
                                                "id" => $shortname."_favicon",
                                                "std" => "",
                                                "type" => "upload");

        $options[] = array( "name" => __('Custom Gravatar','gxg_textdomain'),
                                                "desc" => __('Upload a Gravatar','gxg_textdomain'),
                                                "id" => $shortname."_gravatar",
                                                "std" => "",
                                                "type" => "upload");
        
        $options[] = array( "name" => __('404 Error','gxg_textdomain'),
                                                "desc" => __('Add your own text to display on error pages.','gxg_textdomain'),
                                                "id" => $shortname."_404error",
                                                "std" => "",
                                                "type" => "textarea");

        $options[] = array( "name" => __('Google Analytics Code','gxg_textdomain'),
                                                "desc" => __('Enter your Google Analytics or other tracking code here. It will be inserted before the closing body tag of your theme.','gxg_textdomain'),
                                                "id" => $shortname."_google_analytics",
                                                "std" => "",
                                                "type" => "textarea");
        
// THEME CUSTOMIZATION
        $options[] = array( "name" => __('Theme Customization','gxg_textdomain'),
                                                "desc" => "",
                                                "id" => "general_heading",
                                                "class" => "subheading",
                                                "type" => "info");        

        $options[] = array( "name" => __('Show author info in the news info section','gxg_textdomain'),
                                                "desc" => __('Check this box if your would like to show the name of the author with news posts','gxg_textdomain'),   
                                                "id" => $shortname."_author",
                                                "std" => "0",
                                                "type" => "checkbox");
        
        $options[] = array( "name" => __('Show tags in the news info section','gxg_textdomain'),
                                                "desc" => __('Check this box if your would like to display tags with news posts','gxg_textdomain'),   
                                                "id" => $shortname."_tags",
                                                "std" => "0",
                                                "type" => "checkbox");
        
        $options[] = array( "name" => __('Order of Past Tour Dates','gxg_textdomain'),                                                
                                                "id" => $shortname."_pastdatesorder",
                                                "type" => "select",
                                                "std" => "DESC",
                                                "options" => $options_pastdatesorder);
        
        $options[] = array( "name" => __('Hide Past Tour Dates Section','gxg_textdomain'),
                                                "desc" => __('Check this box if your would like to hide the Past Tour Dates Section','gxg_textdomain'),       
                                                "id" => $shortname."_pastdates",
                                                "std" => "0",
                                                "type" => "checkbox");

        $options[] = array( "name" => __('Hide entire Login Section in header','gxg_textdomain'),
                                                "desc" => __('Check this box if your would like to hide the entire Login Section in the header','gxg_textdomain'),       
                                                "id" => $shortname."_loginsection",
                                                "std" => "0",
                                                "type" => "checkbox");


        $options[] = array( "name" => __('Remove Comments','gxg_textdomain'),
                                                "id" => $shortname."_commentremove",
                                                "desc" => __('Remove all comment sections from the entire website','gxg_textdomain'),
                                                "std" => "0",
                                                "type" => "checkbox");


        $options[] = array( "name" => __('On Album page, display SoundCloud and JW Player within content instead of within a lightbox.','gxg_textdomain'),
                                                "desc" => __('Check this box if your would like to display the SoundCloud and JW Player right on the page instead of just a play icon.','gxg_textdomain'),       
                                                "id" => $shortname."_playerdisplay",
                                                "std" => "0",
                                                "type" => "checkbox");
        
        $options[] = array( "name" => __('Date instead of "time ago"','gxg_textdomain'),
                                                "id" => $shortname."_notimeago",
                                                "desc" => __('In homepage news section, news widget and video widget, display the date instead of "time ago"','gxg_textdomain'),
                                                "std" => "0",
                                                "type" => "checkbox");

//------------------------------------------------------------------------------
// STYLE
//------------------------------------------------------------------------------

        $options[] = array( "name" => __('STYLE','gxg_textdomain'),
                                                "type" => "heading",
                                                "img" => "/includes/admin/images/st.png");

        $options[] = array( "name" => __('Choose between light and dark skin and set a background image or color.','gxg_textdomain'),
        "type" => "info");
        
        $options[] = array( "name" => __('Theme Skin','gxg_textdomain'),
                                                "desc" => __('Choose light or dark skin','gxg_textdomain'),
                                                "id" => $shortname."_skin",
                                                "std" => "dark",
                                                "type" => "select",
                                                "options" => $skin);

        $options[] = array( "name" => __('Background Image','gxg_textdomain'),
                                                "desc" => __('Upload your own custom background image. (Make sure to select <strong>Full Size</strong> and click <strong>Use This Image</strong>)','gxg_textdomain'),
                                                "id" => $shortname."_bg_image_custom",
                                                "type" => "upload");

        $options[] = array( "name" => __('Background Position','gxg_textdomain'),
                                                "desc" => __('Choose background image positioning','gxg_textdomain'),
                                                "id" => $shortname."_bg_position",
                                                "std" => "repeat",
                                                "type" => "radio",
                                                "options" => array(
                                                        'repeat' => __('Repeat Background','gxg_textdomain'),
                                                        'stretched' => __('Stretched Background  /  Fixed Position','gxg_textdomain'),
                                                        'fixed' => __('No Repeat  /  Position: Top Center','gxg_textdomain'),
                                                        )
                                                );

        $options[] = array( "name" => __('Background Color','gxg_textdomain'),
                                                "desc" =>  __('Choose a simple color instead of a background image. This will override all background image settings above.','gxg_textdomain'),
                                                "id" => $shortname."_bg_color",
                                                "std" => "",
                                                "type" => "color");

        $options[] = array( "name" => __('Add box shadow to Menu, Slider and Main Content','gxg_textdomain'),
                                                "id" => $shortname."_shadow",
                                                "std" => "0",
                                                "type" => "checkbox");
        


//------------------------------------------------------------------------------
// HOME
//------------------------------------------------------------------------------

        $options[] = array( "name" => __('HOME','gxg_textdomain'),
                                                "type" => "heading",
                                                "img" => "/includes/admin/images/h.png");

        $options[] = array( "name" => __('Setup the  Homepage.','gxg_textdomain'),
        "type" => "info");

        $options[] = array( "name" => __('Title in center section on Homepage','gxg_textdomain'),
                                                "desc" => __('Enter the Title for the center section on the homepage. If you are displaying text instead of (or additionally to) the News feed, this headline will appear above it.','gxg_textdomain'),
                                                "id" => $shortname."_titlecenter",
                                                "std" => "",
                                                "type" => "text");   
        
        $options[] = array( "name" => __('News Title on Homepage','gxg_textdomain'),
                                                "desc" => __('Enter the Title for the News section on the homepage','gxg_textdomain'),
                                                "id" => $shortname."_newstitle",
                                                "std" => "News",
                                                "type" => "text");        

        $options[] = array( "name" => __('Number of Headlines','gxg_textdomain'),
                                                "desc" => __('Enter the number of headlines you would like to show on the homepage','gxg_textdomain'),
                                                "id" => $shortname."_headlines",
                                                "std" => "6",
                                                "type" => "text");

        $options[] = array( "name" => __('Preview Image for news','gxg_textdomain'),
                                                "id" => $shortname."_previewimage",
                                                "desc" => __('Display a preview image for the news on the homepage','gxg_textdomain'),
                                                "std" => "0",
                                                "type" => "checkbox");
        
        $options[] = array( "name" =>  __('News section on Homepage shows','gxg_textdomain'),
                                                "desc" => __('If you select "excerpt", no text styling or images will be displayed.','gxg_textdomain'),
                                                "id" => $shortname."_homeposts",
                                                "std" => "excerpt",
                                                "type" => "select",
                                                "options" => $options_homeposts );
        
        $options[] = array( "name" => __('MORE NEWS button','gxg_textdomain'),
                                                "desc" => __('If you would like to display a button below the homepage news section, that links to the news page, enter the button text here.','gxg_textdomain'),
                                                "id" => $shortname."_homenewsbutton",
                                                "std" => "",
                                                "type" => "text");        
        
        
     
//------------------------------------------------------------------------------
// TYPOGRAPHY
//------------------------------------------------------------------------------

        $options[] = array( "name" => __('TYPOGRAPHY','gxg_textdomain'),
                                                "type" => "heading",
                                                "img" => "/includes/admin/images/t.png");

        $options[] = array( "name" =>  __('Select your favorite font or upload one of hundreds of Google Web fonts. This applies to Headings, Button Text and the Navigation.','gxg_textdomain'),
        "type" => "info");

        $options[] = array( "name" => __('Predefined Fonts','gxg_textdomain'),
                                                "desc" => __('Choose a font for your Headings, Buttons and Menu','gxg_textdomain'),
                                                "id" => $shortname."_font",
                                                "std" => "Francois One",
                                                "type" => "select",
                                                "options" => $fonts);
        
        $options[] = array( "name" =>  __('Custom Google Web Font','gxg_textdomain'),
                                                "desc" => __('Simply enter the name of the Google font here. The rest is taken care of.','gxg_textdomain'),
                                                "id" => $shortname."_font2",
                                                "std" => "",
                                                "type" => "text");

        $options[] = array( "name" =>  __('Text Transform','gxg_textdomain'),
                                                "desc" => __('Some Headings and the Button text are UPPERCASE letters by default. However, some fonts simply dont look right with just uppercase letters. Here you can set the text transform to NONE','gxg_textdomain'),
                                                "id" => $shortname."_trans",
                                                "std" => "uppercase",
                                                "type" => "select",
                                                "options" => $trans);

        $options[] = array( "name" =>  __('Main menu font size','gxg_textdomain'),
                                                "desc" => __('Enter the font size for the main menu. If you want to use a font size of 14px, enter: 14','gxg_textdomain'),
                                                "id" => $shortname."_navifontsize",
                                                "std" => "14",
                                                "type" => "select",
                                                "options" => $options_navifontsize);


//------------------------------------------------------------------------------
// COLOR
//------------------------------------------------------------------------------

        $options[] = array( "name" => __('COLOR','gxg_textdomain'),
                                                "type" => "heading",
                                                "img" => "/includes/admin/images/col.png");

        $options[] = array( "name" => __('Set the theme color.','gxg_textdomain'),
        "type" => "info");


        $options[] = array( "name" => __('Predefined Theme Color','gxg_textdomain'),
                                                "desc" => __('Choose a color for links and buttons','gxg_textdomain'),
                                                "id" => $shortname."_link_color",
                                                "std" => "#fb2e2e",
                                                "type" => "images",
                                                "options" => array(
                                                        '#FDB813' => $imagepath . '/color/fdb813.jpg',                                                                                                       
                                                        '#ff842c' => $imagepath . '/color/ff842c.jpg', 
                                                        '#EF65A3' => $imagepath . '/color/ef65a3.jpg',
                                                        '#e265e4' => $imagepath . '/color/e265e4.jpg',
                                                        '#ff1190' => $imagepath . '/color/ff1190.jpg',
                                                        '#f05050' => $imagepath . '/color/f05050.jpg',
                                                        '#fb2e2e' => $imagepath . '/color/fb2e2e.jpg',
                                                        '#a02d2d' => $imagepath . '/color/a02d2d.jpg',
                                                        '#14b8f5' => $imagepath . '/color/14b8f5.jpg',
                                                        '#18CECE' => $imagepath . '/color/18cece.jpg',
                                                        '#29da23' => $imagepath . '/color/29da23.jpg',
                                                        '#008D2E' => $imagepath . '/color/008d2e.jpg',
                                                        '#677536' => $imagepath . '/color/677536.jpg',
                                                        '#7b7842' => $imagepath . '/color/7b7842.jpg',
                                                        '#9a8764' => $imagepath . '/color/9a8764.jpg',
                                                        '#c2ad6e' => $imagepath . '/color/c2ad6e.jpg'                                                       
                                                        )
                                                );
        $options[] = array( "name" => __('Custom Theme Color','gxg_textdomain'),
                                                "desc" => __('If you prefer a different color than the ones above,  you can select a custom color for your links and buttons here. This field has priority over the Predefined Primary Color.','gxg_textdomain'),
                                                "id" => $shortname."_link_colorpicker",
                                                "std" => "",
                                                "type" => "color");

                
        $options[] = array( "name" => __('Content Background Color Image','gxg_textdomain'),
                                                "desc" => __('For cross browser reasons (IE 8 does not support transparent background color), the content, slider and navi area are using a 5x5px transparent background image (.png file). Upload your own image here to replace it.)','gxg_textdomain'),
                                                "id" => $shortname."_content_bg_image",
                                                "type" => "upload");
        
        $options[] = array( "name" => __('Footer Color','gxg_textdomain'),
                                                "desc" =>  __('','gxg_textdomain'),
                                                "id" => $shortname."_footer_color",
                                                "std" => "",
                                                "type" => "color");

        $options[] = array( "name" => __('Social/Copyright Color','gxg_textdomain'),
                                                "desc" =>  __('','gxg_textdomain'),
                                                "id" => $shortname."_copyright_color",
                                                "std" => "",
                                                "type" => "color");

//------------------------------------------------------------------------------
// SLIDER
//------------------------------------------------------------------------------

        $options[] = array( "name" => __('SLIDER','gxg_textdomain'),
                                                "type" => "heading",
                                                "img" => "/includes/admin/images/s.png");

        $options[] = array( "name" => __('Set up your slider','gxg_textdomain'),
        "type" => "info");


        $options[] = array( "name" => __('Show Slider on Homepage','gxg_textdomain'),
                                                "id" => $shortname."_slider",
                                                "std" => "1",
                                                "type" => "checkbox");
                                                
                                                
        $options[] = array( "name" => __('Select a Slider','gxg_textdomain'),
                                                "desc" => __('After you have created a slider, you can select it here.','gxg_textdomain'),
                                                "id" => $shortname."_sliderimages",
                                                "type" => "select",
                                                "options" => $options_slides);
        
        $options[] = array( "name" => "Time between transitions",
                                                "desc" => __('How long each image will show. If you want to display each image for 8 second, enter: 8000','gxg_textdomain'),
                                                "id" => $shortname."_sliderspeed",
                                                "std" => "8000",
                                                "type" => "text");        
        
 
//------------------------------------------------------------------------------
// SOCIAL
//------------------------------------------------------------------------------

        $options[] = array( "name" => __('SOCIAL','gxg_textdomain'),
                                                "type" => "heading",
                                                "img" => "/includes/admin/images/so.png");

        $options[] = array( "name" => __('Enter your info for your social network accounts to display them in the footer. Option to remove all share buttons.','gxg_textdomain'), 
        "type" => "info");
        
        
        $options[] = array( "name" => __('Remove all facebook and twitter share buttons from the entire site.','gxg_textdomain'),
                                                "id" => $shortname."_sbshare",
                                                "std" => "0",
                                                "type" => "checkbox");

        $options[] = array( "name" => "iTunes",
                                                "desc" => __('Enter the full URL to your iTunes page','gxg_textdomain'),
                                                "id" => $shortname."_itunes",
                                                "std" => "",
                                                "type" => "text");
        
        $options[] = array( "name" => "Last FM",
                                                "desc" => __('Enter the full URL to your Last FM profile','gxg_textdomain'),
                                                "id" => $shortname."_lastfm",
                                                "std" => "",
                                                "type" => "text");

        $options[] = array( "name" => "Soundcloud",
                                                "desc" =>  __('Enter the full URL to your Soundcloud profile','gxg_textdomain'),
                                                "id" => $shortname."_soundcloud",
                                                "std" => "",
                                                "type" => "text");
        
        $options[] = array( "name" => "Twitter",
                                                "desc" => __('Enter the full URL to your Twitter profile','gxg_textdomain'),
                                                "id" => $shortname."_twitter",
                                                "std" => "",
                                                "type" => "text");

        $options[] = array( "name" => "Facebook",
                                                "desc" => __('Enter the full URL to your Facebook profile','gxg_textdomain'),
                                                "id" => $shortname."_fb",
                                                "std" => "",
                                                "type" => "text");        

        $options[] = array( "name" => "YouTube",
                                                "desc" => __('Enter the full URL to your YouTube profile','gxg_textdomain'),
                                                "id" => $shortname."_youtube",
                                                "std" => "",
                                                "type" => "text");  

        $options[] = array( "name" => "beatport",
                                                "desc" => __('Enter the full URL to your beatport profile','gxg_textdomain'),
                                                "id" => $shortname."_beatport",
                                                "std" => "",
                                                "type" => "text");  

        $options[] = array( "name" => "Link to additional icon 1",
                                                "desc" => __('Enter the full URL to your social icon 1.  Height must be 32px. Color: white (#ffffff).','gxg_textdomain'),
                                                "id" => $shortname."_social1",
                                                "std" => "",
                                                "type" => "text");  

        $options[] = array( "name" => "Width of additional icon 1",
                                                "desc" => __('If your icon has a width of 50px, enter: 50','gxg_textdomain'),
                                                "id" => $shortname."_socialwidth1",
                                                "std" => "",
                                                "type" => "text");  

        $options[] = array( "name" => "URL to additional social profile 1",
                                                "desc" => __('Enter the full URL to your social profile 1.','gxg_textdomain'),
                                                "id" => $shortname."_socialprofile1",
                                                "std" => "",
                                                "type" => "text");  

        $options[] = array( "name" => "Link to additional icon 2",
                                                "desc" => __('Enter the full URL to your social icon 2.  Height must be 32px. Color: white (#ffffff).','gxg_textdomain'),
                                                "id" => $shortname."_social2",
                                                "std" => "",
                                                "type" => "text");
        
        $options[] = array( "name" => "Width of additional icon 2",
                                                "desc" => __('If your icon has a width of 50px, enter: 50','gxg_textdomain'),
                                                "id" => $shortname."_socialwidth2",
                                                "std" => "",
                                                "type" => "text");
        
        $options[] = array( "name" => "URL to additional social profile 2",
                                                "desc" => __('Enter the full URL to your social profile 2','gxg_textdomain'),
                                                "id" => $shortname."_socialprofile2",
                                                "std" => "",
                                                "type" => "text");  

        $options[] = array( "name" => "Link to additional icon 3",
                                                "desc" => __('Enter the full URL to your social icon 3.  Height must be 32px. Color: white (#ffffff).','gxg_textdomain'),
                                                "id" => $shortname."_social3",
                                                "std" => "",
                                                "type" => "text");
        
        $options[] = array( "name" => "Width of additional icon 3",
                                                "desc" => __('If your icon has a width of 50px, enter: 50','gxg_textdomain'),
                                                "id" => $shortname."_socialwidth3",
                                                "std" => "",
                                                "type" => "text");
        
        $options[] = array( "name" => "URL to additional social profile 3",
                                                "desc" => __('Enter the full URL to your social profile 3','gxg_textdomain'),
                                                "id" => $shortname."_socialprofile3",
                                                "std" => "",
                                                "type" => "text");          
        
        $options[] = array( "name" => "Link to additional icon 4",
                                                "desc" => __('Enter the full URL to your social icon 4.  Height must be 32px. Color: white (#ffffff).','gxg_textdomain'),
                                                "id" => $shortname."_social4",
                                                "std" => "",
                                                "type" => "text");
        
        $options[] = array( "name" => "Width of additional icon 4",
                                                "desc" => __('If your icon has a width of 50px, enter: 50','gxg_textdomain'),
                                                "id" => $shortname."_socialwidth4",
                                                "std" => "",
                                                "type" => "text");  

        $options[] = array( "name" => "URL to additional social profile 4",
                                                "desc" => __('Enter the full URL to your social profile 4','gxg_textdomain'),
                                                "id" => $shortname."_socialprofile4",
                                                "std" => "",
                                                "type" => "text");  

        $options[] = array( "name" => "Link to additional icon 5",
                                                "desc" => __('Enter the full URL to your social icon 5.  Height must be 32px. Color: white (#ffffff).','gxg_textdomain'),
                                                "id" => $shortname."_social5",
                                                "std" => "",
                                                "type" => "text");
        
        $options[] = array( "name" => "Width of additional icon 5",
                                                "desc" => __('If your icon has a width of 50px, enter: 50','gxg_textdomain'),
                                                "id" => $shortname."_socialwidth5",
                                                "std" => "",
                                                "type" => "text");  
 
         $options[] = array( "name" => "URL to additional social profile 5",
                                                "desc" => __('Enter the full URL to your social profile 5','gxg_textdomain'),
                                                "id" => $shortname."_socialprofile5",
                                                "std" => "",
                                                "type" => "text");  
        
//------------------------------------------------------------------------------
// CONTACT
//------------------------------------------------------------------------------

        $options[] = array( "name" => __('CONTACT','gxg_textdomain'),
                                                "type" => "heading",
                                                "img" => "/includes/admin/images/cont.png");

        $options[] = array( "name" => __('Enter the settings for your Contact Form.','gxg_textdomain'),
        "type" => "info");


        $options[] = array( "name" => __('Email Address','gxg_textdomain'),
                                                "desc" => __('Enter the email address where the email from the contact form should be sent to.','gxg_textdomain'),
                                                "id" => $shortname."_email_adress",
                                                "std" => "my@email.com",
                                                "type" => "text");

        $options[] = array( "name" => __('Subject','gxg_textdomain'),
                                                "desc" => __('Enter the subject for messages that are sent via the contact form.','gxg_textdomain'),
                                                "id" => $shortname."_email_subject",
                                                "std" => "contact form mail",
                                                "type" => "text");

        return $options;
}