<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file PortaMxAllocator.php
 * The allocator for the Portal.
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* Init all variables and load the settings from the database.
* Check the requests and prepare the templates to load.
*/
function PortalAllocator()
{
	global $context, $txt;

	if(isset($_REQUEST['area']) && substr($_REQUEST['area'], 0, 4) == 'pmx_' && isset($_REQUEST['xml']) && !isset($context['pmx']))
		PortaMx_getSettings(true);

	$allocate = array(
		'pmx_center' => array('AdminCenter.php', 'Portal_AdminCenter'),
		'pmx_settings' => array('AdminSettings.php', 'Portal_AdminSettings'),
		'pmx_blocks' => array('AdminBlocks.php', 'Portal_AdminBlocks'),
		'pmx_articles' => array('AdminArticles.php', 'Portal_AdminArticles'),
		'pmx_categories' => array('AdminCategories.php', 'Portal_AdminCategories'),
	);

	// load admin language and javascript
	if(isset($_GET['area']) && in_array($_GET['area'], explode(',', $context['pmx']['areas'])))
	{
		if(allowPmx('pmx_admin, pmx_blocks, pmx_articles, pmx_categories, pmx_create'))
		{
			loadLanguage($context['pmx_languagedir'] .'Admin');
			$_GET[$context['session_var']] = $context['session_id'];
			require_once($context['pmx_sourcedir'] . $allocate[$_GET['area']][0]);
			$allocate[$_GET['area']][1]();
		}
		else
			fatal_error($txt['pmx_acces_error']);
	}
	else
		fatal_error($txt['pmx_acces_error']);
}
?>