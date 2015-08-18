<?php
class eStore_as3tpadm {
	function settings_menu_html() {
	// Return HTML code for AWS S3 related settings.
	// -- The Assurer, 2010-12-09.
		// Fetch current settings...
		if(!eStore_as3tp::as3key_fetch($eStore_as3tp_aws_acckey, $eStore_as3tp_aws_seckey, $eStore_as3tp_expiry))
			return 'An error occurred while retrieving the current settings from the WordPress database!<br /><br />';
		// Generate HTML menu code...
		$html_code = 'To utilize this feature, you need to first <a href="http://aws.amazon.com/s3" target="_blank">setup an Amazon Web Services (AWS), Simple Storage Service (S3) account</a>.<br />After you have setup your S3 account, you will need to <a href="http://s3.amazonaws.com/mturk/tools/pages/aws-access-identifiers/aws-identifier.html" target="_blank">know what your AWS Access Identifiers are</a>.<br /><br />'.
			'<table width="100%" border="0" cellspacing="0" cellpadding="6">';
			// AWS Access Key ID...
			$html_code .= '<tr valign="top">'.
				'<td width="25%" align="left">AWS Access Key ID</td>'.
				'<td align="left">'.
				'<input type="text" name="eStore_as3tp_aws_acckey" value="'.$eStore_as3tp_aws_acckey.'" size="50" /><i>    Your 20 character AWS Acceess Key ID.</i><br /><br /></td></tr>';
			// AWS Secret Access Key...
			$html_code .= '<tr valign="top">'.
				'<td width="25%" align="left">AWS Secret Access Key</td>'.
				'<td align="left">'.
				'<input type="text" name="eStore_as3tp_aws_seckey" value="'.$eStore_as3tp_aws_seckey.'" size="50" /><i>    Your 40 character AWS Secret Acceess Key.</i><br /><br /></td></tr>';
			// AWS S3 presigned URL expiry...
			$html_code .= '<tr valign="top">'.
				'<td width="25%" align="left">AWS S3 Presigned URL Expiry</td>'.
				'<td align="left">'.
				'<input type="text" name="eStore_as3tp_expiry" value="'.$eStore_as3tp_expiry.'" size="5" /><i>    Number of seconds before a presigned URL expires.<br />Time is measured from the moment an encrypted link is used, until the user\'s browser is transferred to the AWS server.  May need adjustment if the system clock on your server is not in sync with the AWS server.</i><br /><br /></td></tr></table>';						
		return $html_code;
	}

	function settings_menu_post($aws_acckey, $aws_seckey, $expiry) {
	// Save AWS S3 related settings.
	// Returns TRUE if noerrors occurred, otherwise an error message string is returned.
	// -- The Assurer, 2010-12-09.
		if(eStore_as3tp::as3key_update($aws_acckey, $aws_seckey, $expiry) != FALSE) return TRUE;
		return 'An error occurred while updating the AWS S3 related settings.<br />';
	}
}
?>
