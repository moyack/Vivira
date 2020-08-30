<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file Portal.php
 * The Portal Main.
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* Init all variables and load the settings from the database.
* Check the requests and prepare the templates to load.
*/
function Portal($doinit = false)
{
	global $context, $modSettings, $boardurl, $scripturl, $user_info, $maintenance, $language, $pmxCacheFunc, $sc, $cookiename, $txt;

	if(defined('Portal'))
		return;

	// we can exit on this...
	$rqaction = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
	if(!empty($rqaction) && in_array($rqaction, array(
			'dlattach',
			'viewpmxfile',
			'jseditor',
			'jsoption',
			'.xml',
			'xmlhttp',
			'verificationcode',
			'printpage',
			'suggest',
		))
	) return;

	// .. and on this
	if(isset($_REQUEST['area']) && $_REQUEST['area'] == 'popup' || (isset($_REQUEST['xml']) && $rqaction !== 'admin'))
		return;

	// no signup or login for robots please !!
	if(!empty($user_info['possibly_robot']) && isset($_REQUEST['action']) && in_array($_REQUEST['action'], array('login', 'signup')))
		redirectexit($scripturl);

	// redirect on illegal request
	if(!empty($_REQUEST['pmxerror']) && !empty($_REQUEST['action']))
		redirectexit('pmxerror=unknown');

	define('Portal', 1);

	// load all settings
	PortaMx_getSettings();

	// shoutbox POST request?
	if(!empty($_POST['pmx_shout']) && !empty($_POST['shoutbox_id']))
	{
		PortaMx_GetShoutbox($_POST['shoutbox_id']);
		if(checkECL_Cookie())
			$_SESSION['pmx_shoutreload'] = true;
		exit;
	}

	// clear cached block on defined actions
	if((!empty($rqaction) && in_array($rqaction, array('jsmodify')) && !isset($_REQUEST['preview']))
		||(!empty($rqaction) && (isset($_REQUEST['save']) || (isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'modify2')) && isset($_REQUEST['area']) && substr($_REQUEST['area'], 0, 4) !== 'pmx_'))
		clearBlocksCache();

	// check if a spidertest requested
	$pmxadmfunc = (isset($_GET['spidertest']) ? 'spidertest' : (isset($_GET['hideportal']) ? 'hideportal' : ''));
	if(!empty($pmxadmfunc) && in_array($_GET[$pmxadmfunc], array('on', 'off')) && !empty($modSettings['portal_enabled']))
	{
		if(allowPmx('pmx_admin') && $pmxadmfunc == 'spidertest' && $_GET[$pmxadmfunc] == 'on')
		{
			set_cookie($pmxadmfunc, json_encode($_COOKIE[$cookiename], true));
			set_cookie($cookiename, '');
			set_cookie('org_user_agent', $_SERVER['HTTP_USER_AGENT']);
			set_cookie('eclauth', '');
			unset($logCook);
			unset($_GET[$pmxadmfunc]);
			redirectexit(pmx_http_build_query($_GET), true);
		}
		elseif(allowPmx('pmx_admin') && $pmxadmfunc == 'hideportal')
		{
			set_cookie('hideportal', $_GET[$pmxadmfunc] == 'on' ? 'on' : '');
			unset($_GET[$pmxadmfunc]);
			redirectexit(pmx_http_build_query($_GET), true);
		}
		elseif($pmxadmfunc == 'spidertest' && $_GET[$pmxadmfunc] == 'off' && isset($_COOKIE[$pmxadmfunc]))
		{
			$logdata = pmx_json_decode($_COOKIE[$pmxadmfunc], true);
			setECL_Cookie();
			set_cookie($cookiename, $logdata);
			set_cookie('org_user_agent', null);
			set_cookie('spidertest', null);
			unset($_COOKIE['spidertest']);
			unset($_COOKIE['org_user_agent']);
			unset($_SESSION['id_robot']);
			unset($_GET[$pmxadmfunc]);
			$_SESSION['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
			redirectexit(pmx_http_build_query($_GET), true);
		}
		else
		{
			unset($_GET[$pmxadmfunc]);
			redirectexit(pmx_http_build_query($_GET), true);
		}
	}
	elseif(!empty($pmxadmfunc))
	{
		unset($_GET[$pmxadmfunc]);
		redirectexit(pmx_http_build_query($_GET), true);
	}

	// load common javascript
	loadJavascriptFile(PortaMx_loadCompressed('Portal.js'), array('external' => true));

	addInlineJavascript('
	var pmx_restore_top = '. intval(!empty($context['pmx']['settings']['restoretop'])) .';');

	if($doinit)
	{
		loadLanguage($context['pmx_languagedir'] .'Portal');
		loadCSSFile(PortaMx_loadCompressed('portal.css'), array('external' => true));
		addInlineJavascript('
	var pmx_onForum = false;');
		return;
	}

	//exit if Portal temporary disable
	if(!empty(get_cookie('hideportal')))
	{
		$modSettings['portal_enabled'] = 0;
		return;
	}

	// on Admin or Moderate load admin language, css and javascript
	if(($rqaction == 'admin' || $rqaction == 'portal') && isset($_REQUEST['area']) && in_array($_REQUEST['area'], explode(',', $context['pmx']['areas'])))
	{
		loadJavascriptFile(PortaMx_loadCompressed('PortalAdmin.js'), array('external' => true));
		loadJavascriptFile(PortaMx_loadCompressed('PortalPopup.js'), array('external' => true));
		loadCSSFile(PortaMx_loadCompressed('portal_admin.css'), array('external' => true));
		loadLanguage($context['pmx_languagedir'] .'Admin');
		addInlineJavascript("\n\t". 'BlockActive=\''. $txt['pmx_status_activ'] .' - '. $txt['pmx_status_change'] .'\';'."\n\t". 'BlockInactive=\''. $txt['pmx_status_inactiv'] .' - '. $txt['pmx_status_change'] .'\';');
	}

	// Error request?
	if(!empty($_REQUEST['pmxerror']))
		return PmxError();

	// check Error request, Forum request
	$context['pmx']['forumReq'] = (!empty($_REQUEST['action']) || !empty($_REQUEST['board']) || !empty($_REQUEST['topic']));
	if(empty($context['pmx']['forumReq']) && !empty($context['pmx']['settings']['other_actions']))
	{
		$reqtyp = Pmx_StrToArray($context['pmx']['settings']['other_actions']);
		foreach($reqtyp as $rtyp)
		{
			@list($rtyp, $rval) = Pmx_StrToArray($rtyp, '=');
			$context['pmx']['forumReq'] = ($context['pmx']['forumReq'] || (isset($_REQUEST[$rtyp]) && (is_null($rval) || empty($rval) || $_REQUEST[$rtyp] == $rval) ? false : $frontpage));
		}
	}

	// check Page, category, article request
	$pmxRequestTypes = array('spage', 'art', 'cat', 'child');
	$context['pmx']['pageReq'] = array();

	foreach($pmxRequestTypes as $type)
	{
		if(empty($_REQUEST['action']) && !empty($_REQUEST[$type]))
			$context['pmx']['pageReq'][$type] = PortaMx_makeSafe($_REQUEST[$type]);
	}

	// no request on forum or pages and no frontpage .. go to forum
	if(empty($context['pmx']['forumReq']) && empty($context['pmx']['pageReq']) && $context['pmx']['settings']['frontpage'] == 'none')
	{
		$_REQUEST['action'] = $_GET['action'] = 'community';
		$context['pmx']['forumReq'] = true;
	}

	// Admin panel/block hidding ?
	$hideRequest = array_intersect($context['pmx']['extracmd'] , array_keys($_REQUEST));
	if(!empty($hideRequest) && allowPmx('pmx_admin'))
	{
		@list($hideRequest) = array_values($hideRequest);
		$mode = substr($hideRequest, 5);
		$hidetyp = substr($hideRequest, 0, 5);
		$offparts = empty($modSettings['pmx_'. $hidetyp .'off']) ? array() : Pmx_StrToArray($modSettings['pmx_'. $hidetyp .'off']);
		if($mode == 'off')
		{
			if($hidetyp == 'panel')
				$offparts = array_intersect(($_REQUEST[$hideRequest] == 'all' ? $context['pmx']['block_sides'] : array_merge($offparts, Pmx_StrToArray($_REQUEST[$hideRequest]))), $context['pmx']['block_sides']);
			else
				$offparts = array_merge($offparts, Pmx_StrToIntArray($_REQUEST[$hideRequest]));
		}
		else
		{
			if($hidetyp == 'panel')
				$offparts = array_intersect(($_REQUEST[$hideRequest] == 'all' ?  array() : array_diff($offparts, Pmx_StrToArray($_REQUEST[$hideRequest]))), $context['pmx']['block_sides']);
			else
				$offparts = $_REQUEST[$hideRequest] == 'all' ?  array() : array_diff($offparts, Pmx_StrToIntArray($_REQUEST[$hideRequest]));
		}
		updateSettings(array('pmx_'. $hidetyp .'off' => implode(',', $offparts)));
		unset($_GET[$hideRequest]);
		redirectexit(pmx_http_build_query($_GET));
	}

	// check all the actions and more...
	if(empty($context['pmx']['forumReq']))
	{
		// if a redirect request, exit
		$requrl = (strpos($_SERVER['REQUEST_URL'], substr($scripturl, 0, strrpos($scripturl, '/'))) === false ? $_SERVER['REQUEST_URL'] : $scripturl);
		if(substr($requrl, 0, strrpos($requrl, '/')) != substr($scripturl, 0, strrpos($scripturl, '/')))
			return;

		// we use the frontpage ?
		$useFront = ($context['pmx']['settings']['frontpage'] == 'none' && empty($context['pmx']['pageReq'])) ? '' : 'frontpage';

		// get all block on active panels they can view
		$context['pmx']['viewblocks'] = getPanelsToShow($useFront);

		// frontpage and/or Page blocks exist ?
		if((!empty($maintenance) && $context['pmx']['settings']['frontpage'] != 'none') || empty($useFront) || !empty($context['pmx']['show_pagespanel']) || (!empty($context['pmx']['show_frontpanel']) && $context['pmx']['settings']['frontpage'] != 'none'))
		{
			// setup headers
			PortaMx_headers('frontpage');
			$context['robot_no_index'] = empty($context['pmx']['settings']['indexfront']);

			loadTemplate($context['pmx_templatedir'] .'Mainindex');
			$context['template_layers'][] = 'portal';

			if(!empty($context['pmx']['pageReq']) || (empty($context['pmx']['forumReq']) && $context['pmx']['settings']['frontpage'] != 'none'))
				loadTemplate($context['pmx_templatedir'] .'Portal');
		}

		// frontpage empty or locked
		else
		{
			// page req error?
			if(!empty($context['pmx']['pageReq']) && empty($context['pmx']['show_pagespanel']))
				redirectexit('pmxerror=page');

			// else go to forum
			$_REQUEST['action'] = $_GET['action'] = (!empty($maintenance) && empty($user_info['is_admin']) ? '' : 'community');
			$context['pmx']['forumReq'] = true;
			$context['pmx']['viewblocks'] = null;
		}
	}

	if(!empty($context['pmx']['forumReq']))
	{
		// get the action
		$action = (isset($_REQUEST['action']) ? ($_REQUEST['action'] == 'collapse' ? 'community' : $_REQUEST['action']) : (isset($_REQUEST['board']) ? 'boards' : (isset($_REQUEST['topic']) ? 'topics' : '')));

		// get all block on active panels they can view
		$context['pmx']['viewblocks'] = getPanelsToShow($action);

		// setup headers
		PortaMx_headers($action);

		// load the "Main" template on pages, cats or arts
		if(!empty($context['pmx']['pageReq']))
			loadTemplate($context['pmx_templatedir'] .'Portal');

		loadTemplate($context['pmx_templatedir'] .'Mainindex');
		$context['template_layers'][] = 'portal';
	}

	// Load the Frame template
	loadTemplate($context['pmx_templatedir'] .'Frames');

	// Create the linktree
	return pmx_MakeLinktree();
}

/**
* error requested
*/
function PmxError()
{
	global $context, $txt;

	// get all block on active panels
	$context['pmx']['pageReq'] = array();
	$action = 'frontpage';
	$context['pmx']['viewblocks'] = getPanelsToShow($action);

	// setup headers
	PortaMx_headers($action);

	loadTemplate($context['pmx_templatedir'] .'Mainindex');
	$context['template_layers'][] = 'portal';

	loadTemplate($context['pmx_templatedir'] .'Error');
	loadTemplate($context['pmx_templatedir'] .'Frames');

	if(in_array($_REQUEST['pmxerror'], array('page', 'article', 'category', 'unknown')))
	{
		$context['pmx_error_title'] = $txt[$_REQUEST['pmxerror'] .'_reqerror_title'];
		$context['pmx_error_text'] = $txt[$_REQUEST['pmxerror'] .'_reqerror_msg'];
	}
	elseif($_REQUEST['pmxerror'] == 'front')
	{
		$context['pmx_error_title'] = $txt['front_reqerror_title'];
		$context['pmx_error_text'] = $txt['front_reqerror_msg'];
	}
	elseif($_REQUEST['pmxerror'] == 'eclcat')
	{
		$context['pmx_error_title'] = $txt['category_reqerror_title'];
		$context['pmx_error_text'] = $txt['pmxelc_failed_cat'];
	}
	elseif($_REQUEST['pmxerror'] == 'eclart')
	{
		$context['pmx_error_title'] = $txt['article_reqerror_title'];
		$context['pmx_error_text'] = $txt['pmxelc_failed_art'];
	}
	else
	{
		$context['pmx_error_title'] = $txt['download_error_title'];
		if($_REQUEST['pmxerror'] == 'acs')
			$context['pmx_error_text'] = $txt['download_acces_error'];
		elseif($_REQUEST['pmxerror'] == 'fail')
			$context['pmx_error_text'] = $txt['download_notfound_error'];
		else
			$context['pmx_error_text'] = $txt['download_unknown_error'];
	}
	return pmx_MakeLinktree();
}

/**
* Create Linktree and Page Title
**/
function pmx_MakeLinktree()
{
	global $context, $scripturl, $txt, $mbname;

	// Setup page title
	if(empty($context['current_board']) && empty($context['current_topic']) && empty($_REQUEST['action']))
		$context['page_title'] = $context['forum_name'];

	// build the linktree
	$pmxforum = array();
	if(empty($context['linktree']))
		$context['linktree'] = array(array('url' => $scripturl, 'name' => $mbname));

	if(!empty($_GET['pmxerror']))
		$pmxforum[] = array('url' => $scripturl . '?pmxerror='. $_GET['pmxerror'], 'name' => $context['pmx_error_title']);

	$pmxhome[] = array_shift($context['linktree']);
	$inForum = !empty($context['current_board']) || !empty($context['current_topic']) || (!empty($_REQUEST['action']) && in_array($_REQUEST['action'], array('community', 'unread', 'unreadreplies', 'markasread')) || isset($_REQUEST['board']) || isset($_REQUEST['topic']));
	if($context['pmx']['settings']['frontpage'] != 'none' && empty($context['pmx']['pageReq']) && !empty($context['pmx']['showhome']) && !empty($inForum))
		$pmxhome[] = array('url' => $scripturl . '?action=community', 'name' => $txt['forum']);

	if(!empty($context['pmx']['pageReq']))
	{
		if(!isset($context['pmx']['pagenames']))
			$context['pmx']['pagenames'] = $txt['page_reqerror_title'];
		else
		{
			if(array_key_exists('spage', $context['pmx']['pagenames']) && isset($_GET['spage']))
			{
				$pg = $_GET['spage'];
				$pmxforum[] = array('url' => $scripturl . '?spage='. $pg, 'name' => $context['pmx']['pagenames']['spage']);
				$context['page_title'] .= ' - '. $context['pmx']['pagenames']['spage'];
			}
			else
			{
				if(array_key_exists('cat', $context['pmx']['pagenames']) && isset($_GET['cat']))
				{
					$pmxforum[0] = array('url' => $scripturl . '?cat='. $_GET['cat'], 'name' => $context['pmx']['pagenames']['cat']);
					$context['page_title'] .= ' - '. $context['pmx']['pagenames']['cat'];
				}
				if(array_key_exists('child', $context['pmx']['pagenames'])&& isset($_GET['cat']) && isset($_GET['child']))
				{
					$pmxforum[0] = array('url' => $scripturl . '?cat='. $_GET['cat'] .';child='. $_GET['child'], 'name' => $context['pmx']['pagenames']['child']);
					$context['page_title'] = $context['forum_name'] .' - '. $context['pmx']['pagenames']['child'];
				}
				if(array_key_exists('art', $context['pmx']['pagenames']) && isset($_GET['art']))
				{
					$context['page_title'] .= ' - '. $context['pmx']['pagenames']['art'];
					if(array_key_exists('child', $context['pmx']['pagenames']) && isset($_GET['cat']) && isset($_GET['child']) && isset($_GET['art']))
						$pmxforum[0] = array('url' => $scripturl . '?cat='. $_GET['cat'] .';child='. $_GET['child'] .';art='. $_GET['art'], 'name' => $context['pmx']['pagenames']['art']);
					elseif(array_key_exists('cat', $context['pmx']['pagenames']) && isset($_GET['cat']) && isset($_GET['art']))
						$pmxforum[0] = array('url' => $scripturl . '?cat='. $_GET['cat'] .';art='. $_GET['art'], 'name' => $context['pmx']['pagenames']['art']);
					elseif(isset($_GET['art']))
						$pmxforum[0] = array('url' => $scripturl . '?art='. $_GET['art'], 'name' => $context['pmx']['pagenames']['art']);
				}
			}
		}
	}
	else
	{
		if(!empty($_GET['action']) && $_GET['action'] == 'portal')
		{
			$context['linktree'][0] = array('url' => $scripturl . '?action='. $_GET['action'].';area=pmx_center', 'name' => $txt['pmx_extension']);
			if(isset($_GET['area']) && $_GET['area'] != 'pmx_center')
				$context['linktree'][1] = array('url' => $scripturl . '?action='. $_GET['action'] .';area='. $_GET['area'], 'name' => $txt[$_GET['area']]);
		}
	}

	if(empty($pmxforum))
		$context['linktree'] = array_merge($pmxhome, $context['linktree']);
	else
		$context['linktree'] = array_merge($pmxhome, $pmxforum, $context['linktree']);
}
?>