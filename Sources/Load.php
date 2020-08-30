<?php

/**
 * This file has the hefty job of loading information for the forum.
 *
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx & Simple Machines
 * @copyright 2018 PortaMx,  Simple Machines and individual contributors
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 1.41
 */

if (!defined('PMX'))
	die('No direct access...');

/**
 * Load the $modSettings array.
 */
function reloadSettings()
{
	global $modSettings, $boarddir, $pmxcFunc, $txt, $db_character_set, $sourcedir, $context, $pmxCacheFunc, $cache_enable, $forum_version;

	// Most database systems have not set UTF-8 as their default input charset.
	if (!empty($db_character_set))
		$pmxcFunc['db_query']('set_character_set', '
			SET NAMES ' . $db_character_set,
			array(
			)
		);

	// setup cache enabled or not ..
	$modSettings['cache_enable'] = $cache_enable;

	// Try to load it from the cache first; it'll never get cached if the setting is off.
	if (empty($cache_enable) || ($modSettings = $pmxCacheFunc['get']('modSettings')) == null)
	{
		$request = $pmxcFunc['db_query']('', '
			SELECT variable, value
			FROM {db_prefix}settings',
			array()
		);
		$needUpdate = array();
		$modSettings = array();
		if (!$request)
			display_db_error();
		while ($row = $pmxcFunc['db_fetch_row']($request))
		{
			// check we have the right version
			if($row[0] == 'pmxVersion' && $row[1] != '1.41')
			{
				$row[1] = '1.41';
				$needUpdate = array($row[0] => $row[1]);
			}
			$modSettings[$row[0]] = $row[1];
		}
		$pmxcFunc['db_free_result']($request);

		// Do a few things to protect against missing settings or settings with invalid values...
		if (empty($modSettings['defaultMaxTopics']) || $modSettings['defaultMaxTopics'] <= 0 || $modSettings['defaultMaxTopics'] > 999)
			$modSettings['defaultMaxTopics'] = 20;
		if (empty($modSettings['defaultMaxMessages']) || $modSettings['defaultMaxMessages'] <= 0 || $modSettings['defaultMaxMessages'] > 999)
			$modSettings['defaultMaxMessages'] = 15;
		if (empty($modSettings['defaultMaxMembers']) || $modSettings['defaultMaxMembers'] <= 0 || $modSettings['defaultMaxMembers'] > 999)
			$modSettings['defaultMaxMembers'] = 30;
		if (empty($modSettings['defaultMaxListItems']) || $modSettings['defaultMaxListItems'] <= 0 || $modSettings['defaultMaxListItems'] > 999)
			$modSettings['defaultMaxListItems'] = 15;

		$pmxCacheFunc['put']('modSettings', $modSettings, 3600);

		if(count($needUpdate) > 0 && !file_exists($boarddir .'/upgrade.php'))
			updateSettings($needUpdate);
	}

	// disable minimize if cache disabled
	if(empty($cache_enable))
		$modSettings['minimize_files'] = false;

	// check if a Mobile device used
	$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
	$modSettings['isMobile'] = false;
	if(preg_match("/(android|webos|avantgo|iphone|ipad|ipod|blackberry|iemobile|bolt|bo‌​ost|cricket|docomo|fone|hiptop|mini|opera mini|kitkat|lumia|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos|silk\/[0-9\.\s]+safari)/i", $useragent))
		$modSettings['isMobile'] = true;

	// UTF-8 ?
	$utf8 = (empty($modSettings['global_character_set']) ? $txt['lang_character_set'] : $modSettings['global_character_set']) === 'UTF-8';

	// Set a list of common functions.
	$ent_list = empty($modSettings['disableEntityCheck']) ? '&(#\d{1,7}|quot|amp|lt|gt|nbsp);' : '&(#021|quot|amp|lt|gt|nbsp);';
	$ent_check = empty($modSettings['disableEntityCheck']) ? function ($string)
	{
		$string = preg_replace_callback('~(&#(\d{1,7}|x[0-9a-fA-F]{1,6});)~', 'entity_fix__callback', $string);
		return $string;
	} : function ($string)
	{
		return $string;
	};
	$fix_utf8mb4 = function ($string) use ($utf8)
	{
		if (!$utf8)
			return $string;

		$i = 0;
		$len = strlen($string);
		$new_string = '';
		while ($i < $len)
		{
			$ord = ord($string[$i]);
			if ($ord < 128)
			{
				$new_string .= $string[$i];
				$i++;
			}
			elseif ($ord < 224)
			{
				$new_string .= $string[$i] . $string[$i+1];
				$i += 2;
			}
			elseif ($ord < 240)
			{
				$new_string .= $string[$i] . $string[$i+1] . $string[$i+2];
				$i += 3;
			}
			elseif ($ord < 248)
			{
				// Magic happens.
				$val = (ord($string[$i]) & 0x07) << 18;
				$val += (ord($string[$i+1]) & 0x3F) << 12;
				$val += (ord($string[$i+2]) & 0x3F) << 6;
				$val += (ord($string[$i+3]) & 0x3F);
				$new_string .= '&#' . $val . ';';
				$i += 4;
			}
		}
		return $new_string;
	};

	// Preg_replace space characters depend on the character set in use
	$space_chars = $utf8 ? '\x{A0}\x{AD}\x{2000}-\x{200F}\x{201F}\x{202F}\x{3000}\x{FEFF}' : '\x00-\x08\x0B\x0C\x0E-\x19\xA0';

	// global array of anonymous helper functions, used mostly to properly handle multi byte strings
	$pmxcFunc += array(
		'entity_fix' => function ($string)
		{
			$num = $string[0] === 'x' ? hexdec(substr($string, 1)) : (int) $string;
			return $num < 0x20 || $num > 0x10FFFF || ($num >= 0xD800 && $num <= 0xDFFF) || $num === 0x202E || $num === 0x202D ? '' : '&#' . $num . ';';
		},
		'htmlspecialchars' => function ($string, $quote_style = ENT_COMPAT, $charset = 'ISO-8859-1') use ($ent_check, $utf8, $fix_utf8mb4)
		{
			return $fix_utf8mb4($ent_check(htmlspecialchars($string, $quote_style, $utf8 ? 'UTF-8' : $charset)));
		},
		'htmltrim' => function ($string) use ($utf8, $space_chars, $ent_check)
		{
			return preg_replace('~^(?:[ \t\n\r\x0B\x00' . $space_chars . ']|&nbsp;)+|(?:[ \t\n\r\x0B\x00' . $space_chars . ']|&nbsp;)+$~' . ($utf8 ? 'u' : ''), '', $ent_check($string));
		},
		'strlen' => function ($string) use ($ent_list, $utf8, $ent_check)
		{
			return strlen(preg_replace('~' . $ent_list . ($utf8 ? '|.~u' : '~'), '_', $ent_check($string)));
		},
		'strpos' => function ($haystack, $needle, $offset = 0) use ($utf8, $ent_check, $modSettings)
		{
			$haystack_arr = preg_split('~(&#' . (empty($modSettings['disableEntityCheck']) ? '\d{1,7}' : '021') . ';|&quot;|&amp;|&lt;|&gt;|&nbsp;|.)~' . ($utf8 ? 'u' : ''), $ent_check($haystack), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

			if (strlen($needle) === 1)
			{
				$result = array_search($needle, array_slice($haystack_arr, $offset));
				return is_int($result) ? $result + $offset : false;
			}
			else
			{
				$needle_arr = preg_split('~(&#' . (empty($modSettings['disableEntityCheck']) ? '\d{1,7}' : '021') . ';|&quot;|&amp;|&lt;|&gt;|&nbsp;|.)~' . ($utf8 ? 'u' : '') . '', $ent_check($needle), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
				$needle_size = count($needle_arr);

				$result = array_search($needle_arr[0], array_slice($haystack_arr, $offset));
				while ((int) $result === $result)
				{
					$offset += $result;
					if (array_slice($haystack_arr, $offset, $needle_size) === $needle_arr)
						return $offset;
					$result = array_search($needle_arr[0], array_slice($haystack_arr, ++$offset));
				}
				return false;
			}
		},
		'substr' => function ($string, $start, $length = null) use ($utf8, $ent_check, $modSettings)
		{
			$ent_arr = preg_split('~(&#' . (empty($modSettings['disableEntityCheck']) ? '\d{1,7}' : '021') . ';|&quot;|&amp;|&lt;|&gt;|&nbsp;|.)~' . ($utf8 ? 'u' : '') . '', $ent_check($string), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			return $length === null ? implode('', array_slice($ent_arr, $start)) : implode('', array_slice($ent_arr, $start, $length));
		},
		'strtolower' => $utf8 ? function ($string) use ($sourcedir)
		{
			if (!function_exists('mb_strtolower'))
			{
				require_once($sourcedir . '/Subs-Charset.php');
				return utf8_strtolower($string);
			}

			return mb_strtolower($string, 'UTF-8');
		} : 'strtolower',
		'strtoupper' => $utf8 ? function ($string)
		{
			global $sourcedir;

			if (!function_exists('mb_strtolower'))
			{
				require_once($sourcedir . '/Subs-Charset.php');
				return utf8_strtoupper($string);
			}

			return mb_strtoupper($string, 'UTF-8');
		} : 'strtoupper',
		'truncate' => function($string, $length) use ($utf8, $ent_check, $ent_list, &$pmxcFunc)
		{
			$string = $ent_check($string);
			preg_match('~^(' . $ent_list . '|.){' . $pmxcFunc['strlen'](substr($string, 0, $length)) . '}~'.  ($utf8 ? 'u' : ''), $string, $matches);
			$string = $matches[0];
			while (strlen($string) > $length)
				$string = preg_replace('~(?:' . $ent_list . '|.)$~'.  ($utf8 ? 'u' : ''), '', $string);
			return $string;
		},
		'ucfirst' => $utf8 ? function ($string) use (&$pmxcFunc)
		{
			return $pmxcFunc['strtoupper']($pmxcFunc['substr']($string, 0, 1)) . $pmxcFunc['substr']($string, 1);
		} : 'ucfirst',
		'ucwords' => $utf8 ? function ($string) use (&$pmxcFunc)
		{
			$words = preg_split('~([\s\r\n\t]+)~', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
			for ($i = 0, $n = count($words); $i < $n; $i += 2)
				$words[$i] = $pmxcFunc['ucfirst']($words[$i]);
			return implode('', $words);
		} : 'ucwords',
	);

	// Setting the timezone is a requirement for some functions.
	if (isset($modSettings['default_timezone']) && in_array($modSettings['default_timezone'], timezone_identifiers_list()))
		date_default_timezone_set($modSettings['default_timezone']);
	else
	{
		// Get PHP's default timezone, if set
		$ini_tz = ini_get('date.timezone');
		if (!empty($ini_tz))
			$modSettings['default_timezone'] = $ini_tz;
		else
			$modSettings['default_timezone'] = '';

		// If date.timezone is unset, invalid, or just plain weird, make a best guess
		if (!in_array($modSettings['default_timezone'], timezone_identifiers_list()))
		{
			$server_offset = @mktime(0, 0, 0, 1, 1, 1970);
			$modSettings['default_timezone'] = timezone_name_from_abbr('', $server_offset, 0);
		}
		date_default_timezone_set($modSettings['default_timezone']);
	}

	// Check the load averages?
	if (!empty($modSettings['loadavg_enable']))
	{
		if (($modSettings['load_average'] = $pmxCacheFunc['get']('loadavg')) == null)
		{
			$modSettings['load_average'] = @file_get_contents('/proc/loadavg');
			if (!empty($modSettings['load_average']) && preg_match('~^([^ ]+?) ([^ ]+?) ([^ ]+)~', $modSettings['load_average'], $matches) != 0)
				$modSettings['load_average'] = (float) $matches[1];
			elseif (($modSettings['load_average'] = @`uptime`) != null && preg_match('~load average[s]?: (\d+\.\d+), (\d+\.\d+), (\d+\.\d+)~i', $modSettings['load_average'], $matches) != 0)
				$modSettings['load_average'] = (float) $matches[1];
			else
				unset($modSettings['load_average']);

			if (!empty($modSettings['load_average']) || $modSettings['load_average'] === 0.0)
				$pmxCacheFunc['put']('loadavg', $modSettings['load_average'], 90);
		}

		if (!empty($modSettings['load_average']) || $modSettings['load_average'] === 0.0)
			call_integration_hook('integrate_load_average', array($modSettings['load_average']));

		if (!empty($modSettings['loadavg_forum']) && !empty($modSettings['load_average']) && $modSettings['load_average'] >= $modSettings['loadavg_forum'])
			display_loadavg_error();
	}

	// Is post moderation alive and well? Everywhere else assumes this has been defined, so let's make sure it is.
	$modSettings['postmod_active'] = !empty($modSettings['postmod_active']);

	// Here to justify the name of this function. :P
	// It should be added to the install and upgrade scripts.
	// But since the converters need to be updated also. This is easier.
	if (empty($modSettings['currentAttachmentUploadDir']))
	{
		updateSettings(array(
			'attachmentUploadDir' => json_encode(array(1 => $modSettings['attachmentUploadDir']), true),
			'currentAttachmentUploadDir' => 1,
		));
	}

	// update version settings if need
	if(isset($forum_version))
	{
		if(empty($modSettings['pmxVersion']) || $modSettings['pmxVersion'] !== $forum_version)
			updateSettings(array('pmxVersion' => $forum_version));
	}

	// Integration is cool.
	if (defined('PMX_INTEGRATION_SETTINGS'))
	{
		$integration_settings = pmx_json_decode(PMX_INTEGRATION_SETTINGS, true);
		foreach ($integration_settings as $hook => $function)
			add_integration_function($hook, $function, '', false);
	}

	// Load SEF Module
	require_once($sourcedir .'/Subs-Sef.php');

	// Load Portal if enabled
	if (!empty($modSettings['portal_enabled']))
	{
		// load portal files
		require_once($sourcedir .'/Portal/Portal.php');
		require_once($sourcedir .'/Portal/SubsCompat.php');

		// shoutbox reload request?
		if(!empty($_SESSION['pmx_shoutreload']))
		{
			unset($_SESSION['pmx_shoutreload']);
			exit;
		}

		// let us emulate a spider..
		if(isset($_COOKIE['spidertest']) && !empty($_COOKIE['spidertest']))
			$_SERVER['HTTP_USER_AGENT'] = 'PortaMx Spider';

		// integrate is easy
		require_once($sourcedir .'/Portal/LoadData.php');
		require_once($sourcedir .'/Portal/Portal-Integrate.php');
	}

	// Any files to pre include?
	if (!empty($modSettings['integrate_pre_include']))
	{
		$pre_includes = explode(',', $modSettings['integrate_pre_include']);
		foreach ($pre_includes as $include)
		{
			$include = strtr(trim($include), array('$boarddir' => $boarddir, '$sourcedir' => $sourcedir));
			if (file_exists($include))
				require_once($include);
		}
	}

	// This determines the server... not used in many places, except for login fixing.
	$context['server'] = array(
		'is_iis' => isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false,
		'is_apache' => isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false,
		'is_litespeed' => isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false,
		'is_lighttpd' => isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'lighttpd') !== false,
		'is_nginx' => isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false,
		'is_cgi' => isset($_SERVER['SERVER_SOFTWARE']) && strpos(php_sapi_name(), 'cgi') !== false,
		'is_windows' => strpos(PHP_OS, 'WIN') === 0,
		'iso_case_folding' => ord(strtolower(chr(138))) === 154,
	);
	// A bug in some versions of IIS under CGI (older ones) makes cookie setting not work with Location: headers.
	$context['server']['needs_login_fix'] = $context['server']['is_cgi'] && $context['server']['is_iis'];

	// Define a list of icons used across multiple places.
	$context['stable_icons'] = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'poll', 'moved', 'recycled', 'clip');

	// Define an array for custom profile fields placements.
	$context['cust_profile_fields_placement'] = array(
		'standard',
		'icons',
		'above_signature',
		'below_signature',
		'below_avatar',
		'above_member',
		'bottom_poster',
	);

	// Define an array of allowed HTML tags.
	$context['allowed_html_tags'] = array(
		'<img>',
		'<div>',
	);

	// These are the only valid image types for PMX, by default anyway.
	$context['validImageTypes'] = array(
		1 => 'gif',
		2 => 'jpeg',
		3 => 'png',
		5 => 'psd',
		6 => 'bmp',
		7 => 'tiff',
		8 => 'tiff',
		9 => 'jpeg',
		14 => 'iff'
	);

	// Define a list of allowed tags for descriptions.
	$context['description_allowed_tags'] = array('abbr', 'anchor', 'b', 'center', 'color', 'font', 'hr', 'i', 'img', 'iurl', 'left', 'li', 'list', 'ltr', 'pre', 'right', 's', 'sub', 'sup', 'table', 'td', 'tr', 'u', 'url',);

	// Get an error count, if necessary
	if (!isset($context['num_errors']))
	{
		$query = $pmxcFunc['db_query']('', '
			SELECT COUNT(id_error)
			FROM {db_prefix}log_errors',
			array()
		);

		list($context['num_errors']) = $pmxcFunc['db_fetch_row']($query);
		$pmxcFunc['db_free_result']($query);
	}

	// Call pre load integration functions.
	call_integration_hook('integrate_pre_load');

	// call SEF ..
	pmxsef_convertSEF();
}

/**
 * Load all the important user information.
 * What it does:
 * - sets up the $user_info array
 * - assigns $user_info['query_wanna_see_board'] for what boards the user can see.
 * - first checks for cookie or integration validation.
 * - uses the current session if no integration function or cookie is found.
 * - checks password length, if member is activated and the login span isn't over.
 * - if validation fails for the user, $id_member is set to 0.
 * - updates the last visit time when needed.
 */
function loadUserSettings($checkOnly = false)
{
	global $modSettings, $user_settings, $sourcedir, $pmxcFunc, $pmxCacheFunc;
	global $cookiename, $user_info, $language, $txt, $context, $image_proxy_enabled, $image_proxy_secret, $boardurl;

	// Check first the integration, then the cookie, and last the session.
	if (count($integration_ids = call_integration_hook('integrate_verify_user')) > 0)
	{
		$id_member = 0;
		foreach ($integration_ids as $integration_id)
		{
			$integration_id = (int) $integration_id;
			if ($integration_id > 0)
			{
				$id_member = $integration_id;
				$already_verified = true;
				break;
			}
		}
	}
	else
		$id_member = 0;

	if (empty($id_member) && isset($_COOKIE[$cookiename]))
	{
		$cookie_data = pmx_json_decode($_COOKIE[$cookiename], true, false);

		if (empty($cookie_data))
			$cookie_data = safe_unserialize($_COOKIE[$cookiename]);

		list ($id_member, $password) = $cookie_data;
		$id_member = !empty($id_member) && strlen($password) > 0 ? (int) $id_member : 0;
	}
	elseif (empty($id_member) && isset($_SESSION['login_' . $cookiename]) && ($_SESSION['USER_AGENT'] == $_SERVER['HTTP_USER_AGENT'] || !empty($modSettings['disableCheckUA'])) && !isset($_COOKIE['spidertest']))
	{
		// @todo Perhaps we can do some more checking on this, such as on the first octet of the IP?
		$cookie_data = pmx_json_decode($_SESSION['login_' . $cookiename], true);

		if (empty($cookie_data))
			$cookie_data = safe_unserialize($_SESSION['login_' . $cookiename]);

		list ($id_member, $password, $login_span) = $cookie_data;
		$id_member = !empty($id_member) && strlen($password) == 128 && $login_span > time() ? (int) $id_member : 0;
	}

	if(!empty($checkOnly))
		return $id_member;

	// Only load this stuff if the user isn't a guest.
	if ($id_member != 0)
	{
		// Is the member data cached?
		if (empty($modSettings['cache_enable']) || $modSettings['cache_enable'] < 2 || ($user_settings = $pmxCacheFunc['get']('user_settings-' . $id_member)) == null)
		{
			$request = $pmxcFunc['db_query']('', '
				SELECT mem.*, COALESCE(a.id_attach, 0) AS id_attach, a.filename, a.attachment_type
				FROM {db_prefix}members AS mem
					LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = {int:id_member})
				WHERE mem.id_member = {int:id_member}
				LIMIT 1',
				array(
					'id_member' => $id_member,
				)
			);
			$user_settings = $pmxcFunc['db_fetch_assoc']($request);
			$pmxcFunc['db_free_result']($request);

			if (!empty($modSettings['force_ssl']) && $image_proxy_enabled && stripos($user_settings['avatar'], 'http://') !== false)
				$user_settings['avatar'] = strtr($boardurl, array('http://' => 'https://')) . '/proxy.php?request=' . urlencode($user_settings['avatar']) . '&hash=' . md5($user_settings['avatar'] . $image_proxy_secret);

			if (!empty($modSettings['cache_enable']) && $modSettings['cache_enable'] >= 2)
				$pmxCacheFunc['put']('user_settings-' . $id_member, $user_settings, 60);
		}

		// Did we find 'im?  If not, junk it.
		if (!empty($user_settings))
		{
			// As much as the password should be right, we can assume the integration set things up.
			if (!empty($already_verified) && $already_verified === true)
				$check = true;
			// SHA-512 hash should be 128 characters long.
			elseif (strlen($password) == 128)
				$check = hash_salt($user_settings['passwd'], $user_settings['password_salt']) == $password;
			else
				$check = false;

			// Wrong password or not activated - either way, you're going nowhere.
			$id_member = $check && ($user_settings['is_activated'] == 1 || $user_settings['is_activated'] == 11) ? (int) $user_settings['id_member'] : 0;
		}
		else
			$id_member = 0;

		// If we no longer have the member maybe they're being all hackey, stop brute force!
		if (!$id_member)
		{
			require_once($sourcedir . '/LogInOut.php');
			validatePasswordFlood(!empty($user_settings['id_member']) ? $user_settings['id_member'] : $id_member, !empty($user_settings['passwd_flood']) ? $user_settings['passwd_flood'] : false, $id_member != 0);
		}
		// Validate for Two Factor Authentication
		elseif (!empty($modSettings['tfa_mode']) && $id_member && !empty($user_settings['tfa_secret']) && (empty($_REQUEST['action']) || !in_array($_REQUEST['action'], array('login2', 'logintfa'))))
		{
			$tfacookie = $cookiename . '_tfa';
			$tfasecret = null;

			$verified = call_integration_hook('integrate_verify_tfa', array($id_member, $user_settings));

			if (empty($verified) || !in_array(true, $verified))
			{
				if (!empty($_COOKIE[$tfacookie]))
				{
					$tfa_data = pmx_json_decode($_COOKIE[$tfacookie]);

					if (is_null($tfa_data))
						$tfa_data = safe_unserialize($_COOKIE[$tfacookie]);

					list ($tfamember, $tfasecret) = $tfa_data;

					if ((int) $tfamember != $id_member)
						$tfasecret = null;
				}

				if (empty($tfasecret) || hash_salt($user_settings['tfa_backup'], $user_settings['password_salt']) != $tfasecret)
				{
					$id_member = 0;
					redirectexit('action=logintfa');
				}
			}
		}
		// When authenticating their two factor code, make sure to reset their ID for security
		elseif (!empty($modSettings['tfa_mode']) && $id_member && !empty($user_settings['tfa_secret']) && $_REQUEST['action'] == 'logintfa')
		{
			$id_member = 0;
			$context['tfa_member'] = $user_settings;
			$user_settings = array();
		}
		// Are we forcing 2FA? Need to check if the user groups actually require 2FA
		elseif (!empty($modSettings['tfa_mode']) && $modSettings['tfa_mode'] >= 2 && $id_member && empty($user_settings['tfa_secret']))
		{
			if ($modSettings['tfa_mode'] == 2) //only do this if we are just forcing SOME membergroups
			{
				//Build an array of ALL user membergroups.
				$full_groups = array($user_settings['id_group']);
				if (!empty($user_settings['additional_groups']))
				{
					$full_groups = array_merge($full_groups, explode(',', $user_settings['additional_groups']));
					$full_groups = array_unique($full_groups); //duplicates, maybe?
				}

				//Find out if any group requires 2FA
				$request = $pmxcFunc['db_query']('', '
					SELECT COUNT(id_group) AS total
					FROM {db_prefix}membergroups
					WHERE tfa_required = {int:tfa_required}
						AND id_group IN ({array_int:full_groups})',
					array(
						'tfa_required' => 1,
						'full_groups' => $full_groups,
					)
				);
				$row = $pmxcFunc['db_fetch_assoc']($request);
				$pmxcFunc['db_free_result']($request);
			}
			else
				$row['total'] = 1; //simplifies logics in the next "if"

			$area = !empty($_REQUEST['area']) ? $_REQUEST['area'] : '';
			$action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : '';

			if ($row['total'] > 0 && !in_array($action, array('profile', 'logout')) || ($action == 'profile' && $area != 'tfasetup'))
				redirectexit('action=profile;area=tfasetup;forced');
		}
	}

	// Found 'im, let's set up the variables.
	if ($id_member != 0)
	{
		if(!checkECL_Cookie())
			setECL_Cookie();

		// Let's not update the last visit time in these cases...
		// 1. SSI doesn't count as visiting the forum.
		// 2. RSS feeds and XMLHTTP requests don't count either.
		// 3. If it was set within this session, no need to set it again.
		// 4. New session, yet updated < five hours ago? Maybe cache can help.
		// 5. We're still logging in or authenticating
		if (PMX != 'SSI' && !isset($_REQUEST['xml']) && (!isset($_REQUEST['action']) || !in_array($_REQUEST['action'], array('.xml', 'login2', 'logintfa'))) && empty($_SESSION['id_msg_last_visit']) && (empty($modSettings['cache_enable']) || ($_SESSION['id_msg_last_visit'] = $pmxCacheFunc['get']('user_last_visit-' . $id_member)) === null))
		{
			// @todo can this be cached?
			// Do a quick query to make sure this isn't a mistake.
			$result = $pmxcFunc['db_query']('', '
				SELECT poster_time
				FROM {db_prefix}messages
				WHERE id_msg = {int:id_msg}
				LIMIT 1',
				array(
					'id_msg' => $user_settings['id_msg_last_visit'],
				)
			);
			list ($visitTime) = $pmxcFunc['db_fetch_row']($result);
			$pmxcFunc['db_free_result']($result);

			$_SESSION['id_msg_last_visit'] = $user_settings['id_msg_last_visit'];

			// If it was *at least* five hours ago...
			if ($visitTime < time() - 5 * 3600)
			{
				updateMemberData($id_member, array('id_msg_last_visit' => (int) $modSettings['maxMsgID'], 'last_login' => time(), 'member_ip' => $_SERVER['REMOTE_ADDR'], 'member_ip2' => $_SERVER['BAN_CHECK_IP']));
				$user_settings['last_login'] = time();

				if (!empty($modSettings['cache_enable']) && $modSettings['cache_enable'] >= 2)
					$pmxCacheFunc['put']('user_settings-' . $id_member, $user_settings, 60);

				if (!empty($modSettings['cache_enable']))
					$pmxCacheFunc['put']('user_last_visit-' . $id_member, $_SESSION['id_msg_last_visit'], 5 * 3600);
			}
		}
		elseif (empty($_SESSION['id_msg_last_visit']))
			$_SESSION['id_msg_last_visit'] = $user_settings['id_msg_last_visit'];

		$username = $user_settings['member_name'];

		if (empty($user_settings['additional_groups']))
			$user_info = array(
				'groups' => array($user_settings['id_group'], $user_settings['id_post_group'])
			);
		else
			$user_info = array(
				'groups' => array_merge(
					array($user_settings['id_group'], $user_settings['id_post_group']),
					explode(',', $user_settings['additional_groups'])
				)
			);

		// Because history has proven that it is possible for groups to go bad - clean up in case.
		foreach ($user_info['groups'] as $k => $v)
			$user_info['groups'][$k] = (int) $v;

		// This is a logged in user, so definitely not a spider.
		$user_info['possibly_robot'] = false;

		// Figure out the new time offset.
		if (!empty($user_settings['timezone']))
		{
			// Get the offsets from UTC for the server, then for the user.
			$tz_system = new DateTimeZone(@date_default_timezone_get());
			$tz_user = new DateTimeZone($user_settings['timezone']);
			$time_system = new DateTime('now', $tz_system);
			$time_user = new DateTime('now', $tz_user);
			$user_info['time_offset'] = ($tz_user->getOffset($time_user) - $tz_system->getOffset($time_system)) / 3600;
		}
		else
		{
			// !!! Compatibility.
			$user_info['time_offset'] = empty($user_settings['time_offset']) ? 0 :$user_settings['time_offset'];
		}
		if(empty($user_settings['avatar']) && empty($user_settings['filename']))
		{
			if(preg_match('/[a-zA-Z]/', $user_settings['member_name'], $tmp) > 0)
			{
				$user_settings['avatar'] = strtolower($tmp[0]) .'.png';
				$user_settings['avatar_class'] = 'default';
			}
		}
	}
	// If the user is a guest, initialize all the critical user settings.
	else
	{
		// This is what a guest's variables should be.
		$username = '';
		$user_info = array('groups' => array(-1));
		$user_settings = array();

		if (isset($_COOKIE[$cookiename]) && empty($context['tfa_member']))
			$_COOKIE[$cookiename] = '';

		// Expire the 2FA cookie
		if (isset($_COOKIE[$cookiename . '_tfa']) && empty($context['tfa_member']))
		{
			$tfa_data = pmx_json_decode($_COOKIE[$cookiename . '_tfa'], true);

			if (is_null($tfa_data))
				$tfa_data = safe_unserialize($_COOKIE[$cookiename . '_tfa']);

			list ($id, $user, $exp, $state, $preserve) = $tfa_data;

			if (!$preserve || time() > $exp)
			{
				$_COOKIE[$cookiename . '_tfa'] = '';
				setTFACookie(-3600, 0, '');
			}
		}

		// Create a login token if it doesn't exist yet.
		if (!isset($_SESSION['token']['post-login']))
			createToken('login');
		else
			list ($context['login_token_var'],,, $context['login_token']) = $_SESSION['token']['post-login'];

		// Do we perhaps think this is a search robot? Check every five minutes just in case...
		if ((!empty($modSettings['spider_mode']) || !empty($modSettings['spider_group'])) && (!isset($_SESSION['robot_check']) || $_SESSION['robot_check'] < time() - 300) || isset($_COOKIE['spidertest']))
		{
			require_once($sourcedir . '/ManageSearchEngines.php');
			$user_info['possibly_robot'] = SpiderCheck();
		}
		elseif (!empty($modSettings['spider_mode']))
			$user_info['possibly_robot'] = isset($_SESSION['id_robot']) ? $_SESSION['id_robot'] : 0;

		// We don't know the offset...
		$user_info['time_offset'] = 0;
	}

	// Set up the $user_info array.
	$user_info += array(
		'id' => $id_member,
		'username' => $username,
		'name' => isset($user_settings['real_name']) ? $user_settings['real_name'] : '',
		'email' => isset($user_settings['email_address']) ? $user_settings['email_address'] : '',
		'passwd' => isset($user_settings['passwd']) ? $user_settings['passwd'] : '',
		'language' => empty($user_settings['lngfile']) ? $language : $user_settings['lngfile'],
		'is_guest' => $id_member == 0,
		'is_admin' => in_array(1, $user_info['groups']),
		'theme' => empty($user_settings['id_theme']) ? 0 : $user_settings['id_theme'],
		'last_login' => empty($user_settings['last_login']) ? 0 : $user_settings['last_login'],
		'ip' => $_SERVER['REMOTE_ADDR'],
		'ip2' => $_SERVER['BAN_CHECK_IP'],
		'posts' => empty($user_settings['posts']) ? 0 : $user_settings['posts'],
		'time_format' => $modSettings['time_format'],
		'tmdisplay' => !isset($user_settings['tmdisplay']) ? -1 : $user_settings['tmdisplay'],
		'gdpr_date' => !isset($user_settings['gdpr_date']) ? 0 : $user_settings['gdpr_date'],
		'avatar' => array(
			'url' => isset($user_settings['avatar']) ? $user_settings['avatar'] : '',
			'class' => isset($user_settings['avatar_class']) ? $user_settings['avatar_class'] : '',
			'filename' => empty($user_settings['filename']) ? '' : $user_settings['filename'],
			'custom_dir' => !empty($user_settings['attachment_type']) && $user_settings['attachment_type'] == 1,
			'id_attach' => isset($user_settings['id_attach']) ? $user_settings['id_attach'] : 0
		),
		'smiley_set' => isset($user_settings['smiley_set']) ? $user_settings['smiley_set'] : '',
		'messages' => empty($user_settings['instant_messages']) ? 0 : $user_settings['instant_messages'],
		'unread_messages' => empty($user_settings['unread_messages']) ? 0 : $user_settings['unread_messages'],
		'alerts' => empty($user_settings['alerts']) ? 0 : $user_settings['alerts'],
		'total_time_logged_in' => empty($user_settings['total_time_logged_in']) ? 0 : $user_settings['total_time_logged_in'],
		'buddies' => !empty($modSettings['enable_buddylist']) && !empty($user_settings['buddy_list']) ? explode(',', $user_settings['buddy_list']) : array(),
		'ignoreboards' => !empty($user_settings['ignore_boards']) && !empty($modSettings['allow_ignore_boards']) ? explode(',', $user_settings['ignore_boards']) : array(),
		'ignoreusers' => !empty($user_settings['pm_ignore_list']) ? explode(',', $user_settings['pm_ignore_list']) : array(),
		'warning' => isset($user_settings['warning']) ? $user_settings['warning'] : 0,
		'permissions' => array(),
	);
	$user_info['groups'] = array_unique($user_info['groups']);

	// Make sure that the last item in the ignore boards array is valid. If the list was too long it could have an ending comma that could cause problems.
	if (!empty($user_info['ignoreboards']) && empty($user_info['ignoreboards'][$tmp = count($user_info['ignoreboards']) - 1]))
		unset($user_info['ignoreboards'][$tmp]);

	// Allow the user to change their language.
	if (!empty($modSettings['userLanguage']) && !defined('is_sheduled_task'))
	{
		$languages = getLanguages();
		if(!empty($_POST['headlangsel']))
		{
			// Is it valid?
			if (isset($languages[$_POST['headlangsel']]))
			{
				$user_info['language'] = $_POST['headlangsel'];

				// Save only temporary, NOT permanent for memmbers.
				if (checkECL_Cookie())
					$_SESSION['language'] = $user_info['language'];
				else
					set_cookie('language', $user_info['language']);

				// Reload cached blocks
				if (!empty($modSettings['portal_enabled']))
					clearBlocksCache(null, true);

				// close open stats, so he need a reload
				if(isset($_SESSION['expanded_stats']))
					unset($_SESSION['expanded_stats']);
			}
		}
		else
		{
			if (isset($_SESSION['language']) && isset($languages[$_SESSION['language']]))
				$user_info['language'] = $_SESSION['language'];
			elseif (($cooklang = get_cookie('language')) !== null && isset($languages[$cooklang]))
				$user_info['language'] = $cooklang;
		}
	}

	// load the global Language Settings
	loadLanguage('LangSettings', $user_info['language']);
	$modSettings['time_format'] = $txt['default_time_format'];
	
	// Just build this here, it makes it easier to change/use - administrators can see all boards.
	if ($user_info['is_admin'])
		$user_info['query_see_board'] = '1=1';
	// Otherwise just the groups in $user_info['groups'].
	else
		$user_info['query_see_board'] = '((FIND_IN_SET(' . implode(', b.member_groups) != 0 OR FIND_IN_SET(', $user_info['groups']) . ', b.member_groups) != 0)' . (!empty($modSettings['deny_boards_access']) ? ' AND (FIND_IN_SET(' . implode(', b.deny_member_groups) = 0 AND FIND_IN_SET(', $user_info['groups']) . ', b.deny_member_groups) = 0)' : '') . (isset($user_info['mod_cache']) ? ' OR ' . $user_info['mod_cache']['mq'] : '') . ')';

	// Build the list of boards they WANT to see.
	// This will take the place of query_see_boards in certain spots, so it better include the boards they can see also

	// If they aren't ignoring any boards then they want to see all the boards they can see
	if (empty($user_info['ignoreboards']))
		$user_info['query_wanna_see_board'] = $user_info['query_see_board'];
	// Ok I guess they don't want to see all the boards
	else
		$user_info['query_wanna_see_board'] = '(' . $user_info['query_see_board'] . ' AND b.id_board NOT IN (' . implode(',', $user_info['ignoreboards']) . '))';

	call_integration_hook('integrate_user_info');
}

/**
 * Check for moderators and see if they have access to the board.
 * What it does:
 * - sets up the $board_info array for current board information.
 * - if cache is enabled, the $board_info array is stored in cache.
 * - redirects to appropriate post if only message id is requested.
 * - is only used when inside a topic or board.
 * - determines the local moderators for the board.
 * - adds group id 3 if the user is a local moderator for the board they are in.
 * - prevents access if user is not in proper group nor a local moderator of the board.
 */
function loadBoard()
{
	global $txt, $scripturl, $context, $modSettings;
	global $board_info, $board, $topic, $user_info, $pmxcFunc, $pmxCacheFunc;

	// Assume they are not a moderator.
	$user_info['is_mod'] = false;
	$context['user']['is_mod'] = &$user_info['is_mod'];

	// Start the linktree off empty..
	$context['linktree'] = array();

	// Have they by chance specified a message id but nothing else?
	if (empty($_REQUEST['action']) && empty($topic) && empty($board) && !empty($_REQUEST['msg']))
	{
		// Make sure the message id is really an int.
		$_REQUEST['msg'] = (int) $_REQUEST['msg'];

		// Looking through the message table can be slow, so try using the cache first.
		if (($topic = $pmxCacheFunc['get']('msg_topic-' . $_REQUEST['msg'])) === null)
		{
			$request = $pmxcFunc['db_query']('', '
				SELECT id_topic
				FROM {db_prefix}messages
				WHERE id_msg = {int:id_msg}
				LIMIT 1',
				array(
					'id_msg' => $_REQUEST['msg'],
				)
			);

			// So did it find anything?
			if ($pmxcFunc['db_num_rows']($request))
			{
				list ($topic) = $pmxcFunc['db_fetch_row']($request);
				$pmxcFunc['db_free_result']($request);
				// Save save save.
				$pmxCacheFunc['put']('msg_topic-' . $_REQUEST['msg'], $topic, 120);
			}
		}

		// Remember redirection is the key to avoiding fallout from your bosses.
		if (!empty($topic))
			redirectexit('topic=' . $topic . '.msg' . $_REQUEST['msg'] . '#msg' . $_REQUEST['msg']);
		else
		{
			loadPermissions();
			loadTheme();
			fatal_lang_error('topic_gone', false);
		}
	}

	// Load this board only if it is specified.
	if (empty($board) && empty($topic))
	{
		$board_info = array('moderators' => array(), 'moderator_groups' => array());
		return;
	}

	if (!empty($modSettings['cache_enable']) && (empty($topic) || $modSettings['cache_enable'] >= 3))
	{
		// @todo SLOW?
		if (!empty($topic))
			$temp = $pmxCacheFunc['get']('topic_board-' . $topic);
		else
			$temp = $pmxCacheFunc['get']('board-' . $board);

		if (!empty($temp))
		{
			$board_info = $temp;
			$board = $board_info['id'];
		}
	}

	if (empty($temp))
	{
		$request = $pmxcFunc['db_query']('', '
			SELECT
				c.id_cat, b.name AS bname, b.description, b.num_topics, b.member_groups, b.deny_member_groups,
				b.id_parent, c.name AS cname, COALESCE(mg.id_group, 0) AS id_moderator_group, mg.group_name,
				COALESCE(mem.id_member, 0) AS id_moderator,
				mem.real_name' . (!empty($topic) ? ', b.id_board' : '') . ', b.child_level,
				b.id_theme, b.override_theme, b.count_posts, b.id_profile, b.redirect,
				b.unapproved_topics, b.unapproved_posts' . (!empty($topic) ? ', t.approved, t.id_member_started' : '') . '
			FROM {db_prefix}boards AS b' . (!empty($topic) ? '
				INNER JOIN {db_prefix}topics AS t ON (t.id_topic = {int:current_topic})' : '') . '
				LEFT JOIN {db_prefix}categories AS c ON (c.id_cat = b.id_cat)
				LEFT JOIN {db_prefix}moderator_groups AS modgs ON (modgs.id_board = {raw:board_link})
				LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = modgs.id_group)
				LEFT JOIN {db_prefix}moderators AS mods ON (mods.id_board = {raw:board_link})
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = mods.id_member)
			WHERE b.id_board = {raw:board_link}',
			array(
				'current_topic' => $topic,
				'board_link' => empty($topic) ? $pmxcFunc['db_quote']('{int:current_board}', array('current_board' => $board)) : 't.id_board',
			)
		);
		// If there aren't any, skip.
		if ($pmxcFunc['db_num_rows']($request) > 0)
		{
			$row = $pmxcFunc['db_fetch_assoc']($request);

			// Set the current board.
			if (!empty($row['id_board']))
				$board = $row['id_board'];

			// Basic operating information. (globals... :/)
			$board_info = array(
				'id' => $board,
				'moderators' => array(),
				'moderator_groups' => array(),
				'cat' => array(
					'id' => $row['id_cat'],
					'name' => $row['cname']
				),
				'name' => $row['bname'],
				'description' => $row['description'],
				'num_topics' => $row['num_topics'],
				'unapproved_topics' => $row['unapproved_topics'],
				'unapproved_posts' => $row['unapproved_posts'],
				'unapproved_user_topics' => 0,
				'parent_boards' => getBoardParents($row['id_parent']),
				'parent' => $row['id_parent'],
				'child_level' => $row['child_level'],
				'theme' => $row['id_theme'],
				'override_theme' => !empty($row['override_theme']),
				'profile' => $row['id_profile'],
				'redirect' => $row['redirect'],
				'recycle' => !empty($modSettings['recycle_enable']) && !empty($modSettings['recycle_board']) && $modSettings['recycle_board'] == $board,
				'posts_count' => empty($row['count_posts']),
				'cur_topic_approved' => empty($topic) || $row['approved'],
				'cur_topic_starter' => empty($topic) ? 0 : $row['id_member_started'],
			);

			// Load the membergroups allowed, and check permissions.
			$board_info['groups'] = $row['member_groups'] == '' ? array() : explode(',', $row['member_groups']);
			$board_info['deny_groups'] = $row['deny_member_groups'] == '' ? array() : explode(',', $row['deny_member_groups']);

			do
			{
				if (!empty($row['id_moderator']))
					$board_info['moderators'][$row['id_moderator']] = array(
						'id' => $row['id_moderator'],
						'name' => $row['real_name'],
						'href' => $scripturl . '?action=profile;u=' . $row['id_moderator'],
						'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_moderator'] . '">' . $row['real_name'] . '</a>'
					);

				if (!empty($row['id_moderator_group']))
					$board_info['moderator_groups'][$row['id_moderator_group']] = array(
						'id' => $row['id_moderator_group'],
						'name' => $row['group_name'],
						'href' => $scripturl . '?action=groups;sa=members;group=' . $row['id_moderator_group'],
						'link' => '<a href="' . $scripturl . '?action=groups;sa=members;group=' . $row['id_moderator_group'] . '">' . $row['group_name'] . '</a>'
					);
			}
			while ($row = $pmxcFunc['db_fetch_assoc']($request));

			// If the board only contains unapproved posts and the user isn't an approver then they can't see any topics.
			// If that is the case do an additional check to see if they have any topics waiting to be approved.
			if ($board_info['num_topics'] == 0 && $modSettings['postmod_active'] && !allowedTo('approve_posts'))
			{
				// Free the previous result
				$pmxcFunc['db_free_result']($request);

				// @todo why is this using id_topic?
				// @todo Can this get cached?
				$request = $pmxcFunc['db_query']('', '
					SELECT COUNT(id_topic)
					FROM {db_prefix}topics
					WHERE id_member_started={int:id_member}
						AND approved = {int:unapproved}
						AND id_board = {int:board}',
					array(
						'id_member' => $user_info['id'],
						'unapproved' => 0,
						'board' => $board,
					)
				);

				list ($board_info['unapproved_user_topics']) = $pmxcFunc['db_fetch_row']($request);
			}

			if (!empty($modSettings['cache_enable']) && (empty($topic) || $modSettings['cache_enable'] >= 3))
			{
				// @todo SLOW?
				if (!empty($topic))
					$pmxCacheFunc['put']('topic_board-' . $topic, $board_info, 120);
				$pmxCacheFunc['put']('board-' . $board, $board_info, 120);
			}
		}
		else
		{
			// Otherwise the topic is invalid, there are no moderators, etc.
			$board_info = array(
				'moderators' => array(),
				'moderator_groups' => array(),
				'error' => 'exist'
			);
			$topic = null;
			$board = 0;
		}
		$pmxcFunc['db_free_result']($request);
	}

	if (!empty($topic))
		$_GET['board'] = (int) $board;

	if (!empty($board))
	{
		// Get this into an array of keys for array_intersect
		$moderator_groups = array_keys($board_info['moderator_groups']);

		// Now check if the user is a moderator.
		$user_info['is_mod'] = isset($board_info['moderators'][$user_info['id']]) || count(array_intersect($user_info['groups'], $moderator_groups)) != 0;

		if (count(array_intersect($user_info['groups'], $board_info['groups'])) == 0 && !$user_info['is_admin'])
			$board_info['error'] = 'access';
		if (!empty($modSettings['deny_boards_access']) && count(array_intersect($user_info['groups'], $board_info['deny_groups'])) != 0 && !$user_info['is_admin'])
			$board_info['error'] = 'access';

		// Build up the linktree for PortaMx
		$context['linktree'] = array_merge(
			$context['linktree'],
			array(array(
				'url' => $scripturl . (!empty($modSettings['portal_enabled']) && !empty($modSettings['pmx_frontmode']) ? '?action=community;' : '') .'#c' . $board_info['cat']['id'],
				'name' => $board_info['cat']['name']
			)),
			array_reverse($board_info['parent_boards']),
			array(array(
				'url' => $scripturl . '?board=' . $board . '.0',
				'name' => $board_info['name']
			))
		);
	}

	// Set the template contextual information.
	$context['user']['is_mod'] = &$user_info['is_mod'];
	$context['current_topic'] = $topic;
	$context['current_board'] = $board;

	// No posting in redirection boards!
	if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'post' && !empty($board_info['redirect']))
		$board_info['error'] == 'post_in_redirect';

	// Hacker... you can't see this topic, I'll tell you that. (but moderators can!)
	if (!empty($board_info['error']) && (!empty($modSettings['deny_boards_access']) || $board_info['error'] != 'access' || !$user_info['is_mod']))
	{
		// The permissions and theme need loading, just to make sure everything goes smoothly.
		loadPermissions();
		loadTheme();

		$_GET['board'] = '';
		$_GET['topic'] = '';

		// The linktree should not give the game away mate!
		$context['linktree'] = array(
			array(
				'url' => $scripturl,
				'name' => $txt['home']
			)
		);

		// If it's a prefetching agent or we're requesting an attachment.
		if ((isset($_SERVER['HTTP_X_MOZ']) && $_SERVER['HTTP_X_MOZ'] == 'prefetch') || (!empty($_REQUEST['action']) && $_REQUEST['action'] === 'dlattach'))
		{
			ob_end_clean();
			header('HTTP/1.1 403 Forbidden');
			die;
		}
		elseif ($board_info['error'] == 'post_in_redirect')
		{
			// Slightly different error message here...
			fatal_lang_error('cannot_post_redirect', false);
		}
		elseif ($user_info['is_guest'])
		{
			loadLanguage('Errors');
			is_not_guest($txt['topic_gone']);
		}
		else
			fatal_lang_error('topic_gone', false);
	}

	if ($user_info['is_mod'])
		$user_info['groups'][] = 3;
}

/**
 * Load this user's permissions.
 */
function loadPermissions()
{
	global $user_info, $board, $board_info, $modSettings, $pmxcFunc, $pmxCacheFunc, $sourcedir;

	if ($user_info['is_admin'])
	{
		banPermissions();
		return;
	}

	if (!empty($modSettings['cache_enable']))
	{
		$cache_groups = $user_info['groups'];
		asort($cache_groups);
		$cache_groups = implode(',', $cache_groups);
		// If it's a spider then cache it different.
		if ($user_info['possibly_robot'])
			$cache_groups .= '-spider';

		if ($modSettings['cache_enable'] >= 2 && !empty($board) && ($temp = $pmxCacheFunc['get']('permissions' . $cache_groups . '-' . $board)) != null && time() - 240 > $modSettings['settings_updated'])
		{
			list ($user_info['permissions']) = $temp;
			banPermissions();

			return;
		}
		elseif (($temp = $pmxCacheFunc['get']('permissions' . $cache_groups)) != null && time() - 240 > $modSettings['settings_updated'])
			list ($user_info['permissions'], $removals) = $temp;
	}

	// If it is detected as a robot, and we are restricting permissions as a special group - then implement this.
	$spider_restrict = $user_info['possibly_robot'] && !empty($modSettings['spider_group']) ? ' OR (id_group = {int:spider_group} AND add_deny = 0)' : '';

	if (empty($user_info['permissions']))
	{
		// Get the general permissions.
		$request = $pmxcFunc['db_query']('', '
			SELECT permission, add_deny
			FROM {db_prefix}permissions
			WHERE id_group IN ({array_int:member_groups})
				' . $spider_restrict,
			array(
				'member_groups' => $user_info['groups'],
				'spider_group' => !empty($modSettings['spider_group']) ? $modSettings['spider_group'] : 0,
			)
		);
		$removals = array();
		while ($row = $pmxcFunc['db_fetch_assoc']($request))
		{
			if (empty($row['add_deny']))
				$removals[] = $row['permission'];
			else
				$user_info['permissions'][] = $row['permission'];
		}
		$pmxcFunc['db_free_result']($request);

		if (isset($cache_groups))
			$pmxCacheFunc['put']('permissions' . $cache_groups, array($user_info['permissions'], $removals), 240);
	}

	// Get the board permissions.
	if (!empty($board))
	{
		// Make sure the board (if any) has been loaded by loadBoard().
		if (!isset($board_info['profile']))
			fatal_lang_error('no_board');

		$request = $pmxcFunc['db_query']('', '
			SELECT permission, add_deny
			FROM {db_prefix}board_permissions
			WHERE (id_group IN ({array_int:member_groups})
				' . $spider_restrict . ')
				AND id_profile = {int:id_profile}',
			array(
				'member_groups' => $user_info['groups'],
				'id_profile' => $board_info['profile'],
				'spider_group' => !empty($modSettings['spider_group']) ? $modSettings['spider_group'] : 0,
			)
		);
		while ($row = $pmxcFunc['db_fetch_assoc']($request))
		{
			if (empty($row['add_deny']))
				$removals[] = $row['permission'];
			else
				$user_info['permissions'][] = $row['permission'];
		}
		$pmxcFunc['db_free_result']($request);
	}

	// Remove all the permissions they shouldn't have ;).
	if (!empty($modSettings['permission_enable_deny']))
		$user_info['permissions'] = array_diff($user_info['permissions'], $removals);

	if (isset($cache_groups) && !empty($board) && $modSettings['cache_enable'] >= 2)
		$pmxCacheFunc['put']('permissions' . $cache_groups . '-' . $board, array($user_info['permissions'], null), 240);

	// Banned?  Watch, don't touch..
	banPermissions();

	// Load the mod cache so we can know what additional boards they should see, but no sense in doing it for guests
	if (!$user_info['is_guest'])
	{
		if (!isset($_SESSION['mc']) || $_SESSION['mc']['time'] <= $modSettings['settings_updated'])
		{
			require_once($sourcedir . '/Subs-Auth.php');
			rebuildModCache();
		}
		else
			$user_info['mod_cache'] = $_SESSION['mc'];

		// This is a useful phantom permission added to the current user, and only the current user while they are logged in.
		// For example this drastically simplifies certain changes to the profile area.
		$user_info['permissions'][] = 'is_not_guest';
		// And now some backwards compatibility stuff for mods and whatnot that aren't expecting the new permissions.
		$user_info['permissions'][] = 'profile_view_own';
		if (in_array('profile_view', $user_info['permissions']))
			$user_info['permissions'][] = 'profile_view_any';
	}
}

/**
 * Loads an array of users' data by ID or member_name.
 *
 * @param array|string $users An array of users by id or name or a single username/id
 * @param bool $is_name Whether $users contains names
 * @param string $set What kind of data to load (normal, profile, minimal)
 * @return array The ids of the members loaded
 */
function loadMemberData($users, $is_name = false, $set = 'normal')
{
	global $user_profile, $modSettings, $board_info, $pmxcFunc, $pmxCacheFunc, $context;
	global $image_proxy_enabled, $image_proxy_secret, $boardurl;

	// Can't just look for no users :P.
	if (empty($users))
		return array();

	// Pass the set value
	$context['loadMemberContext_set'] = $set;

	// Make sure it's an array.
	$users = !is_array($users) ? array($users) : array_unique($users);
	$loaded_ids = array();

	if (!$is_name && !empty($modSettings['cache_enable']) && $modSettings['cache_enable'] >= 3)
	{
		$users = array_values($users);
		for ($i = 0, $n = count($users); $i < $n; $i++)
		{
			$data = $pmxCacheFunc['get']('member_data-' . $set . '-' . $users[$i]);
			if ($data == null)
				continue;

			$loaded_ids[] = $data['id_member'];
			$user_profile[$data['id_member']] = $data;
			unset($users[$i]);
		}
	}

	// Used by default
	$select_columns = '
			COALESCE(lo.log_time, 0) AS is_online, COALESCE(a.id_attach, 0) AS id_attach, a.filename, a.attachment_type,
			mem.signature, mem.personal_text, mem.avatar, mem.id_member, mem.member_name,
			mem.real_name, mem.email_address, mem.date_registered, mem.website_title, mem.website_url, mem.tmdisplay,
			mem.birthdate, mem.member_ip, mem.member_ip2, mem.posts, mem.last_login, mem.id_post_group, mem.lngfile, mem.id_group, mem.time_offset, mem.show_online,
			mg.online_color AS member_group_color, COALESCE(mg.group_name, {string:blank_string}) AS member_group,
			pg.online_color AS post_group_color, COALESCE(pg.group_name, {string:blank_string}) AS post_group,
			mem.is_activated, mem.warning, ' . (!empty($modSettings['titlesEnable']) ? 'mem.usertitle, ' : '') . '
			CASE WHEN mem.id_group = 0 OR mg.icons = {string:blank_string} THEN pg.icons ELSE mg.icons END AS icons';
	$select_tables = '
			LEFT JOIN {db_prefix}log_online AS lo ON (lo.id_member = mem.id_member)
			LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = mem.id_member)
			LEFT JOIN {db_prefix}membergroups AS pg ON (pg.id_group = mem.id_post_group)
			LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = mem.id_group)';

	// We add or replace according the the set
	switch ($set)
	{
		case 'normal':
			$select_columns .= ', mem.buddy_list,  mem.additional_groups, mem.location, mem.gender';
		break;
		case 'profile':
			$select_columns .= ', mem.additional_groups, mem.id_theme, mem.pm_ignore_list, mem.pm_receive_from,
			mem.timezone, mem.secret_question, mem.smiley_set, mem.tfa_secret, mem.location, mem.gender,
			mem.total_time_logged_in, lo.url, mem.ignore_boards, mem.password_salt, mem.pm_prefs, mem.buddy_list, mem.alerts';
		break;
		case 'minimal':
			$select_columns = '
			mem.id_member, mem.member_name, mem.real_name, mem.email_address, mem.date_registered,
			mem.posts, mem.last_login, mem.member_ip, mem.member_ip2, mem.lngfile, mem.id_group, mem.location, mem.gender';
			$select_tables = '';
		break;
		default:
			trigger_error('loadMemberData(): Invalid member data set \'' . $set . '\'', E_USER_WARNING);
	}

	// Allow mods to easily add to the selected member data
	call_integration_hook('integrate_load_member_data', array(&$select_columns, &$select_tables, &$set));

	if (!empty($users))
	{
		// Load the member's data.
		$new_loaded_ids = array();

		$request = $pmxcFunc['db_query']('', '
			SELECT' . $select_columns . '
			FROM {db_prefix}members AS mem' . $select_tables . '
			WHERE mem.' . ($is_name ? 'member_name' : 'id_member') . ' IN ({' . ($is_name ? 'array_string' : 'array_int') . ':users})',
			array(
				'blank_string' => '',
				'users' => $users,
			)
		);
		while ($row = $pmxcFunc['db_fetch_assoc']($request))
		{
			// Take care of proxying avatar if required, do this here for maximum reach
			if ($image_proxy_enabled && !empty($row['avatar']) && stripos($row['avatar'], 'http://') !== false)
				$row['avatar'] = $boardurl . '/proxy.php?request=' . urlencode($row['avatar']) . '&hash=' . md5($row['avatar'] . $image_proxy_secret);

			if ( isset($row['member_ip']) )
				$row['member_ip'] = inet_dtop($row['member_ip']);
			if ( isset($row['member_ip2']) )
				$row['member_ip2'] = inet_dtop($row['member_ip2']);
			$new_loaded_ids[] = $row['id_member'];
			$loaded_ids[] = $row['id_member'];
			$row['options'] = array();
			$user_profile[$row['id_member']] = $row;
		}
		$pmxcFunc['db_free_result']($request);
	}

	if (!empty($new_loaded_ids) && $set !== 'minimal')
	{
		$request = $pmxcFunc['db_query']('', '
			SELECT *
			FROM {db_prefix}themes
			WHERE id_member IN ({array_int:loaded_ids})',
			array(
				'loaded_ids' => $new_loaded_ids,
			)
		);
		while ($row = $pmxcFunc['db_fetch_assoc']($request))
			$user_profile[$row['id_member']]['options'][$row['variable']] = $row['value'];
		$pmxcFunc['db_free_result']($request);
	}

	$additional_mods = array();

	// Are any of these users in groups assigned to moderate this board?
	if (!empty($loaded_ids) && !empty($board_info['moderator_groups']) && $set === 'normal')
	{
		foreach ($loaded_ids as $a_member)
		{
			if (!empty($user_profile[$a_member]['additional_groups']))
				$groups = array_merge(array($user_profile[$a_member]['id_group']), explode(',', $user_profile[$a_member]['additional_groups']));
			else
				$groups = array($user_profile[$a_member]['id_group']);

			$temp = array_intersect($groups, array_keys($board_info['moderator_groups']));

			if (!empty($temp))
			{
				$additional_mods[] = $a_member;
			}
		}
	}

	if (!empty($new_loaded_ids) && !empty($modSettings['cache_enable']) && $modSettings['cache_enable'] >= 3)
	{
		for ($i = 0, $n = count($new_loaded_ids); $i < $n; $i++)
			$pmxCacheFunc['put']('member_data-' . $set . '-' . $new_loaded_ids[$i], $user_profile[$new_loaded_ids[$i]], 240);
	}

	// Are we loading any moderators?  If so, fix their group data...
	if (!empty($loaded_ids) && (!empty($board_info['moderators']) || !empty($board_info['moderator_groups'])) && $set === 'normal' && count($temp_mods = array_merge(array_intersect($loaded_ids, array_keys($board_info['moderators'])), $additional_mods)) !== 0)
	{
		if (($row = $pmxCacheFunc['get']('moderator_group_info')) == null)
		{
			$request = $pmxcFunc['db_query']('', '
				SELECT group_name AS member_group, online_color AS member_group_color, icons
				FROM {db_prefix}membergroups
				WHERE id_group = {int:moderator_group}
				LIMIT 1',
				array(
					'moderator_group' => 3,
				)
			);
			$row = $pmxcFunc['db_fetch_assoc']($request);
			$pmxcFunc['db_free_result']($request);

			$pmxCacheFunc['put']('moderator_group_info', $row, 480);
		}

		foreach ($temp_mods as $id)
		{
			// By popular demand, don't show admins or global moderators as moderators.
			if ($user_profile[$id]['id_group'] != 1 && $user_profile[$id]['id_group'] != 2)
				$user_profile[$id]['member_group'] = $row['member_group'];

			// If the Moderator group has no color or icons, but their group does... don't overwrite.
			if (!empty($row['icons']))
				$user_profile[$id]['icons'] = $row['icons'];
			if (!empty($row['member_group_color']))
				$user_profile[$id]['member_group_color'] = $row['member_group_color'];
		}
	}

	return $loaded_ids;
}

/**
 * Loads the user's basic values... meant for template/theme usage.
 *
 * @param int $user The ID of a user previously loaded by {@link loadMemberData()}
 * @param bool $display_custom_fields Whether or not to display custom profile fields
 * @return boolean Whether or not the data was loaded successfully
 */
function loadMemberContext($user, $display_custom_fields = false)
{
	global $memberContext, $user_profile, $txt, $scripturl, $user_info;
	global $context, $modSettings, $settings, $pmxcFunc;
	static $dataLoaded = array();

	// If this person's data is already loaded, skip it.
	if (isset($dataLoaded[$user]))
		return true;

	// We can't load guests or members not loaded by loadMemberData()!
	if ($user == 0)
		return false;
	if (!isset($user_profile[$user]))
	{
		trigger_error('loadMemberContext(): member id ' . $user . ' not previously loaded by loadMemberData()', E_USER_WARNING);
		return false;
	}

	// Well, it's loaded now anyhow.
	$dataLoaded[$user] = true;
	$profile = $user_profile[$user];

	// Censor everything.
	censorText($profile['signature']);
	censorText($profile['personal_text']);

	// Set things up to be used before hand.
	$context['lbimage_data'] = null;
	$profile['signature'] = str_replace(array("\n", "\r"), array('<br>', ''), $profile['signature']);
	$profile['signature'] = parse_bbc($profile['signature'], true, 'sig' . $profile['id_member']);

	$profile['is_online'] = (!empty($profile['show_online']) || allowedTo('moderate_forum')) && $profile['is_online'] > 0;
	$profile['icons'] = empty($profile['icons']) ? array('', '') : explode('#', $profile['icons']);
	// Setup the buddy status here (One whole in_array call saved :P)
	$profile['buddy'] = in_array($profile['id_member'], $user_info['buddies']);
	$buddy_list = !empty($profile['buddy_list']) ? explode(',', $profile['buddy_list']) : array();

	//We need a little fallback for the membergroup icons. If it doesn't exist in the current theme, fallback to default theme
	if (isset($profile['icons'][1]) && file_exists($settings['actual_theme_dir'] . '/images/membericons/' . $profile['icons'][1])) //icon is set and exists
		$group_icon_url = $settings['images_url'] . '/membericons/' . $profile['icons'][1];
	elseif (isset($profile['icons'][1])) //icon is set and doesn't exist, fallback to default
		$group_icon_url = $settings['default_images_url'] . '/membericons/' . $profile['icons'][1];
	else //not set, bye bye
		$group_icon_url = '';

	// These minimal values are always loaded
	$memberContext[$user] = array(
		'username' => $profile['member_name'],
		'name' => $profile['real_name'],
		'id' => $profile['id_member'],
		'href' => $scripturl . '?action=profile;u=' . $profile['id_member'],
		'link' => '<a href="' . $scripturl . '?action=profile;u=' . $profile['id_member'] . '" title="' . $txt['profile_of'] . ' ' . $profile['real_name'] . '" '. (!empty($modSettings['onlineEnable']) ? 'class="pm_icon"' : '').'>' . $profile['real_name'] . '</a>',
		'email' => $profile['email_address'],
		'show_email' => !$user_info['is_guest'] && ($user_info['id'] == $profile['id_member'] || allowedTo('moderate_forum')),
		'registered' => empty($profile['date_registered']) ? $txt['not_applicable'] : timeformat($profile['date_registered']),
		'registered_timestamp' => empty($profile['date_registered']) ? 0 : forum_time(true, $profile['date_registered']),
		'location' => $profile['location'],
		'gender' => $profile['gender'],
		'tmdisplay' => $profile['tmdisplay'],
	);

	// If the set isn't minimal then load the monstrous array.
	if ($context['loadMemberContext_set'] != 'minimal')
		$memberContext[$user] += array(
			'username_color' => '<span '. (!empty($profile['member_group_color']) ? 'style="color:'. $profile['member_group_color'] .';"' : '') .'>'. $profile['member_name'] .'</span>',
			'name_color' => '<span '. (!empty($profile['member_group_color']) ? 'style="color:'. $profile['member_group_color'] .';"' : '') .'>'. $profile['real_name'] .'</span>',
			'link_color' => '<a href="' . $scripturl . '?action=profile;u=' . $profile['id_member'] . '" title="' . $txt['profile_of'] . ' ' . $profile['real_name'] . '" '. (!empty($profile['member_group_color']) ? 'style="color:'. $profile['member_group_color'] .';"' : '') .'>' . $profile['real_name'] . '</a>',
			'is_buddy' => $profile['buddy'],
			'is_reverse_buddy' => in_array($user_info['id'], $buddy_list),
			'buddies' => $buddy_list,
			'title' => !empty($modSettings['titlesEnable']) ? $profile['usertitle'] : '',
			'blurb' => $profile['personal_text'],
			'website' => array(
				'title' => $profile['website_title'],
				'url' => $profile['website_url'],
			),
			'birth_date' => empty($profile['birthdate']) || $profile['birthdate'] === '0001-01-01' ? '0000-00-00' : (substr($profile['birthdate'], 0, 4) === '0004' ? '0000' . substr($profile['birthdate'], 4) : $profile['birthdate']),
			'signature' => $profile['signature'],
			'real_posts' => $profile['posts'],
			'posts' => $profile['posts'] > 500000 ? $txt['geek'] : comma_format($profile['posts']),
			'last_login' => empty($profile['last_login']) ? $txt['never'] : timeformat($profile['last_login']),
			'last_login_timestamp' => empty($profile['last_login']) ? 0 : forum_time(0, $profile['last_login']),
			'ip' => $pmxcFunc['htmlspecialchars']($profile['member_ip']),
			'ip2' => $pmxcFunc['htmlspecialchars']($profile['member_ip2']),
			'online' => array(
				'is_online' => $profile['is_online'],
				'text' => $pmxcFunc['htmlspecialchars']($txt[$profile['is_online'] ? 'online' : 'offline']),
				'member_online_text' => sprintf($txt[$profile['is_online'] ? 'member_is_online' : 'member_is_offline'], $pmxcFunc['htmlspecialchars']($profile['real_name'])),
				'href' => $scripturl . '?action=pm;sa=send;u=' . $profile['id_member'],
				'link' => '<a href="' . $scripturl . '?action=pm;sa=send;u=' . $profile['id_member'] . '">' . $txt[$profile['is_online'] ? 'online' : 'offline'] . '</a>',
				'label' => $txt[$profile['is_online'] ? 'online' : 'offline']
			),
			'language' => $pmxcFunc['ucwords'](strtr($profile['lngfile'], array('_' => ' ', '-utf8' => ''))),
			'is_activated' => isset($profile['is_activated']) ? $profile['is_activated'] : 1,
			'is_banned' => isset($profile['is_activated']) ? $profile['is_activated'] >= 10 : 0,
			'options' => $profile['options'],
			'is_guest' => false,
			'group' => $profile['member_group'],
			'group_color' => $profile['member_group_color'],
			'group_id' => $profile['id_group'],
			'post_group' => $profile['post_group'],
			'post_group_color' => $profile['post_group_color'],
			'group_icons' => str_repeat('<img src="' . str_replace('$language', $context['user']['language'], isset($profile['icons'][1]) ? $group_icon_url : '') . '" alt="*">', empty($profile['icons'][0]) || empty($profile['icons'][1]) ? 0 : $profile['icons'][0]),
			'warning' => $profile['warning'],
			'warning_status' => !empty($modSettings['warning_mute']) && $modSettings['warning_mute'] <= $profile['warning'] ? 'mute' : (!empty($modSettings['warning_moderate']) && $modSettings['warning_moderate'] <= $profile['warning'] ? 'moderate' : (!empty($modSettings['warning_watch']) && $modSettings['warning_watch'] <= $profile['warning'] ? 'watch' : (''))),
			'local_time' => timeformat(time() + ($profile['time_offset'] - $user_info['time_offset']) * 3600, false),
			'custom_fields' => array(),
		);

	// If the set isn't minimal then load their avatar as well.
	if ($context['loadMemberContext_set'] != 'minimal')
	{
		$class = '';
		if (!empty($modSettings['gravatarOverride']) || (!empty($modSettings['gravatarEnabled']) && stristr($profile['avatar'], 'gravatar://')))
		{
			if (!empty($modSettings['gravatarAllowExtraEmail']) && stristr($profile['avatar'], 'gravatar://') && strlen($profile['avatar']) > 11)
				$image = get_gravatar_url($pmxcFunc['substr']($profile['avatar'], 11));
			else
				$image = get_gravatar_url($profile['email_address']);
		}
		else
		{
			// So it's stored in the member table?
			if (!empty($profile['avatar']))
			{
				$image = (stristr($profile['avatar'], 'http://') || stristr($profile['avatar'], 'https://')) ? $profile['avatar'] : $modSettings['avatar_url'] . '/' . $profile['avatar'];
			}
			elseif (!empty($profile['filename']))
				$image = $modSettings['custom_avatar_url'] . '/' . $profile['filename'];
			// Right... no avatar...use the default one
			else
			{
				if(preg_match('~[a-zA-Z]~', $profile['member_name'], $tmp) > 0)
					$image = $modSettings['avatar_url'] .'/'. strtolower($tmp[0]) .'.png';
				else
					$image = $modSettings['avatar_url'] . '/default.png';
				$class = 'default';
			}
		}

		if (!empty($image))
			$memberContext[$user]['avatar'] = array(
				'name' => $profile['avatar'],
				'image' => '<img class="avatar" src="' . $image . '" alt="avatar_'. $profile['member_name'].'" oncontextmenu="return false">',
				'href' => $image,
				'url' => $image,
				'class' => $class
			);
	}

	// Are we also loading the members custom fields into context?
	if ($display_custom_fields && !empty($modSettings['displayFields']))
	{
		$memberContext[$user]['custom_fields'] = array();
		if (!isset($context['display_fields']))
			$context['display_fields'] = pmx_json_decode($modSettings['displayFields'], true);

		if(is_array($context['display_fields']))
		{
			foreach ($context['display_fields'] as $custom)
			{
				if (!isset($custom['col_name']) || trim($custom['col_name']) == '' || empty($profile['options'][$custom['col_name']]))
					continue;

				$value = $profile['options'][$custom['col_name']];

				// Don't show the "disabled" option for the "gender" field.
				if ($custom['col_name'] == 'cust_gender' && $value == 'Disabled')
					continue;

				// BBC?
				if ($custom['bbc'])
				{
					$context['lbimage_data'] = null;
					$value = parse_bbc($value);
				}
				// ... or checkbox?
				elseif (isset($custom['type']) && $custom['type'] == 'check')
					$value = $value ? $txt['yes'] : $txt['no'];

				// Enclosing the user input within some other text?
				if (!empty($custom['enclose']))
					$value = strtr($custom['enclose'], array(
						'{SCRIPTURL}' => $scripturl,
						'{IMAGES_URL}' => $settings['images_url'],
						'{DEFAULT_IMAGES_URL}' => $settings['default_images_url'],
						'{INPUT}' => $value,
					));

				$memberContext[$user]['custom_fields'][] = array(
					'title' => !empty($custom['title']) ? $custom['title'] : $custom['col_name'],
					'col_name' => $custom['col_name'],
					'value' => un_htmlspecialchars($value),
					'placement' => !empty($custom['placement']) ? $custom['placement'] : 0,
				);
			}
		}
	}

	call_integration_hook('integrate_member_context', array(&$memberContext[$user], $user, $display_custom_fields));
	return true;
}

/**
 * Loads information about what browser the user is viewing with and places it in $context
 *  - uses the class from {@link Class-BrowserDetect.php}
 */
function detectBrowser()
{
	// Load the current user's browser of choice
	$detector = new browser_detector;
	$detector->detectBrowser();
}

/**
 * Are we using this browser?
 *
 * Wrapper function for detectBrowser
 * @param string $browser The browser we are checking for.
 * @return bool Whether or not the current browser is what we're looking for
*/
function isBrowser($browser)
{
	global $context;

	// Don't know any browser!
	if (empty($context['browser']))
		detectBrowser();

	return !empty($context['browser'][$browser]) || !empty($context['browser']['is_' . $browser]) ? true : false;
}

/**
 * Load a theme, by ID.
 *
 * @param int $id_theme The ID of the theme to load
 * @param bool $initialize Whether or not to initialize a bunch of theme-related variables/settings
 */
function loadTheme($id_theme = 0, $initialize = true)
{
	global $user_info, $user_settings, $board_info, $boarddir, $maintenance;
	global $txt, $boardurl, $scripturl, $mbname, $modSettings, $cookiename, $cache_enable;
	global $context, $settings, $options, $sourcedir, $ssi_theme, $pmxcFunc, $pmxCacheFunc, $language, $board, $image_proxy_enabled;

	// The theme was specified by parameter.
	if (!empty($id_theme))
		$id_theme = (int) $id_theme;
	// The theme was specified by REQUEST.
	elseif (!empty($_REQUEST['theme']) && (!empty($modSettings['theme_allow']) || allowedTo('admin_forum')))
	{
		$id_theme = (int) $_REQUEST['theme'];
		$_SESSION['id_theme'] = $id_theme;
	}
	// The theme was specified by REQUEST... previously.
	elseif (!empty($_SESSION['id_theme']) && (!empty($modSettings['theme_allow']) || allowedTo('admin_forum')))
		$id_theme = (int) $_SESSION['id_theme'];
	// The theme is just the user's choice. (might use ?board=1;theme=0 to force board theme.)
	elseif (!empty($user_info['theme']) && !isset($_REQUEST['theme']))
		$id_theme = $user_info['theme'];
	// The theme was specified by the board.
	elseif (!empty($board_info['theme']))
		$id_theme = $board_info['theme'];
	// The theme is the forum's default.
	else
		$id_theme = $modSettings['theme_guests'];

	// Verify the id_theme... no foul play.
	// Always allow the board specific theme, if they are overriding.
	if (!empty($board_info['theme']) && $board_info['override_theme'])
		$id_theme = $board_info['theme'];
	// If they have specified a particular theme to use with SSI allow it to be used.
	elseif (!empty($ssi_theme) && $id_theme == $ssi_theme)
		$id_theme = (int) $id_theme;
	elseif (!empty($modSettings['enableThemes']) && !allowedTo('admin_forum'))
	{
		$themes = explode(',', $modSettings['enableThemes']);
		if (!in_array($id_theme, $themes))
			$id_theme = $modSettings['theme_guests'];
		else
			$id_theme = (int) $id_theme;
	}
	else
		$id_theme = (int) $id_theme;

	$member = empty($user_info['id']) ? -1 : $user_info['id'];

	// Disable image proxy if we don't have SSL enabled
	if (empty($modSettings['force_ssl']) || $modSettings['force_ssl'] < 2)
		$image_proxy_enabled = false;

	if (!empty($modSettings['cache_enable']) && $modSettings['cache_enable'] >= 2 && ($temp = $pmxCacheFunc['get']('theme_settings-' . $id_theme . '-' . $member)) != null && time() - 60 > $modSettings['settings_updated'])
	{
		$themeData = $temp;
		$flag = true;
	}
	elseif (($temp = $pmxCacheFunc['get']('theme_settings-' . $id_theme)) != null && time() - 60 > $modSettings['settings_updated'])
		$themeData = $temp + array($member => array());
	else
		$themeData = array(-1 => array(), 0 => array(), $member => array());

	if (empty($flag))
	{
		// Load variables from the current or default theme, global or this user's.
		$result = $pmxcFunc['db_query']('', '
			SELECT variable, value, id_member, id_theme
			FROM {db_prefix}themes
			WHERE id_member' . (empty($themeData[0]) ? ' IN (-1, 0, {int:id_member})' : ' = {int:id_member}') . '
				AND id_theme' . ($id_theme == 1 ? ' = {int:id_theme}' : ' IN ({int:id_theme}, 1)'),
			array(
				'id_theme' => $id_theme,
				'id_member' => $member,
			)
		);
		// Pick between $settings and $options depending on whose data it is.
		while ($row = $pmxcFunc['db_fetch_assoc']($result))
		{
			// There are just things we shouldn't be able to change as members.
			if ($row['id_member'] != 0 && in_array($row['variable'], array('actual_theme_url', 'actual_images_url', 'base_theme_dir', 'base_theme_url', 'default_images_url', 'default_theme_dir', 'default_theme_url', 'default_template', 'images_url', 'number_recent_posts', 'smiley_sets_default', 'theme_dir', 'theme_id', 'theme_layers', 'theme_templates', 'theme_url')))
				continue;

			// If this is the theme_dir of the default theme, store it.
			if (in_array($row['variable'], array('theme_dir', 'theme_url', 'images_url')) && $row['id_theme'] == '1' && empty($row['id_member']))
				$themeData[0]['default_' . $row['variable']] = $row['value'];

			// If this isn't set yet, is a theme option, or is not the default theme..
			if (!isset($themeData[$row['id_member']][$row['variable']]) || $row['id_theme'] != '1')
				$themeData[$row['id_member']][$row['variable']] = substr($row['variable'], 0, 5) == 'show_' ? $row['value'] == '1' : $row['value'];
		}
		$pmxcFunc['db_free_result']($result);

		if (!empty($themeData[-1]))
			foreach ($themeData[-1] as $k => $v)
			{
				if (!isset($themeData[$member][$k]))
					$themeData[$member][$k] = $v;
			}

		if (!empty($modSettings['cache_enable']) && $modSettings['cache_enable'] >= 2)
			$pmxCacheFunc['put']('theme_settings-' . $id_theme . '-' . $member, $themeData, 60);
		// Only if we didn't already load that part of the cache...
		elseif (!isset($temp))
			$pmxCacheFunc['put']('theme_settings-' . $id_theme, array(-1 => $themeData[-1], 0 => $themeData[0]), 90);
	}

	$settings = $themeData[0];
	$options = $themeData[$member];

	$settings['theme_id'] = $id_theme;

	$settings['actual_theme_url'] = $settings['theme_url'];
	$settings['actual_images_url'] = $settings['images_url'];
	$settings['actual_theme_dir'] = $settings['theme_dir'];

	$settings['template_dirs'] = array();
	// This theme first.
	$settings['template_dirs'][] = $settings['theme_dir'];

	// Based on theme (if there is one).
	if (!empty($settings['base_theme_dir']))
		$settings['template_dirs'][] = $settings['base_theme_dir'];

	// Lastly the default theme.
	if ($settings['theme_dir'] != $settings['default_theme_dir'])
		$settings['template_dirs'][] = $settings['default_theme_dir'];

	if (!$initialize)
		return;

	// Check to see if we're forcing SSL
	if (!empty($modSettings['force_ssl']) && $modSettings['force_ssl'] == 2 && empty($maintenance) &&
		(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off') && PMX != 'SSI')
		redirectexit(strtr($_SERVER['REQUEST_URL'], array('http://' => 'https://')));

	// Check to see if they're accessing it from the wrong place.
	if (isset($_SERVER['HTTP_HOST']) || isset($_SERVER['SERVER_NAME']))
	{
		$detected_url = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ? 'https://' : 'http://';
		$detected_url .= empty($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] . (empty($_SERVER['SERVER_PORT']) || $_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT']) : $_SERVER['HTTP_HOST'];
		$temp = preg_replace('~/' . basename($scripturl) . '(/.+)?$~', '', strtr(dirname($_SERVER['PHP_SELF']), '\\', '/'));
		if ($temp != '/')
			$detected_url .= $temp;
	}
	if (isset($detected_url) && $detected_url != $boardurl)
	{
		// Try #1 - check if it's in a list of alias addresses.
		if (!empty($modSettings['forum_alias_urls']))
		{
			$aliases = explode(',', $modSettings['forum_alias_urls']);

			foreach ($aliases as $alias)
			{
				// Rip off all the boring parts, spaces, etc.
				if ($detected_url == trim($alias) || strtr($detected_url, array('http://' => '', 'https://' => '')) == trim($alias))
					$do_fix = true;
			}
		}

		// Hmm... check #2 - is it just different by a www?  Send them to the correct place!!
		if (empty($do_fix) && strtr($detected_url, array('://' => '://www.')) == $boardurl && (empty($_GET) || count($_GET) == 1) && PMX != 'SSI')
		{
			// Okay, this seems weird, but we don't want an endless loop - this will make $_GET not empty ;).
			if (empty($_GET))
				redirectexit('wwwRedirect');
			else
			{
				list ($k, $v) = pmx_each($_GET);

				if ($k != 'wwwRedirect')
					redirectexit('wwwRedirect;' . $k . '=' . $v);
			}
		}

		// #3 is just a check for SSL...
		if (strtr($detected_url, array('https://' => 'http://')) == $boardurl)
			$do_fix = true;

		// Okay, #4 - perhaps it's an IP address?  We're gonna want to use that one, then. (assuming it's the IP or something...)
		if (!empty($do_fix) || preg_match('~^http[s]?://(?:[\d\.:]+|\[[\d:]+\](?::\d+)?)(?:$|/)~', $detected_url) == 1)
		{
			// Caching is good ;).
			$oldurl = $boardurl;

			// Fix $boardurl and $scripturl.
			$boardurl = $detected_url;
			$scripturl = strtr($scripturl, array($oldurl => $boardurl));
			$_SERVER['REQUEST_URL'] = strtr($_SERVER['REQUEST_URL'], array($oldurl => $boardurl));

			// Fix the theme urls...
			$settings['theme_url'] = strtr($settings['theme_url'], array($oldurl => $boardurl));
			$settings['default_theme_url'] = strtr($settings['default_theme_url'], array($oldurl => $boardurl));
			$settings['actual_theme_url'] = strtr($settings['actual_theme_url'], array($oldurl => $boardurl));
			$settings['images_url'] = strtr($settings['images_url'], array($oldurl => $boardurl));
			$settings['default_images_url'] = strtr($settings['default_images_url'], array($oldurl => $boardurl));
			$settings['actual_images_url'] = strtr($settings['actual_images_url'], array($oldurl => $boardurl));

			// And just a few mod settings :).
			$modSettings['smileys_url'] = strtr($modSettings['smileys_url'], array($oldurl => $boardurl));
			$modSettings['avatar_url'] = strtr($modSettings['avatar_url'], array($oldurl => $boardurl));

			// Clean up after loadBoard().
			if (isset($board_info['moderators']))
			{
				foreach ($board_info['moderators'] as $k => $dummy)
				{
					$board_info['moderators'][$k]['href'] = strtr($dummy['href'], array($oldurl => $boardurl));
					$board_info['moderators'][$k]['link'] = strtr($dummy['link'], array('"' . $oldurl => '"' . $boardurl));
				}
			}
			foreach ($context['linktree'] as $k => $dummy)
				$context['linktree'][$k]['url'] = strtr($dummy['url'], array($oldurl => $boardurl));
		}
	}
	// Set up the contextual user array.
	if (!empty($user_info))
	{
		$context['user'] = array(
			'id' => $user_info['id'],
			'is_logged' => !$user_info['is_guest'],
			'is_guest' => &$user_info['is_guest'],
			'is_admin' => &$user_info['is_admin'],
			'is_mod' => &$user_info['is_mod'],
			// A user can mod if they have permission to see the mod center, or they are a board/group/approval moderator.
			'can_mod' => allowedTo('access_mod_center') || (!$user_info['is_guest'] && ($user_info['mod_cache']['gq'] != '0=1' || $user_info['mod_cache']['bq'] != '0=1' || ($modSettings['postmod_active'] && !empty($user_info['mod_cache']['ap'])))),
			'username' => $user_info['username'],
			'language' => $user_info['language'],
			'email' => $user_info['email'],
			'ignoreusers' => $user_info['ignoreusers'],
		);
		if (!$context['user']['is_guest'])
			$context['user']['name'] = $user_info['name'];
		elseif ($context['user']['is_guest'] && !empty($txt['guest_title']))
			$context['user']['name'] = $txt['guest_title'];

		// Determine the current smiley set.
		$user_info['smiley_set'] = (!in_array($user_info['smiley_set'], explode(',', $modSettings['smiley_sets_known'])) && $user_info['smiley_set'] != 'none') || empty($modSettings['smiley_sets_enable']) ? (!empty($settings['smiley_sets_default']) ? $settings['smiley_sets_default'] : $modSettings['smiley_sets_default']) : $user_info['smiley_set'];
		$context['user']['smiley_set'] = $user_info['smiley_set'];

		if(preg_match('/[a-z]\.png|default\.png/', strtolower($user_info['avatar']['url']), $tmp) > 0)
			$user_info['avatar']['class'] = $context['user']['avatar']['class'] = 'default';
	}
	else
	{
		$context['user'] = array(
			'id' => -1,
			'is_logged' => false,
			'is_guest' => true,
			'is_mod' => false,
			'can_mod' => false,
			'name' => $txt['guest_title'],
			'language' => $language,
			'email' => '',
			'ignoreusers' => array(),
		);
	}

	// Some basic information...
	if (!isset($context['html_headers']))
		$context['html_headers'] = '';
	if (!isset($context['html_footers']))
		$context['html_footers'] = '';
	if (!isset($context['javascript_files']))
		$context['javascript_files'] = array();
	if (!isset($context['css_files']))
		$context['css_files'] = array();
	if (!isset($context['css_header']))
		$context['css_header'] = array();
	if (!isset($context['javascript_inline']))
		$context['javascript_inline'] = array('standard' => array(), 'defer' => array());
	if (!isset($context['javascript_vars']))
		$context['javascript_vars'] = array();

	$context['login_url'] = (!empty($modSettings['force_ssl']) && $modSettings['force_ssl'] < 2 ? strtr($scripturl, array('http://' => 'https://')) : $scripturl) . '?action=login2';
	$context['menu_separator'] = !empty($settings['use_image_buttons']) ? ' ' : ' | ';
	$context['session_var'] = isset($_SESSION['session_var']) ? $_SESSION['session_var'] : '';
	$context['session_id'] = isset($_SESSION['session_value']) ? $_SESSION['session_value'] : '';
	$context['forum_name'] = $mbname;
	$context['forum_name_html_safe'] = $pmxcFunc['htmlspecialchars']($context['forum_name']);
	$context['header_logo_url_html_safe'] = empty($settings['header_logo_url']) ? '' : $pmxcFunc['htmlspecialchars']($settings['header_logo_url']);
	$context['current_action'] = isset($_REQUEST['action']) ? $pmxcFunc['htmlspecialchars']($_REQUEST['action']) : (!empty($modSettings['portal_enabled']) ? 'home' : '');
	$context['current_subaction'] = isset($_REQUEST['sa']) ? $_REQUEST['sa'] : null;
	$context['can_register'] = empty($modSettings['registration_method']) || $modSettings['registration_method'] != 3;
	if (isset($modSettings['load_average']))
		$context['load_average'] = $modSettings['load_average'];

	// Detect the browser. This is separated out because it's also used in attachment downloads
	detectBrowser();

	// This allows sticking some HTML on the page output - useful for controls.
	$context['insert_after_template'] = '';

	if (!isset($txt))
		$txt = array();

	$simpleActions = array(
		'findmember',
		'helpadmin',
		'printpage',
		'spellcheck',
	);

	// Parent action => array of areas
	$simpleAreas = array(
		'profile' => array('popup', 'alerts_popup',),
	);

	// Parent action => array of subactions
	$simpleSubActions = array(
		'pm' => array('popup'),
		'signup' => array('usernamecheck')
	);
	// Actions that specifically uses XML output.
	$xmlActions = array(
		'quotefast',
		'jsmodify',
		'xmlhttp',
		'post2',
		'stats',
		'splittopics',
		'notifyboard',
		'notifytopic'
	);

	call_integration_hook('integrate_simple_actions', array(&$simpleActions, &$simpleAreas, &$simpleSubActions, &$xmlActions));

	$context['simple_action'] = in_array($context['current_action'], $simpleActions) ||
	(isset($simpleAreas[$context['current_action']]) && isset($_REQUEST['area']) && in_array($_REQUEST['area'], $simpleAreas[$context['current_action']])) ||
	(isset($simpleSubActions[$context['current_action']]) && in_array($context['current_subaction'], $simpleSubActions[$context['current_action']]));

	// Output is fully XML, so no need for the index template.
	if (isset($_REQUEST['xml']) && in_array($context['current_action'], $xmlActions))
	{
		loadLanguage('index+Modifications+LangSettings');
		loadTemplate('Xml');
		$context['template_layers'] = array();
	}
	// These actions don't require the index template at all.
	elseif (!empty($context['simple_action']))
	{
		loadLanguage('index+Modifications+LangSettings');
		$context['template_layers'] = array();
	}
	else
	{
		// Custom templates to load, or just default?
		if (isset($settings['theme_templates']))
			$templates = explode(',', $settings['theme_templates']);
		else
			$templates = array('index');

		// Load each template...
		foreach ($templates as $template)
			loadTemplate($template);

		// ...and attempt to load their associated language files.
		$required_files = implode('+', array_merge($templates, array('Modifications')));
		loadLanguage($required_files, '', false);

		// Custom template layers?
		if (isset($settings['theme_layers']))
			$context['template_layers'] = explode(',', $settings['theme_layers']);
		else
			$context['template_layers'] = array('html', 'body');
	}

	// Set the top level linktree up.
	array_unshift($context['linktree'], array(
		'url' => $scripturl,
		'name' => $txt['home']
	));

	// load additional tools language
	loadLanguage('Tools');

	// Initialize the theme.
	loadSubTemplate('init', 'ignore');

	// Allow overriding the board wide time/number formats.
	if(!empty($txt['default_time_format']))
		$modSettings['time_format'] = $txt['default_time_format'];
	$user_info['time_format'] = $modSettings['time_format'];

	// Set the character set from the template.
	$context['character_set'] = empty($modSettings['global_character_set']) ? $txt['lang_character_set'] : $modSettings['global_character_set'];
	$context['utf8'] = $context['character_set'] === 'UTF-8';
	$context['right_to_left'] = !empty($txt['lang_rtl']);

	// Guests may still need a name.
	if ($context['user']['is_guest'] && empty($context['user']['name']))
		$context['user']['name'] = $txt['guest_title'];

	// Any theme-related strings that need to be loaded?
	if (!empty($settings['require_theme_strings']))
		loadLanguage('ThemeStrings', '', false);

	// Make a special URL for the language.
	$settings['lang_images_url'] = $settings['images_url'] . '/' . (!empty($txt['image_lang']) ? $txt['image_lang'] : $user_info['language']);

	// And of course, let's load the default CSS file.
	loadCSSFile('index.css', array('minimize' => true), 'pmx_index');
	if ((isBrowser('webkit') || isBrowser('opera')) && !empty($modSettings['webkit_scrollbars']))
	{
		$context['LoadWebkit'] = true;
		loadCSSFile('webkit.css', array(), 'pmx_webkit');
	}

	// Here is my luvly Mobile CSS
	if(!empty($modSettings['isMobile']))
	{
		if(!empty($modSettings['minimize_files']))
		{
			if(($pmxCacheFunc['get']('minified_mobile_'. $settings['theme_id'] .'_css')) === null)
			{
				$path = '';
				if(file_exists($settings['theme_dir'] .'/css/mobile.css'))
					$path = $settings['theme_dir'] .'/css/';
				elseif(file_exists($settings['default_theme_dir'] .'/css/mobile.css'))
					$path = $settings['default_theme_dir'] .'/css/';

				if(!empty($path))
				{
					static $fnd = array('~/\*[^*]*\*+([^/][^*]*\*+)*/~', '~[\n\r]+~m', '~[\s\t]+~m', '~[\s]+\{~m', '~\{[\s]+~m', '~[\s]+\}~m', '~\}[\s]+~m', '~[\s]+\(~m', '~\([\s]+~m', '~[\s]+\)~m', '~\)[\s]+~m', '~[\s]+\,~m', '~\,[\s]+~m', '~[\s]+\:~m', '~\:[\s]+~m', '~[\s]+\;~m', '~\;[\s]+~m', '~\}~m', '~\}\n\}\n~m', '~\}\nto\{~m');
					static $repl = array('', '', ' ', '{', '{', '}', '}', ' (', '(', ')', ') ', ',', ',', ':', ':', ';', ';', "}", "}}", "}to{");
					file_put_contents($path .'minified.mobile.css', trim(preg_replace($fnd, $repl, file_get_contents($path .'mobile.css')), "\n\r\t"));
					$pmxCacheFunc['put']('minified_mobile_'. $settings['theme_id'] .'_css', $path .'minified.mobile.css', 60*60*24);
				}
			}
			loadCSSFile('minified.mobile.css', array(), 'pmx_mobile'. $settings['theme_id']);
		}
		else
			loadCSSFile('mobile.css', array(), 'pmx_mobile');
	}

	if ($context['right_to_left'])
		loadCSSFile('rtl.css', array(), 'pmx_rtl');

	// We allow theme variants, because we're cool.
	$context['theme_variant'] = '';
	$context['theme_variant_url'] = '';
	if (!empty($settings['theme_variants']))
	{
		// Overriding - for previews and that ilk.
		if (!empty($_REQUEST['variant']))
			$_SESSION['id_variant'] = $_REQUEST['variant'];
		// User selection?
		if (empty($settings['disable_user_variant']) || allowedTo('admin_forum'))
			$context['theme_variant'] = !empty($_SESSION['id_variant']) ? $_SESSION['id_variant'] : (!empty($options['theme_variant']) ? $options['theme_variant'] : '');
		// If not a user variant, select the default.
		if ($context['theme_variant'] == '' || !in_array($context['theme_variant'], $settings['theme_variants']))
			$context['theme_variant'] = !empty($settings['default_variant']) && in_array($settings['default_variant'], $settings['theme_variants']) ? $settings['default_variant'] : $settings['theme_variants'][0];

		// Do this to keep things easier in the templates.
		$context['theme_variant'] = '_' . $context['theme_variant'];
		$context['theme_variant_url'] = $context['theme_variant'] . '/';

		if (!empty($context['theme_variant']))
		{
			loadCSSFile('index' . $context['theme_variant'] . '.css', array(), 'pmx_index' . $context['theme_variant']);
			if ($context['right_to_left'])
				loadCSSFile('rtl' . $context['theme_variant'] . '.css', array(), 'pmx_rtl' . $context['theme_variant']);
		}
	}

	// Let's be compatible with old themes!
	if (!function_exists('template_html_above') && in_array('html', $context['template_layers']))
		$context['template_layers'] = array('main');

	$context['tabindex'] = 1;

	// Compatibility.
	if (!isset($settings['theme_version']))
		$modSettings['memberCount'] = $modSettings['totalMembers'];

	// Default JS variables for use in every theme
	$context['javascript_vars'] = array(
'pmx_theme_url' => '"' . $settings['theme_url'] . '"',
'pmx_default_theme_url' => '"' . $settings['default_theme_url'] . '"',
'pmx_images_url' => '"' . $settings['images_url'] . '"',
'pmx_smileys_url' => '"' . $modSettings['smileys_url'] . '"',
'pmx_scripturl' => '"' . $scripturl .'"',
'pmx_iso_case_folding' => $context['server']['iso_case_folding'] ? 'true' : 'false',
'pmx_charset' => '"' . $context['character_set'] . '"',
'pmx_session_id' => '"' . $context['session_id'] . '"',
'pmx_session_var' => '"' . $context['session_var'] . '"',
'pmx_member_id' => $context['user']['id'],
'ajax_notification_text' => JavaScriptEscape($txt['ajax_in_progress']),
'help_popup_heading_text' => JavaScriptEscape($txt['help_popup']),
'mobile_device' => !empty($modSettings['isMobile']) ? 'true' : 'false',
'Portal_enabled' => !empty($modSettings['portal_enabled']) ? 'true' : 'false',
'ecl_cache' => (checkECL_Cookie() ? 'true' : !empty($cache_enable) ? 'true' : 'false'),
'is_search_robot' => !empty($user_info['possibly_robot']) ? 'true' : 'false',
'pmx_onForum' => isset($_REQUEST['action']) || !empty($board) || !empty($topic) ? 'true' : 'false');

	// Add the JQuery library to the list of files to load.
	if (isset($modSettings['jquery_source']) && $modSettings['jquery_source'] == 'cdn')
		loadJavascriptFile('https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js', array('external' => true), 'pmx_jquery');

	elseif (isset($modSettings['jquery_source']) && $modSettings['jquery_source'] == 'local')
		loadJavascriptFile('jquery-3.2.1.min.js', array('seed' => false), 'pmx_jquery');

	elseif (isset($modSettings['jquery_source'], $modSettings['jquery_custom']) && $modSettings['jquery_source'] == 'custom')
		loadJavascriptFile($modSettings['jquery_custom'], array(), 'pmx_jquery');

	// Auto loading? template_javascript() will take care of the local half of this.
	else
		loadJavascriptFile('jquery-3.2.1.min.js', array('seed' => false), 'pmx_jquery');

	// script.js and theme.js, always required, so always add them! Makes index.template.php cleaner and all.
	loadJavascriptFile('script.js', array('minimize' => true), 'pmx_script');
	loadJavascriptFile('theme.js', array('minimize' => true), 'pmx_theme');

	// Need for JQuery plugins!
	loadJavascriptFile('jquery_plugins.js', array('minimize' => true), 'jquery_plugins');

	if(isset($modSettings['recaptcha_enabled']) && !empty($modSettings['recaptcha_enabled']))
	{
		$temp = explode(',', $modSettings['pm_spam_settings']);
		$PMverify = isset($temp[1]) ? intval($temp[1]) : 0;
		$needChapcha = false;
		if($user_info['is_guest'])
		{
			if(!empty($modSettings['reg_verification']) && getREQcnt('signup,signup2') > 0)
				$needChapcha = true;
			else if(!empty($modSettings['guests_require_captcha']) && getREQcnt('post,post2') > 0)
				$needChapcha = true;
			else if(!empty($modSettings['search_enable_captcha']) && getREQcnt('search,search2') > 0)
				$needChapcha = true;
		}
		else if(!$user_info['is_mod'] && !$user_info['is_admin'])
		{
			if(!empty($modSettings['posts_require_captcha']) && $user_info['posts'] < $modSettings['posts_require_captcha'] && getREQcnt('post,post2') > 0)
				$needChapcha = true;
			else if($PMverify && $user_info['posts'] < $PMverify && getREQcnt('pm,send,send2') > 1)
				$needChapcha = true;
		}
		if($needChapcha)
		{
			$modSettings['need_capcha'] = true;
			loadJavascriptFile('https://www.google.com/recaptcha/api.js?hl='. $txt['gcapcha_lang'], array('external' => true), 'pmx_recapcha');
			if(getREQcnt('search') > 0)
			{
				$_SESSION['ss_vv_passed'] = false;
				$_SESSION['last_ss'] = ' ';
			}
		}
	}

	// load lightbox
	$enableOnRequest = false;
	if(isset($_REQUEST['area']) && $_REQUEST['area'] == 'manageattachments' && isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'browse')
		$enableOnRequest = true;
	else if(isset($_REQUEST['area']) && $_REQUEST['area'] == 'showposts' && isset($_REQUEST['sa']) && $_REQUEST['sa'] == 'attach')
		$enableOnRequest = true;

	if (empty($modSettings['dont_use_lightbox']) || $enableOnRequest)
	{
		addInlineJavascript('
	var Lightbox_help = \''. $txt['lightbox_help'] .'\';
	var Lightbox_label = \''. $txt['lightbox_label'] .'\';
	var lb_prev_ttl = \''. $txt['lightbox_prev'] .'\';
	var lb_next_ttl = \''. $txt['lightbox_next'] .'\';');

		loadCSSFile('lightbox.css', array(), 'lightbox');
		loadJavascriptFile('lightbox.js', array(), 'lightbox');
	}

	// add favicon?
	if (!empty($modSettings['add_favicon_to_links']))
		addInlineJavascript('
	function fSetFavicon(jQuery){$(\':not(.signature)>.bbc_link,.ecl_link\').each(function(){var url=$(this).attr(\'href\'),domain=url.match(/:\/\/(.[^/]+)/)[1],schema=url.match(/^(http[s]*):\/\//)[1];$(this).css({\'background-image\':\'url(//www.google.com/s2/favicons?domain=\'+schema+\'://\'+domain+\')\',\'background-repeat\':\'no-repeat\',\'padding-left\':\'20px\',\'background-position\':\'1px\'});});}');
	else
		addInlineJavascript('
	function fSetFavicon(){}');

	// message icon pos..
	if($user_info['is_guest'])
		addInlineCss($modSettings['isMobile'] ? '.messageicon{margin:0px 5px -6px 2px !important;}' : '.messageicon{margin:1px 5px -6px 2px !important;}');
	else
		addInlineCss($modSettings['isMobile'] ? '.messageicon{margin:-3px 2px -6px -1px !important;}' : '.messageicon{margin:-2px 2px -6px -1px !important;}');

	// correct the top position on certain situations
	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : ''; 
	$noTopicBoardActs = array('post', 'editpoll', 'splittopics', 'movetopic');
	$tbpad = '0';
	if(!empty($_GET['topic']) && !in_array($action, $noTopicBoardActs))
		$tbpad = '0';
	elseif(!empty($_GET['board']) && !in_array($action, $noTopicBoardActs))
		$tbpad = '-8';
	else
	{
		switch ($action)
		{
			case 'community':
			case 'collapse':
			case 'splittopics':
			case 'calendar':
				$tbpad = '0';
			break;

			case 'mlist':
				$tbpad = show_gdpr_agreement() ? '0' : '-5';
			break;

			case 'post':
				$tbpad = (isset($_REQUEST['pmxerror']) && $_REQUEST['pmxerror'] == 'pmx_eclauth') ? '0' : '-19';
			break;
		}
	}
	addInlineCss('
	#preview_section{margin-top:'. abs($tbpad) .'px;}
	#portal_main{'. (intval($tbpad) < 0 ? 'margin' : 'padding') .'-top:'. $tbpad .'px;}');

	if(!empty($context['browser']['is_firefox']))
		addInlineCss('
	.previous_page{margin-top:-1px;}
	.videocontainer{margin:7px 0 0 0;width:269px;}');

	// if(!empty($modSettings['isMobile'])) $ofs = array(90, 70, 50); else $ofs = array(100, 80, 60);
	$ofs = $txt['footer_offset'][intval(!empty($modSettings['isMobile']))];
	addInlineJavascript('
	var footerHeight='. (!empty($modSettings['showCacheStatus']) && !empty($modSettings['timeLoadPageEnable']) ? $ofs[0] : (!empty($modSettings['showCacheStatus']) || !empty($modSettings['timeLoadPageEnable']) ? $ofs[1] : $ofs[2])) .';
	function fSetContentHeight(){document.getElementById(\'content_section\').style.minHeight=window.innerHeight-(document.getElementById(\'top_section\').clientHeight+document.getElementById(\'header\').clientHeight+document.getElementById(\'upper_section\').clientHeight+footerHeight)+\'px\';}');

	if (!empty($modSettings['portal_enabled']))
	{
		addInlineJavascript('
	window.addEventListener(\'resize\', eResizeFunc, true);
	$(document).ready(function(){portamx_onload();fSetFavicon()});');

		$tmp[0] = 'var allCookies={Name:[';
		$tmp[1] = 'Value:[';
		if(isset($_COOKIE) && is_array($_COOKIE))
		{
			foreach($_COOKIE as $name => $value)
			{
				if(!in_array($name, array('PHPSESSID', 'YOfs', 'screen', 'currentVersion', $cookiename)))
				{
					$tmp[0] .= '\''. $name .'\',';
					$tmp[1] .= '\''. $value .'\',';
				}
			}
		}
		$tmp[0] = rtrim($tmp[0], ',') .'],';
		$tmp[1] = rtrim($tmp[1], ',') .']' .'};';

		addInlineJavascript('
	'. $tmp[0] . $tmp[1]);
	}
	else
		addInlineJavascript('
	window.addEventListener(\'resize\', sysOnLoad, true);
	$(document).ready(function(){sysOnLoad()});');

	// If we think we have mail to send, let's offer up some possibilities... robots get pain (Now with scheduled task support!)
	if ((!empty($modSettings['mail_next_send']) && $modSettings['mail_next_send'] < time() && empty($modSettings['mail_queue_use_cron'])) || empty($modSettings['next_task_time']) || $modSettings['next_task_time'] < time())
	{
		if (isBrowser('possibly_robot'))
		{
			// @todo Maybe move this somewhere better?!
			require_once($sourcedir . '/ScheduledTasks.php');

			// What to do, what to do?!
			if (empty($modSettings['next_task_time']) || $modSettings['next_task_time'] < time())
				AutoTask();
			else
				ReduceMailQueue();
		}
		else
		{
			$type = empty($modSettings['next_task_time']) || $modSettings['next_task_time'] < time() ? 'task' : 'mailq';
			$ts = $type == 'mailq' ? $modSettings['mail_next_send'] : $modSettings['next_task_time'];

			addInlineJavascript('
	function pmxAutoTask(){$.get(pmx_scripturl + "?scheduled='. $type . ';ts='. $ts .'");} window.setTimeout("pmxAutoTask();", 1);');
		}
	}

	// And we should probably trigger the cron too.
	if (empty($modSettings['cron_is_real_cron']))
	{
		$ts = time();
		$ts -= $ts % 15;
		addInlineJavaScript('
	function triggerCron(){$.get('. JavaScriptEscape($boardurl) .' + "/cron.php?ts='. $ts .'");}
	window.setTimeout(triggerCron, 1);', true);
	}

	// Filter out the restricted boards from the linktree
	if (!$user_info['is_admin'] && !empty($board))
	{
		foreach ($context['linktree'] as $k => $element)
		{
			if (!empty($element['groups']) &&
				(count(array_intersect($user_info['groups'], $element['groups'])) == 0 ||
				(!empty($modSettings['deny_boards_access']) && count(array_intersect($user_info['groups'], $element['deny_groups'])) != 0)))
			{
				$context['linktree'][$k]['name'] = $txt['restricted_board'];
				$context['linktree'][$k]['extra_before'] = '<i>';
				$context['linktree'][$k]['extra_after'] = '</i>';
				unset($context['linktree'][$k]['url']);
			}
		}
	}

	// Any files to include at this point?
	if (!empty($modSettings['integrate_theme_include']))
	{
		$theme_includes = explode(',', $modSettings['integrate_theme_include']);
		foreach ($theme_includes as $include)
		{
			$include = strtr(trim($include), array('$boarddir' => $boarddir, '$sourcedir' => $sourcedir, '$themedir' => $settings['theme_dir']));
			if (file_exists($include))
				require_once($include);
		}
	}

	// Call load theme integration functions.
	call_integration_hook('integrate_load_theme');

	// We are ready to go.
	$context['theme_loaded'] = true;
}

/**
 * Load a template - if the theme doesn't include it, use the default.
 * What this function does:
 *  - loads a template file with the name template_name from the current, default, or base theme.
 *  - detects a wrong default theme directory and tries to work around it.
 *
 * @uses the template_include() function to include the file.
 * @param string $template_name The name of the template to load
 * @param array|string $style_sheets The name of a single stylesheet or an array of names of stylesheets to load
 * @param bool $fatal If true, dies with an error message if the template cannot be found
 * @return boolean Whether or not the template was loaded
 */
function loadTemplate($template_name, $style_sheets = array(), $fatal = true)
{
	global $context, $settings, $txt, $scripturl, $boarddir, $db_show_debug;

	// Do any style sheets first, cause we're easy with those.
	if (!empty($style_sheets))
	{
		if (!is_array($style_sheets))
			$style_sheets = array($style_sheets);

		foreach ($style_sheets as $sheet)
			loadCSSFile($sheet . '.css', array(), $sheet);
	}

	// No template to load?
	if ($template_name === false)
		return true;

	$loaded = false;
	foreach ($settings['template_dirs'] as $template_dir)
	{
		if (file_exists($template_dir . '/' . $template_name . '.template.php'))
		{
			$loaded = true;
			template_include($template_dir . '/' . $template_name . '.template.php', true);
			break;
		}
	}

	if ($loaded)
	{
		// For compatibility reasons, if this is the index template without new functions, include compatible stuff.
		if (substr($template_name, 0, 5) == 'index' && !function_exists('template_button_strip'))
			loadTemplate('Compat');

		if ($db_show_debug === true)
			$context['debug']['templates'][] = $template_name . ' (' . basename($template_dir) . ')';

		// If they have specified an initialization function for this template, go ahead and call it now.
		if (function_exists('template_' . $template_name . '_init'))
			call_user_func('template_' . $template_name . '_init');
	}
	// Hmmm... doesn't exist?!  I don't suppose the directory is wrong, is it?
	elseif (!file_exists($settings['default_theme_dir']) && file_exists($boarddir . '/Themes/default'))
	{
		$settings['default_theme_dir'] = $boarddir . '/Themes/default';
		$settings['template_dirs'][] = $settings['default_theme_dir'];

		if (!empty($context['user']['is_admin']) && !isset($_GET['th']))
		{
			loadLanguage('Errors');
			echo '
<div class="alert errorbox">
	<a href="', $scripturl . '?action=admin;area=theme;sa=list;' . $context['session_var'] . '=' . $context['session_id'], '" class="alert">', $txt['theme_dir_wrong'], '</a>
</div>';
		}

		loadTemplate($template_name);
	}
	// Cause an error otherwise.
	elseif ($template_name != 'Errors' && $template_name != 'index' && $fatal)
		fatal_lang_error('theme_template_error', 'template', array((string) $template_name));
	elseif ($fatal)
		die(log_error(sprintf(isset($txt['theme_template_error']) ? $txt['theme_template_error'] : 'Unable to load Themes/default/%s.template.php!', (string) $template_name), 'template'));
	else
		return false;
}

/**
 * Load a sub-template.
 * What it does:
 * - loads the sub template specified by sub_template_name, which must be in an already-loaded template.
 *  - if ?debug is in the query string, shows administrators a marker after every sub template
 * for debugging purposes.
 *
 * @todo get rid of reading $_REQUEST directly
 *
 * @param string $sub_template_name The name of the sub-template to load
 * @param bool $fatal Whether to die with an error if the sub-template can't be loaded
 */
function loadSubTemplate($sub_template_name, $fatal = false)
{
	global $context, $txt, $db_show_debug;

	if ($db_show_debug === true)
		$context['debug']['sub_templates'][] = $sub_template_name;

	// Figure out what the template function is named.
	$theme_function = 'template_' . $sub_template_name;
	if (function_exists($theme_function))
		$theme_function();
	elseif ($fatal === false)
		fatal_lang_error('theme_template_error', 'template', array((string) $sub_template_name));
	elseif ($fatal !== 'ignore')
		die(log_error(sprintf(isset($txt['theme_template_error']) ? $txt['theme_template_error'] : 'Unable to load the %s sub template!', (string) $sub_template_name), 'template'));
}

/**
 * Add a CSS file for output later
 *
 * @param string $fileName The name of the file to load
 * @param array $params An array of parameters
 * Keys are the following:
 * - ['external'] (true/false): define if the file is a externally located file. Needs to be set to true if you are loading an external file
 * - ['default_theme'] (true/false): force use of default theme url
 * - ['force_current'] (true/false): if this is false, we will attempt to load the file from the default theme if not found in the current theme
 * - ['validate'] (true/false): if true script will validate the local file exists
 * - ['rtl'] (string): additional file to load in RTL mode
 * - ['seed'] (true/false/string): if true or null, use cache stale, false do not, or used a supplied string
 * - ['minimize'] boolean to add your file to the main minimized file. Useful when you have a file thats loaded everywhere and for everyone.
 * @param string $id An ID to stick on the end of the filename for caching purposes
 */
function loadCSSFile($fileName, $params = array(), $id = '')
{
	global $settings, $context, $modSettings, $pmxCacheFunc;

	if(!empty($id))
		$params['seed'] = str_replace('.', '', $id);
	$id = '';

	$params['seed'] = (!array_key_exists('seed', $params) || (array_key_exists('seed', $params) && $params['seed'] === true)) ? (array_key_exists('browser_cache', $modSettings) ? $modSettings['browser_cache'] : '') : (is_string($params['seed']) ? ($params['seed'] = $params['seed'][0] === '?' ? $params['seed'] : '?' . $params['seed']) : '');
	$params['force_current'] = isset($params['force_current']) ? $params['force_current'] : false;
	$themeRef = !empty($params['default_theme']) ? 'default_theme' : 'theme';
	$params['minimize'] = isset($params['minimize']) ? $params['minimize'] : false;
	$params['external'] = isset($params['external']) ? $params['external'] : false;
	$params['validate'] = isset($params['validate']) ? $params['validate'] : true;

	// If this is an external file, automatically set this to false.
	if (!empty($params['external']))
		$params['minimize'] = false;

	// Account for shorthand like admin.css?alp21 filenames
	$has_seed = strpos($fileName, '.css?');
	$id = empty($id) ? strtr(basename(str_replace('.css', '', $fileName)), '?', '_') : $id;

	// Is this a local file?
	if (empty($params['external']))
	{
		// Are we validating the the file exists?
		if (!empty($params['validate']) && !file_exists($settings[$themeRef . '_dir'] . '/css/' . $fileName))
		{
			// Maybe the default theme has it?
			if ($themeRef === 'theme' && !$params['force_current'] && file_exists($settings['default_theme_dir'] . '/css/' . $fileName))
			{
				$fileUrl = $settings['default_theme_url'] . '/css/' . $fileName . ($has_seed ? '' : $params['seed']);
				$filePath = $settings['default_theme_dir'] . '/css/' . $fileName . ($has_seed ? '' : $params['seed']);
			}

			else
				$fileUrl = false;
		}

		else
		{
			$fileUrl = $settings[$themeRef . '_url'] . '/css/' . $fileName . ($has_seed ? '' : $params['seed']);
			$filePath = $settings[$themeRef . '_dir'] . '/css/' . $fileName . ($has_seed ? '' : $params['seed']);
		}
	}

	// An external file doesn't have a filepath. Mock one for simplicity.
	else
	{
		$fileUrl = $fileName;
		$filePath = $fileName;
	}

	// Add it to the array for use in the template
	if (!empty($fileName))
		$context['css_files'][$id] = array('fileUrl' => $fileUrl, 'filePath' => $filePath, 'fileName' => $fileName, 'options' => $params);

	if (!empty($context['right_to_left']) && !empty($params['rtl']))
		loadCSSFile($params['rtl'], array_diff_key($params, array('rtl' => 0)));
}

/**
 * Add a block of inline css code to be executed later
 *
 * - only use this if you have to, generally external css files are better, but for very small changes
 *   or for scripts that require help from PHP/whatever, this can be useful.
 * - all code added with this function is added to the same <style> tag so do make sure your css is valid!
 *
 * @param string $css Some css code
 * @return void|bool Adds the CSS to the $context['css_header'] array or returns if no CSS is specified
 */
function addInlineCss($css)
{
	global $context;

	// Gotta add something...
	if (empty($css))
		return false;

	$context['css_header'][] = $css;
}

/**
 * Add a Javascript file for output later

 * @param string $filename The name of the file to load
 * @param array $params An array of parameter info
 * Keys are the following:
 * - ['external'] (true/false): define if the file is a externally located file. Needs to be set to true if you are loading an external file
 * - ['default_theme'] (true/false): force use of default theme url
 * - ['defer'] (true/false): define if the file should load in <head> or before the closing <html> tag
 * - ['force_current'] (true/false): if this is false, we will attempt to load the file from the
 * default theme if not found in the current theme
 * - ['async'] (true/false): if the script should be loaded asynchronously (HTML5)
 * - ['validate'] (true/false): if true script will validate the local file exists
 * - ['seed'] (true/false/string): if true or null, use cache stale, false do not, or used a supplied string
 * - ['minimize'] boolean to add your file to the main minimized file. Useful when you have a file thats loaded everywhere and for everyone.
 *
 * @param string $id An ID to stick on the end of the filename
 */
function loadJavascriptFile($fileName, $params = array(), $id = '')
{
	global $settings, $context, $modSettings;

	if(!empty($id))
		$params['seed'] = str_replace('.', '', $id);
	$id = '';

	$params['seed'] = (!array_key_exists('seed', $params) || (array_key_exists('seed', $params) && $params['seed'] === true)) ? (array_key_exists('browser_cache', $modSettings) ? $modSettings['browser_cache'] : '') : (is_string($params['seed']) ? ($params['seed'] = $params['seed'][0] === '?' ? $params['seed'] : '?' . $params['seed']) : '');
	$params['force_current'] = isset($params['force_current']) ? $params['force_current'] : false;
	$themeRef = !empty($params['default_theme']) ? 'default_theme' : 'theme';
	$params['minimize'] = isset($params['minimize']) ? $params['minimize'] : false;
	$params['external'] = isset($params['external']) ? $params['external'] : false;
	$params['validate'] = isset($params['validate']) ? $params['validate'] : true;

	// If this is an external file, automatically set this to false.
	if (!empty($params['external']))
		$params['minimize'] = false;

	// Account for shorthand like admin.js?alp21 filenames
	$has_seed = strpos($fileName, '.js?');
	$id = empty($id) ? strtr(basename(str_replace('.js', '', $fileName)), '?', '_') : $id;

	// Is this a local file?
	if (empty($params['external']))
	{
		// Are we validating it exists on disk?
		if (!empty($params['validate']) && !file_exists($settings[$themeRef . '_dir'] . '/scripts/' . $fileName))
		{
			// Can't find it in this theme, how about the default?
			if ($themeRef === 'theme' && !$params['force_current'] && file_exists($settings['default_theme_dir'] . '/scripts/' . $fileName))
			{
				$fileUrl = $settings['default_theme_url'] . '/scripts/' . $fileName . ($has_seed ? '' : $params['seed']);
				$filePath = $settings['default_theme_dir'] . '/scripts/' . $fileName . ($has_seed ? '' : $params['seed']);
			}

			else
			{
				$fileUrl = false;
				$filePath = false;
			}
		}

		else
		{
			$fileUrl = $settings[$themeRef . '_url'] . '/scripts/' . $fileName . ($has_seed ? '' : $params['seed']);
			$filePath = $settings[$themeRef . '_dir'] . '/scripts/' . $fileName . ($has_seed ? '' : $params['seed']);
		}
	}

	// An external file doesn't have a filepath. Mock one for simplicity.
	else
	{
		$fileUrl = $fileName;
		$filePath = $fileName;
	}

	// Add it to the array for use in the template
	if (!empty($fileName))
		$context['javascript_files'][$id] = array('fileUrl' => $fileUrl, 'filePath' => $filePath, 'fileName' => $fileName, 'options' => $params);
}

/**
 * Add a Javascript variable for output later (for feeding text strings and similar to JS)
 * Cleaner and easier (for modders) than to use the function below.
 *
 * @param string $key The key for this variable
 * @param string $value The value
 * @param bool $escape Whether or not to escape the value
 */
function addJavascriptVar($key, $value, $escape = false)
{
	global $context;

	if (!empty($key) && (!empty($value) || $value === '0'))
		$context['javascript_vars'][$key] = !empty($escape) ? JavaScriptEscape($value) : $value;
}

/**
 * Add a block of inline Javascript code to be executed later
 *
 * - only use this if you have to, generally external JS files are better, but for very small scripts
 *   or for scripts that require help from PHP/whatever, this can be useful.
 * - all code added with this function is added to the same <script> tag so do make sure your JS is clean!
 *
 * @param string $javascript Some JS code
 * @param bool $defer Whether the script should load in <head> or before the closing <html> tag
 * @return void|bool Adds the code to one of the $context['javascript_inline'] arrays or returns if no JS was specified
 */
function addInlineJavascript($javascript, $defer = false)
{
	global $context;

	if (empty($javascript))
		return false;

	$context['javascript_inline'][($defer === true ? 'defer' : 'standard')][] = $javascript;
}

/**
 * Load a language file.  Tries the current and default themes as well as the user and global languages.
 *
 * @param string $template_name The name of a template file
 * @param string $lang A specific language to load this file from
 * @param bool $fatal Whether to die with an error if it can't be loaded
 * @param bool $force_reload Whether to load the file again if it's already loaded
 * @return string The language actually loaded.
 */
function loadLanguage($template_name, $lang = '', $fatal = true, $force_reload = false)
{
	global $user_info, $language, $settings, $context, $modSettings;
	global $db_show_debug, $sourcedir, $txt, $birthdayEmails, $txtBirthdayEmails;
	static $already_loaded = array();

	if(!defined('is_sheduled_task') && !isset($_SESSION['language']) && !isset($context['isEmailLang']))
	{
		$templang = get_cookie('language');
		if(!empty($templang))
		{
			$lang = $templang;
			$force_reload = ($lang != $user_info['language']);
		}
	}

	// Default to the user's language.
	if (empty($lang))
		$lang = empty($user_info['language']) ? $language : $user_info['language'];
	if (!$force_reload && isset($already_loaded[$template_name]) && $already_loaded[$template_name] == $lang)
		return $lang;

	// Make sure we have $settings - if not we're in trouble and need to find it!
	if (empty($settings['default_theme_dir']))
	{
		require_once($sourcedir . '/ScheduledTasks.php');
		loadEssentialThemeData();
	}

	// What theme are we in?
	$theme_name = basename($settings['theme_url']);
	if (empty($theme_name))
		$theme_name = 'unknown';

	// For each file open it up and write it out!
	foreach (explode('+', $template_name) as $template)
	{
		// Obviously, the current theme is most important to check.
		$attempts = array(
			array($settings['theme_dir'], $template, $lang, $settings['theme_url']),
			array($settings['theme_dir'], $template, $language, $settings['theme_url']),
		);

		// Do we have a base theme to worry about?
		if (isset($settings['base_theme_dir']))
		{
			$attempts[] = array($settings['base_theme_dir'], $template, $lang, $settings['base_theme_url']);
			$attempts[] = array($settings['base_theme_dir'], $template, $language, $settings['base_theme_url']);
		}

		// Fall back on the default theme if necessary.
		$attempts[] = array($settings['default_theme_dir'], $template, $lang, $settings['default_theme_url']);
		$attempts[] = array($settings['default_theme_dir'], $template, $language, $settings['default_theme_url']);

		// Fall back on the English language if none of the preferred languages can be found.
		if (!in_array('english', array($lang, $language)))
		{
			$attempts[] = array($settings['theme_dir'], $template, 'english', $settings['theme_url']);
			$attempts[] = array($settings['default_theme_dir'], $template, 'english', $settings['default_theme_url']);
		}

		// Try to find the language file.
		$found = false;
		foreach ($attempts as $k => $file)
		{
			if (file_exists($file[0] . '/languages/' . $file[1] . '.' . $file[2] . '.php'))
			{
				// Include it!
				template_include($file[0] . '/languages/' . $file[1] . '.' . $file[2] . '.php');

				// Note that we found it.
				$found = true;

				// setlocale is required for basename() & pathinfo() to work properly on the selected language
				if (!empty($txt['lang_locale']) && !empty($modSettings['global_character_set']))
					setlocale(LC_CTYPE, $txt['lang_locale'] . '.' . $modSettings['global_character_set']);

				break;
			}
		}

		// That couldn't be found!  Log the error, but *try* to continue normally.
		if (!$found && $fatal)
		{
			log_error(sprintf($txt['theme_language_error'], $template_name . '.' . $lang, 'template'));
			break;
		}

		// For the sake of backward compatibility
		if (!empty($txt['emails']))
		{
			foreach ($txt['emails'] as $key => $value)
			{
				$txt[$key . '_subject'] = $value['subject'];
				$txt[$key . '_body'] = $value['body'];
			}
			$txt['emails'] = array();
		}
		// For sake of backward compatibility: $birthdayEmails is supposed to be
		// empty in a normal install. If it isn't it means the forum is using
		// something "old" (it may be the translation, it may be a mod) and this
		// code (like the piece above) takes care of converting it to the new format
		if (!empty($birthdayEmails))
		{
			foreach ($birthdayEmails as $key => $value)
			{
				$txtBirthdayEmails[$key . '_subject'] = $value['subject'];
				$txtBirthdayEmails[$key . '_body'] = $value['body'];
				$txtBirthdayEmails[$key . '_author'] = $value['author'];
			}
			$birthdayEmails = array();
		}
	}

	// Keep track of what we're up to soldier.
	if ($db_show_debug === true)
		$context['debug']['language_files'][] = $template_name . '.' . $lang . ' (' . $theme_name . ')';

	// Remember what we have loaded, and in which language.
	$already_loaded[$template_name] = $lang;

	// Return the language actually loaded.
	return $lang;
}

/**
 * Get all parent boards (requires first parent as parameter)
 * It finds all the parents of id_parent, and that board itself.
 * Additionally, it detects the moderators of said boards.
 *
 * @param int $id_parent The ID of the parent board
 * @return array An array of information about the boards found.
 */
function getBoardParents($id_parent)
{
	global $scripturl, $pmxcFunc, $pmxCacheFunc;

	// First check if we have this cached already.
	if (($boards = $pmxCacheFunc['get']('board_parents-' . $id_parent)) === null)
	{
		$boards = array();
		$original_parent = $id_parent;

		// Loop while the parent is non-zero.
		while ($id_parent != 0)
		{
			$result = $pmxcFunc['db_query']('', '
				SELECT
					b.id_parent, b.name, {int:board_parent} AS id_board, b.member_groups, b.deny_member_groups,
					b.child_level, COALESCE(mem.id_member, 0) AS id_moderator, mem.real_name,
					COALESCE(mg.id_group, 0) AS id_moderator_group, mg.group_name
				FROM {db_prefix}boards AS b
					LEFT JOIN {db_prefix}moderators AS mods ON (mods.id_board = b.id_board)
					LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = mods.id_member)
					LEFT JOIN {db_prefix}moderator_groups AS modgs ON (modgs.id_board = b.id_board)
					LEFT JOIN {db_prefix}membergroups AS mg ON (mg.id_group = modgs.id_group)
				WHERE b.id_board = {int:board_parent}',
				array(
					'board_parent' => $id_parent,
				)
			);
			// In the EXTREMELY unlikely event this happens, give an error message.
			if ($pmxcFunc['db_num_rows']($result) == 0)
				fatal_lang_error('parent_not_found', 'critical');
			while ($row = $pmxcFunc['db_fetch_assoc']($result))
			{
				if (!isset($boards[$row['id_board']]))
				{
					$id_parent = $row['id_parent'];
					$boards[$row['id_board']] = array(
						'url' => $scripturl . '?board=' . $row['id_board'] . '.0',
						'name' => $row['name'],
						'level' => $row['child_level'],
						'groups' => explode(',', $row['member_groups']),
						'deny_groups' => explode(',', $row['deny_member_groups']),
						'moderators' => array(),
						'moderator_groups' => array()
					);
				}
				// If a moderator exists for this board, add that moderator for all children too.
				if (!empty($row['id_moderator']))
					foreach ($boards as $id => $dummy)
					{
						$boards[$id]['moderators'][$row['id_moderator']] = array(
							'id' => $row['id_moderator'],
							'name' => $row['real_name'],
							'href' => $scripturl . '?action=profile;u=' . $row['id_moderator'],
							'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_moderator'] . '">' . $row['real_name'] . '</a>'
						);
					}

				// If a moderator group exists for this board, add that moderator group for all children too
				if (!empty($row['id_moderator_group']))
					foreach ($boards as $id => $dummy)
					{
						$boards[$id]['moderator_groups'][$row['id_moderator_group']] = array(
							'id' => $row['id_moderator_group'],
							'name' => $row['group_name'],
							'href' => $scripturl . '?action=groups;sa=members;group=' . $row['id_moderator_group'],
							'link' => '<a href="' . $scripturl . '?action=groups;sa=members;group=' . $row['id_moderator_group'] . '">' . $row['group_name'] . '</a>'
						);
					}
			}
			$pmxcFunc['db_free_result']($result);
		}

		$pmxCacheFunc['put']('board_parents-' . $original_parent, $boards, 480);
	}

	return $boards;
}

/**
 * Attempt to reload our known languages.
 *
 * @param bool $use_cache Whether or not to use the cache
 * @return array An array of information about available languages
 */
function getLanguages($use_cache = true)
{
	global $context, $pmxcFunc, $settings, $modSettings, $pmxCacheFunc;

	// Either we don't use the cache, or its expired.
	if (!$use_cache || ($context['languages'] = $pmxCacheFunc['get']('known_languages')) == null)
	{
		// If we don't have our ucwords function defined yet, let's load the settings data.
		if (empty($pmxcFunc['ucwords']))
			reloadSettings();

		// If we don't have our theme information yet, let's get it.
		if (empty($settings['default_theme_dir']))
			loadTheme(0, false);

		// Default language directories to try.
		$language_directories = array(
			$settings['default_theme_dir'] . '/languages',
		);
		if (!empty($settings['actual_theme_dir']) && $settings['actual_theme_dir'] != $settings['default_theme_dir'])
			$language_directories[] = $settings['actual_theme_dir'] . '/languages';

		// We possibly have a base theme directory.
		if (!empty($settings['base_theme_dir']))
			$language_directories[] = $settings['base_theme_dir'] . '/languages';

		// Remove any duplicates.
		$language_directories = array_unique($language_directories);

		foreach ($language_directories as $language_dir)
		{
			// Can't look in here... doesn't exist!
			if (!file_exists($language_dir))
				continue;

			$dir = dir($language_dir);
			while ($entry = $dir->read())
			{
				// Look for the index language file....
				if (!preg_match('~^index\.(.+)\.php$~', $entry, $matches))
					continue;

				$context['languages'][$matches[1]] = array(
					'name' => $pmxcFunc['ucwords'](strtr($matches[1], array('_' => ' '))),
					'selected' => false,
					'filename' => $matches[1],
					'location' => $language_dir . '/index.' . $matches[1] . '.php',
				);

			}
			$dir->close();
		}

		// Let's cash in on this deal.
		if (!empty($modSettings['cache_enable']))
			$pmxCacheFunc['put']('known_languages', $context['languages'], !empty($modSettings['cache_enable']) && $modSettings['cache_enable'] < 1 ? 86400 : 3600);
	}

	return $context['languages'];
}

/**
 * Replace all vulgar words with respective proper words. (substring or whole words..)
 * What this function does:
 *  - it censors the passed string.
 *  - if the theme setting allow_no_censored is on, and the theme option
 *	show_no_censored is enabled, does not censor, unless force is also set.
 *  - it caches the list of censored words to reduce parsing.
 *
 * @param string &$text The text to censor
 * @param bool $force Whether to censor the text regardless of settings
 * @return string The censored text
 */
function censorText(&$text, $force = false)
{
	global $modSettings, $options, $txt;
	static $censor_vulgar = null, $censor_proper;

	if ((!empty($options['show_no_censored']) && !empty($modSettings['allow_no_censored']) && !$force) || empty($modSettings['censor_vulgar']) || trim($text) === '')
		return $text;

	// If they haven't yet been loaded, load them.
	if ($censor_vulgar == null)
	{
		$censor_vulgar = explode("\n", $modSettings['censor_vulgar']);
		$censor_proper = explode("\n", $modSettings['censor_proper']);

		// Quote them for use in regular expressions.
		if (!empty($modSettings['censorWholeWord']))
		{
			for ($i = 0, $n = count($censor_vulgar); $i < $n; $i++)
			{
				$censor_vulgar[$i] = str_replace(array('\\\\\\*', '\\*', '&', '\''), array('[*]', '[^\s]*?', '&amp;', '&#039;'), preg_quote($censor_vulgar[$i], '/'));
				$censor_vulgar[$i] = '/(?<=^|\W)' . $censor_vulgar[$i] . '(?=$|\W)/' . (empty($modSettings['censorIgnoreCase']) ? '' : 'i') . ((empty($modSettings['global_character_set']) ? $txt['lang_character_set'] : $modSettings['global_character_set']) === 'UTF-8' ? 'u' : '');
			}
		}
	}

	// Censoring isn't so very complicated :P.
	if (empty($modSettings['censorWholeWord']))
	{
		$func = !empty($modSettings['censorIgnoreCase']) ? 'str_ireplace' : 'str_replace';
		$text = $func($censor_vulgar, $censor_proper, $text);
	}
	else
		$text = preg_replace($censor_vulgar, $censor_proper, $text);

	return $text;
}

/**
 * Load the template/language file using require
 *
 * @param string $filename The name of the file to include
 * @param bool $once If true only includes the file once (like include_once)
 */
function template_include($filename, $once = false)
{
	global $context, $settings, $txt, $scripturl, $modSettings;
	global $boardurl, $boarddir, $sourcedir;
	global $maintenance, $mtitle, $mmessage;
	static $templates = array();

	// We want to be able to figure out any errors...
	@ini_set('track_errors', '1');

	// Don't include the file more than once, if $once is true.
	if ($once && in_array($filename, $templates))
		return;
	// Add this file to the include list, whether $once is true or not.
	else
		$templates[] = $filename;

	$file_found = file_exists($filename);
	if ($once && $file_found)
		require_once($filename);
	elseif ($file_found)
		require($filename);

	if ($file_found !== true)
	{
		ob_end_clean();
		if (!empty($modSettings['enableCompressedOutput']))
			@ob_start('ob_gzhandler');
		else
			ob_start();

		if (isset($_GET['debug']))
			header('Content-Type: application/xhtml+xml; charset=' . (empty($context['character_set']) ? 'ISO-8859-1' : $context['character_set']));

		// Don't cache error pages!!
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-cache');

		if (!isset($txt['template_parse_error']))
		{
			$txt['template_parse_error'] = 'Template Parse Error!';
			$txt['template_parse_error_message'] = 'It seems something has gone sour on the forum with the template system.  This problem should only be temporary, so please come back later and try again.  If you continue to see this message, please contact the administrator.<br><br>You can also try <a href="javascript:location.reload();">refreshing this page</a>.';
			$txt['template_parse_error_details'] = 'There was a problem loading the <pre><strong>%1$s</strong></pre> template or language file.  Please check the syntax and try again - remember, single quotes (<pre>\'</pre>) often have to be escaped with a slash (<pre>\\</pre>).  To see more specific error information from PHP, try <a href="' . $boardurl . '%1$s" class="extern">accessing the file directly</a>.<br><br>You may want to try to <a href="javascript:location.reload();">refresh this page</a> or <a href="' . $scripturl . '?theme=1">use the default theme</a>.';
			$txt['template_parse_errmsg'] = 'Unfortunately more information is not available at this time as to exactly what is wrong.';
		}

		// First, let's get the doctype and language information out of the way.
		echo '<!DOCTYPE html>
<html', !empty($context['right_to_left']) ? ' dir="rtl"' : '', '>
	<head>';
		if (isset($context['character_set']))
			echo '
		<meta charset="', $context['character_set'], '">';

		if (!empty($maintenance) && !allowedTo('admin_forum'))
			echo '
		<title>', $mtitle, '</title>
	</head>
	<body>
		<h3>', $mtitle, '</h3>
		', $mmessage, '
	</body>
</html>';
		elseif (!allowedTo('admin_forum'))
			echo '
		<title>', $txt['template_parse_error'], '</title>
	</head>
	<body>
		<h3>', $txt['template_parse_error'], '</h3>
		', $txt['template_parse_error_message'], '
	</body>
</html>';
		else
		{
			require_once($sourcedir . '/Subs-Package.php');

			$error = fetch_web_data($boardurl . strtr($filename, array($boarddir => '', strtr($boarddir, '\\', '/') => '')));
			if (empty($error) && ini_get('track_errors') && !empty($php_errormsg))
				$error = $php_errormsg;
			if (empty($error))
				$error = $txt['template_parse_errmsg'];

			$error = strtr($error, array('<b>' => '<strong>', '</b>' => '</strong>'));

			echo '
		<title>', $txt['template_parse_error'], '</title>
	</head>
	<body>
		<h3>', $txt['template_parse_error'], '</h3>
		', sprintf($txt['template_parse_error_details'], strtr($filename, array($boarddir => '', strtr($boarddir, '\\', '/') => '')));

			if (!empty($error))
				echo '
		<hr>

		<div style="margin: 0 20px;"><pre>', strtr(strtr($error, array('<strong>' . $boarddir => '<strong>...', '<strong>' . strtr($boarddir, '\\', '/') => '<strong>...')), '\\', '/'), '</pre></div>';

			// I know, I know... this is VERY COMPLICATED.  Still, it's good.
			if (preg_match('~ <strong>(\d+)</strong><br( /)?' . '>$~i', $error, $match) != 0)
			{
				$data = file($filename);
				$data2 = highlight_php_code(implode('', $data));
				$data2 = preg_split('~\<br( /)?\>~', $data2);

				// Fix the PHP code stuff...
				if (!isBrowser('gecko'))
					$data2 = str_replace("\t", '<span style="white-space: pre;">' . "\t" . '</span>', $data2);
				else
					$data2 = str_replace('<pre style="display: inline;">' . "\t" . '</pre>', "\t", $data2);

				// Now we get to work around a bug in PHP where it doesn't escape <br>s!
				$j = -1;
				foreach ($data as $line)
				{
					$j++;

					if (substr_count($line, '<br>') == 0)
						continue;

					$n = substr_count($line, '<br>');
					for ($i = 0; $i < $n; $i++)
					{
						$data2[$j] .= '&lt;br /&gt;' . $data2[$j + $i + 1];
						unset($data2[$j + $i + 1]);
					}
					$j += $n;
				}
				$data2 = array_values($data2);
                array_unshift($data2, '');

				echo '
					<div style="margin: 2ex 20px; width: 96%; overflow: auto;"><pre style="margin: 0;">';

				// Figure out what the color coding was before...
                $line = max($match[1] - 9, 1);
                $last_line = '';
				for ($line2 = $line - 1; $line2 > 1; $line2--)
				if (strpos($data2[$line2], '<') !== false)
				{
                    if (preg_match('~(<[^/>]+>)[^<]*$~', $data2[$line2], $color_match) != 0)
						$last_line = $color_match[1];
					break;
				}

				// Show the relevant lines...
				for ($n = min($match[1] + 4, count($data2) + 1); $line <= $n; $line++)
				{
					if ($line == $match[1])
						echo '</pre><div style="background-color: #ffb0b5;"><pre style="margin: 0;">';

					echo '<span style="color: black;">', sprintf('%' . strlen($n) . 's', $line), ':</span> ';
					if (isset($data2[$line]) && $data2[$line] != '')
						echo substr($data2[$line], 0, 2) == '</' ? preg_replace('~^</[^>]+>~', '', $data2[$line]) : $last_line . $data2[$line];

					if (isset($data2[$line]) && preg_match('~(<[^/>]+>)[^<]*$~', $data2[$line], $color_match) != 0)
					{
						$last_line = $color_match[1];
						echo '</', substr($last_line, 1, 4), '>';
					}
					elseif ($last_line != '' && strpos($data2[$line], '<') !== false)
						$last_line = '';
					elseif ($last_line != '' && $data2[$line] != '')
						echo '</', substr($last_line, 1, 4), '>';

					if ($line == $match[1])
						echo '</pre></div><pre style="margin: 0;">';
					else
						echo "\n";
				}

				echo '</pre></div>';
			}

			echo '
	</body>
</html>';
		}

		die;
	}
}

/**
 * Initialize a database connection.
 */
function loadDatabase()
{
	global $db_persist, $db_connection, $db_server, $db_user, $db_passwd;
	global $db_type, $db_name, $ssi_db_user, $ssi_db_passwd, $sourcedir, $db_prefix, $db_port;

	// Figure out what type of database we are using.
	if (empty($db_type) || !file_exists($sourcedir . '/Subs-Db-' . $db_type . '.php'))
		$db_type = 'mysql';

	// Load the file for the database.
	require_once($sourcedir . '/Subs-Db-' . $db_type . '.php');

	$db_options = array();

	// Add in the port if needed
	if (!empty($db_port))
		$db_options['port'] = $db_port;

	// If we are in SSI try them first, but don't worry if it doesn't work, we have the normal username and password we can use.
	if (PMX == 'SSI' && !empty($ssi_db_user) && !empty($ssi_db_passwd))
	{
		$options = array_merge($db_options, array('persist' => $db_persist, 'non_fatal' => true, 'dont_select_db' => true));

		$db_connection = pmx_db_initiate($db_server, $db_name, $ssi_db_user, $ssi_db_passwd, $db_prefix, $options);
	}

	// Either we aren't in SSI mode, or it failed.
	if (empty($db_connection))
	{
		$options = array_merge($db_options, array('persist' => $db_persist, 'dont_select_db' => PMX == 'SSI'));

		$db_connection = pmx_db_initiate($db_server, $db_name, $db_user, $db_passwd, $db_prefix, $options);
	}

	// Safe guard here, if there isn't a valid connection lets put a stop to it.
	if (!$db_connection)
		display_db_error();

	// If in SSI mode fix up the prefix.
	if (PMX == 'SSI')
		db_fix_prefix($db_prefix, $db_name);
}

/**
 * Try to retrieve a cache entry. On failure, call the appropriate function.
 *
 * @param string $key The key for this entry
 * @param string $file The file associated with this entry
 * @param string $function The function to call
 * @param array $params Parameters to be passed to the specified function
 * @param int $level The cache level
 * @return string The cached data
 */
function cache_quick_get($key, $file, $function, $params, $level = 1)
{
	global $modSettings, $sourcedir, $pmxCacheFunc;

	// @todo Why are we doing this if caching is disabled?

	if (function_exists('call_integration_hook'))
		call_integration_hook('pre_cache_quick_get', array(&$key, &$file, &$function, &$params, &$level));

	/* Refresh the cache if either:
		1. Caching is disabled.
		2. The cache level isn't high enough.
		3. The item has not been cached or the cached item expired.
		4. The cached item has a custom expiration condition evaluating to true.
		5. The expire time set in the cache item has passed (needed for Zend).
	*/
	if (empty($modSettings['cache_enable']) || $modSettings['cache_enable'] < $level || !is_array($cache_block = $pmxCacheFunc['get']($key)) || (!empty($cache_block['refresh_eval']) && eval($cache_block['refresh_eval'])) || (!empty($cache_block['expires']) && $cache_block['expires'] < time()))
	{
		require_once($sourcedir . '/' . $file);
		$cache_block = call_user_func_array($function, $params);

		if (!empty($modSettings['cache_enable']) && $modSettings['cache_enable'] >= $level)
			$pmxCacheFunc['put']($key, $cache_block, $cache_block['expires'] - time());
	}

	// Some cached data may need a freshening up after retrieval.
	if (!empty($cache_block['post_retri_eval']))
		eval($cache_block['post_retri_eval']);

	if (function_exists('call_integration_hook'))
		call_integration_hook('post_cache_quick_get', array(&$cache_block));

	return $cache_block['data'];
}

/**
 * Helper function to set an array of data for an user's avatar.
 *
 * Makes assumptions based on the data provided, the following keys are required:
 * - avatar The raw "avatar" column in members table
 * - email The user's email. Used to get the gravatar info
 * - filename The attachment filename
 *
 * @param array $data An array of raw info
 * @return array An array of avatar data
 */
function set_avatar_data($data = array())
{
	global $modSettings, $boardurl, $pmxcFunc, $image_proxy_enabled, $image_proxy_secret;

	// Come on!
	if (empty($data))
		return array();

	// Set a nice default var.
	$image = '';
	$class = '';
	$default_image = $modSettings['avatar_url'] . '/default.png';

	// Gravatar has been set as mandatory!
	if (!empty($modSettings['gravatarOverride']))
	{
		if (!empty($modSettings['gravatarAllowExtraEmail']) && !empty($data['avatar']) && stristr($data['avatar'], 'gravatar://'))
			$image = get_gravatar_url($pmxcFunc['substr']($data['avatar'], 11));

		else if (!empty($data['email']))
			$image = get_gravatar_url($data['email']);
	}

	// Look if the user has a gravatar field or has set an external url as avatar.
	else
	{
		// So it's stored in the member table?
		if (!empty($data['avatar']))
		{
			// Gravatar.
			if (stristr($data['avatar'], 'gravatar://'))
			{
				if ($data['avatar'] == 'gravatar://')
					$image = get_gravatar_url($data['email']);

				elseif (!empty($modSettings['gravatarAllowExtraEmail']))
					$image = get_gravatar_url($pmxcFunc['substr']($data['avatar'], 11));
			}

			// External url.
			else
			{
				// Using ssl?
				if (!empty($modSettings['force_ssl']) && $image_proxy_enabled && stripos($data['avatar'], 'http://') !== false)
					$image = strtr($boardurl, array('http://' => 'https://')) . '/proxy.php?request=' . urlencode($data['avatar']) . '&hash=' . md5($data['avatar'] . $image_proxy_secret);

				// Just a plain external url.
				else
					$image = (stristr($data['avatar'], 'http://') || stristr($data['avatar'], 'https://')) ? $data['avatar'] : $modSettings['avatar_url'] . '/' . $data['avatar'];
			}
		}

		// Perhaps this user has an attachment as avatar...
		else if (!empty($data['filename']) && !empty($data['id']))
		{
			if(file_exists($modSettings['custom_avatar_dir'] . '/' . $data['filename']))
				$image = $modSettings['custom_avatar_url'] . '/' . $data['filename'];
		}

		// Right... no avatar... use our default image.
		else if(empty($image))
		{
			if(empty($data['id']))
				$image = $modSettings['avatar_url'] .'/default.png';

			else if(preg_match('~[a-zA-Z]+~', $data['username'], $tmp) > 0)
				$image = $modSettings['avatar_url'] .'/'. strtolower(substr($tmp[0], 0, 1)) .'.png';
			else
				$image = $default_image;

			$class = ' default';
		}
	}

	call_integration_hook('integrate_set_avatar_data', array(&$image, &$data));

	// At this point in time $image has to be filled unless you chose to force gravatar and the user doesn't have the needed data to retrieve it... thus a check for !empty() is still needed.
	if (!empty($image))
		return array(
			'name' => !empty($data['avatar']) ? $data['avatar'] : '',
			'image' => '<img class="avatar" src="' . $image . '" />',
			'href' => $image,
			'url' => $image,
			'class' => $class,
		);

	// Fallback to make life easier for everyone...
	else
		return array(
			'name' => '',
			'image' => '',
			'href' => '',
			'url' => '',
			'class' => '',
		);
}

?>