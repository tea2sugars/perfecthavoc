<?php
// Download Validation Script class, WP eStore edition.
// -- The Assurer, 2011-09-19.
class eStore_dlvs{
	function error($reason, $contact = TRUE) {
	// Displays a Windows 8 style "blue screen of death."
	// Gives the $reason for the error, and if $contact is TRUE, directs the user to contact the site administrator.
	// -- The Assurer, 2011-09-18.
		// Set the ambient mood, draw the emoticon, and give the bad news to the user...
		echo '<body bgcolor="#F8FAFA">';
		echo '<div style="width:800px;border:1px solid#DDDDDD;padding:15px;margin:15px;"><p><font color="#454545" face="Arial, sans-serif" style="font-size: 20pt">'.ESTORE_DLVS_HI1;
		if($contact===TRUE) echo '&nbsp;'.ESTORE_DLVS_CP1;
		// Now explain the reason for the error...
		echo '</font></p><p><font color="#454545" face="Arial, sans-serif" style="font-size: 16pt">';
		if($contact===TRUE)
			echo ESTORE_DLVS_TA1;
		else
			echo ESTORE_DLVS_TU1;
		echo '<br /></font><font color="#454545" face="Arial, sans-serif" style="font-size: 12pt">'."$reason".'</font></p>';
		echo '</div></body>';
		return;
	}
}
?>
