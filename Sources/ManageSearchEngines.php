<?php

/**
 * This file contains all the screens that relate to search engines.
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
 * Entry point for this section.
 */
function SearchEngines()
{
	global $context, $txt, $modSettings;

	isAllowedTo('admin_forum');

	loadLanguage('Search');
	loadTemplate('ManageSearch');

	if (!empty($modSettings['spider_mode']))
	{
		$subActions = array(
			'editspiders' => 'EditSpider',
			'logs' => 'SpiderLogs',
			'settings' => 'ManageSearchEngineSettings',
			'spiders' => 'ViewSpiders',
			'stats' => 'SpiderStats',
		);
		$default = 'stats';
	}
	else
	{
		$subActions = array(
			'settings' => 'ManageSearchEngineSettings',
		);
		$default = 'settings';
	}

	// Ensure we have a valid subaction.
	$context['sub_action'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : $default;

	$context['page_title'] = $txt['search_engines'];

	// Some more tab data.
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['search_engines'],
		'description' => $txt['search_engines_description'],
	);

	call_integration_hook('integrate_manage_search_engines', array(&$subActions));

	// Call the function!
	call_helper($subActions[$context['sub_action']]);
}

/**
 * This is really just the settings page.
 *
 * @param bool $return_config Whether to return the config_vars array (used for admin search)
 * @return void|array Returns nothing or returns the $config_vars array if $return_config is true
 */
function ManageSearchEngineSettings($return_config = false)
{
	global $context, $txt, $scripturl, $sourcedir, $pmxcFunc;

	$config_vars = array(
		// How much detail?
		array('select', 'spider_mode', 'subtext' => $txt['spider_mode_note'], array($txt['spider_mode_off'], $txt['spider_mode_standard'], $txt['spider_mode_high'], $txt['spider_mode_vhigh']), 'onchange' => 'disableFields();'),
		'spider_group' => array('select', 'spider_group', 'subtext' => $txt['spider_group_note'], array($txt['spider_group_none'], $txt['membergroups_members'])),
		array('select', 'show_spider_online', array($txt['show_spider_online_no'], $txt['show_spider_online_summary'], $txt['show_spider_online_detail'], $txt['show_spider_online_detail_admin'])),
	);

	// Set up a message.
	$context['settings_message'] = '<span>' . sprintf($txt['spider_settings_desc'], $scripturl . '?action=admin;area=logs;sa=settings;' . $context['session_var'] . '=' . $context['session_id']) . '</span>';

	// Do some javascript.
	$javascript_function = '
		function disableFields()
		{
			disabledState = document.getElementById(\'spider_mode\').value == 0;';

	foreach ($config_vars as $variable)
		if ($variable[1] != 'spider_mode')
			$javascript_function .= '
			if (document.getElementById(\'' . $variable[1] . '\'))
				document.getElementById(\'' . $variable[1] . '\').disabled = disabledState;';

	$javascript_function .= '
		}
		disableFields();';

	call_integration_hook('integrate_modify_search_engine_settings', array(&$config_vars));

	if ($return_config)
		return $config_vars;

	// We need to load the groups for the spider group thingy.
	$request = $pmxcFunc['db_query']('', '
		SELECT id_group, group_name
		FROM {db_prefix}membergroups
		WHERE id_group != {int:admin_group}
			AND id_group != {int:moderator_group}',
		array(
			'admin_group' => 1,
			'moderator_group' => 3,
		)
	);
	while ($row = $pmxcFunc['db_fetch_assoc']($request))
		$config_vars['spider_group'][2][$row['id_group']] = $row['group_name'];
	$pmxcFunc['db_free_result']($request);

	// Make sure it's valid - note that regular members are given id_group = 1 which is reversed in Load.php - no admins here!
	if (isset($_POST['spider_group']) && !isset($config_vars['spider_group'][2][$_POST['spider_group']]))
		$_POST['spider_group'] = 0;

	// We'll want this for our easy save.
	require_once($sourcedir . '/ManageServer.php');

	// Setup the template.
	$context['page_title'] = $txt['settings'];
	$context['sub_template'] = 'show_settings';

	// Are we saving them - are we??
	if (isset($_GET['save']))
	{
		checkSession();

		call_integration_hook('integrate_save_search_engine_settings');
		saveDBSettings($config_vars);
		recacheSpiderNames();
		$_SESSION['adm-save'] = true;
		redirectexit('action=admin;area=sengines;sa=settings');
	}

	// Final settings...
	$context['post_url'] = $scripturl . '?action=admin;area=sengines;save;sa=settings';
	$context['settings_title'] = $txt['settings'];
	addInlineJavascript($javascript_function, true);

	// Prepare the settings...
	prepareDBSettingContext($config_vars);
}

/**
 * View a list of all the spiders we know about.
 */
function ViewSpiders()
{
	global $context, $txt, $sourcedir, $scripturl, $pmxcFunc, $pmxCacheFunc, $modSettings;

	if (!isset($_SESSION['spider_stat']) || $_SESSION['spider_stat'] < time() - 60)
	{
		consolidateSpiderStats();
		$_SESSION['spider_stat'] = time();
	}

	// Are we adding a new one?
	if (!empty($_POST['addSpider']))
		return EditSpider();

	// User pressed the 'remove selection button'.
	elseif (!empty($_POST['updateSpiders']))
	{
		checkSession();
		validateToken('admin-ser');

		$removeList = array();
		$blockList = array();
		$cleanList = array();

		// create the forbidden list ...
		if(!empty($_POST['blockspider']) && is_array($_POST['blockspider']))
		{
			foreach ($_POST['blockspider'] as $id => $doit)
			{
				$blockList[$id] = $doit;
				if(!empty(intval($doit)))
					$cleanList[$id] = $id;
			}
		}

		// Create the remove list
		if(!empty($_POST['remove']) && is_array($_POST['remove']))
		{
			foreach ($_POST['remove'] as $id => $doit)
			{
				if(!empty(intval($doit)))
				{
					$removeList[$id] = $id;
					$cleanList[$id] = $id;
				}
			}
		}

		// Block them!
		if(count($blockList) > 0)
		{
			foreach($blockList as $id => $value)
				$pmxcFunc['db_query']('', '
					UPDATE {db_prefix}spiders
					SET forbidden = {int:blkval}
					WHERE id_spider = {int:block_spiders}',
					array(
						'block_spiders' => (int) $id,
						'blkval' => (int) $value,
					)
				);
		}

		// Delete them!
		if(count($removeList) > 0)
			$pmxcFunc['db_query']('', '
				DELETE FROM {db_prefix}spiders
				WHERE id_spider IN ({array_int:remove_list})',
				array(
					'remove_list' => $removeList,
				)
			);

		if(count($cleanList) > 0)
		{
			$pmxcFunc['db_query']('', '
				DELETE FROM {db_prefix}log_spider_hits
				WHERE id_spider IN ({array_int:remove_list})',
				array(
					'remove_list' => $cleanList,
				)
			);
			$pmxcFunc['db_query']('', '
				DELETE FROM {db_prefix}log_spider_stats
				WHERE id_spider IN ({array_int:remove_list})',
				array(
					'remove_list' => $cleanList,
				)
			);

			$pmxCacheFunc['drop']('spider_search');
			recacheSpiderNames();
		}
	}

	// Get the last seens.
	$request = $pmxcFunc['db_query']('', '
		SELECT id_spider, MAX(last_seen) AS last_seen_time
		FROM {db_prefix}log_spider_stats
		GROUP BY id_spider',
		array(
		)
	);
	$context['spider_last_seen'] = array();
	while ($row = $pmxcFunc['db_fetch_assoc']($request))
		$context['spider_last_seen'][$row['id_spider']] = $row['last_seen_time'];
	$pmxcFunc['db_free_result']($request);

	// Get the blocked spider.
	$request = $pmxcFunc['db_query']('', '
		SELECT id_spider, forbidden
		FROM {db_prefix}spiders
		GROUP BY id_spider',
		array(
		)
	);
	$context['spider_forbidden'] = array();
	while ($row = $pmxcFunc['db_fetch_assoc']($request))
		$context['spider_forbidden'][$row['id_spider']] = $row['forbidden'];
	$pmxcFunc['db_free_result']($request);

	createToken('admin-ser');
	$listOptions = array(
		'id' => 'spider_list',
		'title' => $txt['spiders'],
		'items_per_page' => $modSettings['defaultMaxListItems'],
		'base_href' => $scripturl . '?action=admin;area=sengines;sa=spiders',
		'default_sort_col' => 'name',
		'get_items' => array(
			'function' => 'list_getSpiders',
		),
		'get_count' => array(
			'function' => 'list_getNumSpiders',
		),
		'no_items_label' => $txt['spiders_no_entries'],
		'columns' => array(
			'name' => array(
				'header' => array(
					'value' => $txt['spider_name'],
				),
				'data' => array(
					'function' => function ($rowData) use ($pmxcFunc, $scripturl)
					{
						return sprintf('<a href="%1$s?action=admin;area=sengines;sa=editspiders;sid=%2$d">%3$s</a>', $scripturl, $rowData['id_spider'], $pmxcFunc['htmlspecialchars']($rowData['spider_name']));
					},
				),
				'sort' => array(
					'default' => 'spider_name',
					'reverse' => 'spider_name DESC',
				),
			),
			'last_seen' => array(
				'header' => array(
					'value' => $txt['spider_last_seen'],
				),
				'data' => array(
					'function' => function ($rowData) use ($context, $txt)
					{
						return isset($context['spider_last_seen'][$rowData['id_spider']]) ? timeformat($context['spider_last_seen'][$rowData['id_spider']]) : (!empty($rowData['forbidden']) ? $txt['spider_is_blocked'] : $txt['spider_last_never']);
					},
					'style' => 'white-space:nowrap'
				),
			),
			'user_agent' => array(
				'header' => array(
					'value' => $txt['spider_agent'],
				),
				'data' => array(
					'db_htmlsafe' => 'user_agent',
				),
				'sort' => array(
					'default' => 'user_agent',
					'reverse' => 'user_agent DESC',
				),
			),
			'ip_info' => array(
				'header' => array(
					'value' => $txt['spider_ip_info'],
				),
				'data' => array(
					'db_htmlsafe' => 'ip_info',
					'class' => 'smalltext',
				),
				'sort' => array(
					'default' => 'ip_info',
					'reverse' => 'ip_info DESC',
				),
			),
			'forbidden' => array(
				'header' => array(
					'value' => $txt['spider_block'],
				),
				'data' => array(
					'function' => function ($rowData) use ($context)
					{
						if($rowData['spider_name'] == 'PortaMx Spider')
							return '';
						return '<input type="hidden" name="blockspider['. $rowData['id_spider'].']" value="0"><input type="checkbox" name="blockspider['. $rowData['id_spider'].']" value="1"'. (!empty($rowData['forbidden']) ? ' checked="checked"' : '') .' class="input_check">';
					},
					'class' => 'centercol',
				),
				'sort' => array(
					'default' => 'forbidden DESC',
					'reverse' => 'forbidden',
				),
			),
			'remove' => array(
				'header' => array(
					'value' => $txt['spider_remove'],
					'class' => 'centercol',
				),
				'data' => array(
					'function' => function ($rowData) use ($context)
					{
						if($rowData['spider_name'] == 'PortaMx Spider')
							return '';
						return '<input type="hidden" name="remove['. $rowData['id_spider'].']" value="0"><input type="checkbox" name="remove['. $rowData['id_spider'].']" value="1" class="input_check">';
					},
					'class' => 'centercol',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=admin;area=sengines;sa=spiders',
			'token' => 'admin-ser',
		),
		'additional_rows' => array(
			array(
				'position' => 'bottom_of_list',
				'value' => '
					<input type="submit" name="updateSpiders" value="' . $txt['spiders_change_selected'] . '" data-confirm="' . $txt['spider_change_selected_confirm'] . '" class="button_submit you_sure">
					<input type="submit" name="addSpider" value="' . $txt['spiders_add'] . '" class="button_submit">
				',
			),
		),
	);

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	$context['sub_template'] = 'show_list';
	$context['default_list'] = 'spider_list';
}

/**
 * Callback function for createList()
 * @param int $start The item to start with (for pagination purposes)
 * @param int $items_per_page The number of items to show per page
 * @param string $sort A string indicating how to sort the results
 * @return array An array of information about known spiders
 */
function list_getSpiders($start, $items_per_page, $sort)
{
	global $pmxcFunc;

	$request = $pmxcFunc['db_query']('', '
		SELECT id_spider, spider_name, user_agent, ip_info, forbidden
		FROM {db_prefix}spiders
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:items}',
		array(
			'sort' => $sort,
			'start' => $start,
			'items' => $items_per_page,
		)
	);
	$spiders = array();
	while ($row = $pmxcFunc['db_fetch_assoc']($request))
		$spiders[$row['id_spider']] = $row;
	$pmxcFunc['db_free_result']($request);

	return $spiders;
}

/**
 * Callback function for createList()
 * @return int The number of known spiders
 */
function list_getNumSpiders()
{
	global $pmxcFunc;

	$request = $pmxcFunc['db_query']('', '
		SELECT COUNT(*) AS num_spiders
		FROM {db_prefix}spiders',
		array(
		)
	);
	list ($numSpiders) = $pmxcFunc['db_fetch_row']($request);
	$pmxcFunc['db_free_result']($request);

	return $numSpiders;
}

/**
 * Here we can add, and edit, spider info!
 */
function EditSpider()
{
	global $context, $pmxcFunc, $pmxCacheFunc, $txt;

	// Some standard stuff.
	$context['id_spider'] = !empty($_GET['sid']) ? (int) $_GET['sid'] : 0;
	$context['page_title'] = $context['id_spider'] ? $txt['spiders_edit'] : $txt['spiders_add'];
	$context['sub_template'] = 'spider_edit';

	// Are we saving?
	if (!empty($_POST['save']))
	{
		checkSession();
		validateToken('admin-ses');

		$ips = array();
		// Check the IP range is valid.
		$ip_sets = explode(',', $_POST['spider_ip']);
		foreach ($ip_sets as $set)
		{
			$test = ip2range(trim($set));
			if (!empty($test))
				$ips[] = $set;
		}
		$ips = implode(',', $ips);

		// Goes in as it is...
		if ($context['id_spider'] && !empty($context['id_spider']))
			$pmxcFunc['db_query']('', '
				UPDATE {db_prefix}spiders
				SET spider_name = {string:spider_name}, user_agent = {string:spider_agent},
					ip_info = {string:ip_info}, forbidden = {int:forbidden}
				WHERE id_spider = {int:current_spider}',
				array(
					'current_spider' => $context['id_spider'],
					'spider_name' => $_POST['spider_name'],
					'spider_agent' => $_POST['spider_agent'],
					'ip_info' => $ips,
					'forbidden' => $_POST['forbidden']
				)
			);
		else
			$pmxcFunc['db_insert']('replace',
				'{db_prefix}spiders',
				array(
					'spider_name' => 'string', 'user_agent' => 'string', 'ip_info' => 'string', 'forbidden' => 'int'
				),
				array(
					$_POST['spider_name'], $_POST['spider_agent'], $ips, 0,
				),
				array('id')
			);

		$pmxCacheFunc['drop']('spider_search');
		recacheSpiderNames();

		redirectexit('action=admin;area=sengines;sa=spiders');
	}

	// The default is new.
	$context['spider'] = array(
		'id' => 0,
		'name' => '',
		'agent' => '',
		'ip_info' => '',
		'forbidden' => 0
	);

	// An edit?
	if ($context['id_spider'])
	{
		$request = $pmxcFunc['db_query']('', '
			SELECT id_spider, spider_name, user_agent, ip_info, forbidden
			FROM {db_prefix}spiders
			WHERE id_spider = {int:current_spider}',
			array(
				'current_spider' => $context['id_spider'],
			)
		);
		if ($row = $pmxcFunc['db_fetch_assoc']($request))
			$context['spider'] = array(
				'id' => $row['id_spider'],
				'name' => $row['spider_name'],
				'agent' => $row['user_agent'],
				'ip_info' => $row['ip_info'],
				'forbidden' => $row['forbidden']
			);
		$pmxcFunc['db_free_result']($request);
	}

	createToken('admin-ses');
}

/**
 * Do we think the current user is a spider?
 * @return int The ID of the spider if it's known or 0 if it isn't known/isn't a spider
 */
function SpiderCheck()
{
	global $modSettings, $pmxcFunc, $pmxCacheFunc, $boarddir;

	if (isset($_SESSION['id_robot']))
		unset($_SESSION['id_robot']);
	$_SESSION['robot_check'] = time();

	// We cache the spider data for one day if we can.
	$spider_data = $pmxCacheFunc['get']('spider_search');
	if($spider_data === null)
	{
		$request = $pmxcFunc['db_query']('', '
			SELECT id_spider, spider_name, user_agent, ip_info, forbidden
			FROM {db_prefix}spiders
			ORDER BY user_agent ASC',
			array()
		);
		$spider_data = array();
		while ($row = $pmxcFunc['db_fetch_assoc']($request))
			$spider_data[hash('md5', $row['user_agent'])] = $row;

		$pmxcFunc['db_free_result']($request);
	}

	// check if is a Bot
	$CurrentSpider = possible_is_bot();
	if(!empty($CurrentSpider))
	{
		// if this a new Spider, add this
		if(!in_array(hash('md5', $CurrentSpider), array_keys($spider_data)))
		{
			$newSpider = ucfirst(ltrim($CurrentSpider, '\/.|'));
			$newSpider = rtrim($newSpider, '\/.|0123456789');
			$pmxcFunc['db_insert']('insert',
				'{db_prefix}spiders',
				array(
					'spider_name' => 'string', 'user_agent' => 'string', 'ip_info' => 'string', 'forbidden' => 'int'
				),
				array(
					$newSpider, $CurrentSpider, '', 0
				),
				array('id_spider')
			);

			$request = $pmxcFunc['db_query']('', '
				SELECT id_spider, user_agent, ip_info, forbidden
				FROM {db_prefix}spiders
				ORDER BY user_agent ASC',
				array(
				)
			);
			$spider_data = array();
			while ($row = $pmxcFunc['db_fetch_assoc']($request))
				$spider_data[hash('md5', $row['user_agent'])] = $row;
			$pmxcFunc['db_free_result']($request);
		}
	}

	// cache the spiderlist
	$pmxCacheFunc['put']('spider_search', $spider_data, 86400);

	if (!empty($spider_data))
	{
		foreach ($spider_data as $key => $spider)
		{
			// User agent is easy.
			if (!empty($spider['user_agent']) && strpos($_SERVER['HTTP_USER_AGENT'], $spider['user_agent']) !== false)
				$_SESSION['id_robot'] = $spider['id_spider'];

			// IP stuff is harder.
			elseif ($_SERVER['REMOTE_ADDR'] && !empty($spider['ip_info']))
			{
				$ips = explode(',', $spider['ip_info']);
				foreach ($ips as $ip)
				{
					if ($ip === '')
						continue;

					$ip = ip2range($ip);
					if (!empty($ip))
					{
						if (inet_ptod($ip['low']) <= inet_ptod($_SERVER['REMOTE_ADDR']) && inet_ptod($ip['high']) >= inet_ptod($_SERVER['REMOTE_ADDR']))
							$_SESSION['id_robot'] = $spider['id_spider'];
					}
				}
			}

			if (isset($_SESSION['id_robot']))
				break;
		}
	}

	// if this Spider have a forbidden, inform him ;-)
	if(!empty($_SESSION['id_robot']) && !empty($spider_data[hash('md5', $CurrentSpider)]['forbidden']))
	{
		header($_SERVER["SERVER_PROTOCOL"]." 403 Forbidden");
		exit;
	}

	// If this is low server tracking then log the spider here as opposed to the main logging function.
	if (!empty($modSettings['spider_mode']) && $modSettings['spider_mode'] == 1 && !empty($_SESSION['id_robot']))
		logSpider(!empty($newSpider));
	elseif(isset($newSpider) && !empty($newSpider) && !empty($modSettings['spider_mode']) && !empty($_SESSION['id_robot']))
		logSpider(!empty($newSpider));

	return !empty($_SESSION['id_robot']) ? $_SESSION['id_robot'] : 0;
}

/**
 * Log the spider presence online.
 *
 * @todo Different file?
 */
function logSpider($isNew = false)
{
	global $pmxcFunc, $modSettings, $context;

	if (empty($modSettings['spider_mode']) || empty($_SESSION['id_robot']))
		return;

	// Attempt to update today's entry.
	if (!empty($isNew) || $modSettings['spider_mode'] == 1)
	{
		if(getREQcnt('action,board,topic', true) >= 0 && getREQcnt('login,signup,signup2') == 0)
		{
			$date = strftime('%Y-%m-%d', forum_time(false));
			$pmxcFunc['db_query']('', '
				UPDATE {db_prefix}log_spider_stats
				SET last_seen = {int:current_time}, page_hits = page_hits + 1
				WHERE id_spider = {int:current_spider}
					AND stat_date = {date:current_date}',
				array(
					'current_date' => $date,
					'current_time' => time(),
					'current_spider' => $_SESSION['id_robot'],
				)
			);

			// Nothing updated?
			if ($pmxcFunc['db_affected_rows']() == 0)
			{
				$pmxcFunc['db_insert']('ignore',
					'{db_prefix}log_spider_stats',
					array(
						'id_spider' => 'int', 'last_seen' => 'int', 'stat_date' => 'date', 'page_hits' => 'int',
					),
					array(
						$_SESSION['id_robot'], time(), $date, 1,
					),
					array('id_spider', 'stat_date')
				);
			}
		}
	}
	// If we're tracking better stats than track, better stats - we sort out the today thing later.
	else
	{
		if(getREQcnt('action,board,topic', true) >= 0 && getREQcnt('login,signup,signup2,spidertest') == 0)
		{
			if ($modSettings['spider_mode'] > 2)
			{
				$url = $_GET + array('USER_AGENT' => $_SERVER['HTTP_USER_AGENT']);
				if(isset($context['session_var']))
					unset($url['sesc'], $url[$context['session_var']]);
				$url = json_encode($url, true);
			}
			else
				$url = '';

			$pmxcFunc['db_insert']('insert',
				'{db_prefix}log_spider_hits',
				array('id_spider' => 'int', 'log_time' => 'int', 'url' => 'string'),
				array($_SESSION['id_robot'], time(), $url),
				array()
			);
		}
	}
}

/**
 * This function takes any unprocessed hits and turns them into stats.
 */
function consolidateSpiderStats()
{
	global $pmxcFunc;

	$request = $pmxcFunc['db_query']('consolidate_spider_stats', '
		SELECT id_spider, MAX(log_time) AS last_seen, COUNT(*) AS num_hits
		FROM {db_prefix}log_spider_hits
		WHERE processed = {int:not_processed}
		GROUP BY id_spider, MONTH(log_time), DAYOFMONTH(log_time)',
		array(
			'not_processed' => 0,
		)
	);
	$spider_hits = array();
	while ($row = $pmxcFunc['db_fetch_assoc']($request))
		$spider_hits[] = $row;
	$pmxcFunc['db_free_result']($request);

	if (empty($spider_hits))
		return;

	// Attempt to update the master data.
	$stat_inserts = array();
	foreach ($spider_hits as $stat)
	{
		// We assume the max date is within the right day.
		$date = strftime('%Y-%m-%d', $stat['last_seen']);
		$pmxcFunc['db_query']('', '
			UPDATE {db_prefix}log_spider_stats
			SET page_hits = page_hits + {int:hits},
				last_seen = CASE WHEN last_seen > {int:last_seen} THEN last_seen ELSE {int:last_seen} END
			WHERE id_spider = {int:current_spider}
				AND stat_date = {date:last_seen_date}',
			array(
				'last_seen_date' => $date,
				'last_seen' => $stat['last_seen'],
				'current_spider' => $stat['id_spider'],
				'hits' => $stat['num_hits'],
			)
		);
		if ($pmxcFunc['db_affected_rows']() == 0)
			$stat_inserts[] = array($date, $stat['id_spider'], $stat['num_hits'], $stat['last_seen']);
	}

	// New stats?
	if (!empty($stat_inserts))
		$pmxcFunc['db_insert']('ignore',
			'{db_prefix}log_spider_stats',
			array('stat_date' => 'date', 'id_spider' => 'int', 'page_hits' => 'int', 'last_seen' => 'int'),
			$stat_inserts,
			array('stat_date', 'id_spider')
		);

	// All processed.
	$pmxcFunc['db_query']('', '
		UPDATE {db_prefix}log_spider_hits
		SET processed = {int:is_processed}
		WHERE processed = {int:not_processed}',
		array(
			'is_processed' => 1,
			'not_processed' => 0,
		)
	);
}

/**
 * See what spiders have been up to.
 */
function SpiderLogs()
{
	global $context, $txt, $sourcedir, $scripturl, $pmxcFunc, $modSettings;

	// Load the template and language just incase.
	loadLanguage('Search');
	loadTemplate('ManageSearch');

	// Did they want to delete some entries?
	if ((!empty($_POST['delete_entries']) && isset($_POST['older'])) || !empty($_POST['removeAll']))
	{
		checkSession();
		validateToken('admin-sl');

		if (!empty($_POST['delete_entries']) && isset($_POST['older']))
		{
			$deleteTime = time() - (((int)$_POST['older']) * 24 * 60 * 60);

			// Delete the entires.
			$pmxcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_spider_hits
			WHERE log_time < {int:delete_period}',
				array(
					'delete_period' => $deleteTime,
				)
			);
		}
		else
		{
			// Deleting all of them
			$pmxcFunc['db_query']('', '
			TRUNCATE TABLE {db_prefix}log_spider_hits',
				array()
			);
		}
	}

	$listOptions = array(
		'id' => 'spider_logs',
		'items_per_page' => $modSettings['defaultMaxListItems'],
		'title' => $txt['spider_logs'],
		'no_items_label' => $txt['spider_logs_empty'],
		'base_href' => $context['admin_area'] == 'sengines' ? $scripturl . '?action=admin;area=sengines;sa=logs' : $scripturl . '?action=admin;area=logs;sa=spiderlog',
		'default_sort_col' => 'log_time',
		'get_items' => array(
			'function' => 'list_getSpiderLogs',
		),
		'get_count' => array(
			'function' => 'list_getNumSpiderLogs',
		),
		'columns' => array(
			'name' => array(
				'header' => array(
					'value' => $txt['spider'],
				),
				'data' => array(
					'db' => 'spider_name',
				),
				'sort' => array(
					'default' => 's.spider_name',
					'reverse' => 's.spider_name DESC',
				),
			),
			'log_time' => array(
				'header' => array(
					'value' => $txt['spider_time'],
				),
				'data' => array(
					'function' => function ($rowData)
					{
						return timeformat($rowData['log_time']);
					},
					'style' => 'white-space:nowrap',
				),
				'sort' => array(
					'default' => 'sl.id_hit DESC',
					'reverse' => 'sl.id_hit',
				),
			),
			'viewing' => array(
				'header' => array(
					'value' => $txt['spider_viewing'],
				),
				'data' => array(
					'db' => 'url',
				),
			),
		),
		'form' => array(
			'token' => 'admin-sl',
			'href' => $scripturl . '?action=admin;area=sengines;sa=logs',
		),
		'additional_rows' => array(
			array(
				'position' => 'after_title',
				'value' => $txt['spider_logs_info'],
				'class' => '',
			),
			array(
				'position' => 'below_table_data',
				'value' => '<input type="submit" name="removeAll" value="' . $txt['spider_log_empty_log'] . '" data-confirm="' . $txt['spider_log_empty_log_confirm'] . '" class="button_submit you_sure">',
			),
		),
	);

	createToken('admin-sl');

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	// Now determine the actions of the URLs.
	if (!empty($context['spider_logs']['rows']))
	{
		$urls = array();

		// Grab the current /url.
		foreach ($context['spider_logs']['rows'] as $k => $row)
		{
			// Feature disabled?
			if (empty($row['data']['viewing']['value']) && isset($modSettings['spider_mode']) && $modSettings['spider_mode'] < 3)
				$context['spider_logs']['rows'][$k]['viewing']['value'] = '<em>' . $txt['spider_disabled'] . '</em>';
			else
				$urls[$k] = array($row['data']['viewing']['value'], -1);
		}

		// Now stick in the new URLs.
		require_once($sourcedir . '/Who.php');
		$urls = determineActions($urls, 'whospider_');
		foreach ($urls as $k => $new_url)
		{
			$context['spider_logs']['rows'][$k]['data']['viewing']['value'] = $new_url;
		}
	}

	$context['page_title'] = $txt['spider_logs'];
	$context['sub_template'] = 'show_spider_logs';
	$context['default_list'] = 'spider_logs';
}

/**
 * Callback function for createList()
 *
 * @param int $start The item to start with (for pagination purposes)
 * @param int $items_per_page How many items to show per page
 * @param string $sort A string indicating how to sort the results
 * @return array An array of spider log data
 */
function list_getSpiderLogs($start, $items_per_page, $sort)
{
	global $pmxcFunc;

	$request = $pmxcFunc['db_query']('', '
		SELECT sl.id_spider, sl.url, sl.log_time, s.spider_name
		FROM {db_prefix}log_spider_hits AS sl
			INNER JOIN {db_prefix}spiders AS s ON (s.id_spider = sl.id_spider)
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:items}',
		array(
			'sort' => $sort,
			'start' => $start,
			'items' => $items_per_page,
		)
	);
	$spider_logs = array();
	while ($row = $pmxcFunc['db_fetch_assoc']($request))
		$spider_logs[] = $row;
	$pmxcFunc['db_free_result']($request);

	return $spider_logs;
}

/**
 * Callback function for createList()
 * @return int The number of spider log entries
 */
function list_getNumSpiderLogs()
{
	global $pmxcFunc;

	$request = $pmxcFunc['db_query']('', '
		SELECT COUNT(*) AS num_logs
		FROM {db_prefix}log_spider_hits',
		array(
		)
	);
	list ($numLogs) = $pmxcFunc['db_fetch_row']($request);
	$pmxcFunc['db_free_result']($request);

	return $numLogs;
}

/**
 * Show the spider statistics.
 */
function SpiderStats()
{
	global $context, $txt, $sourcedir, $scripturl, $pmxcFunc, $modSettings;

	// Force an update of the stats every 60 seconds.
	if (!isset($_SESSION['spider_stat']) || $_SESSION['spider_stat'] < time() - 60)
	{
		consolidateSpiderStats();
		$_SESSION['spider_stat'] = time();
	}

	// Are we cleaning up some old stats?
	if (!empty($_POST['delete_entries']) && isset($_POST['older']))
	{
		checkSession();
		validateToken('admin-ss');

		$deleteTime = time() - (((int) $_POST['older']) * 24 * 60 * 60);

		// Delete the entires.
		$pmxcFunc['db_query']('', '
			DELETE FROM {db_prefix}log_spider_stats
			WHERE last_seen < {int:delete_period}',
			array(
				'delete_period' => $deleteTime,
			)
		);
	}

	// Get the earliest and latest dates.
	$request = $pmxcFunc['db_query']('', '
		SELECT MIN(stat_date) AS first_date, MAX(stat_date) AS last_date
		FROM {db_prefix}log_spider_stats',
		array(
		)
	);

	list ($min_date, $max_date) = $pmxcFunc['db_fetch_row']($request);
	$pmxcFunc['db_free_result']($request);

	$min_year = (int) substr($min_date, 0, 4);
	$max_year = (int) substr($max_date, 0, 4);
	$min_month = (int) substr($min_date, 5, 2);
	$max_month = (int) substr($max_date, 5, 2);

	// Prepare the dates for the drop down.
	$date_choices = array();
	for ($y = $min_year; $y <= $max_year; $y++)
		for ($m = 1; $m <= 12; $m++)
		{
			// This doesn't count?
			if ($y == $min_year && $m < $min_month)
				continue;
			if ($y == $max_year && $m > $max_month)
				break;

			$date_choices[$y . $m] = $txt['months_short'][$m] . ' ' . $y;
		}

	// What are we currently viewing?
	$current_date = isset($_REQUEST['new_date']) && isset($date_choices[$_REQUEST['new_date']]) ? $_REQUEST['new_date'] : $max_date;

	// Prepare the HTML.
	$date_select = '
		' . $txt['spider_stats_select_month'] . ':&nbsp;
		<select name="new_date" onchange="document.spider_stat_list.submit();" style="float:right;">';

	if (empty($date_choices))
		$date_select .= '
			<option></option>';
	else
		foreach ($date_choices as $id => $text)
			$date_select .= '
			<option value="' . $id . '"' . ($current_date == $id ? ' selected' : '') . '>' . $text . '</option>';

	$date_select .= '
		</select>
		<noscript>
			<input type="submit" name="go" value="' . $txt['go'] . '" class="button_submit">
		</noscript>';

	// If we manually jumped to a date work out the offset.
	if (isset($_REQUEST['new_date']))
	{
		$date_query = sprintf('%04d-%02d-01', substr($_REQUEST['new_date'], 0, 4), substr($_REQUEST['new_date'], 4));
		$request = $pmxcFunc['db_query']('', '
			SELECT COUNT(*) AS offset
			FROM {db_prefix}log_spider_stats
			WHERE stat_date < {date:date_being_viewed}
			ORDER BY stat_date ASC',
			array(
				'date_being_viewed' => $date_query,
			)
		);
		list ($_REQUEST['start']) = $pmxcFunc['db_fetch_row']($request);
		$pmxcFunc['db_free_result']($request);
	}

	$listOptions = array(
		'id' => 'spider_stat_list',
		'title' => $txt['spider'] . ' ' . $txt['spider_stats'],
		'items_per_page' => $modSettings['defaultMaxListItems'],
		'base_href' => $scripturl . '?action=admin;area=sengines;sa=stats',
		'default_sort_col' => 'stat_date',
		'get_items' => array(
			'function' => 'list_getSpiderStats',
		),
		'get_count' => array(
			'function' => 'list_getNumSpiderStats',
		),
		'no_items_label' => $txt['spider_stats_no_entries'],
		'columns' => array(
			'stat_date' => array(
				'header' => array(
					'value' => $txt['date'],
				),
				'data' => array(
					'db' => 'stat_date',
				),
				'sort' => array(
					'default' => 'stat_date',
					'reverse' => 'stat_date DESC',
				),
			),
			'name' => array(
				'header' => array(
					'value' => $txt['spider_name'],
				),
				'data' => array(
					'db' => 'spider_name',
				),
				'sort' => array(
					'default' => 's.spider_name',
					'reverse' => 's.spider_name DESC',
				),
			),
			'page_hits' => array(
				'header' => array(
					'value' => $txt['spider_stats_page_hits'],
				),
				'data' => array(
					'db' => 'page_hits',
				),
				'sort' => array(
					'default' => 'ss.page_hits',
					'reverse' => 'ss.page_hits DESC',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=admin;area=sengines;sa=stats',
			'name' => 'spider_stat_list',
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => $date_select,
				'style' => 'text-align:end;padding-top:5px;padding-bottom:10px;display:flow-root;float:none;',
			),
		),
	);

	createToken('admin-ss');

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	$context['sub_template'] = 'show_spider_stats';
	$context['default_list'] = 'spider_stat_list';
}

/**
 * Callback function for createList()
 * Get a list of spider stats from the log_spider table
 *
 * @param int $start The item to start with (for pagination purposes)
 * @param int $items_per_page The number of items to show per page
 * @param string $sort A string indicating how to sort the results
 * @return array An array of spider statistics info
 */
function list_getSpiderStats($start, $items_per_page, $sort)
{
	global $pmxcFunc, $modSettings;

	$request = $pmxcFunc['db_query']('', '
		SELECT ss.id_spider, ss.stat_date, ss.page_hits, s.spider_name
		FROM {db_prefix}log_spider_stats AS ss
			INNER JOIN {db_prefix}spiders AS s ON (s.id_spider = ss.id_spider)
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:items}',
		array(
			'sort' => $sort,
			'start' => $start,
			'items' => $items_per_page,
		)
	);
	$spider_stats = array();
	while ($row = $pmxcFunc['db_fetch_assoc']($request))
	{
		$d = explode('-', $row['stat_date']);
		$row['stat_date'] = sprintf($modSettings['stats_format'], $d[0], $d[1], $d[2]);
		$spider_stats[] = $row;
	}
	$pmxcFunc['db_free_result']($request);

	return $spider_stats;
}

/**
 * Callback function for createList()
 * Get the number of spider stat rows from the log spider stats table
 *
 * @return int The number of rows in the log_spider_stats table
 */
function list_getNumSpiderStats()
{
	global $pmxcFunc;

	$request = $pmxcFunc['db_query']('', '
		SELECT COUNT(*) AS num_stats
		FROM {db_prefix}log_spider_stats',
		array(
		)
	);
	list ($numStats) = $pmxcFunc['db_fetch_row']($request);
	$pmxcFunc['db_free_result']($request);

	return $numStats;
}

/**
 * Recache spider names?
 */
function recacheSpiderNames()
{
	global $pmxcFunc;

	$request = $pmxcFunc['db_query']('', '
		SELECT id_spider, spider_name
		FROM {db_prefix}spiders',
		array()
	);
	$spiders = array();
	while ($row = $pmxcFunc['db_fetch_assoc']($request))
		$spiders[$row['id_spider']] = $row['spider_name'];
	$pmxcFunc['db_free_result']($request);

	updateSettings(array('spider_name_cache' => json_encode($spiders, true)));
}

?>