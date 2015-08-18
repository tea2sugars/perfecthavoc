<?php
// Define the various success levels...
define('ESTORE_LEVEL_SUCCESS', 2);						// Success.
define('ESTORE_LEVEL_ADVISORY', 1);						// Advice.
define('ESTORE_LEVEL_STATUS', 0);							// Status.
define('ESTORE_LEVEL_WARNING', -1);						// Warning.
define('ESTORE_LEVEL_FAILURE', -2);						// Failure.

// Define the various debug log files...
define('ESTORE_LOGFILE_DLMGR',		'download_manager_debug.log');		// Download manager log.
define('ESTORE_LOGFILE_IPN',		'ipn_handle_debug.log');		// IPN debug log.
define('ESTORE_LOGFILE_POSTPAYMENT',	'eStore_post_payment_debug.log');	// Post payment debug log.
define('ESTORE_LOGFILE_PLUGIN',		'eStore_plugin_degug.log');		// Plugin debug log.
define('ESTORE_LOGFILE_SUBSCRIPTION',	'subscription_handle_debug.log');	// Subscription debug log.
define('ESTORE_LOGFILE_SQUEEZE_FORM',	'squeeze_form_debug.log');		// Download manager log.
define('ESTORE_LOGFILE_GENERAL',	'eStore_debug.log');

define('ESTORE_LOG_BREAK', "\n------------------------------------------------------------------------\n\n");

// We now declare a "global" instance of the debug manager.  Remember, that in order to access it from within another
// class of function, you must declare it as a global variable!
// -- The Assurer, 2010-10-20.
$eStore_debug_manager = new eStore_dbgmgr(dirname(__FILE__).'/../');// Activate the debug manager.

class eStore_dbgmgr {
	function eStore_dbgmgr($eStore_logfile_dir='./') {
	// Debug manager constructor...
		$this->debug_enabled = (get_option('eStore_cart_enable_debug') != '' ? 1 : -1);
		$this->debug_logfile_dir = $eStore_logfile_dir;
		return;
	}

	var $debug_enabled = 0;
	// Debug switch property: -1 = Off, 0 = Uninitialized, 1 = On.

	var $debug_logfile_dir = './';
	// Directory of where log files are written.

	function level($success) {
	// Return the appropriate $success level string.
		// Transliterate "old-style" eStore debug levels...
		if($success === TRUE) $success = ESTORE_LEVEL_SUCCESS;
		if($success === FALSE) $success = ESTORE_LEVEL_FAILURE;
		switch($success) {
		// Return "new-style" success level strings...
			case ESTORE_LEVEL_SUCCESS	: return 'SUCCESS';
			case ESTORE_LEVEL_ADVISORY	: return 'Advisory';
			case ESTORE_LEVEL_STATUS	: return 'Status';
			case ESTORE_LEVEL_WARNING	: return 'Warning';
			case ESTORE_LEVEL_FAILURE	: return 'FAILURE';
			default				: return 'UNDEFINED SUCCESS LEVEL';
		}
	}

	function timestamp($message, $success=FALSE, $end=FALSE, $logfile=LOGFILE_PLUGIN, $force=FALSE, $reset=FALSE) {
	// Write $message to the specified $logfile, with the optional $success indicator and $end of debug thread tags.
	// Returns the timestamped $message if successfully written to the $logfile, an empty string if debug logs are disabled,
	// or FALSE if the operation failed.
		$retVal = '';		// Default return value.
		switch($this->debug_enabled) {
		// Evaluate $debug_enabled state...
		// Please, DO NOT re-order these CASE statements, or "bad things" will happen!
			case  0:	// Class initialization required...
					$retVal = '['.date('m/d/Y g:i A')."] - FAILURE : Uninitialized eStore_dbgmgr()\n";
					$fp = @fopen($this->debug_logfile_dir.ESTORE_LOGFILE_PLUGIN, 'a');
					if($fp != FALSE) {
						@fwrite($fp, $retVal);
						@fclose($fp);
					}
					$retVal = FALSE;
					break;
			case -1:	// No action required, unless forced...
					if(($this->debug_enabled == -1) && !$force) break;
			case  1:	// Create timestamp...
					$retVal = '['.date('m/d/Y g:i A').'] - '.
						$this->level($success)." : $message\n".
						($end === FALSE ? '' : ESTORE_LOG_BREAK);
					// Write timestamp...
					$fp = @fopen($this->debug_logfile_dir.$logfile, ($reset ? 'w' : 'a'));
					if($fp != FALSE) {
						@fwrite($fp, $retVal);
						@fclose($fp);
					} else {
					// Fopen of $logfile was unsuccessful...
						$retVal = '['.date('m/d/Y g:i A')."] - FAILURE : fopen($logfile)\n";
						$fp = @fopen($this->debug_logfile_dir.ESTORE_LOGFILE_PLUGIN, 'a');
						if($fp != FALSE) {
							@fwrite($fp, $retVal);
							@fclose($fp);
						}
						$retVal = FALSE;
					}
					break;
		}
		return $retVal;
	}

	function downloads($message, $status=FALSE, $end=FALSE, $force=FALSE) {
	// Writes a timestamped $message to the download manager debug file.
	// Returns TRUE if the timestamped $message is successfully written to the debug file, or  FALSE if the operation failed.
	// -- The Assurer, 2010-10-18.
		return ($this->timestamp($message, $status, $end, ESTORE_LOGFILE_DLMGR, $force) != FALSE ? TRUE : FALSE);
	}

	function squeeze_form($message, $status=FALSE, $end=FALSE, $force=FALSE) {
	// Writes a timestamped $message to the squeeze form debug file.
	// Returns TRUE if the timestamped $message is successfully written to the debug file, or  FALSE if the operation failed.
		return ($this->timestamp($message, $status, $end, ESTORE_LOGFILE_SQUEEZE_FORM, $force) != FALSE ? TRUE : FALSE);
	}
		
	function reset_logfiles() {
	// Reset all log files...
	// -- The Assurer, 2010-10-18.
		// List of log files to be reset...
		$eStore_logfile_list = array (
			ESTORE_LOGFILE_DLMGR,
			ESTORE_LOGFILE_IPN,
			ESTORE_LOGFILE_POSTPAYMENT,
			ESTORE_LOGFILE_SQUEEZE_FORM,
			ESTORE_LOGFILE_SUBSCRIPTION,
                        ESTORE_LOGFILE_GENERAL
		);
		foreach($eStore_logfile_list as $logfile) {
		// Clear each $logfile and initialize with a "reset" timestamp...
			$this->timestamp('Log file reset.', ESTORE_LEVEL_ADVISORY, TRUE, $logfile, TRUE, TRUE);
		}
	}
}
