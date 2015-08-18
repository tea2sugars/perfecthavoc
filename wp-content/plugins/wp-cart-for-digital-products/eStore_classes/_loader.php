<?php
// This is the class loader for eStore, and should be called as the first line of code in all eStore PHP files.
// HOWEVER, in cases where wp-load.php is required, it should be the SECOND line of code.
//	require_once 'eStore_classes/_loader.php';
// -- The Assurer, 2010-10-13.
define('ESTORE_LOGFILE_LOADER', 'eStore_class_loader.log');	// Class loader log file.
$eStore_class_dir = dirname(__FILE__);			// Class directory.
// Populate the following array with the names of all eStore classes to be loaded...
$eStore_class_list = array(
	'eStore_aprtp',
	'eStore_as3tp',
	'eStore_as3tpadm',
	'eStore_dbgmgr',
	'eStore_dbgmgradm',
	'eStore_dlfilepath',
	'eStore_dlmgradm',
	'eStore_dlvs'
);
do {
// Do a require_once on each class file...
	$eStore_class_name = array_shift($eStore_class_list); // Get the next class name.
	if(!empty($eStore_class_name)) {
		$eStore_class_file = $eStore_class_dir.'/'.$eStore_class_name.'.php'; // Convert into a file path.
		if(!file_exists($eStore_class_file)) {
		// If $eStore_class_file does not exist, silently log that fact...
			$message = '['.date('m/d/Y g:i A')."] - FAILURE : Missing file: $eStore_class_file\n";
			$fp = fopen($eStore_class_dir.'/../'.ESTORE_LOGFILE_LOADER, 'a');
			if($fp != FALSE) {
				fwrite($fp, $message);
				fclose($fp);
			}
		} // Even if the $eStore_class_file does not exist, we still will try loading it, because we need
		  // to "crash & burn."  Better to know now, than later that a file is missing 8(
		require_once $eStore_class_file; // Attempt to load the $eStore_class_file.
	}
} while($eStore_class_list <> NULL);
?>