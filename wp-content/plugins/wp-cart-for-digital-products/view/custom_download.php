<?php
// This script is used if you want visitors to think the downloads are in a directory other than the eStore plugin directory.
//     1. Copy this file to the directory you want people to think the files are stored at.  For example, if you want visitors
//        to think the files are stored at www.yoursite.com/downloads you would create a "downloads" directory in your site's
//        document root directory and copy this file there.
//     2. Rename this file:  download.php
//     3. If your copy of WordPress is installed in a subdirectory (www.yoursite.com/blog), you must change the value of
//        $wp_home_dir to the name of the subdirectory that contains WordPress.  In this example, the line should look like:
//            $wp_home_dir='blog';   <--- Type everything EXCEPT the "//" characters.
//     4. On the WordPress -> WP eStore -> Settings page, set the "Download Validation Script Location" to the URL of the
//        directory that corresponds with this script.  In this example, if you would set the location to:
//            http://www.yoursite.com/downloads
// -- The Assurer, 2010-09-14.
//
// Special notes concerning networked WordPress configurations...
//     * In step 1, all networked blogs share the same PHYSICAL root directory.  You must place the script in a PHYSICAL
//       directory, that will properly map to the correct LOGICAL blog directory desired.
//     * Step 3 only applies to subdirectories that are not subjected to server mod_rewrite rules.  If you have a networked
//       blog at www.yoursite.com/blog4 then step 3 does not apply, because "blog4" is a virtual subdirectory, managed by
//       managed by mod_rewrite rules.  However, if the blog is located at www.yoursite.com/blog4/wordpress then the
//       value "wordpress" would be used for the $wp_home_dir setting.
// -- The Assurer, 2011-09-18.

// If you installed WordPress in a subdirectory, you must put that subdirectory name here...
$wp_home_dir = '';

// Localization opportunities...
define('ESTORE_DLVS_HI0',	'The wp_eStore download manager ran into a problem that it couldn&rsquo;t handle, and is unable to process your download request.');
define('ESTORE_DLVS_CP0',	'Please contact the site administrator.');
define('ESTORE_DLVS_TA0',	'Please tell the site administrator the problem was due to the following reason:');
define('ESTORE_DLVS_TU0',	'The problem was due to the following reason:');
define('ESTORE_DLVS_IR0',	'A valid download request (query string) wasn&rsquo;t specified, as part of the link used.&nbsp;&nbsp;Please check the correctness of the download link used, and try again.&nbsp;&nbsp;Sometimes links sent by email get mangled by the email client, or you did not correctly copy the complete link.');
define('ESTORE_DLVS_RDC',	'The Custom Download Validation Script couldn&rsquo;t chdir() to the site&rsquo;s document root directory, because the UID (User ID) of the script doesn&rsquo;t match the root directory&rsquo;s UID.');
define('ESTORE_DLVS_HDS',	'The Custom Download Validation Script couldn&rsquo;t find the WordPress home directory, because the script is not properly configured.');
define('ESTORE_DLVS_HDC',	'The Custom Download Validation Script couldn&rsquo;t chdir() to the WordPress home directory, because the UID (User ID) of the script doesn&rsquo;t match the home directory&rsquo;s UID.');
define('ESTORE_DLVS_PDS',	'The Custom Download Validation Script couldn&rsquo;t find the wp_eStore plugin directory, because it may have been moved, renamed, or the plugin was deleted.');
define('ESTORE_DLVS_PDC',	'The Custom Download Validation Script couldn&rsquo;t chdir() to the wp_eStore plugin directory, because the UID (User ID) of the script doesn&rsquo;t match the plugin directory&rsquo;s UID.');
define('ESTORE_DLVS_DLS',	'The Custom Download Validation Script couldn&rsquo;t find the download.php file in the wp_eStore plugin directory.');

/********** Please do not change anything below this line **********/

// Ensure that a valid download request query string was specified...
if(!isset($_GET['file']) || empty($_GET['file'])) {	// Required "file=" URL query string is missing!
	dlvs_error(ESTORE_DLVS_IR0, FALSE);
	exit;
}	

// Then chdir() to the site's document root directory...
if(@chdir($_SERVER['DOCUMENT_ROOT']) === FALSE) {	// Could not chdir() to the site's root directory!
	dlvs_error(ESTORE_DLVS_RDC);
	exit;
}

// Next, if necessary, chdir() to the location of where WordPress is installed...
$wp_home_dir = trim($wp_home_dir);			// Just in case, trim leading/trailing whitespace.
$wp_home_dir = trim($wp_home_dir, '/');			// And any leading/trailing slashes.
if(strlen($wp_home_dir) > 0) {				// WordPress is in a subdirectory...
	if(@scandir($wp_home_dir) === FALSE) {		// Could not find the WordPress home directory!
		dlvs_error(ESTORE_DLVS_HDS);
		exit;
	}
	if(@chdir($wp_home_dir) === FALSE) {		// Could not chdir() to WordPress home directory!
		dlvs_error(ESTORE_DLVS_HDC);
		exit;
	}
}

// Now chdir() over to the eStore plugin directory...
$wp_eStore_plugin_dir = 'wp-content/plugins/wp-cart-for-digital-products';
if(@scandir($wp_eStore_plugin_dir) === FALSE) {		// Could not find the eStore plugin directory!
	dlvs_error(ESTORE_DLVS_PDS);
	exit;
}
if(@chdir($wp_eStore_plugin_dir) ===FALSE) {		// Could not chdir() to eStore plugin directory!
	dlvs_error(ESTORE_DLVS_PDC);
	exit;
}

// Almost there!  Transfer control over to the main download validation script...
if(@file_exists('download.php') === FALSE) {		// Could not find main download validation script.
	dlvs_error(ESTORE_DLVS_DLS);
	exit;
}
	require_once('download.php');			// And, go!
	return;

function dlvs_error($reason, $contact = TRUE) {
// Displays a Windows 8 style "blue screen of death."
// Gives the $reason for the error, and if $contact is TRUE, directs the user to contact the site administrator.
// -- The Assurer, 2011-09-18.
	// Set the ambient mood, draw the emoticon, and give the bad news to the user...
	echo '<body bgcolor="#0066cc">
<p><font color="#ffffff" face="Times New Roman, serif" style="font-size: 72pt">:(</font></p>
<p><font color="#ffffcc" face="Arial, sans-serif" style="font-size: 22pt">'.ESTORE_DLVS_HI0;
	if($contact===TRUE) echo '&nbsp;&nbsp;'.ESTORE_DLVS_CP0;
	// Now explain the reason for the error...
	echo '</font></p><p><font color="#ffffcc" face="Arial, sans-serif" style="font-size: 16pt">';
	if($contact===TRUE)
		echo ESTORE_DLVS_TA0;
	else
		echo ESTORE_DLVS_TU0;
	echo '<br /></font><font color="#ffff33" face="Arial, sans-serif" style="font-size: 12pt">'."$reason".'</font></p></body>';
	return;
}
?>
