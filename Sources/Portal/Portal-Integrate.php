<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file Portal-Integrate.php
 * Integration functions for the Portal
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* check guest can browse the forum.
* called from index.php
**/
function Portal_allow_guest($board, $topic)
{
	if(empty(!$board) || !empty($topic) || empty(Portal_frontactions()))
		return 'KickGuest';
	else
		return 'Portal';
}

/**
* tell the index.php if we have frontactions.
* called from index.php and PortaMx_allow_guest
**/
function Portal_frontactions()
{
	global $modSettings;

	$result = false;
	if(!empty($modSettings['portal_enabled']))
	{
		if(empty($_REQUEST['action']))
			$result = true;
		else
		{
			foreach(array('spage', 'cat', 'art', 'pmxerror') as $act)
				$result = !empty($_REQUEST[$act]) ? true : $result;
		}
	}
	return $result;
}

/**
* Add actions to the index actions array
* Called from hook integrate_actions
**/
function Portal_Actions(&$actiondata)
{
	global $modSettings;

	if(!empty($modSettings['portal_enabled']))
	{
		$actiondata = array_merge(
			$actiondata,
			array(
				'community' => array('BoardIndex.php', 'BoardIndex'),
				'portal' => array('Portal/PortalAllocator.php', 'PortalAllocator'),
			)
		);
	}
}

/**
* Add Admin menu context
* Called from hook integrate_admin_areas
**/
function Portal_AdminMenu(&$menudata)
{
	global $txt, $context, $modSettings, $boarddir, $scripturl;

	if(!empty($modSettings['portal_enabled']))
	{
		// insert Portamx menu part before 'config'
		$fnd = array_search('config', array_keys($menudata)) +1;
		$menudata = array_merge(
			array_slice($menudata, 0, $fnd),
			array(
				'portal' => array(
					'title' => $txt['pmx_extension'],
					'permission' => array('admin_forum'),
					'areas' => array(
						'pmx_center' => array(
							'label' => $txt['pmx_ext_center'],
							'bigicon' => $context['pmx_imageurl'] .'adm_center.png',
							'file' => $context['pmx_templatedir'] .'AdminCenter.php',
							'function' => 'Portal_AdminCenter',
						),
						'pmx_settings' => array(
							'label' => $txt['pmx_settings'],
							'bigicon' => $context['pmx_imageurl'] .'adm_settings.png',
							'file' => $context['pmx_templatedir'] .'AdminSettings.php',
							'function' => 'Portal_AdminSettings',
							'subsections' => array(
								'globals' => array($txt['pmx_admSet_globals']),
								'panels' => array($txt['pmx_admSet_panels']),
								'control' => array($txt['pmx_admSet_control']),
								'access' => array($txt['pmx_admSet_access']),
							),
						),
						'pmx_blocks' => array(
							'label' => $txt['pmx_blocks'],
							'bigicon' => $context['pmx_imageurl'] .'adm_blocks.png',
							'file' => $context['pmx_templatedir'] .'AdminBlocks.php',
							'function' => 'Portal_AdminBlocks',
							'subsections' => array(
								'all' => array($txt['pmx_admBlk_panels']['all']),
								'front' => array($txt['pmx_admBlk_panels']['front']),
								'head' => array($txt['pmx_admBlk_panels']['head']),
								'top' => array($txt['pmx_admBlk_panels']['top']),
								'left' => array($txt['pmx_admBlk_panels']['left']),
								'right' => array($txt['pmx_admBlk_panels']['right']),
								'bottom' => array($txt['pmx_admBlk_panels']['bottom']),
								'foot' => array($txt['pmx_admBlk_panels']['foot']),
								'pages' => array($txt['pmx_admBlk_panels']['pages']),
							),
						),
						'pmx_articles' => array(
							'label' => $txt['pmx_articles'],
							'bigicon' => $context['pmx_imageurl'] .'adm_article.png',
							'file' => $context['pmx_templatedir'] .'AdminArticles.php',
							'function' => 'Portal_AdminArticles',
						),
						'pmx_categories' => array(
							'label' => $txt['pmx_categories'],
							'bigicon' => $context['pmx_imageurl'] .'adm_category.png',
							'file' => $context['pmx_templatedir'] .'AdminCategories.php',
							'function' => 'Portal_AdminCategories',
						),
					),
				),
			),
			array_slice($menudata, $fnd, count($menudata) - $fnd)
		);
	}
}

/**
* Add menu to MenuContext
* Called from hook integrate_menu_buttons
**/
function Portal_MenuContext(&$menudata)
{
	global $txt, $context, $modSettings, $scripturl, $boarddir;

	if(strpos($_SERVER['QUERY_STRING'], 'xml') === false && strpos($_SERVER['QUERY_STRING'], 'popup') === false)
	{
		// Load the Portal if enabled and not loaded
		if(!empty($modSettings['portal_enabled']) && !defined('Portal'))
			Portal();

		// add community button after 'home'
		$fnd = array_search('home', array_keys($menudata)) + 1;
		if(!empty($context['pmx']['showhome']))
		{
			$menudata = array_merge(
				array_slice($menudata, 0, $fnd),
				array(
					'community' => array(
						'title' => $txt['forum'],
						'href' => $scripturl . '?action=community',
						'icon' => '<span class="generic_icons community"></span>',
						'active_button' => false,
						'sub_buttons' => array(
						),
					),
				),
				array_slice($menudata, $fnd, count($menudata) - $fnd)
			);
			$fnd++;
		}

		// add download button if enabled and accessible
		$dlact = array(0 => '', 1 => '');
		$dlactErr = array(0 => '', 1 => '');
		$dlaccess = isset($context['pmx']['settings']['dl_access']) ? $context['pmx']['settings']['dl_access'] : '';
		if(allowPmxGroup($dlaccess) && !empty($context['pmx']['settings']['download']) && preg_match('/(p:|c:|a:|)(.*)$/i', $context['pmx']['settings']['dl_action'], $match) > 0)
		{
			if($match[1] == 'a:')
				$dlact = array(0 => 'art', 1 => $match[2]);
			elseif($match[1] == 'c:')
				$dlact = array(0 => 'cat', 1 => $match[2]);
			elseif($match[1] == 'p:')
				$dlact = array(0 => 'spage', 1 => $match[2]);
			else
				$dlact = array(0 => 'action', 1 => $match[2]);

			if(!empty($_REQUEST['pmxerror']) && in_array($_REQUEST['pmxerror'], array('acs', 'fail')))
				$dlactErr = array(0 => 'pmxerror', 1 => $_REQUEST['pmxerror']);

			$menudata = array_merge(
				array_slice($menudata, 0, $fnd),
				array(
					'download' => array(
						'title' => $txt['download'],
						'href' => $scripturl .'?'. $dlact[0] .'='. $dlact[1],
						'icon' => '<span class="generic_icons download"></span>',
						'active_button' => false,
						'sub_buttons' => array(
						),
					),
				),
				array_slice($menudata, $fnd, count($menudata) - $fnd)
			);

			if((isset($_REQUEST[$dlact[0]]) && $_REQUEST[$dlact[0]] == $dlact[1]) || (isset($_REQUEST[$dlactErr[0]]) && $_REQUEST[$dlactErr[0]] == $dlactErr[1]))
				$context['current_action'] = 'download';
		}

		// add admin submenu before 'featuresettings'
		if(!empty($context['allow_admin']))
		{
			$curract = pmx_http_build_query($_GET);
			$fnd = array_search('featuresettings', array_keys($menudata['admin']['sub_buttons']));
			$menudata['admin']['sub_buttons'] = array_merge(
				array_slice($menudata['admin']['sub_buttons'], 0, $fnd),
				array(
					'portal' => array(
						'title' => $txt['pmx_ext_center'],
						'href' => $scripturl . '?action=admin;area=pmx_center',
						'show' => $context['allow_admin'],
						'sub_buttons' => array(
							'pmxsettings' => array(
								'title' => $txt['pmx_settings'],
								'href' => $scripturl . '?action=admin;area=pmx_settings',
								'show' => $context['allow_admin'],
							),
							'pmxblocks' => array(
								'title' => $txt['pmx_blocks'],
								'href' => $scripturl . '?action=admin;area=pmx_blocks',
								'show' => $context['allow_admin'],
							),
							'pmxarticles' => array(
								'title' => $txt['pmx_articles'],
								'href' => $scripturl . '?action=admin;area=pmx_articles',
								'show' => $context['allow_admin'],
							),
							'pmxcategories' => array(
								'title' => $txt['pmx_categories'],
								'href' => $scripturl . '?action=admin;area=pmx_categories',
								'show' => $context['allow_admin'],
							),
						),
					),
				),
				array_slice($menudata['admin']['sub_buttons'], $fnd, count($menudata['admin']['sub_buttons']) - $fnd)
			);
		}

		/**
		* Highlight the active button
		**/
		if(!empty($context['pmx']['showhome']))
		{
			if(isset($_REQUEST['board']) || isset($_REQUEST['topic']))
				$context['current_action'] = 'community';
			elseif(isset($_REQUEST['action']) && in_array($_REQUEST['action'], array('community', 'recent', 'unreadreplies', 'unread', 'who', 'collapse', 'stats', 'markasread')))
				$context['current_action'] = 'community';
		}
	}
}

/**
* add actions for the Who display
* Called from hook integrate_whos_online
**/
function Portal_whos_online($actions)
{
	global $txt, $context, $modSettings;

	if(!empty($modSettings['portal_enabled']))
	{
		$result = '';
		if(!empty($actions['action']) && $actions['action'] == 'community')
			$result = $txt['who_index'];

		elseif(isset($actions['spage']) || isset($actions['art']) || isset($actions['cat']) || isset($actions['child']))
			$result = getWhoTitle($actions);

		elseif(empty($actions['action']) && empty($actions['topic']) && empty($actions['board']))
		{
			$frontpage = true;
			if(!empty($context['pmx']['settings']['other_actions']))
			{
				$reqtyp = Pmx_StrToArray($context['pmx']['settings']['other_actions']);
				foreach($reqtyp as $rtyp)
				{
					@list($rtyp, $rval) = Pmx_StrToArray($rtyp, '=');
					$frontpage = (isset($_REQUEST[$rtyp]) && (is_null($rval) || empty($rval) || $_REQUEST[$rtyp] == $rval) ? false : $frontpage);
				}
			}
			if(!empty($frontpage))
				$result = $txt['pmx_who_frontpage'];
		}
		return $result;
	}
}
?>