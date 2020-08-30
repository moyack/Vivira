<?php
/**
 * This file has all the SEF (Search Engine Friendly) functions.
 *
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * @version 1.41
 *
 * Developer of the Original Code is Matt Zuba.
 * License: MPL 1.1
*/

if (!defined('PMX'))
	die('No direct access...');

/*************************************
* Initiate the SEF enging, then convert
* called after: integrate_pre_load
***************************************/
function pmxsef_convertSEF()
{
	global $boardurl, $modSettings, $scripturl, $cache_enable, $pmxSEF, $pmxCacheFunc;

	// if cache disabled, disable SEF
	if(empty($cache_enable))
		$modSettings['sef_enabled'] = 0;

	if(empty($modSettings['sef_enabled']) || PMX == 'SSI')
	{
		if(isset($_GET['sef']))
			unset($_GET['sef']);
		return;
	}

	// internal calls
	if(isset($_GET['jscook']) || isset($_GET['ts']))
		return;

	pmxsef_LoadSettings();

	// Make sure we know the URL of the current request.
	parse_str(preg_replace('~&(\w+)(?=&|$)~', '&$1=', strtr(str_replace('sef=', '', $_SERVER['QUERY_STRING']), array(';?' => '&', '/;' => '/', ';' => '&', '%00' => '', "\0" => ''))), $_GET);

	$scripturl = $boardurl . '/index.php';
	if(empty($_SERVER['REQUEST_URI']))
		$_SERVER['REQUEST_URL'] = $scripturl . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
	elseif(preg_match('~^([^/]+//[^/]+)~', $scripturl, $match) == 1)
		$_SERVER['REQUEST_URL'] = $match[1] . $_SERVER['REQUEST_URI'];
	else
		$_SERVER['REQUEST_URL'] = $_SERVER['REQUEST_URI'];

	// replace a simple domain.tld/? to /index.php?
	if(strpos($_SERVER['REQUEST_URL'], 'index.php') === false && strpos($_SERVER['REQUEST_URL'], $boardurl .'/?') !== false)
		$_SERVER['REQUEST_URL'] = str_replace($boardurl .'/?', $scripturl .'?', $_SERVER['REQUEST_URL']);

	// redirect on bord/topic (like called from external), return if is a ignore action, convert other
	if(strpos($_SERVER['REQUEST_URL'], $scripturl) !== false)
	{
		if(isset($_GET['action']) && in_array($_GET['action'], $pmxSEF['settings']['ignoreactions']))
			return;

		// topic or board requested .. redirect
		if(!isset($_GET['action']) && (isset($_GET['board']) || isset($_GET['topic'])))
			redirectexit($_SERVER['REQUEST_URL']);

		// else convert the query
		$_SERVER['REQUEST_URL'] = create_sefurl($_SERVER['REQUEST_URL']);
	}

	// special handling for likes
	$_SERVER['REQUEST_URL'] = preg_replace(array('~;js=~', '~\?_=~'), array('js/', '/_/'), $_SERVER['REQUEST_URL']);

	// convert the SEF query
 	$_GET = pmxsef_query(rawurldecode(ltrim(str_replace($boardurl, '', $_SERVER['REQUEST_URL']), '/')));
	$_SERVER['QUERY_STRING'] = pmxsef_build_query($_GET);
	if(isset($_REQUEST['sef']))
		unset($_REQUEST['sef']);
	$_REQUEST = array_merge($_REQUEST, $_GET);

	// check if a topic subject changed
	if(isset($_GET['action']) && in_array($_GET['action'], array('post2', 'jsmodify')) && !empty($_POST) && !isset($_POST['preview']) && isset($pmxSEF['TopicNameList'][$_POST['topic']]))
	{
		$temp = explode('/', $pmxSEF['TopicNameList'][$_POST['topic']]);
		$pmxSEF['TopicNameList'][$_POST['topic'] .'-mod'] = CheckDupe($temp[0] .'/', $_POST['subject'], array('Topic', 'Board', 'Categorie', 'User', 'Page', 'Art', 'Cat'));
		$pmxCacheFunc['put']('sef_topiclist', $pmxSEF['TopicNameList'], 43200);
	}
}

/**************************************
/* Load the SEF settings settings
***************************************/
function pmxsef_LoadSettings()
{
	global $pmxSEF, $pmxCacheFunc, $modSettings;

	if(($pmxSEF['settings'] = $pmxCacheFunc['get']('sef_settings')) === null)
	{
		$pmxSEF['settings'] = array(
			'actions' => array_unique(explode(',', $modSettings['sef_actions'])),
			'ignoreactions' => array_unique(array_merge(array('admin', 'portal', 'viewpmxfile', 'uploadAttach', 'verificationcode'), explode(',', $modSettings['sef_ignoreactions']))),
			'stripchars' => base64_encode($modSettings['sef_stripchars']),
			'spacechar' => trim($modSettings['sef_spacechar']),
			'lowercase' => $modSettings['sef_lowercase'],
			'autosave' => $modSettings['sef_autosave'],
		);
		$pmxCacheFunc['put']('sef_settings', $pmxSEF['settings'], 86400);
	}
	$pmxSEF['settings']['stripchars'] = str_split(str_replace('-', '', base64_decode($pmxSEF['settings']['stripchars'])));
	$pmxSEF['settings']['allactions'] = array_filter(array_unique(array_merge($pmxSEF['settings']['actions'], $pmxSEF['settings']['ignoreactions'])));
	$modSettings['queryless_urls'] = 0;
}

/**************************************
* convert the requested SEF url
* called from pmxsef_convertSEF
***************************************/
function pmxsef_query($query)
{
	global $boardurl, $sourcedir, $modSettings, $pmxSEF;

	$querystring = $queryfragment = array();

	if(!empty($query) && $query != '/')
	{
		// cleanup the url
		$url_array = explode('/', trim($query, '/'));

		// check all the actions
		if(($act = array_intersect($url_array, $pmxSEF['settings']['allactions'])) !== array() && array_intersect($url_array, array('category', 'article', 'pages')) == array())
		{
			$querystring['action'] = array_values($act)[0];
			array_splice($url_array, array_keys($act)[0], 1);

			// if is a reminder / activate action?
			if(($querystring['action'] == 'profile' && array_intersect($url_array, array('popup', 'showalerts', 'alerts_popup', 'alerts', 'notification', 'area')) == array()) || ($querystring['action'] == 'reminder' && empty($_POST)) || $querystring['action'] == 'activate')
			{
				if(empty($pmxSEF['UserNameList']))
					getUserNameList();

				if(($user = array_search(current($url_array), $pmxSEF['UserNameList'], true)) !== false)
					$querystring['u'] = $user;
				else
					$querystring['u'] = getUserID(current($url_array));

				array_shift($url_array);
			}

			// if is a dlattach action?
			if($querystring['action'] == 'dlattach')
			{
				if(($isAtt = array_search('attach', $url_array, true)) !== false)
				{
					$querystring[$url_array[$isAtt]] = $url_array[$isAtt +1];
					array_splice($url_array, $isAtt, 2);

					// image attach ?
					if($url_array[$isAtt] == 'image')
					{
						$querystring[$url_array[$isAtt]] = '';
						array_splice($url_array, $isAtt, 1);
					}

					// any other attach
					elseif($url_array[$isAtt] == 'type')
					{
						$querystring[$url_array[$isAtt]] = $url_array[$isAtt +1];
						array_splice($url_array, $isAtt, 2);
						if($url_array[$isAtt] == 'file')
						{
							$querystring[$url_array[$isAtt]] = '';
							array_splice($url_array, $isAtt, 1);
						}
					}
				}
			}

			// check sub-actions / area
			if(!empty($url_array)&& count($url_array) >= 2)
			{
				if(($subact = array_search('sa', $url_array, true)) !== false || ($subact = array_search('area', $url_array, true)) !== false)
				{
					$querystring[$url_array[$subact]] = $url_array[$subact +1];
					array_splice($url_array, $subact, 2);
				}
			}
		}

		if(!empty($url_array))
		{
			// forum categorie ?
			if(empty($pmxSEF['CategorieNameList']))
				getCategorieNameList();
	
			if(($value = array_search(current($url_array), $pmxSEF['CategorieNameList'], true)) !== false)
			{
				$querystring['c'] = $value;
				array_shift($url_array);
			}

			// not a category .. check board or topic
			else
			{
				$value = false;
				if(count($url_array) >= 1)
				{
					if(empty($pmxSEF['BoardNameList']))
						getBoardNameList();

					// is a board ?
					if(($boardID = array_search(current($url_array), $pmxSEF['BoardNameList'], true)) !== false)
					{
						$boardName = current($url_array);
						$page = isset($url_array[1]) && is_numeric($url_array[1]) ? $url_array[1] : '0';
						$querystring['board'] = $boardID .'.'. $page;
						array_splice($url_array, 0, 1 + intval(!empty($page)));

						// if a topic given ?
						if(count($url_array) >= 1)
						{
							if(empty($pmxSEF['TopicNameList']))
								getTopicNameList();

							if(($topicID = array_search($boardName .'/'. current($url_array), $pmxSEF['TopicNameList'], true)) === false)
							{
								// modificated topi subject ?
								if(strpos($topicID, '-mod') !== false)
									$topicID = str_replace('-mod', '', $topicID);

								// if directly called, get topics in the board
								getTopicNameList(array(), $boardID);
								$topicID = array_search($boardName .'/'. current($url_array), $pmxSEF['TopicNameList'], true);
							}

							// have a topic?
							if($topicID !== false)
							{
								$page = isset($url_array[1]) && preg_match('/msg([0-9]+)|[0-9]+/', $url_array[1], $msgPage) > 0 && $msgPage[0] == $url_array[1] ? strval($msgPage[0]) : '0';
								$querystring['topic'] = $topicID .'.'. $page;
								array_splice($url_array, 0, 1 + intval(!empty($page)));

								//finally remove the board id
 								unset($querystring['board']);
							}
						}

						// we have a xxxSEEN request?
						if(count($url_array) >= 1 && ($seen = array_intersect($url_array, array('topicseen', 'boardseen'))) !== array())
						{ 
							$querystring[array_values($seen)[0]] = true;
							array_splice($url_array, array_keys($seen)[0], 1);
						}
					}
				}
			}

			// we have username ?
			if(!empty($url_array) && !isset($querystring['u']))
			{
				if(empty($pmxSEF['UserNameList']))
					getUserNameList();

				if(($user = array_search(current($url_array), $pmxSEF['UserNameList'], true)) !== false)
				{
					$querystring['u'] = $user;
					array_shift($url_array);
				}
			}
		}

		// check Portal category, article, pages request
		if(!empty($url_array))
		{
			preg_match('~^(category|article|pages)$~', current($url_array), $match);
			if(!empty($match[1]) && count($url_array) >= 2)
			{
				// single page?
				if($match[1] == 'pages')
				{
					getPagesNameList();
					if(($page = array_search($url_array[1], $pmxSEF['PageNameList'], true)) !== false)
						$querystring['spage'] = $page;
					elseif(($page = array_search($url_array[1], array_keys($pmxSEF['PageNameList']), true)) !== false)
						$querystring['spage'] = array_keys($pmxSEF['PageNameList'])[$page];
					else
						$querystring['pmxerror'] = 'page';

					array_splice($url_array, 0, 2);
				}

				// category?
				elseif($match[1] == 'category')
				{
					getCatNameList();
					if(($cat = array_search($url_array[1], $pmxSEF['CatNameList'], true)) !== false)
						$querystring['cat'] = $cat;
					elseif(($cat = array_search($url_array[1], array_keys($pmxSEF['CatNameList']), true)) !== false)
						$querystring['cat'] = array_keys($pmxSEF['CatNameList'])[$cat];
					else
						$querystring['pmxerror'] = 'category';

					array_splice($url_array, 0, 2);
					if($cat !== false)
					{
						// child category and not page request?
						if(count($url_array) > 0 && current($url_array) != 'pgkey' && current($url_array) != 'article')
						{
							if(($child = array_search($url_array[0], $pmxSEF['CatNameList'], true)) === false)
							{
								$child = array_search($url_array[0], array_keys($pmxSEF['CatNameList']), true);
								if($child !== false)
									$child = array_keys($pmxSEF['CatNameList'])[$child];
							}
							if($child !== false)
							{
								$querystring['child'] = $child;
								array_splice($url_array, 0, 1);
							}
							else
							{
								$url_array = array();
								$querystring['pmxerror'] = 'category';
							}
						}
					}
					else
					{
						$url_array = array();
						$querystring['pmxerror'] = 'category';
					}
				}

				// check article
				if(current($url_array) == 'article' && count($url_array) >= 2)
				{
					getArtNameList();
					if(($art = array_search($url_array[1], $pmxSEF['ArtNameList'], true)) !== false)
					{
						$querystring['art'] = $art;
						array_splice($url_array, 0, 2);
					}
					elseif(($art = array_search($url_array[1], array_keys($pmxSEF['ArtNameList']), true)) !== false)
					{
						$querystring['art'] = array_keys($pmxSEF['ArtNameList'])[$art];
						array_splice($url_array, 0, 2);
					}
					else
					{
						$url_array = array();
						$querystring['pmxerror'] = 'article';
					}
				}
			}
		}

		// do the rest
		while(!empty($url_array))
			$querystring[array_shift($url_array)] = count($url_array) !== 0 ? array_shift($url_array) : '';
	}

	return $querystring;
}

/*******************************************
* convert redirected urls to SEF format
* called before: integrate_redirect
********************************************/
function pmxsef_Redirect(&$setLocation)
{
	global $scripturl, $pmxSEF, $pmxCacheFunc, $modSettings;

	if(empty($modSettings['sef_enabled']))
		return;

	// load settings
	if(!isset($pmxSEF['settings']))
		pmxsef_LoadSettings();

	// Only do this if it's an URL for this board
	if(strpos($setLocation, $scripturl) !== false)
	{
		$setLocation = create_sefurl($setLocation);

		// Check to see if we need to update the actions lists
		if(!empty($pmxSEF['settings']['autosave']) && count(explode(',', $modSettings['sef_actions'])) != count($pmxSEF['settings']['actions']))
		{
			$changeArray['settings']['sef_actions'] = implode(',', array_filter(array_unique($pmxSEF['settings']['actions'])));
			updateSettings($changeArray);
			$pmxCacheFunc['drop']('sef_settings');
		}
		$pmxSEF['redirect'] = true;
	}
}

/**********************************
* convert XML urls to SEF format
* called before: integrate_exit
***********************************/
function pmxsef_XMLOutput($do_footer)
{
	global $pmxSEF, $modSettings;

	if(empty($modSettings['sef_enabled']))
		return;

	if(empty($do_footer) && empty($pmxSEF['redirect']))
	{
		$temp = ob_get_contents();

		ob_end_clean();
		ob_start('ob_pmxsef');

		echo $temp;
	}
}

/********************************************
* convert eMail urls to SEF format
* called before: integrate_outgoing_email
*********************************************/
function pmxsef_EmailOutput(&$subject, &$message, &$header)
{
	global $modSettings;

	if(!empty($modSettings['sef_enabled']))
	{
		// load settings
		if(!isset($pmxSEF['settings']['allactions']))
			pmxsef_LoadSettings();

		// We're just fixing the subject and message
		$subject = ob_pmxsef($subject);
		$message = ob_pmxsef($message);
	}

	// We must return true, otherwise we fail!
	return true;
}

/********************************************
* convert urls to SEF format
* called before integrate_fix_url
*********************************************/
function pmxsef_fixurl($url)
{
	global $modSettings;

	if(empty($modSettings['sef_enabled']))
		return $url;

	$url = create_sefurl($url);
	return $url;
}

/**************************************************
* Convert all urls in the outbuffer to SEF format
* called after integrate_buffer
***************************************************/
function ob_pmxsef($buffer)
{
	global $scripturl, $boardurl, $pmxSEF, $pmxCacheFunc, $modSettings;

	if(isset($_REQUEST['jscook']) || empty($modSettings['sef_enabled']))
		return $buffer;

	// load settings
	if(empty($pmxSEF['settings']['allactions']))
		pmxsef_LoadSettings();

	// fix javascript and queryles url's
	$buffer = str_replace('/index.php\'+\'?', '/index.php?', $buffer);
	$buffer = str_replace($boardurl .'/?', $boardurl .'/index.php?', $buffer);

	// Get user..
	$matches = array();
	preg_match_all('~\b'. preg_quote($scripturl) .'.*?u=([0-9]+)~', $buffer, $matches);
	if(!empty($matches[1]))
		getUserNameList(array_values(array_unique($matches[1])));

	// Get all categories..
	getCategorieNameList();

	// Get all bords..
	getBoardNameList();

	// Get topics..
	$matches = array();
	preg_match_all('~\b' . preg_quote($scripturl) . '.*?topic=([0-9\.]+)~', $buffer, $matches);
	if(!empty($matches[1]))
		getTopicNameList(array_unique($matches[1]));

	// Do the rest of the URLs, skip admin urls
	$matches = array();
	preg_match_all('~\b('. preg_quote($scripturl) .'(?!\?action=admin)(?!\?action=portal)[-a-zA-Z0-9+&@#/%?=\~_|!:,.;\[\]]*[-a-zA-Z0-9+&@#/%=\~_|\[\]]?)([^-a-zA-Z0-9+&@#/%=\~_|])~', $buffer, $matches);
	if(!empty($matches[1]))
	{
		$replacements = array();
		foreach($matches[1] as $i => $url)
		{
			$replace = create_sefurl($url);
			if($url != $replace)
				$replacements[$matches[0][$i]] = $replace . str_replace(';', '', $matches[2][$i]);
		}
		$buffer = str_replace(array_keys($replacements), array_values($replacements), $buffer);
	}

	// Check if we need to update the actions lists
	if(!empty($pmxSEF['settings']['autosave']) && count(explode(',', $modSettings['sef_actions'])) != count($pmxSEF['settings']['actions']))
	{
		$changeArray['sef_actions'] = implode(',', array_filter(array_unique($pmxSEF['settings']['actions'])));
		updateSettings($changeArray);
		$pmxCacheFunc['drop']('sef_settings');
	}

	// done
	return $buffer;
}

/******************************************
* redirected for unknow urls
* called internal
*******************************************/
function pmxsef_redir_perm($url)
{
	redirectexit($url);
}

/************************************************
* convert urls to SEF format
* called by sef_fixRedirectUrl, ob_pmxsef
*************************************************/
function create_sefurl($url)
{
	global $boardurl, $modSettings, $pmxSEF;

	if(empty($modSettings['sef_enabled']))
		return $url;

	if(empty($pmxSEF['settings']['allactions']))
		pmxsef_LoadSettings();

	// Init..
	$sefstring = $sefstring1 = $sefstring2 = '';

	// Get the query string
	$params = array();
	$url_parts = parse_url($url);

	// security .. check illegal url's
	parse_str(!empty($url_parts['query']) ? preg_replace('~&(\w+)(?=&|$)~', '&$1=', strtr($url_parts['query'], array('&amp;' => '&', ';' => '&'))) : '', $params);
	if(!empty($params))
	{
		// check ingnore actions
		if(!empty($params['action']) && in_array($params['action'], $pmxSEF['settings']['ignoreactions']))
			return $url;

		// actions
		if(isset($params['action']))
		{
			if(!in_array($params['action'], array_merge($pmxSEF['settings']['actions'], array('theme', 'language'))))
			{
				preg_match('/[a-zA-Z0-9\_\-\.]+/', $params['action'], $action);
				if(!empty($action[0]))
					$pmxSEF['settings']['actions'][] = $action[0];
			}
			$sefstring .= $params['action'] .'/';
			unset($params['action']);
		}

		// categories
		if(isset($params['c']))
		{
			$sefstring .= getCategorieName($params['c']);
			unset($params['c']);
		}

		// boards
		elseif(isset($params['board']))
		{
			$sefstring .= getBoardName($params['board']);
			$brdID = explode('.', $params['board']);
			unset($params['board']);
		}

		// topics
		elseif(isset($params['topic']))
		{
			$sefstring .= getTopicName($params['topic']);
			unset($params['topic']);
		}

		// user
		if(isset($params['u']))
		{
			$sefstring .= ($params['u'] == 'all' ? $params['u'] .'/' : getUserName($params['u']));
			unset($params['u']);
		}

		// Portal category & article
		if(isset($params['cat']))
		{
			// root cat
			$sefstring .= getCatName($params['cat']);
			unset($params['cat']);

			// have child cat?
			if(isset($params['child']))
			{
				$sefstring .= getCatName($params['child'], true);
				unset($params['child']);
			}

			// have article?
			if(isset($params['art']))
			{
				$sefstring .= getArtName($params['art']);
				unset($params['art']);
			}
		}

		// Portal article?
		elseif(isset($params['art']))
		{
			$sefstring .= getArtName($params['art']);
			unset($params['art']);
		}

		// Portal pages request?
		elseif(isset($params['spage']))
		{
			$sefstring .= getPageName($params['spage']);
			unset($params['spage']);
		}

		// do the rest
		foreach($params as $key => $value)
		{
			if($key == 'start')
				$sefstring2 .= ($value != '' ? $key .'/'. $value .'/' : '');
			elseif(is_array($value))
				$sefstring1 .= $key .'['. key($value) .']/'. $value[key($value)] .'/';
			else
				$sefstring1 .= $key .'/'. ($key == 'subname' ? pmxsef_encode($value) : $value) .'/';
		}

		// Build the URL
		$sefstring .= $sefstring1 . $sefstring2;
	}
	return $boardurl .'/'. (!empty($sefstring) ? rtrim($sefstring, '/') .'/' : '') . (!empty($url_parts['fragment']) ? '#' . $url_parts['fragment'] : '');
}

/**
* convert all Portal single page titles to SEF
**/
function getPagesNameList()
{
	global $pmxSEF, $pmxcFunc, $pmxCacheFunc, $language;

	if(empty($pmxSEF['PageNameList']))
		$pmxSEF['PageNameList'] = $pmxCacheFunc['get']('sef_pagelist');

	if(empty($pmxSEF['PageNameList']))
	{
		$pmxSEF['PageNameList'] = array();

		$request = $pmxcFunc['db_query']('', '
			SELECT id, config
			FROM {db_prefix}portal_blocks
			WHERE side = {string:pages} AND active > 0',
			array('pages' => 'pages')
		);
		while ($row = $pmxcFunc['db_fetch_assoc']($request))
		{
			$title = getCustomTitle($row, 'Pages', $language);
			$pmxSEF['PageNameList'][array_keys($title)[0]] = array_values($title)[0];
		}
		$pmxcFunc['db_free_result']($request);

		$pmxCacheFunc['put']('sef_pagelist', $pmxSEF['PageNameList'], 86400);
	}
}

/**
* get a SEF Portal Singe Page name
* called from: create_sefurl
**/
function getPageName($name)
{
	global $pmxSEF;

	getPagesNameList();

	if(!empty($pmxSEF['PageNameList'][$name]))
		return 'pages/'. $pmxSEF['PageNameList'][$name] .'/';
	else
		return 'pmxerror/page/';
}

/**
* convert all Portal Category Titles to SEF
* called from: getCatName, pmxsef_convertQuery
**/
function getCatNameList()
{
	global $pmxSEF, $pmxcFunc, $pmxCacheFunc, $language;

	if(empty($pmxSEF['CatNameList']))
		$pmxSEF['CatNameList'] = $pmxCacheFunc['get']('sef_catlist');

	if(empty($pmxSEF['CatNameList']))
	{
		$pmxSEF['CatNameList'] = array();

		$request = $pmxcFunc['db_query']('', '
			SELECT id, name, config
			FROM {db_prefix}portal_categories
			ORDER by catorder',
			array()
		);
		while ($row = $pmxcFunc['db_fetch_assoc']($request))
		{
			$title = getCustomTitle($row, 'Cat', $language);
			$pmxSEF['CatNameList'][array_keys($title)[0]] = array_values($title)[0];
		}
		$pmxcFunc['db_free_result']($request);

		$pmxCacheFunc['put']('sef_catlist', $pmxSEF['CatNameList'], 86400);
	}
}

/**
* get the SEF Portal Category name for $catname
* called from: create_sefurl
**/
function getCatName($name, $isChild = false)
{
	global $pmxSEF;

	getCatNameList();

	if(!empty($pmxSEF['CatNameList'][$name]))
		return (empty($isChild) ? 'category/' : '') . $pmxSEF['CatNameList'][$name] .'/';
	else
		return 'pmxerror/category/';
}

/**
* convert all Portal articles to SEF
* called from: getArtName
**/
function getArtNameList()
{
	global $pmxSEF, $pmxcFunc, $pmxCacheFunc, $language;

	$pmxSEF['ArtNameList'] = $pmxCacheFunc['get']('sef_artlist');
	if(empty($pmxSEF['ArtNameList']))
	{
		$pmxSEF['ArtNameList'] = array();

		$request = $pmxcFunc['db_query']('', '
			SELECT name, config
			FROM {db_prefix}portal_articles
			WHERE active > 0 AND approved > 0',
			array()
		);
		while ($row = $pmxcFunc['db_fetch_assoc']($request))
		{
			$title = getCustomTitle($row, 'Art', $language);
			$pmxSEF['ArtNameList'][array_keys($title)[0]] = array_values($title)[0];
		}
		$pmxcFunc['db_free_result']($request);

		$pmxCacheFunc['put']('sef_artlist', $pmxSEF['ArtNameList'], 86400);
	}
}

/**
* get a SEF Article name
* called from: create_sefurl
**/
function getArtName($name)
{
	global $pmxSEF;

	getArtNameList();

	if(!empty($pmxSEF['ArtNameList'][$name]))
		return 'article/'. $pmxSEF['ArtNameList'][$name] .'/';
	else
		return 'pmxerror/article/';
}

/**
* get the SEF Categorie name for id
* called from: create_sefurl
**/
function getCategorieName($id)
{
	global $pmxSEF;

	if(!empty($id))
	{
		getCategorieNameList();

		if(!empty($pmxSEF['CategorieNameList'][$id]))
			return $pmxSEF['CategorieNameList'][$id] .'/';
	}
	return '';
}

/**
* convert categorie names to SEF
* called from: getCategorieName, pmxsef_query
**/
function getCategorieNameList()
{
	global $pmxSEF, $pmxcFunc, $pmxCacheFunc;

	$pmxSEF['CategorieNameList'] = $pmxCacheFunc['get']('sef_categorielist');
	if(empty($pmxSEF['CategorieNameList']))
	{
		$request = $pmxcFunc['db_query']('', '
			SELECT id_cat, name
			FROM {db_prefix}categories',
			array()
		);
		while ($row = $pmxcFunc['db_fetch_assoc']($request))
			$pmxSEF['CategorieNameList'][$row['id_cat']] = CheckDupe('', $row['name'], array('Categorie', 'Board', 'Topic', 'User', 'Page', 'Art', 'Cat'));

		$pmxcFunc['db_free_result']($request);
		$pmxCacheFunc['put']('sef_categorielist', $pmxSEF['CategorieNameList'], 86400);
	}
}

/**
* convert board names to SEF
* called from: getBoardName, pmxsef_query
**/
function getBoardNameList()
{
	global $pmxSEF, $pmxcFunc, $pmxCacheFunc;

	$pmxSEF['BoardNameList'] = $pmxCacheFunc['get']('sef_boardlist');
	if(empty($pmxSEF['BoardNameList']))
	{
		$request = $pmxcFunc['db_query']('', '
			SELECT id_board, name
			FROM {db_prefix}boards',
			array()
		);
		while ($row = $pmxcFunc['db_fetch_assoc']($request))
			$pmxSEF['BoardNameList'][$row['id_board']] = CheckDupe('', $row['name'], array('Board', 'Categorie', 'Topic', 'User', 'Page', 'Art', 'Cat'));

		$pmxcFunc['db_free_result']($request);
		$pmxCacheFunc['put']('sef_boardlist', $pmxSEF['BoardNameList'], 86400);
	}
}

/**
* get the SEF board name for id
* called from: create_sefurl
**/
function getBoardName($id)
{
	global $pmxSEF;

	if(!empty($id))
	{
		@list($sefboard, $sefpage) = explode('.', (strpos($id, '.') === false ? $id .'.0' : $id));
		getBoardNameList();

		if(!empty($pmxSEF['BoardNameList'][$sefboard]))
			return $pmxSEF['BoardNameList'][$sefboard] .(!empty($sefpage) ? '/'. str_replace('\\', '', $sefpage) : '') .'/';
	}
	return '';
}

/**
* convert topic subjects to SEF
* called from: getTopicName, create_sefurl
**/
function getTopicNameList($seftopics = array(), $boardID = null)
{
	global $pmxSEF, $pmxcFunc, $pmxCacheFunc, $boarddir;

	// make integers
	array_walk($seftopics, function(&$v, $k){$v = intval(trim($v));});

	$pmxSEF['TopicNameList'] = $pmxCacheFunc['get']('sef_topiclist');
	if(empty($pmxSEF['TopicNameList']))
		$pmxSEF['TopicNameList'] = array();

	if(!empty($boardID))
	{
		$board = getBoardName($boardID);
		$request = $pmxcFunc['db_query']('', '
			SELECT t.id_topic, m.subject, t.id_board
			FROM {db_prefix}topics AS t
			INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			WHERE t.id_board = {int:board}',
			array('board' => $boardID)
		);
		while($row = $pmxcFunc['db_fetch_assoc']($request))
		{
			if(empty($pmxSEF['TopicNameList'][$row['id_topic']]))
				$pmxSEF['TopicNameList'][$row['id_topic']] = CheckDupe($board, $row['subject'], array('Topic', 'Board', 'Categorie', 'User', 'Page', 'Art', 'Cat'));
		}
		$pmxcFunc['db_free_result']($request);
		$pmxCacheFunc['put']('sef_topiclist', $pmxSEF['TopicNameList'], 86400);
	}
	else
	{
		$notcached = array_diff($seftopics, array_keys($pmxSEF['TopicNameList']));
		if(!empty($notcached))
		{
			$boardID = '';
			$request = $pmxcFunc['db_query']('', '
				SELECT t.id_topic, m.subject, t.id_board
				FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
				WHERE t.id_topic IN ({array_int:topics})
				ORDER by t.id_board',
				array('topics' => $notcached)
			);
			while($row = $pmxcFunc['db_fetch_assoc']($request))
			{
				if($boardID != $row['id_board'])
				{
					$boardID = $row['id_board'];
					$board = getBoardName($row['id_board']);
				}
				$pmxSEF['TopicNameList'][$row['id_topic']] = CheckDupe($board, $row['subject'], array('Topic', 'Board', 'Categorie', 'User', 'Page', 'Art', 'Cat'));
			}
			$pmxcFunc['db_free_result']($request);
			$pmxCacheFunc['put']('sef_topiclist', $pmxSEF['TopicNameList'], 86400);
		}
	}
}

/**
* get the SEF topic name for id
* called from: ob_pmxsef, create_sefurl
**/
function getTopicName($id)
{
	global $pmxSEF;

	if(!empty($id))
	{
		$id = (strpos($id, '.') === false ? $id .'.0' : $id);
		@list($seftopic, $sefpage) = explode('.', $id);
		getTopicNameList(array($seftopic));

		if(!empty($pmxSEF['TopicNameList'][$seftopic]))
			return $pmxSEF['TopicNameList'][$seftopic] . (!empty($sefpage) ? '/'. $sefpage : '') .'/';
	}
	return '';
}

/**
* convert user real name to SEF
* called from: pmxsef_query
**/
function getUserNameList($user = array())
{
	global $pmxSEF, $pmxcFunc, $pmxCacheFunc;

	// Make interger
	foreach($user as $i => $uid)
		$user[$i] = intval($uid);

	$pmxSEF['UserNameList'] = $pmxCacheFunc['get']('sef_userlist');
	if(empty($pmxSEF['UserNameList']))
	{
		$pmxSEF['UserNameList'] = array();
		$notcached = $user;
	}
	else
		$notcached = array_diff($user, array_keys($pmxSEF['UserNameList']));

	if(!empty($notcached))
	{
		$request = $pmxcFunc['db_query']('', '
			SELECT id_member, real_name
			FROM {db_prefix}members
			WHERE id_member IN ({array_int:members})',
			array('members' => $notcached)
		);

		while($row = $pmxcFunc['db_fetch_assoc']($request))
			$pmxSEF['UserNameList'][$row['id_member']] = CheckDupe('', $row['real_name'], array('User', 'Topic', 'Board', 'Categorie', 'Page', 'Art', 'Cat'));

		$pmxcFunc['db_free_result']($request);
		$pmxCacheFunc['put']('sef_userlist', $pmxSEF['UserNameList'], 86400);
	}
}

/**
* get the SEF user name for id
* called from: create_sefurl
**/
function getUserName($id)
{
	global $pmxSEF;

	if(!empty($id))
	{
		getUserNameList(array($id));

		if(!empty($pmxSEF['UserNameList'][$id]))
			return $pmxSEF['UserNameList'][$id] .'/';
	}
	return '';
}

/**
* Find the userID for a UserName
* called from: create_sefurl (action = reminder)
**/
function getUserID($user_name)
{
	global $pmxSEF, $pmxcFunc, $pmxCacheFunc;

	$result = '-1';
	$request = $pmxcFunc['db_query']('', '
		SELECT id_member, real_name
		FROM {db_prefix}members
		WHERE real_name LIKE {string:username}',
			array('username' => '%'. rtrim($user_name, '0123456789-') .'%')
	);
	while($row = $pmxcFunc['db_fetch_assoc']($request))
	{
		$name = CheckDupe('', $row['real_name'], array('Topic', 'Board', 'Categorie', 'Page', 'Art', 'Cat'));
		if(pmxsef_encode($user_name) == $name)
		{
			$pmxSEF['UserNameList'][$row['id_member']] = $name;
			$result = $row['id_member'];
			break;
		}
	}
	$pmxcFunc['db_free_result']($request);

	return $result;
}

/**
* convert a title to SEF
* called from: getPagesNameList, getCatNameList, getArtNameList
*/
function getCustomTitle($row, $type, $defaultlang)
{
	$title = '';
	if(!is_array($row['config']))
		$cfg = pmx_json_decode($row['config'], true);
	else
		$cfg = $row['config'];

	if(array_key_exists('title', $cfg))
	{
		if(isset($defaultlang) && is_string($defaultlang) && array_key_exists($defaultlang, $cfg['title']))
			$title = $cfg['title'][$defaultlang];
		elseif(array_key_exists('english', $cfg['title']))
			$title = $cfg['title']['english'];
	}
	if($type == 'Pages')
		return !empty($title) ? array($cfg['pagename'] => pmxsef_encode($title)) : array($cfg['pagename'] => pmxsef_encode($cfg['pagename']));
	else
		return !empty($title) ? array($row['name'] => pmxsef_encode($title)) : array($row['name'] => pmxsef_encode($row['name']));
}

/**
* Dupe check
* called from xxxNameList
*/
function CheckDupe($prefix, $search, $listNames)
{
	global $pmxSEF;

	$name = pmxsef_encode($search);
	$pmxSEF['#dupes#'] = !isset($pmxSEF['#dupes#']) ? 0 : $pmxSEF['#dupes#'];
	$dupes = array_search($name, $pmxSEF['settings']['allactions'], true) ? 1 : 0;

	foreach($listNames as $LName)
	{
		if(isset($pmxSEF[$LName .'NameList']) && is_array($pmxSEF[$LName .'NameList']) && count($pmxSEF[$LName .'NameList']) > 0)
		{
			$done = false;
			do {
				$searchName = $prefix . (empty($dupes) ? $name : $name . strval($dupes));
				if(($key = array_search($searchName, $pmxSEF[$LName . 'NameList'], true)) !== false)
					$dupes = ++$pmxSEF['#dupes#'];
				else
					$done = true;
			} while (!$done);
		}
	}
	$pmxSEF['#dupes#'] = $dupes > $pmxSEF['#dupes#'] ? $dupes : $pmxSEF['#dupes#'];
	return $prefix . (empty($dupes) ? $name : $name . strval($dupes));
}

/**
* build the "normal" querystring
* called from: pmxsef_query
**/
function pmxsef_build_query($data, $prefix = '', $sep = ';')
{
	$ret = array();
	foreach ((array) $data as $k => $v)
	{
		$k = urlencode($k);
		if(is_int($k) && !empty($prefix))
			$k = $prefix . $k;
		if(is_array($v) || is_object($v))
			array_push($ret, pmxsef_build_query($v, '', $sep));
		elseif($v == '')
			array_push($ret, $k);
		else
			array_push($ret, $k .'='. urlencode($v));
	}

	if(empty($sep))
		$sep = ini_get("arg_separator.output");

	return implode($sep, $ret);
}

/**
* convert a string for SEF url's
**/
function pmxsef_encode($string)
{
	global $modSettings, $sourcedir, $pmxSEF, $txt;
	static $utf8_db = array();

	$string = trim($string);
	if(empty($string))
		return '';

	$string = htmlspecialchars_decode($string, ENT_QUOTES);
	if(function_exists('mb_convert_encoding'))
		$string = mb_convert_encoding($string, 'UTF-8', mb_detect_encoding($string, 'UTF-8, ISO-8859-1, ISO-8859-15, Windows-1251, Windows-1252, Windows-1254', true));
	elseif(function_exists('iconv'))
		$string = iconv($char_set, 'UTF-8//TRANSLIT', $string);

	// convert
	$character = 0;
	$result = '';
	$length = strlen($string);
	$i = 0;

	while($i < $length)
	{
		$charInt = ord($string[$i++]);
		// normal Ascii character
		if(($charInt & 0x80) == 0)
			$character = $charInt;

		// Two byte unicode
		elseif(($charInt & 0xE0) == 0xC0)
		{
			$temp1 = ord($string[$i++]);
			if (($temp1 & 0xC0) != 0x80)
				$character = 63;
			else
				$character = ($charInt & 0x1F) << 6 | ($temp1 & 0x3F);
		}

		// Three byte ..
		elseif(($charInt & 0xF0) == 0xE0)
		{
			$temp1 = ord($string[$i++]);
			$temp2 = ord($string[$i++]);
			if (($temp1 & 0xC0) != 0x80 || ($temp2 & 0xC0) != 0x80)
				$character = 63;
			else
				$character = ($charInt & 0x0F) << 12 | ($temp1 & 0x3F) << 6 | ($temp2 & 0x3F);
		}

		// Four byte ..
		elseif(($charInt & 0xF8) == 0xF0)
		{
			$temp1 = ord($string[$i++]);
			$temp2 = ord($string[$i++]);
			$temp3 = ord($string[$i++]);
			if (($temp1 & 0xC0) != 0x80 || ($temp2 & 0xC0) != 0x80 || ($temp3 & 0xC0) != 0x80)
				$character = 63;
			else
				$character = ($charInt & 0x07) << 18 | ($temp1 & 0x3F) << 12 | ($temp2 & 0x3F) << 6 | ($temp3 & 0x3F);
		}

		// Thats wrong... use ?
		else
			$character = 63;

		// get the codepage for this character.
		$charBank = $character >> 8;
		if(!isset($utf8_db[$charBank]))
		{
			// Load up the codepage if it's not already in memory
			$cpFile = $sourcedir . '/sefcodepages/x' . sprintf('%02x', $charBank) . '.php';
			if(file_exists($cpFile))
				include_once($cpFile);
			else
				$utf8_db[$charBank] = array();
		}

		$finalChar = $character & 255;
		$result .= isset($utf8_db[$charBank][$finalChar]) ? $utf8_db[$charBank][$finalChar] : '';
	}

	// cleanup and insert spacechar
	$result = preg_replace('/['. preg_quote($pmxSEF['settings']['spacechar']) .']+/', ' ', trim(str_replace($pmxSEF['settings']['stripchars'], '', $result)));
	return preg_replace('~\s+~', $pmxSEF['settings']['spacechar'], (!empty($pmxSEF['settings']['lowercase']) ? strtolower($result) : $result));
}
?>