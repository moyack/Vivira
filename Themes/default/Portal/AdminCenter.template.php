<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file AdminCenter.template.php
 * Template for the Admin Center.
 *
 * @version 1.41
 */

function template_main()
{
	global $context, $scripturl, $txt;

	$curarea = isset($_GET['area']) ? $_GET['area'] : 'pmx_center';
	$context['pmx']['admmode'] = $_REQUEST['action'];

	if(allowPmx('pmx_admin, pmx_blocks, pmx_articles, pmx_categories, pmx_create', true))
	{
		echo '
		<div class="cat_bar"><h3 class="catbg">'. $txt['pmx_admin_center'] .'</h3></div>';
		if(allowPmx('pmx_admin', true))
			echo '
		<p class="information">'. sprintf($txt['pmx_admin_main_welcome'] ,'<span class="generic_icons help" title="'. $txt['help'] .'"></span>') .'</p>';		
		else
			echo '
		<p class="information">'. sprintf($txt['pmx_admin_main_custom'] ,'<span class="generic_icons help" title="'. $txt['help'] .'"></span>') .'</p>';
	}

	echo '
		<fieldset class="windowbg portal_group">
			<legend>'. $txt['pmx_button'] .'</legend>
			'. (allowPmx('pmx_admin') ? '<a href="'. $scripturl .'?action='. $context['pmx']['admmode'] .';area=pmx_settings;'. $context['session_var'] .'='. $context['session_id'] .'"><div><img src="'. $context['pmx_imageurl'] .'adm_settings.png" alt="settings"></div>'. preg_replace('~\<.?b\>~', '', $txt['pmx_center_mansettings']) .'</a>' : '') .'
			'. (allowPmx('pmx_admin, pmx_blocks') ? '<a href="'. $scripturl .'?action='. $context['pmx']['admmode'] .';area=pmx_blocks;'. $context['session_var'] .'=' .$context['session_id'] .'"><div><img src="'. $context['pmx_imageurl'] .'adm_blocks.png" alt="blocks"></div>'. preg_replace('~\<.?b\>~', '', $txt['pmx_center_manblocks']) .'</a>' : '') .'
			'. (allowPmx('pmx_admin, pmx_articles, pmx_create') ? '<a href="'. $scripturl .'?action='. $context['pmx']['admmode'] .';area=pmx_articles;'. $context['session_var'] .'=' .$context['session_id'] .'"><div><img src="'. $context['pmx_imageurl'] .'adm_article.png" alt="articles"></div>'. preg_replace('~\<.?b\>~', '', $txt['pmx_center_manarticles']) .'</a>' : '') .'
			'. (allowPmx('pmx_admin') ? '<a href="'. $scripturl .'?action='. $context['pmx']['admmode'] .';area=pmx_categories;'. $context['session_var'] .'=' .$context['session_id'] .'"><div><img src="'. $context['pmx_imageurl'] .'adm_category.png" alt="articles"></div>'. preg_replace('~\<.?b\>~', '', $txt['pmx_center_mancategories']) .'</a>' : '') .'
		</fieldset>';
}
?>