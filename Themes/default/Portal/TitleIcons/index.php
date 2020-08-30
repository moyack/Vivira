<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx & Simple Machines
 * @copyright 2018 PortaMx,  Simple Machines and individual contributors
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 1.41
 */

if(file_exists(realpath('../../../../Settings.php')))
{
	require(realpath('../../../../Settings.php'));
	header('Location: ' . $boardurl);
}
else
	exit;
?>
