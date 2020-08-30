<?php
/**
* \file index.php
* Supress direct acceess to the directory.
*
* PortaMx Forum
* @package PortaMx
* @copyright 2018 PortaMx
*/

if(file_exists(realpath('../Settings.php')))
{
	require(realpath('../Settings.php'));
	header('Location: ' . $boardurl);
}
else
	exit;
?>