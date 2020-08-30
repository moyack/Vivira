<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file AdminSettings.php
 * AdminSettings reached all Posts from Settings Manager.
 * Checks the values and saved the parameter to the database.
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* Receive all the Posts from Settings Manager, check and save it.
* Finally the Admin settings are prepared and the templare loaded.
*/
function Portal_AdminSettings()
{
	global $boarddir, $scripturl, $pmxcFunc, $context, $modSettings, $txt, $pmxCacheFunc;

	$_GET = PortaMx_makeSafe($_GET);
	$admMode = $_GET['action'];
	$pmx_area = $_GET['area'];

	if(($admMode == 'admin' || $admMode == 'portal'))
	{
		require_once($context['pmx_sourcedir'] .'AdminSubs.php');
		$context['pmx']['subaction'] = isset($_GET['sa']) ? $_GET['sa'] : 'globals';

		// From template ?
		if(PortaMx_checkPOST())
		{
			checkSession('post');
			$currentPanel = '';

			// check the Post array
			if(isset($_POST['save_settings']) && !empty($_POST['save_settings']))
			{
				// check defined numeric vars (check_num_vars holds the posted array to check like [varname][varname] ...)
				if(isset($_POST['check_num_vars']))
				{
					foreach($_POST['check_num_vars'] as $val)
					{
						$data = explode(',', $val);
						$post = '$_POST'. str_replace(array('[', ']'), array('[\'', '\']'), $data[0]);
						if(eval("return isset($post);") && eval("return !is_numeric($post);"))
								eval("$post = $data[1];");
					}
					unset($_POST['check_num_vars']);
				}

				if(!empty($_POST['curPanel']))
					$currentPanel = 'pn='. $_POST['curPanel'] .';';

				// access update?
				if(!empty($_POST['update_access']))
				{
					$perms = array('pmx_promote' => array(), 'pmx_create' => array(), 'pmx_articles' => array(), 'pmx_blocks' => array(), 'pmx_admin' => array());
					if(isset($_POST['setaccess']))
						foreach($_POST['setaccess'] as $acsname => $acsdata)
							$perms[$acsname] = $acsdata;

					$pmxcFunc['db_insert']('replace', '
						{db_prefix}portal_settings',
						array(
							'varname' => 'string',
							'config' => 'string',
						),
						array(
							'permissions',
							json_encode($perms, true)
						),
						array('varname')
					);

					// clear cache
					$pmxCacheFunc['clean']();
				}

				// other settings update
				else
				{
					$config = array();
					$request = $pmxcFunc['db_query']('', '
							SELECT config
							FROM {db_prefix}portal_settings
							WHERE varname = {string:settings}',
						array('settings' => 'settings')
					);
					if($pmxcFunc['db_num_rows']($request) > 0)
					{
						$row = $pmxcFunc['db_fetch_assoc']($request);
						$pmxcFunc['db_free_result']($request);
						$config = pmx_json_decode($row['config'], true);
					}

					$setKeys = array_diff(array_keys($_POST), array('curPanel', 'save_settings', 'sa', 'sc'));
					foreach($setKeys as $key)
					{
						if($key == 'promotes')
						{
							$promo = Pmx_StrToIntArray($_POST[$key]);
							$pmxcFunc['db_query']('', '
								UPDATE {db_prefix}portal_settings
									SET config = {string:config}
									WHERE varname = {string:settings}',
								array('config' => json_encode($promo, true), 'settings' => 'promotes')
							);

							// find all promoted block
							$blocks = null;
							$request = $pmxcFunc['db_query']('', '
								SELECT id
								FROM {db_prefix}portal_blocks
								WHERE active = 1 AND blocktype = {string:blocktype}',
								array('blocktype' => 'promotedposts')
							);
							while($row = $pmxcFunc['db_fetch_assoc']($request))
								$blocks[] = $row['id'];
							$pmxcFunc['db_free_result']($request);

							$_SESSION['pmx_refresh_promote'] = $blocks;
						}
						else
						{
							if($key == 'dl_access')
								$_POST['dl_access'] = implode(',', $_POST['dl_access']);

							$config[$key] = $_POST[$key];
						}
					}

					$pmxcFunc['db_query']('', '
						UPDATE {db_prefix}portal_settings
							SET config = {string:config}
							WHERE varname = {string:settings}',
						array('config' => json_encode($config, true), 'settings' => 'settings')
					);

					if(isset($_POST['frontpage']))
						updateSettings(array('pmx_frontmode' => ($_POST['frontpage'] == 'centered' ? '1' : '0')));

					// clear cached values
					$pmxCacheFunc['clean']();
				}
				$_SESSION['saved_successful'] = true;
			}
			redirectexit('action='. $admMode .';area='. $pmx_area . (!empty($context['pmx']['subaction']) ? ';sa='. $context['pmx']['subaction'] : '') .';'. $currentPanel . $context['session_var'] .'='. $context['session_id']);
		}

		// Load data for the other settings
		else
		{
			$context['pmx']['admgroups'] = PortaMx_getUserGroups(true);
			$context['pmx']['limitgroups'] = PortaMx_getUserGroups(true, false);
			$context['pmx']['acsgroups'] = PortaMx_getUserGroups(false, !empty($context['pmx']['settings']['postcountacs']));
		}

		// setup pagetitle
		$context['page_title'] = $txt['pmx_settings'];
		$context['pmx']['AdminMode'] = $admMode;

		// load css, language and execute template
		loadCSSFile(PortaMx_loadCompressed('portal_settings.css'), array('external' => true));
		loadLanguage($context['pmx_languagedir'] .'AdminSettings');
		loadTemplate($context['pmx_templatedir'] .'AdminSettings');
	}
	else
		fatal_error($txt['pmx_acces_error']);
}
?>