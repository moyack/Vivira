<?php
/**
* \file index.php
* Supress direct acceess to the css cache directory.
*/

if(file_exists(realpath('../../../../Settings.php')))
{
	require(realpath('../../../../Settings.php'));
	header('Location: ' . $boardurl);
}
else
	exit;
?>