<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file LoadData.php
 * Subroutines for the Portal.
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class XmlElement
* XML elements for the RSS reader
*/
class XmlElement
{
	var $name;
	var $attributes;
	var $content;
	var $childs;
};

/**
* Get all data from a RSS feed url
* Returns a header array and a post array
*/
function getRSSfeedPosts(&$feedheader, $feedurl, $maxentrys, $resposetime)
{
	$feedpost = array();

	// get the xml file from url
	$XmlRoot = ParseXmlurl($feedurl, $resposetime);
	if(!empty($XmlRoot))
	{
		$feedtyp = strtolower($XmlRoot->name);

		if(strtolower($feedtyp) == 'feed')
		{
			$Alnk = GetFirstChildByName($XmlRoot, 'link');
			$hLink = (!empty($Alnk) ? GetAttribByName($Alnk, 'href') : '');

			$desc = GetFirstChildContentByName($XmlRoot, 'tagline');
			if(empty($desc))
				$desc = GetFirstChildContentByName($XmlRoot, 'subtitle');

			$feedheader = array(
				'title' => GetFirstChildContentByName($XmlRoot, 'title'),
				'link' => $hLink,
				'desc' => $desc,
				'author' => GetFirstChildContentByPath($XmlRoot, 'author/name'),
				'alink' => GetFirstChildContentByPath($XmlRoot, 'author/uri'),
			);

			// find / replace for atom date string
			$dtfnd = array('T', 'Z');
			$dtrep = array(' ', ' ');
		}
		else
		{
			$ttl = GetFirstChildContentByPath($XmlRoot, 'channel/ttl');
			if(GetFirstChildContentByPath($XmlRoot, 'channel/sy:updateperiod'))
			{
				$period = GetFirstChildContentByPath($XmlRoot, 'channel/sy:updateperiod');
				$freq = GetFirstChildContentByPath($XmlRoot, 'channel/sy:updatefrequency');
				if($period == 'hourly')
					$ttl = $freq * 60;
				if($period == 'daily')
					$ttl = $freq * (24*60);
			}
			$feedheader = array(
				'title' => GetFirstChildContentByPath($XmlRoot, 'channel/title'),
				'link' => GetFirstChildContentByPath($XmlRoot, 'channel/link'),
				'desc' => GetFirstChildContentByPath($XmlRoot, 'channel/description'),
				'ttl' => $ttl,
			);
		}

		if(strtolower($feedtyp) == 'rss')
			$elmlist = GetChildByPathAndName($XmlRoot, 'channel', 'item');
		else
			$elmlist = $XmlRoot->childs;

		if(!empty($elmlist))
		{
			// get all the items
			foreach ($elmlist as $elm)
			{
				// is a RSS or RDF feed ?
				if(strtolower($feedtyp) == 'rss' || strtolower($feedtyp) == 'rdf:rdf')
				{
					if(strtolower($feedtyp) == 'rdf:rdf' && strtolower($elm->name) != 'item')
						continue;

					$poster = GetFirstChildContentByName($elm, 'author');
					if(empty($poster))
						$poster = GetFirstChildContentByName($elm, 'dc:creator');

					$date = GetFirstChildContentByName($elm, 'pubDate');
					if(empty($date))
						$date = GetFirstChildContentByName($elm, 'dc:date');
					if(!empty($date))
						$date = htmlspecialchars(preg_replace('~<[^>]*>~i', '', timeformat(strtotime($date))));

					$category = GetFirstChildContentByName($elm, 'category');
					if(empty($category))
						$category = GetFirstChildContentByName($elm, 'dc:category');

					$subject = GetFirstChildContentByName($elm, 'subject');
					if(empty($subject))
						$subject = GetFirstChildContentByName($elm, 'title');

					$feedpost[] = array(
						'subject' => $subject,
						'slink' => GetFirstChildContentByName($elm, 'link'),
						'tlink' => '',
						'poster' => $poster,
						'plink' => '',
						'date' => $date,
						'category' => $category,
						'board' => '',
						'blink' => '',
						'message' => GetFirstChildContentByName($elm, 'description'),
						'contenc' => GetFirstChildContentByName($elm, 'content:encoded'),
					);
				}
				// or is a SMF/PMX type feed ?
				elseif(strtolower($feedtyp) == 'smf:xml-feed' || strtolower($feedtyp) == 'pmx:xml-feed')
				{
					$feedpost[] = array(
						'subject' => GetFirstChildContentByName($elm, 'subject'),
						'slink' => GetFirstChildContentByName($elm, 'link'),
						'tlink' => str_replace('.new#new', '.0', GetFirstChildContentByPath($elm, 'topic/link')),
						'poster' => GetFirstChildContentByPath($elm, 'poster/name'),
						'plink' => GetFirstChildContentByPath($elm, 'poster/link'),
						'date' => GetFirstChildContentByName($elm, 'time'),
						'category' => '',
						'board' => GetFirstChildContentByPath($elm, 'board/name'),
						'blink' => GetFirstChildContentByPath($elm, 'board/link'),
						'message' => GetFirstChildContentByName($elm, 'body'),
						'contenc' => '',
					);
				}
				// or is a Atom feed ?
				elseif(strtolower($feedtyp) == 'feed')
				{
					if(strtolower($elm->name) != 'entry')
						continue;

					$date = str_replace($dtfnd, $dtrep, GetFirstChildContentByName($elm, 'published'));
					if(!empty($date))
						$date = htmlspecialchars(preg_replace('~<[^>]*>~i', '', timeformat(strtotime($date))));

					$linkattr = GetFirstChildByName($elm, 'link');
					if(!empty($linkattr))
						$sLink = GetAttribByName($linkattr, 'href');
					$tLink = '';
					if(strpos($sLink, '.msg') !== false)
						$tLink = substr($sLink, 0, strpos($sLink, '.msg')) .'.0';

					$author = GetFirstChildContentByPath($elm, 'author/name');
					$alink = GetFirstChildContentByPath($elm, 'author/uri');
					$message = GetFirstChildContentByName($elm, 'summary');
					if(empty($message))
						$message = GetFirstChildContentByName($elm, 'content');

					$feedpost[] = array(
						'subject' => GetFirstChildContentByName($elm, 'title'),
						'slink' => $sLink,
						'tlink' => $tLink,
						'poster' => empty($author) ? $feedheader['author'] : $author,
						'plink' => empty($alink) ? $feedheader['alink'] : $alink,
						'date' => $date,
						'category' => GetFirstChildContentByPath($elm, 'category/label'),
						'board' => '',
						'blink' => '',
						'message' => $message,
						'contenc' => '',
					);
				}

				if(!empty($maxentrys))
				{
					$maxentrys--;
					if($maxentrys <= 0)
						break;
				}
			}
		}
	}
	return $feedpost;
}

/**
* Parse a xml stream
* Returns the xml content
*/
function ParseXml($xml)
{
	global $context;

	$encoding = in_array($context['character_set'], array('UTF-8', 'ISO-8859-1')) ? $context['character_set'] : '';
	$parser = xml_parser_create($encoding);
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, $xml, $tags);
	xml_parser_free($parser);

	$elements = array();
	$stack = array();
	foreach ($tags as $tag)
	{
		$index = count($elements);
		if(($tag['type'] == "complete") || ($tag['type'] == "open"))
		{
			$elements[$index] = new XmlElement;
			if(isset($tag['tag']))
				$elements[$index]->name = $tag['tag'];
			if(isset($tag['attributes']))
				$elements[$index]->attributes = $tag['attributes'];
			if(isset($tag['value']))
				$elements[$index]->content = $tag['value'];
			if($tag['type'] == "open")
			{
				$elements[$index]->childs = array();
				$stack[count($stack)] = &$elements;
				$elements = &$elements[$index]->childs;
			}
		}
		if($tag['type'] == "close")
		{
			$elements = &$stack[count($stack) - 1];
			unset($stack[count($stack) - 1]);
		}
	}
	return isset($elements[0]) ? $elements[0] : array();
}

/**
* Get Child By Path And Name
* Returns the child
*/
function GetChildByPathAndName($XmlRoot, $sPath, $sName)
{
	$aPath = preg_split('~\/~', $sPath, -1, PREG_SPLIT_NO_EMPTY);
	$oRes = array();
	$elm = $XmlRoot;
	if(!empty($sPath))
	{
		foreach ($aPath as $p)
		{
			$elm = GetFirstChildByName($elm, $p);
			if(empty($elm))
				return '';
		}
	}
	foreach ($elm->childs as $c)
	{
		if (strcasecmp($c->name, $sName) == 0)
			$oRes[count($oRes)] = $c;
	}
	return $oRes;
}

/**
* Get First ChildContent By Path
* Returns the content
*/
function GetFirstChildContentByPath($XmlRoot, $sPath)
{
	$elm = GetFirstChildByPath($XmlRoot, $sPath);
	if(!empty($elm))
		return $elm->content;
	else
		return '';
}

/**
* Get First ChildContent By Name
* Returns the content
*/
function GetFirstChildContentByName($oParent, $sName)
{
	$elm = GetFirstChildByName($oParent, $sName);
	if(!empty($elm))
		return $elm->content;
	else
		return '';
}

/**
* Get First Child By Path
* Returns the name
*/
function GetFirstChildByPath($XmlRoot, $sPath, $bCase = false)
{
	$aPath = preg_split('~\/~', $sPath, -1, PREG_SPLIT_NO_EMPTY);
	$elm = $XmlRoot;
	foreach ($aPath as $p)
	{
		$elm = GetFirstChildByName($elm, $p);
		if(empty($elm))
			return '';
	}
	return $elm;
}

/**
* Get First Child By Name
* Returns the name
*/
function GetFirstChildByName($oParent, $sName, $bCase = false)
{
	if(isset($oParent->childs) && count($oParent->childs) > 0)
	{
		foreach ($oParent->childs as $c)
			if(strcasecmp($c->name, $sName) == 0)
				return $c;
	}
	return '';
}

/**
* Get Attribute By Name
* Returns the name
*/
function GetAttribByName($XmlNode, $sName)
{
	$aAttributes = array_change_key_case($XmlNode->attributes, CASE_LOWER);
	if(isset($aAttributes[$sName]))
		return $aAttributes[$sName];
	else
		return '';
}

/**
* char separared string to Integer array
*/
function Pmx_StrToIntArray($value, $sepchr = ',')
{
	$result = array();
	if($value != '')
	{
		$result = preg_split('~'. preg_quote($sepchr) .'~', $value, -1, PREG_SPLIT_NO_EMPTY);
		array_walk($result, function(&$v,$k){$v = intval(trim($v));});
	}
	return $result;
}

/**
* char separared string to array
*/
function Pmx_StrToArray($value, $sepchr = ',', $MakeIndex = '')
{
	$result = array();
	if($value != '')
	{
		$result = preg_split('~'. preg_quote($sepchr) .'~', $value, -1, PREG_SPLIT_NO_EMPTY);
		array_walk($result, function(&$v,$k){$v = trim($v);});

		if(!empty($MakeIndex))
		{
			$residx = array();
			$res = array();
			foreach($result as $data)
			{
				$tmp = Pmx_StrToArray($data, $MakeIndex);
				 $res[] = $tmp[0];
				if(count($tmp) == 2 && empty($tmp[1]))
					$residx[] = $tmp[0];
			}
			$result = array($res, $residx);
		}
	}
	elseif(!empty($MakeIndex))
		$result = array($result, $result);

	return $result;
}

/**
* get innerpad value
*/
function Pmx_getInnerPad($value, $ofs = null)
{
	if(strpos($value, ',') === false)
		$result = array(abs($value), abs($value));
	else
		$result = Pmx_StrToArray($value);
	foreach($result as $k => $d)
		$result[$k] = abs($d);
	return is_null($ofs) ? $result : $result[$ofs];
}

/**
* get page, category or article title for who display
*/
function getWhoTitle($action)
{
	global $pmxcFunc, $scripturl, $txt;

	$acs = '';
	$result = '';

	if(isset($action['spage']))
	{
		$rqType = 'spage';
		$request = $pmxcFunc['db_query']('', '
			SELECT config, acsgrp
			FROM {db_prefix}portal_blocks
			WHERE side = {string:side} AND active > 0',
			array('side' => 'pages')
		);
		if($pmxcFunc['db_num_rows']($request) > 0)
		{
			while($row = $pmxcFunc['db_fetch_assoc']($request))
			{
				$cfg = pmx_json_decode($row['config'], true);
				if($cfg['pagename'] == $action['spage'])
				{
					$acs = $row['acsgrp'];
					break;
				}
				else
					unset($cfg);
			}
		}
		$pmxcFunc['db_free_result']($request);
	}

	elseif(isset($action['art']))
	{
		$rqType = 'art';
		$request = $pmxcFunc['db_query']('', '
			SELECT config, acsgrp
			FROM {db_prefix}portal_articles
			WHERE name = {string:reqname} AND active > 0 and approved > 0',
			array('reqname' => $action['art'])
		);
		if($pmxcFunc['db_num_rows']($request) > 0)
		{
			$row = $pmxcFunc['db_fetch_assoc']($request);
			$cfg = pmx_json_decode($row['config'], true);
			$acs = $row['acsgrp'];
		}
		$pmxcFunc['db_free_result']($request);
	}

	elseif(isset($action['child']))
	{
		$rqType = 'cat';
		$request = $pmxcFunc['db_query']('', '
			SELECT config, acsgrp
			FROM {db_prefix}portal_categories
			WHERE name = {string:reqname}',
			array('reqname' => $action['child'])
		);
		if($pmxcFunc['db_num_rows']($request) > 0)
		{
			$row = $pmxcFunc['db_fetch_assoc']($request);
			$cfg = pmx_json_decode($row['config'], true);
			$acs = $row['acsgrp'];
		}
		$pmxcFunc['db_free_result']($request);
	}

	elseif(isset($action['cat']))
	{
		$rqType = 'cat';
		$request = $pmxcFunc['db_query']('', '
			SELECT config, acsgrp
			FROM {db_prefix}portal_categories
			WHERE name = {string:reqname}',
			array('reqname' => $action['cat'])
		);
		if($pmxcFunc['db_num_rows']($request) > 0)
		{
			$row = $pmxcFunc['db_fetch_assoc']($request);
			$cfg = pmx_json_decode($row['config'], true);
			$acs = $row['acsgrp'];
		}
		$pmxcFunc['db_free_result']($request);
	}

	if(isset($cfg) && is_array($cfg))
	{
		$title = PortaMx_getTitle($cfg);
		if(empty($title))
			$title = htmlspecialchars($action[$rqType], ENT_QUOTES);

		if(allowPmxGroup($acs))
		{
			if($rqType == 'cat')
			{
				if(isset($action['child']))
					$result = sprintf($txt['pmx_who_cat'], '<a href="'. $scripturl .'?cat='. $action['cat'] .';child='. $action['child'] .'">'. $title .'</a>');
				else
					$result = sprintf($txt['pmx_who_cat'], '<a href="'. $scripturl .'?cat='. $action['cat'] .'">'. $title .'</a>');
			}
			elseif($rqType == 'art' && (isset($action['cat']) || isset($action['child'])))
			{
				if(isset($action['child']))
					$result = sprintf($txt['pmx_who_art'], '<a href="'. $scripturl .'?cat='. $action['cat'] .';child='. $action['child'] .';art='. $action['art'] .'">'. $title .'</a>');
				else
					$result = sprintf($txt['pmx_who_art'], '<a href="'. $scripturl .'?cat='. $action['cat'] .';art='. $action['art'] .'">'. $title .'</a>');
			}
			else
				$result = sprintf($txt['pmx_who_'. $rqType], '<a href="'. $scripturl .'?'. $rqType .'='. $action[$rqType] .'">'. $title .'</a>');
		}
		else
			$result = sprintf($txt['pmx_who_'. $rqType], $title);
	}
	return $result;
}

/**
* Get customer mpt files
*/
function PortaMx_getCustomCssDefs()
{
	global $context;

	$result = array();
	$dir = dir($context['pmx_customcssdir']);
	while($file = $dir->read())
	{
		if(substr($file, -4) == '.mpt')
		{
			$file = substr($file, 0, -4);
			$result[$file] = PortaMx_loadCustomCss($file);
			$result[$file]['file'] = $file;
		}
	}
	$dir->close();
	return $result;
}

/**
* Get customer mpt/css definitions
*/
function PortaMx_getCssDefs(&$css)
{
	// make array from def xml
	$result = array();
	$css = preg_replace('~/\*.*?\*/~s', '', $css);

	if(preg_match('~<class>(.*)<\/class>~s', $css, $match) > 0)
	{
		$data = ParseXml('<css>'. $match[1] .'</css>');
		$css = str_replace('}', "}\n\t", str_replace(array("\n", "\r", "\t"), array('', '', ' '), str_replace($match[0], '', $css)));
		$result['class'] = array();
		foreach($data->childs as $def)
		{
			$thmask = true;
			$cname = $def->name;
			if(!empty($def->attributes['theme']))
			{
				$ctheme = Pmx_StrToArray($def->attributes['theme']);
				if(!empty($ctheme))
				{
					while(!empty($thmask) && (list($d, $th) = pmx_each($ctheme)))
						$thmask = ($th{0} == '^' ? (substr($th, 1) == $settings['theme_id'] ? false : $thmask) : ($th == $settings['theme_id'] ? $thmask : false));
				}
				$result['class'][$cname] = (!empty($thmask) && !empty($ctheme) ? $def->content : '');
			}
			else
				$result['class'][$cname] = $def->content;
		}
	}
	return $result;
}

/**
* Load css or mpt files (mpt are converted if not cached)
* return true if css loaded, else false
*/
function PortaMx_loadCustomCss($cssfile, $addheader = false)
{
	global $context, $settings, $pmxCacheFunc;

	if(empty($cssfile))
		return array();

	// get css file timestamp
	$cssFileTime = (file_exists($context['pmx_customcssdir'] . $cssfile .'.mpt') ? filemtime($context['pmx_customcssdir'] . $cssfile .'.mpt') : 0);

	// cacheName
	$cachefile = $cssfile.'-mpt'. $settings['theme_id'];

	// css already loaded, unchanged and in cache?
	if(($css = $pmxCacheFunc['get']($cachefile, false)) !== null && $cssFileTime == $css['ftime'] && in_array($cachefile, $context['pmx_blockCSSfiles']))
		return $css['def'];

	elseif(!empty($cssFileTime))
	{
		// if cached ?
		if($css !== null)
		{
			// unchanged ?
			if($css['ftime'] == $cssFileTime)
			{
				if(!empty($addheader) && !in_array($cachefile, $context['pmx_blockCSSfiles']))
				{
					$context['pmx_blockCSSfiles'][] = $cachefile;
					addInlineCss('
	'. $css['data']);
				}
				// done
				return $css['def'];
			}
		}

		// not cached or file is changed
		$cssdata = file_get_contents($context['pmx_customcssdir'] . $cssfile .'.mpt');
		$result = PortaMx_getCssDefs($cssdata);
		$hasClass = 0;

		foreach($result['class'] as $k => $v)
			$hasClass += intval(!empty($v));

		$css = array(
			'ftime' => $cssFileTime,
			'file' => $cssfile,
			'def' => $result,
			'data' => (!empty($hasClass) ? $cssdata : '')
		);

		// convert css image pathes
		$tpath = pmx_parse_url($context['pmx_customcssurl'], PHP_URL_PATH);
		$css['data'] = str_replace('@@/', $tpath, $css['data']);

		// store in cache
		$pmxCacheFunc['put']($cachefile, $css, 86400, false);

		// put css on the header
		if(!empty($addheader))
		{
			$context['pmx_blockCSSfiles'][] = $cachefile;
			if(!empty($hasClass))
				addInlineCss('
	'. $css['data']);
		}

		// done
		return $css['def'];
	}
	else
		return array();
}

/**
/* Add inline css
**/
function PortaMx_addInlineCss($cssData)
{
	global $context;

	if(!isset($context['pmx']['customCSS']))
		$context['pmx']['customCSS'] = '';

	$context['pmx']['customCSS'] .= $cssData;
}

/**
* compress ccs data
*/
function PortaMx_compressCSS($cssdata)
{
	static $fnd = array('~/\*[^*]*\*+([^/][^*]*\*+)*/~', '~[\n\r]+~m', '~[\s\t]+~m', '~[\s]+\{~m', '~\{[\s]+~m', '~[\s]+\}~m', '~\}[\s]+~m', '~[\s]+\(~m', '~\([\s]+~m', '~[\s]+\)~m', '~\)[\s]+~m', '~[\s]+\,~m', '~\,[\s]+~m', '~[\s]+\:~m', '~\:[\s]+~m', '~[\s]+\;~m', '~\;[\s]+~m', '~\}~m', '~\}\n\}\n~m', '~\}\nto\{~m');
	static $repl = array('', '', ' ', '{', '{', '}', '}', ' (', '(', ')', ') ', ',', ',', ':', ':', ';', ';', "}\n\t\t", "}}\n\t\t", "}to{");

	return trim(preg_replace($fnd, $repl, $cssdata), "\n\r\t");
}

function PortaMx_compressJS($data)
{
	global $context;

	if(file_exists($context['pmx_sourcedir'] .'Compress.php'))
	{
		require_once($context['pmx_sourcedir'] .'Compress.php');
		return JSMin::minify($data);
	}
	else
		return $data;
}

/**
* load compressed Javascript or CSS
*/
function PortaMx_loadCompressed($file, $path = array(), $isInline = false)
{
	global $context, $modSettings, $pmxCacheFunc;

	$ext = substr($file, strrpos($file, '.'));
	if(!empty($isInline))
	{
		if(!empty($modSettings['minimize_files']))
			addInlineJavascript("\t". str_replace("\n", "\n\t", PortaMx_compressJS($file)));
		else
			addInlineJavascript("\t". str_replace("\n", "\n\t", $file));
		return;
	}

	// create compressed Name
	$minfile = str_replace($ext, '', $file) .'.min'. $ext;

	// path not given, use defaults
	if(empty($path))
	{
		$path['dir'] = ($ext == '.js' ? $context['pmx_scriptdir'] : $context['pmx_syscssdir']);
		$path['url'] = ($ext == '.js' ? $context['pmx_scripturl'] : $context['pmx_syscssurl']);
	}

	// do Minimize set?
	if(!empty($modSettings['minimize_files']))
	{
		// check if minimized
		if(($curTime = $pmxCacheFunc['get']('minimized_'. $file)) === null)
		{
			$curTime = time();

			if($ext == '.js')
				$minimized = PortaMx_compressJS(file_get_contents($path['dir'] . $file));
			else
				$minimized = str_replace("\n\t\t", "\n", PortaMx_compressCSS(file_get_contents($path['dir'] . $file)));

			// store time in cache
			$pmxCacheFunc['put']('minimized_'. $file, $curTime, 86400);

			// erase old AND save new minimized
			@unlink($path['dir'] . $minfile);
			file_put_contents($path['dir'] . $minfile, $minimized);
		}
		return $path['url'] . $minfile .'?'. $curTime;
	}
	else
		$curTime = filemtime($path['dir'] . $file);

	return $path['url'] . $file .'?'. $curTime;
}

/**
* read the settings from database.
*/
function PortaMx_getSettings($acsOnly = false)
{
	global $pmxcFunc, $context, $boarddir, $sourcedir, $boardurl, $settings, $modSettings, $user_info, $language, $pmxCacheFunc, $txt, $sc, $db_character_set;

	if(($buffer = $pmxCacheFunc['get']('settings', false)) !== null)
		@list(
			$context['pmx']['settings'],
			$context['pmx']['cache'],
			$context['pmx']['areas'],
			$context['pmx']['registerblocks'],
			$context['pmx']['permissions'],
			$context['pmx']['promotes'],
			$context['pmx']['languages'],
			$context['pmx']['extracmd'],
			$context['pmx']['ca_find'],
			$context['pmx']['ca_repl'],
			$context['pmx']['ca_grep'],
			$context['pmx']['ca_keys']
		) = $buffer;
	else
	{
		$request = $pmxcFunc['db_query']('', '
				SELECT varname, config
				FROM {db_prefix}portal_settings',
			array()
		);

		if($pmxcFunc['db_num_rows']($request) > 0)
		{
			while($row = $pmxcFunc['db_fetch_assoc']($request))
			{
				if(substr($row['varname'], 0, 1) == '_')
					continue;
				if(in_array($row['varname'], array('registerblocks', 'areas')))
					$context['pmx'][$row['varname']] = $row['config'];
				else
					$context['pmx'][$row['varname']] = pmx_json_decode($row['config'], true);
			}
			$pmxcFunc['db_free_result']($request);
		}
		else
			fatal_error('portamx_setting: table is empty.');

		$context['pmx']['languages'] = PortaMx_getLanguages();
		$context['pmx']['extracmd'] = array('paneloff', 'panelon', 'blockoff', 'blockon');

		// customer action vars
		$context['pmx']['ca_find'] = array(0 => '/([\@\s\r\n\t]+)/', 1 => '/(\[host\=[a-zA-Z0-9\.\-\_\*\?\,\^]+\])/', 2 => '/([\^\,]+|)([a-zA-Z0-9\=\.\-\_\*\?\[\]\;\:\&\*\?\^\|]+)/');
		$context['pmx']['ca_repl'] = array(0 => 'return "";', 1 => 'return "";', 2 => 'return !isset($fnd[2]) ? "" : (strpos($fnd[2], ":") === false ? $fnd[1] .":". $fnd[2] : $fnd[0]);');
		$context['pmx']['ca_grep'] = '/(\^|)(a:|c:|p:|:|)([\&\^\|]+|)([a-zA-Z0-9\=\.\-\_\*\?\[\]\;]+)/';
		$context['pmx']['ca_keys'] = array('action' => ':', 'art' => 'a:', 'cat' => 'c:', 'child' => 'c:', 'spage' => 'p:');

		$buffer = array(
			$context['pmx']['settings'],
			$context['pmx']['cache'],
			$context['pmx']['areas'],
			$context['pmx']['registerblocks'],
			$context['pmx']['permissions'],
			$context['pmx']['promotes'],
			$context['pmx']['languages'],
			$context['pmx']['extracmd'],
			$context['pmx']['ca_find'],
			$context['pmx']['ca_repl'],
			$context['pmx']['ca_grep'],
			$context['pmx']['ca_keys']
		);
		$pmxCacheFunc['put']('settings', $buffer, $context['pmx']['cache']['default']['settings_time'], false);
	}

	// setup dirs
	$context['pmx_sourcedir'] = $sourcedir .'/Portal/';
	$context['pmx_classdir'] = $sourcedir .'/Portal/Class/';
	$context['pmx_sysclassdir'] = $sourcedir .'/Portal/Class/System/';
	$context['pmx_templatedir'] = 'Portal/';
	$context['pmx_customcssdir'] = $settings['default_theme_dir'] .'/Portal/BlockCss/';
	$context['pmx_syscssdir'] = $settings['theme_dir'] .'/Portal/SysCss/';
	$context['pmx_languagedir'] = 'Portal/';
	$context['pmx_scriptdir'] = $settings['default_theme_dir'] .'/Portal/Scripts/';

	// setup urls
	$context['pmx_imageurl'] = $settings['default_theme_url'] .'/Portal/SysCss/Images/';
	$context['pmx_syscssurl'] = $settings['theme_url'] .'/Portal/SysCss/';
	$context['pmx_customcssurl'] = $settings['default_theme_url'] .'/Portal/BlockCss/';
	$context['pmx_scripturl'] = $settings['default_theme_url'] .'/Portal/Scripts/';
	$context['pmx_jsrel'] = '?pmx2virgo';

	// title icons link/path
	$context['pmx_Iconsurl'] = $settings['default_theme_url'] .'/Portal/TitleIcons/';
	$context['pmx_Iconsdir'] = $settings['default_theme_dir'] .'/Portal/TitleIcons/';
	$context['pmx_shortIconsurl'] = str_replace($boardurl .'/', '', $settings['default_theme_url']) .'/Portal/TitleIcons/';

	// check if utf8 charset used
	$context['pmx']['uses_utf8'] = isset($db_character_set) && $db_character_set == 'utf8';
	$context['pmx']['encoding'] = isset($db_character_set) && $db_character_set == 'utf8' ? 'UTF-8' : 'ISO-8859-1';

	// init frame counter & Image counter;
	$context['pmx_framecount'] = 0;
	$context['pmx']['LB_ImgCount'] = 0;

	// set Admin Flag..
	$context['pmx']['inAdmin'] = isset($_REQUEST['action']) && in_array($_REQUEST['action'], array('admin', 'portal')) && allowedTo(array('admin_forum'));

	// load common language
	loadLanguage($context['pmx_languagedir'] .'Portal');

	if(!empty($acsOnly))
		return;

	// load blocks class
	require_once($context['pmx_sysclassdir']. 'BlocksClass.php');

	// load the SSI.php
	require_once($boarddir .'/SSI.php');

	// setup upskrink images
	$context['pmx']['settings']['shrinkimages'] = 1;
	$context['pmx_img_expand'] = ' toggle_up"';
	$context['pmx_img_colapse'] = ' toggle_down"';

	// forum button
	$context['pmx']['show_forum_button'] = $context['pmx']['settings']['frontpage'] != 'none';

	// setup all block sides
	$context['pmx']['block_sides'] = array_keys($txt['pmx_block_sides']);

	// setup panel collapse, xbars and xbarkeys
	foreach($context['pmx']['block_sides'] as $side)
	{
		if($side != 'front')
		{
			$context['pmx']['xbar_'.$side] = isset($context['pmx']['settings']['xbars']) && in_array($side, array_values($context['pmx']['settings']['xbars']));
			$context['pmx']['xbarkeys'] = isset($context['pmx']['settings']['xbarkeys']) && !empty($context['pmx']['settings']['xbarkeys']);
			$context['pmx']['collapse'][$side] = empty($context['pmx']['settings'][$side.'_panel']['collapse']) && ($context['pmx']['xbar_'.$side] || $context['pmx']['xbarkeys']);
		}
	}

	// handle promotes request
	$context['pmx']['can_promote'] = allowPmx('pmx_admin, pmx_promote') && !empty($context['pmx']['settings']['manager']['promote']);
	if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'promote' && isset($_POST['message']) && !empty($context['pmx']['can_promote']))
	{
		$promote = intval(str_replace('pmx_promo_img_', '', $_POST['message']));
		if(in_array($promote, $context['pmx']['promotes']))
		{
			$context['pmx']['promotes'] = array_diff($context['pmx']['promotes'], array($promote));
			$result = $promote .',set,'. $txt['pmx_set_promote'];
			$mode = false;
		}
		else
		{
			$context['pmx']['promotes'] = array_merge($context['pmx']['promotes'], array($promote));
			$result = $promote .',unset,'. $txt['pmx_unset_promote'];
			$mode = true;
		}

		// update the promoted posts list
		$pmxcFunc['db_query']('', '
			UPDATE {db_prefix}portal_settings
			SET config = {string:cfg}
			WHERE varname = {string:name}',
			array('cfg' => json_encode($context['pmx']['promotes'], true), 'name' => 'promotes')
		);

		// find all promoted block
		$request = $pmxcFunc['db_query']('', '
			SELECT id, config
			FROM {db_prefix}portal_blocks
			WHERE blocktype = {string:blocktype}',
			array('blocktype' => 'promotedposts')
		);
		while($row = $pmxcFunc['db_fetch_assoc']($request))
		{
			$cfg[$row['id']] = pmx_json_decode($row['config'], true);
			if($cfg[$row['id']]['settings']['selectby'] == 'posts')
			{
				if(!is_array($cfg[$row['id']]['settings']['posts']))
					$cfg[$row['id']]['settings']['posts'] = array();

				// if not all posts selected ...
				if(array_search('0', $cfg[$row['id']]['settings']['posts'], true) === false)
				{
					if(empty($mode))		// remove promote
						$cfg[$row['id']]['settings']['posts'] = array_diff($cfg[$row['id']]['settings']['posts'], array($promote));
					else								// add promote
						$cfg[$row['id']]['settings']['posts'] = array_merge($cfg[$row['id']]['settings']['posts'], array($promote));
				}
			}
		}
		$pmxcFunc['db_free_result']($request);

		if(isset($cfg) && is_array($cfg) && count($cfg) > 0)
		{
			foreach($cfg as $id => $data)
			{
				$pmxcFunc['db_query']('', '
					UPDATE {db_prefix}portal_blocks
					SET config = {string:cfg}
					WHERE id = {int:id}',
					array('cfg' => json_encode($data, true), 'id'  => $id)
				);

				// clear cache by membergroup
				$pmxCacheFunc['drop']('promotedposts'. $id, true);
			}
		}

		// clear settings cache
		$pmxCacheFunc['drop']('settings');

		ob_end_clean();
		ob_start();
		echo $result;
		ob_end_flush();
		die;
	}

	// set default language (user if exist, else system default)
	if(isset($user_info['language']) && array_key_exists($user_info['language'], $context['pmx']['languages']))
		$context['pmx']['languages'][$user_info['language']] = true;
	else
		$context['pmx']['languages'][$language] = true;

	foreach($context['pmx']['languages'] as $lang => $sel)
	{
		if(!empty($sel))
			$context['pmx']['currlang'] = $lang;
	}
}

/**
* create a blockobject (and init, if $blockinit true)
* if not visible destroy the object
* returns the object handle or null
*/
function createBlockObject($config, $blockinit = false, $destroy = true)
{
	global $context;

	$handle = null;
	if(is_array($config))
	{
		// check if the classfile loaded
		$blocktype = 'pmxc_'. $config['blocktype'];
		if(!class_exists($blocktype))
			require_once($context['pmx_classdir'] . $config['blocktype'] .'.php');

		// call the contructor
		$handle = new $blocktype($config, $visible);

		// if visible and init = true, init the object
		if(!empty($visible) && !empty($blockinit))
			$visible = initBlockObject($handle);

		// if not visible destroy the object
		if(empty($visible) && !empty($destroy))
		{
			unset($handle);
			$handle = null;
		}
		else
		{
			if(empty($context['pmx']['have2colblocks']) && in_array($config['blocktype'], array('promotedposts', 'boardnews', 'boardnewsmult', 'newposts', 'rss_reader')))
				$context['pmx']['have2colblocks'] = true;
		}
	}
	return $handle;
}

/**
* Init a blockobject.
* returns true if visible
*/
function initBlockObject(&$handle)
{
	$visible = is_object($handle);
	if($visible)
		$visible = (bool) $handle->pmxc_InitContent();
	return $visible;
}

/**
* show a blockobject.
* returns true if the object handle exists.
*/
function showBlockObject(&$handle)
{
	$result = is_object($handle);
	if($result)
		$handle->pmxc_ShowBlock();

	return $result;
}

/**
* get the current url.
*/
function getCurrentUrl($addsep = false)
{
	global $scripturl;

	return (empty($_SERVER['QUERY_STRING']) ? $scripturl . (!empty($addsep) ? '?' : '') : ($scripturl .'?'. preg_replace('~(<\/?)(.+)([^>]*>)~', '', $_SERVER['QUERY_STRING']) . (!empty($addsep) ? ';' : '')));
}

/**
* Get the data for a Shoutbox and call the Init instance.
*/
function PortaMx_GetShoutbox($id)
{
	global $pmxcFunc;

	$request = $pmxcFunc['db_query']('', '
		SELECT id, side, pos, active, cache, blocktype, acsgrp, config, content
		FROM {db_prefix}portal_blocks
		WHERE id = {int:id} and active = 1',
		array('id' => $id)
	);

	$row = $pmxcFunc['db_fetch_assoc']($request);
	$pmxcFunc['db_free_result']($request);

	// call the contructor and init
	$result = createBlockObject($row, true);
	unset($result);
}

/**
* clear blockscache the have cache enabled on defined actions
**/
function clearBlocksCache($id = null, $catart = false)
{
	global $pmxcFunc, $pmxCacheFunc, $context;

	if(!isset($context['pmx']))
		PortaMx_getSettings();

	$request = $pmxcFunc['db_query']('', '
		SELECT id, blocktype
		FROM {db_prefix}portal_blocks
		WHERE blocktype IN ({array_string:cachedblocks}) AND cache > 0'. (is_numeric($id) ? ' AND id = {int:id}' : ''),
		array(
			'cachedblocks' => !empty($catart) ? array_merge(array('category', 'article'), array_keys($context['pmx']['cache']['blocks'])) : array_keys($context['pmx']['cache']['blocks']),
			'id' => $id,
		)
	);
	while($row = $pmxcFunc['db_fetch_assoc']($request))
	{
		if($row['blocktype'] == 'mini_calendar')
		{
			$pmxCacheFunc['drop']($row['blocktype'] . $row['id'] .'-0', false);
			$pmxCacheFunc['drop']($row['blocktype'] . $row['id'] .'-1', false);
			$pmxCacheFunc['drop']($row['blocktype'] . $row['id'] .'-6', false);
		}
		else
			$pmxCacheFunc['drop']($row['blocktype'] . $row['id'], $context['pmx']['cache']['blocks'][$row['blocktype']]['mode']);
	}
	$pmxcFunc['db_free_result']($request);

	if(!empty($catart))
	{
		$pmxCacheFunc['drop']('sef_artlist', false);
		$pmxCacheFunc['drop']('sef_catlist', false);
	}
}

/**
* check the show/hide panel, empty panels are not show.
*/
function getPanelsToShow(&$action)
{
	global $pmxcFunc, $context, $user_info, $maintenance, $modSettings;

	// get active panels for this action
	$activePanels = array();
	$allsides = $context['pmx']['block_sides'];

	if($action != 'frontpage'  || $context['pmx']['settings']['frontpage'] == 'none' || (!empty($modSettings['pmx_mobile']['detect']) && is_bool($modSettings['pmx_mobile']['detect'])))
		$allsides = array_diff($allsides, array('front'));
	elseif(!empty($context['pmx']['settings']['hidefrontonpages']) && !empty($context['pmx']['pageReq']))
	{
		$tmp = Pmx_StrToArray($context['pmx']['settings']['hidefrontonpages']);
		foreach($tmp as $pgn)
		{
			foreach($context['pmx']['pageReq'] as $rqType => $rqVal)
				if(preg_match('~'. str_replace(array(($rqType == 'spage' ? 'p:' : ($rqType == 'cat' ? 'c:' : 'a:')),'*','?'), array('','.*','.?'), trim($pgn)) .'~i', $_GET[$rqType], $match) != 0 && $match[0] == $rqVal)
					$allsides = array_diff($allsides, array('front'));
		}
	}

	foreach($allsides as $side)
	{
		$hidepanels = isset($context['pmx']['settings'][$side .'_panel']['hide']) ? $context['pmx']['settings'][$side .'_panel']['hide'] : array();
		$customhide = isset($context['pmx']['settings'][$side .'_panel']['custom_hide']) ? $context['pmx']['settings'][$side .'_panel']['custom_hide'] : '';

		// hide pages panel?
		if($side == 'pages' && !array_key_exists('spage', $context['pmx']['pageReq']))
			$context['pmx']['show_'. $side .'panel'] = false;

		// any hide action?
		elseif(!empty($hidepanels) || !empty($customhide))
		{
			$itemData = array('pmxact' => $hidepanels, 'pmxcust' => $customhide);
			$show = pmx_checkExtOpts(true, $itemData);

			$context['pmx']['show_'. $side .'panel'] = empty($show);
			$context['pmx']['collapse'][$side] = empty($show);
			if(empty($show))
				$activePanels[] = $side;
		}
		else
			$activePanels[] = $side;

		// hide panel on device types?
		if(!empty($context['pmx']['settings'][$side .'_panel']['device']))
		{
			if(!empty($modSettings['isMobile']) && $context['pmx']['settings'][$side .'_panel']['device'] != '1')
			{
				$activePanels = array_diff($activePanels,	array($side));
				$context['pmx']['show_'. $side .'panel'] = false;
			}
			if(empty($modSettings['isMobile']) && $context['pmx']['settings'][$side .'_panel']['device'] != '2')
			{
				$activePanels = array_diff($activePanels,	array($side));
				$context['pmx']['show_'. $side .'panel'] = false;
			}
		}
	}

	// used for external mods or via url parameter
	if(!empty($modSettings['pmx_paneloff']))
	{
		$offPanels = is_array($modSettings['pmx_paneloff']) ? $modSettings['pmx_paneloff'] : explode(',', $modSettings['pmx_paneloff']);
		$activePanels = array_diff($activePanels, $offPanels);
		foreach($offPanels as $side)
			$context['pmx']['show_'. $side .'panel'] = false;
	}

	// set IsAdmin flag
	$context['Is_Admin'] = allowPmx('pmx_admin');
	$context['In_Administration'] = $context['Is_Admin'] && isset($_REQUEST['action']) && in_array($_REQUEST['action'], array('admin', 'portal'));
	if(!empty($context['In_Administration']) && !empty($context['pmx']['settings']['loadinactive']))
	{
		$queryPart = 'active = 1 OR (active = 0 AND side IN({array_string:adminsides}))';
		$adminsides = array('top', 'head', 'left', 'right', 'foot', 'bottom');
	}
	else
	{
		$queryPart = 'active = 1';
		$adminsides = array();
	}

	// hide frontpage on Maintenance
	if(!empty($maintenance) && empty($context['Is_Admin']))
	{
		$activePanels = array_diff($activePanels, array('front'));
		$context['pmx']['show_frontpanel'] = false;
	}

	// read the panels from database
	$context['pmx']['pagenames'] = array();
	$context['pmx_blockCSSfiles'] = array();
	$context['pmx']['showhome'] = 0;
	$cachedblocks = array_keys($context['pmx']['cache']['blocks']);

	$result = array();
	$request = $pmxcFunc['db_query']('', '
		SELECT id, side, pos, active, cache, blocktype, acsgrp, config, content,
			CASE WHEN side = {string:front} THEN 1 WHEN side = {string:pages} THEN 2 ELSE 0 END AS SortFlag
		FROM {db_prefix}portal_blocks
		WHERE '. $queryPart . (!empty($modSettings['pmx_blockoff']) ? ' AND NOT id IN({array_int:offblocks})' : '') .' AND (side IN ({array_string:sides})'. (empty($modSettings['pmx_paneloff']) ? ' OR (blocktype IN ({array_string:cachedblocks}) AND cache > 0)' : '') .')
		ORDER BY SortFlag DESC, side ASC, pos ASC',
		array(
			'sides' => ($context['pmx']['settings']['frontpage'] != 'none' ? array_unique(array_merge($activePanels, array('front'))) : array_merge($activePanels, array('none'))),
			'cachedblocks' => $cachedblocks,
			'offblocks' => !empty($modSettings['pmx_blockoff']) ? explode(',', $modSettings['pmx_blockoff']) : array(),
			'front' => 'front',
			'pages' => 'pages',
			'adminsides' => $adminsides,
		)
	);

	while($row = $pmxcFunc['db_fetch_assoc']($request))
	{
		unset($row['SortFlag']);

		// call the contructor and init if visible
		if(($result[$row['side']][$row['id']] = createBlockObject($row, true)) === null)
			unset($result[$row['side']][$row['id']]);

		// destroy blocks in not active panels
		if(isset($result[$row['side']][$row['id']]) && !in_array($row['side'], $activePanels))
			unset($result[$row['side']][$row['id']]);
	}
	$pmxcFunc['db_free_result']($request);

	// check category/article request
	if(count(array_intersect(array('art', 'cat'), array_keys($context['pmx']['pageReq']))) > 0)
	{
		$nullobj = null;
		if(array_key_exists('cat', $context['pmx']['pageReq']))
		{
			$row = PortaMx_getCatByID(null, $context['pmx']['pageReq']['cat']);
			if(!empty($row))
			{
				// check ECL mode on requested art/cat
				if(!checkECL_Cookie())
				{
					$cfg = pmx_json_decode($row['config'], true);
					if(!empty($cfg['check_ecl']))
						pmx_fatalerror('eclcat', $nullobj);
				}

				$row['side'] = 'pages';
				$row['blocktype'] = 'category';
				$row['static_block'] = 0;
				$row['active'] = 1;
			}
			else
				pmx_fatalerror('category', $nullobj);
		}

		elseif(array_key_exists('art', $context['pmx']['pageReq']))
		{
			$request = $pmxcFunc['db_query']('', '
				SELECT id, name, acsgrp, config
				FROM {db_prefix}portal_articles
				WHERE name = {string:reqname} AND active > 0 AND approved > 0',
				array('reqname' => $context['pmx']['pageReq']['art'])
			);
			if($pmxcFunc['db_num_rows']($request) > 0)
			{
				$row = $pmxcFunc['db_fetch_assoc']($request);

				// check ECL mode on requested art/cat
				if(!checkECL_Cookie())
				{
					$cfg = pmx_json_decode($row['config'], true);
					if(!empty($cfg['check_ecl']))
						pmx_fatalerror('eclart', $nullobj);
				}

				$row['side'] = 'pages';
				$row['blocktype'] = 'article';
				$row['static_block'] = 0;
				$row['active'] = 1;
			}
			$pmxcFunc['db_free_result']($request);

			if(empty($row))
				pmx_fatalerror('article', $nullobj);
		}

		if(($result[$row['side']][$row['id']] = createBlockObject($row, true)) === null)
		{
			unset($result[$row['side']][$row['id']]);
			pmx_fatalerror($row['blocktype'], $result);
		}
		else
			$context['pmx']['show_pagespanel'] = true;
	}

	// switch off empty panels
	$ecloff = (isset($_REQUEST['pmxerror']) && $_REQUEST['pmxerror'] == 'pmx_eclauth');
	foreach($activePanels as $side)
	{
		$context['pmx']['show_'. $side .'panel'] = ($ecloff ? false : (isset($result[$side]) && !empty($result[$side])));
		if(empty($context['pmx']['show_'. $side .'panel']))
			$context['pmx']['collapse'][$side] =  false;
	}

	return $result;
}

/**
* get a categorie by id or name
* the categorie (with childs) or false is returned
*/
function PortaMx_getCatByID($cats, $id)
{
	if(is_null($cats))
		$cats = PortaMx_getCategories();

	$fnd = null;
	if(is_array($cats))
	{
		reset($cats);
		while((list($ofs, $cat) = pmx_each($cats)) && empty($fnd))
		{
			if((is_numeric($id) && $cat['id'] == $id) || (is_string($id) && $cat['name'] == $id))
				$fnd = $cat;

			elseif(isset($cat['childs']) && is_array($cat['childs']))
				$fnd = PortaMx_getCatByID($cat['childs'], $id);
		}
	}
	return $fnd;
}

/**
* get a categorie by catorder
* the categorie (with childs) or false is returned
*/
function PortaMx_getCatByOrder($cats, $order, $dept = 0)
{
	reset($cats);
	do
	{
		@list($d, $cat) = pmx_each($cats);
		if(isset($cat['childs']) && is_array($cat['childs']) && $cat['catorder'] != $order)
		{
			$cat = PortaMx_getCatByOrder($cat['childs'], $order, $dept +1);
			$cat = !is_array($cat) ? array('catorder' => 0) : $cat;
		}
	} while(is_array($cat) && $cat['catorder'] != $order);
	return $cat;
}

/**
* get next categorie by catorder
*/
function PortaMx_getNextCat($order)
{
	global $context;

	$maxorder = $context['pmx']['catorder'][count($context['pmx']['catorder']) -1] +1;
	$key = array_search($order, $context['pmx']['catorder']);
	return ($key === false ? $maxorder : (isset($context['pmx']['catorder'][$key +1]) ? $context['pmx']['catorder'][$key +1] : $maxorder));
}

/**
* get previose categorie by catorder
*/
function PortaMx_getPrevCat($order)
{
	global $context;

	$key = array_search($order, $context['pmx']['catorder']);
	return ($key === false ? -1 : (isset($context['pmx']['catorder'][$key -1]) ? $context['pmx']['catorder'][$key -1] : -1));
}

/**
* get all categories and sort them by catorder
*/
function find_cat_insert_pos(&$cats, $cat, $id)
{
	$fnd = false;
	reset($cats);

	while((list($ofs, $data) = pmx_each($cats)) && empty($fnd))
	{
		if($data['id'] == $id)
		{
			$cats[$ofs]['childs'][] = &$cat;
			$fnd = true;
		}
		elseif(isset($data['childs']) && is_array($data['childs']))
			$fnd = find_cat_insert_pos($cats[$ofs]['childs'], $cat, $id);
	}
	return $fnd;
}

function PortaMx_getCategories($getart = false)
{
	global $context, $pmxcFunc;

	$context['pmx']['catorder'] = array();
	$result = array();
	$articles = array();

	if(!empty($getart))
	{
		$request = $pmxcFunc['db_query']('', '
			SELECT id, name, catid, owner, active, created, updated, approved
			FROM {db_prefix}portal_articles
			ORDER BY catid',
			array()
		);
		while($row = $pmxcFunc['db_fetch_assoc']($request))
			$articles[$row['catid']][] = $row;

		$pmxcFunc['db_free_result']($request);
	}

	$request = $pmxcFunc['db_query']('', '
			SELECT a.id, a.name, a.parent, a.level, a.catorder, a.acsgrp, a.artsort, a.config, COUNT(c.id) AS artsum
			FROM {db_prefix}portal_categories AS a
			LEFT JOIN {db_prefix}portal_articles AS c ON(c.catid = a.id)
			GROUP by a.id
			ORDER BY catorder',
		array(
		)
	);
	while($row = $pmxcFunc['db_fetch_assoc']($request))
	{
		$cat = array(
			'id' => $row['id'],
			'name' => $row['name'],
			'parent' => $row['parent'],
			'level' => $row['level'],
			'catorder' => $row['catorder'],
			'acsgrp' => $row['acsgrp'],
			'artsort' => $row['artsort'],
			'config' => $row['config'],
			'artsum' => $row['artsum'],
			'childs' => null,
		);

		if(!empty($getart) && array_key_exists($row['id'], $articles))
			$cat['articles'] = PortaMx_ArticleSort($articles[$row['id']], $row['artsort']);

		$context['pmx']['catorder'][] = $row['catorder'];
		if($cat['parent'] != 0)
			find_cat_insert_pos($result, $cat, $cat['parent']);
		elseif(find_cat_insert_pos($result, $cat, 0) == false)
			$result[] = $cat;
	}
	$pmxcFunc['db_free_result']($request);

	return $result;
}

/**
* get short article data in one or more categories
*/
function PortaMx_getArticles($cats, $intcats = false)
{
	global $scripturl, $pmxcFunc;

	$result = array();
	$request = $pmxcFunc['db_query']('', '
			SELECT c.id AS catid, c.name AS catname, c.acsgrp AS catacs, c.artsort, c.config as catcfg, CASE WHEN m.real_name = {string:empty} THEN m.member_name ELSE m.real_name END AS owner_name,
				a.id AS artid, a.name AS artname, a.acsgrp AS artacs, a.owner, a.config AS artcfg, a.active, a.created, a.approved, a.updated
			FROM {db_prefix}portal_categories AS c
			LEFT JOIN {db_prefix}portal_articles AS a ON (c.id = a.catid)
			LEFT JOIN {db_prefix}members AS m ON (a.owner = m.id_member)
			WHERE '. (empty($intcats) ? 'c.name IN ({array_string:cats})' : 'c.id IN ({array_int:cats})') .' AND a.active > 0 AND a.approved  > 0
			ORDER BY c.catorder',
		array(
			'cats' => empty($intcats) ? Pmx_StrToArray($cats) : Pmx_StrToIntArray($cats),
			'empty' => '',
		)
	);
	while($row = $pmxcFunc['db_fetch_assoc']($request))
	{
		if(!isset($result[$row['catid']]) && allowPmxGroup($row['catacs']))
		{
			$cfg = pmx_json_decode($row['catcfg'], true);
			if(empty($cfg['request']) || (!empty($cfg['request']) && allowPmx('pmx_admin')))
			{
				$title = PortaMx_getTitle($cfg);
				if(empty($title))
					$title = htmlspecialchars($row['catname'], ENT_QUOTES);

				$result[$row['catid']] = array(
					'name' => $row['catname'],
					'title' => $title,
					'acsgrp' => $row['catacs'],
					'acsinherit' => !empty($cfg['settings']['inherit_acs']),
					'artsort' => $row['artsort'],
					'link' => '<a href="'. $scripturl .'?cat='. $row['catname'] .'">'. $title .'</a>',
					'href' => $scripturl .'?cat='. $row['catname'],
					'articles' => array(),
				);
			}
		}

		if(isset($result[$row['catid']]) && (!empty($result[$row['catid']]['acsinherit']) || allowPmxGroup($row['artacs'])))
		{
			$title = PortaMx_getTitle($row['artcfg']);
			if(empty($title))
				$title = htmlspecialchars($row['artname'], ENT_QUOTES);

			$result[$row['catid']]['articles'][] = array(
				'id' => $row['artid'],
				'name' => $row['artname'],
				'title' => $title,
				'acsgrp' => $row['artacs'],
				'active' => $row['active'],
				'created' => $row['created'],
				'approved' => $row['approved'],
				'updated' => $row['updated'],
				'owner' => $row['owner'],
				'time_created' => timeformat($row['created'], false),
				'link' => '<a href="'. $scripturl .'?art='. $row['artname'] .'">'. $title .'</a>',
				'href' => $scripturl .'?art='. $row['artname'],
				'member' => array(
					'member_id' => $row['owner'],
					'member_name' => $row['owner_name'],
					'link' => '<a href="'. $scripturl .'?action=profile;u='. $row['owner'] .'">'. $row['owner_name'] .'</a>',
					'href' => $scripturl .'?action=profile;u='. $row['owner'],
				),
			);
		}
	}
	$pmxcFunc['db_free_result']($request);

	// sort the articles
	foreach($result as $cid => $data)
		$result[$cid]['articles'] = PortaMx_ArticleSort($data['articles'], $data['artsort']);

	return $result;
}

/**
* get the language depended titel.
*/
function PortaMx_getTitle($cfg)
{
	global $language, $user_info;

	$cfg = !is_array($cfg) ? pmx_json_decode($cfg, true) : $cfg;
	if(is_array($cfg) && isset($cfg['title']) && is_array($cfg['title']))
		$titles = $cfg['title'];
	else if(is_array($cfg['config']) && isset($cfg['config']['title']) && is_array($cfg['config']['title']))
		$titles = $cfg['config']['title'];
	else
		return '';

	if(!empty($user_info['language']) && !empty($titles[$user_info['language']]))
		return htmlspecialchars($titles[$user_info['language']], ENT_QUOTES);
	else if(is_array($language) && !empty($language['filename']) && !empty($titles[$language['filename']]))
		return htmlspecialchars($titles[$language['filename']], ENT_QUOTES);
	else if(!is_array($language) && !empty($titles[$language]))
		return htmlspecialchars($titles[$language], ENT_QUOTES);
	else if(isset($titles['english']) && !empty($titles['english']))
		return htmlspecialchars($titles['english'], ENT_QUOTES);
	else
		return '';
}

/**
* get all languages.
*/
function PortaMx_getLanguages()
{
	global $settings;

	// check languages
	$lang_dir = $settings['default_theme_dir'] . '/languages';
	$dir = dir($lang_dir);
	while ($entry = $dir->read())
	{
		preg_match('~^Admin\.?([a-zA-Z0-9\_\-]+)\.php~', $entry, $match);
		if(!empty($match))
			$result[$match[1]] = false;
	}
	$dir->close();

	// check Portal languages
	$lang_dir = $settings['default_theme_dir'] . '/languages/Portal';
	$dir = dir($lang_dir);
	while ($entry = $dir->read())
	{
		preg_match('~^Portal\.?([a-zA-Z0-9\_\-]+)\.php~', $entry, $match);
		if(!empty($match) && !array_key_exists($match[1], $result))
			unset($result[$match[1]]);
	}
	$dir->close();

	return $result;
}

/**
* show the blocks for a side.
*/
function PortaMx_ShowBlocks($side, $spacer = 0, $placement = '')
{
	global $context, $txt, $scripturl;

	$placed = 0;
	$pages = array();
	$count = isset($context['pmx']['viewblocks'][$side]) ? count($context['pmx']['viewblocks'][$side]) : 0;
	if($count > 0)
	{
		$count += intval($spacer);
		foreach($context['pmx']['viewblocks'][$side] as $ObjHdl)
		{
			$count--;
			$doShow = $ObjHdl->pmxc_ShowBlock($count, $placement);
			$placed += $doShow;
			if($ObjHdl->cfg['side'] == 'pages' && array_key_exists('spage', $context['pmx']['pageReq']))
				$pages[] = $ObjHdl->cfg['config']['pagename'];
		}

		$context['pmx']['xbar_Show'. $side] = $placed > 0;

		if($side == 'pages' && array_key_exists('spage', $context['pmx']['pageReq']) && !in_array($_GET['spage'], $pages))
			pmx_fatalerror('page', $context['pmx']['viewblocks']);
	}
	else
	{
		if($side == 'pages' && array_key_exists('spage', $context['pmx']['pageReq']))
			pmx_fatalerror('page', $context['pmx']['viewblocks']);
	}
	return $placed;
}

/**
* check the visibility access by extend options,
* (action, custom action, board, topic, themes and language)
*/
function pmx_checkExtOpts($show, $itemData, $blockpagename = '')
{
	global $context, $settings;

	// find items they have values
	$allitems = array('pmxact', 'pmxcust', 'pmxbrd', 'pmxthm', 'pmxlng');
	$checkitems = array();
	foreach($allitems as $item)
	{
		if(isset($itemData[$item]) && !empty($itemData[$item]))
			$checkitems[] = $item;
	}

	// nothing to do?
	if(empty($checkitems))
		return $show;

	// check exist items
	$bits = pmx_setBits(null);
	foreach($checkitems as $item)
	{
		// convert elements for simpler checking
		if($item != 'pmxcust')
			$data = pmx_decodeOptions($itemData[$item]);

		switch($item)
		{
			// actions...
			case 'pmxact':
				// frontpage ?
				if(array_key_exists('frontpage', $data) && empty($context['pmx']['forumReq']) && empty($context['pmx']['pageReq']))
					$bits['front'] = $data['frontpage'];

				// Pages ?
				elseif(array_key_exists('pages', $data) && array_key_exists('spage', $context['pmx']['pageReq']))
					$bits['spage'] = $data['pages'];

				// Articles ?
				elseif(array_key_exists('articles', $data) && array_key_exists('art', $context['pmx']['pageReq']))
					$bits['art'] = $data['articles'];

				// Categories ?
				elseif(array_key_exists('categories', $data) && array_key_exists('cat', $context['pmx']['pageReq']))
					$bits['cat'] = $data['categories'];

				// global on topics?
				elseif(array_key_exists('topics', $data) && !empty($context['current_topic']))
					$bits['topic'] = $data['topics'];

				// global on boards?
				elseif(array_key_exists('boards', $data) && !empty($context['current_board']))
					$bits['board'] = $data['boards'];

				// action ?
				elseif(isset($_GET['action']) && array_key_exists($_GET['action'], $data))
					$bits['action'] = $data[$_GET['action']];

				// action && any option set?
				elseif(isset($_GET['action']) && !array_key_exists($_GET['action'], $data) && array_key_exists('any', $data))
					$bits['action'] = $data['any'];

				// other && any option set?
				elseif((!empty($context['pmx']['pageReq']) || !empty($context['current_topic']) || !empty($context['current_board'])) && array_key_exists('any', $data))
					$bits['action'] = $data['any'];

			break;

			// custom actions...
			case 'pmxcust':
				// page, category, article request?
				if(!empty($context['pmx']['pageReq']))
				{
					foreach(array('spage', 'cat', 'child', 'art') as $tok)
					{
						$bits[$tok] = (is_null($bits[$tok]) ? 0 : $bits[$tok]);

						if($tok == 'spage' && isset($context['pmx']['pageReq']['spage']) && $context['pmx']['pageReq']['spage'] == $blockpagename)
							$bits['spage'] = 1;
						elseif(array_key_exists($tok, $context['pmx']['pageReq']))
							pmx_checkCustActions($bits, $itemData[$item], $tok);
					}
				}

				// any other request?
				elseif(isset($_GET))
					pmx_checkCustActions($bits, $itemData[$item], 'action');

			break;

			// specific board ?
			case 'pmxbrd':
				if(!empty($context['current_board']))
				{
					if(array_key_exists($context['current_board'], $data))
					{
						if(empty($data[$context['current_board']]) || (!empty($context['current_topic']) && $bits['topic'] === '0'))
							$bits = pmx_setBits(0);
						else
							$bits['board'] = $data[$context['current_board']];
					}
					// any action ?
					else
					{
						if(array_key_exists('any', $data))
							$bits['board'] = $data['any'];
						elseif(!empty($bits['topic']))
							$bits = pmx_setBits(0);
					}
				}
			break;

			// theme ?
			case 'pmxthm':
				if(isset($settings['theme_id']))
				{
					$state = pmx_getBits($bits);
					if(is_null($state) || !empty($state))
					{
						if(array_key_exists($settings['theme_id'], $data))
						{
							$bits['theme'] = $data[$settings['theme_id']];
							if(empty($bits['theme']))
								$bits = pmx_setBits(0);
						}
						elseif(array_key_exists('any', $data))
							$bits['theme'] = $data['any'];
						else
							$bits = pmx_setBits(0);
					}
				}
			break;

			// language ?
			case 'pmxlng':
				if(isset($context['user']['language']))
				{
					$state = pmx_getBits($bits);
					if(is_null($state) || !empty($state))
					{
						if(array_key_exists($context['user']['language'], $data))
						{
							$bits['lang'] = $data[$context['user']['language']];
							if(empty($bits['lang']))
								$bits = pmx_setBits(0);
						}
						elseif(array_key_exists('any', $data))
							$bits['lang'] = $data['any'];
						else
							$bits = pmx_setBits(0);
					}
				}
			break;
		}
	}

	return (int) (intval(implode('', $bits)) > 0);
}

/**
* check customer actions & subactions
*/
function pmx_checkCustActions(&$bits, $item, $actname)
{
	global $context;

	foreach($context['pmx']['ca_find'] as $key => $regex)
	{
		preg_match_all($regex, $item, $fndall, PREG_SET_ORDER);
		foreach($fndall as $fnd)
			$item = !empty($fnd) ? str_replace($fnd[0], eval($context['pmx']['ca_repl'][$key]), $item) : $item;
	}

	preg_match_all($context['pmx']['ca_grep'], $item, $actions);
	$key = $context['pmx']['ca_keys'][$actname];
	$keyCtl = 1; $keyPos = 2; $actCtl = 3; $actPos = 4;

	// process the current action
	$indexes = array_keys(array_values($actions[$keyPos]), $key);
	if(count($indexes) > 0)
	{
		$fnd = false;
		$autoAny = true;

		// loop through all entrys until we found one
		while((list($idxPos, $aix) = pmx_each($indexes)) && empty($fnd))
		{
			$hideAct = strpos($actions[$keyCtl][$indexes[$idxPos]], '^') === false;

			// work on all entries..
			do
			{
				// check action or subaction
				if(strpos($actions[$actCtl][$aix], '&') === false)
				{
					// action..
					if(pmx_checkActions($actions[$actPos][$aix], $actname, false))
					{
						// ..found
						$fnd = true;

						// have a subaction?
						if(isset($actions[$actCtl][$aix+1]) && strpos($actions[$actCtl][$aix+1], '&') !== false)
						{
							// subaction ..
							$aix++;
							$hideSubAct = strpos($actions[$actCtl][$aix], '^') === false;
							$subact = pmx_checkActions($actions[$actPos][$aix], $actname);
							if(!empty($subact))
								$bits[$actname] = intval($hideAct && $hideSubAct);
							else
							{
								$bits[$actname] = intval($hideAct && !$hideSubAct);
								$autoAny = false;
							}
						}

						// no subaction given
						else
						{
							$bits[$actname] = intval($hideAct);
							$autoAny = false;
						}
					}

					// action not found .. check subaction
					else
					{
						if(isset($actions[$actCtl][$aix+1]) && strpos($actions[$actCtl][$aix+1], '&') !== false)
						{
							// get the subaction
							$aix++;
							$hideSubAct = strpos($actions[$actCtl][$aix], '^') === false;
							$autoAny = false;
							$subfnd = pmx_checkActions($actions[$actPos][$aix], $actname);
							if(!empty($subfnd))
								$bits[$actname] = intval(is_null($bits[$actname]) ? (!$hideAct && $hideSubAct) : ($bits[$actname] && intval(!$hideAct && $hideSubAct)));
							elseif(isset($_GET[$actname]))
								$bits[$actname] = intval(is_null($bits[$actname]) ? (!$hideAct && !$hideSubAct) : ($bits[$actname] && intval(!$hideAct && !$hideSubAct)));
						}
						// nothing .. check request
						elseif(isset($_GET[$actname]))
							$bits[$actname] = intval(is_null($bits[$actname]) ? !$hideAct : ($bits[$actname] == 2 ? $bits[$actname] && intval(!$hideAct) : $bits[$actname]));
					}
				}

				// only subaction
				elseif(isset($_GET[$actname]))
				{
					$autoAny = false;
					$hideSubAct = strpos($actions[$actCtl][$aix], '^') === false;
					$fnd = pmx_checkActions($actions[$actPos][$aix], $actname);
					$bits[$actname] = intval($bits[$actname] && intval(!empty($fnd) ? $hideSubAct : ($bits[$actname] != 2 ? intval(!$hideSubAct) : $bits[$actname] && intval(!$hideSubAct))));
				}

				// next action if nothing found..
				$aix++;
			} while(empty($fnd) && isset($actions[$keyPos][$aix]) && empty($actions[$keyPos][$aix]));
		}

		// nothing found, we have a ANY action?
		if(empty($fnd) && !empty($autoAny) && count(array_keys($actions[1], '^')) != 0)
		{
			$tmp = array_intersect($indexes, array_keys($actions[1], '^'));
			$any = is_array($tmp) ? count($tmp) : 0;
			$bits[$actname] = isset($_GET[$actname]) && $any == count($actions[$keyCtl]) ? 2 : $bits[$actname];
		}

		// action/subaction found and set to 0 ?
		elseif(!empty($fnd) && !is_null($bits[$actname]) && empty($bits[$actname]))
			$bits = pmx_setBits(0);
	}
}

/**
* check actions
*/
function pmx_checkActions($actionlist, $actname, $check = true)
{
	$fnd = null;
	$getacts = array_diff(array_keys($_GET), array($actname));
	if(!empty($getacts) || empty($check))
	{
		$actions = explode(';', $actionlist);
		while(empty($fnd) && (@list($d, $action) = pmx_each($actions)) && !empty($action))
		{
			@list($act, $val) = explode('=', (strpos($action, '=') === false ? $actname .'=' : '') . $action);
			$val = str_replace(array('*','?'), array('.*','.?'), $val);
			if(isset($_GET[$act]) && preg_match('~'. $val .'~i', $_GET[$act], $match) != 0 && $match[0] == $_GET[$act])
				$fnd = is_null($fnd) ? true : $fnd && true;
			else
				$fnd = false;
		}
	}
	return is_null($fnd) ? false : $fnd;
}

/**
* prepare the extend option bits.
*/
function pmx_setBits($val)
{
	return array('front' => $val, 'spage' => $val, 'art' => $val, 'cat' => $val, 'child' => $val, 'action' => $val, 'board' => $val, 'topic' => $val, 'theme' => $val, 'lang' => $val);
}

/**
* check the extend option bits.
*/
function pmx_getBits($bits, $strip = array())
{
	$result = null;
	foreach($bits as $key => $val)
	{
		if(!in_array($key, $strip))
			$result = (!is_null($val) ? $result .= $val : $result);
	}
	return (is_null($result) ? null : intval(implode('', $bits)) > 0);
}

/**
* convert Extend Option for faster check
* for actions, boards, themes, languages
*/
function pmx_decodeOptions($item)
{
	global $__dat;
	array_walk($item, function($val, $key, $__dat){global $__dat; $temp = explode("=", $val); $multi = explode(",", $temp[0]); foreach($multi as $ky) $__dat[$ky] = $temp[1];}, $__dat = array());

	// if all data negated add the any action
	if(($tmp = array_count_values(array_diff_assoc($__dat, array('frontpage' => 0, 'frontpage' => 1)))) && count($tmp) == 1 & key($tmp) == 0)
		$__dat['any'] = 2;

	return $__dat;
}

/**
* remove html, the $ and array[element].
*/
function PortaMx_makeSafe($value)
{
	if(is_array($value))
	{
		$result = array();
		foreach($value as $key => $val)
		{
			$key = preg_replace('~(\[\/?)(.+)([^\]]*\])|\$~', '', preg_replace('~(<\/?)(.+)([^>]*>)~', '', $key));
			if(is_array($val))
				$result[$key] = PortaMx_makeSafe($val);
			else
			{
				if(in_array($key, array('content', 'content_init', 'check_num_vars')))
					$result[$key] = $val;
				else
					$result[$key] = preg_replace('~(<\/?)(.+)([^>]*>)~', '', $val);
			}
		}
		return $result;
	}
	else
		return preg_replace('~(\[\/?)(.+)([^\]]*\])|\$~', '', preg_replace('~(<\/?)(.+)([^>]*>)~', '', $value));
}

/**
* remove all unnecessary <br> and lf/cr from content.
*/
function PortaMx_makeSafeContent($content, $type = '')
{
	// remove <br> and lf/cr from end
	$content = rtrim($content);
	if(in_array($type, array('html', 'script', 'code')))
	{
		$res = true;
		while($res)
		{
			$last = substr($content, -6);
			$res = preg_match('~<br[^>]*>~i', $last, $found);
			if(!empty($res))
				$content = rtrim(substr($content, 0, -(strlen($found[0])+1)));
			else
			{
				$res = preg_match('~\&nbsp\;~i', $last, $found);
				if(!empty($res))
					$content = rtrim(substr($content, 0, -strlen($found[0])));
			}
		}
		$content = rtrim($content, "\n\r\t");

		// fix smaily path and white space
		if(in_array($type, array('html', 'script', 'code')))
			$content = str_replace('/ckeditor/../Smileys/', '/Smileys/', $content);
		if(in_array($type, array('bbc', 'bbc_script')))
			$content = strtr($content, array("\xC2\xA0" => ' ', "\xA0" => ' '));
		if($type == 'bbc_script')
			$content .= "\n";
	}
	return $content;
}

/**
* Sort articles by sortmodes
* $sortData: sortstring
* $articles: array(articledata)
**/
function PortaMx_ArticleSort($articles, $sortData)
{
	$cmpStr = '';
	$sorts = Pmx_StrToArray($sortData);
	foreach($sorts as $sort)
	{
		@list($sKey, $sDir) = Pmx_StrToArray($sort, '=');
		$cmpStr .= (empty($cmpStr) ? 'return ' : ' xor ') .'($articles[$s1][\''. $sKey .'\'] '. (empty($sDir) ? '<' : '>') .' $articles[$s2][\''. $sKey .'\'])';
	}

	$cmpStr .= ';';
	for($s1 = 0; $s1 < sizeof($articles); $s1++)
	{
		for($s2 = $s1 + 1; $s2 < sizeof($articles); $s2++)
		{
			if(eval($cmpStr) == true)
			{
				$tmp = $articles[$s1];
				$articles[$s1] = $articles[$s2];
				$articles[$s2] = $tmp;
			}
		}
	}
	return $articles;
}

/**
* remove Links from content.
* unlinkimg = true: remove images.
* unlinkhref = true: remove href.
*/
function PortaMx_revoveLinks($content, $unlinkhref = false, $unlinkimg = false)
{
	global $modSettings;

	// remove links
	if($unlinkhref)
	{
		if(preg_match_all("!<a[^>]*>(.+?)</a>!iS", $content, $matches, PREG_SET_ORDER) > 0)
			foreach($matches as $i => $data)
			{
				if(strpos(strtolower($data[1]), '<img') === false)
					$content = str_replace($data[0], $data[1], $content);
			}
	}

	// remove embedded images
	if($unlinkimg)
	{
		// remove lightbox images
		if(preg_match_all("!<a[^>]*>[^<]*<img[^>]*>[^<]*</a>!iS", $content, $matches, PREG_SET_ORDER) > 0)
		{
			foreach($matches[0] as $data)
				$content = str_replace($data, '', $content);
		}

		// remove normal images, exept smileys
		if(preg_match_all('!<img[^>]*>!iS', $content, $matches, PREG_SET_ORDER) > 0)
		{
			foreach($matches[0] as $data)
			{
				if(strpos($data, $modSettings['smileys_url']) === false)
					$content = str_replace($data, '', $content);
			}
		}

		// remove embedded objects
		if(preg_match_all('!<object[^>]*>.*<\/object[^>]*>!iS', $content, $matches, PREG_SET_ORDER) > 0)
		{
			foreach($matches[0] as $data)
				$content = str_replace($data, '', $content);
		}
	}

	return $content;
}

/**
* Check for php code in html or script blocks
**/
function PortaMx_GetInsidePHP(&$content)
{
	$phpcount = preg_match_all('/(<\?)(php)(.*)\?>/Ums', $content, $matches, PREG_SET_ORDER);
	if($phpcount > 0)
	{
		// remove duplicate code
		$cnt = $phpcount -1;
		for($i = 0; $i < $cnt; $i++) {
			if($matches[$i][0] == $matches[$i+1][0])
			{
				unset($matches[$i]);
				$cnt--;
			}
		}

		// create find/replace array,
		foreach($matches as $key => $phpevals)
		{
			$phpcode[$key] = '\';' . "\n". trim($phpevals[3]) ."\n". 'echo \'';
			$remove[$key] = $phpevals[0];
			$marker[$key] = '@['. $key .']@';
		}
		$content = str_replace($remove, $marker, $content);

		// remove spaces, cr, lf before and after php code
		$start = 0;
		foreach($marker as $find)
		{
			$end = strpos($content, $find, $start);
			$content = str_replace(substr($content, $start, $end), trim(substr($content, $start, $end)), $content);
			$start = strpos($content, $find, $start) + strlen($find);
		}

		// escape single quotes for php echo
		$content = str_replace("'", "\'", $content);

		// put a echo arond for php eval and replace the marker with plain php code
		$content = "echo '". str_replace($marker, $phpcode, $content) ."';";

		// cleanup
		unset($matches);
		unset($phpcode);
	}
	return $phpcount > 0;
}

/**
* Post teaser (shorten posts by given word/character count).
* $remtags = true: remove Links from content.
* $remimgs = true: remove images.
* $morelink: if give, it's added at the end of the content
*/
function PortaMx_Tease_posts($content, $wordcount, $morelink = '', $remtags = false, $remimgs = false)
{
	global $context, $txt, $pmxcFunc, $pmx_transchr, $pmx_voidtags;

	// remove images/links
	if($remtags || $remimgs)
		$content = PortaMx_revoveLinks($content, $remtags, $remimgs);

	// cleanup EOT
	$content = pmx_tease_cleanup($content);

	// setup Post teaser mode
	$PmxTeaseCount = (empty($context['pmx']['settings']['teasermode']) ? 'pmx_teasecountwords' : 'pmx_teasecountchars');
	$PmxTeaseShorten = (empty($context['pmx']['settings']['teasermode']) ? 'pmx_teasegetwords' : 'pmx_teasegetchars');
	$pmx_transchr = array_flip(get_html_translation_table(HTML_SPECIALCHARS, ENT_QUOTES)) + array('&#039;' => '\'', '&nbsp;' => ' ', '&#160;' => ' ');
	$teaseMode = intval(!empty($context['pmx']['settings']['teasermode']));
	$content = str_replace(array("\n", "\r"), '', $content);
	$contentlen = $PmxTeaseCount($content);
	$teased = false;
	$wordcount = ($wordcount == -1 ? pmx_teasecountchars($content) : $wordcount);
	$pmx_voidtags = array('area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr');

	// we have a html teaser?
	if(preg_match('/(<span|<div)\s+style=\"page-break-after\:/is', $content, $match) > 0)
	{
		$pgbrk = $pmxcFunc['strpos']($content, $match[0]);
		$content = pmx_teasegetchars($pmxcFunc['substr']($content, 0, $pgbrk), $pgbrk);
		$tags = pmx_tease_gettags($content);
		$words = preg_split('/ /', $content);
		$words[count($words)-1] = preg_replace('/<(br|p|div|span|ol|ul|li|table|tr|td)[^>]*>/', '', $words[count($words)-1]);
		$content = pmx_tease_settags(implode(' ', $words), $tags);
		$context['pmx']['is_teased'] = $PmxTeaseCount($content);
		$teased = true;
	}
	else
	{
		if(($contentlen = $PmxTeaseCount($content)) > $wordcount)
		{
			$content = $PmxTeaseShorten($content, $wordcount);
			$teased = true;
		}
	}

	if(!empty($teased))
	{
		// close open tags
		$content = pmx_tease_closetags($content);

		// cleanup EOT
		$content = pmx_tease_cleanup($content);

		// get the teased length
		$context['pmx']['is_teased'] = $PmxTeaseCount($content);

		// insert teaser mark [...]
		$content .= '<span class="pmx_tease"'. sprintf($txt['pmx_teaserinfo'][$teaseMode], $context['pmx']['is_teased'], $contentlen) .'> [...]</span>';

		if(!empty($morelink))
			$content .= $morelink;
	}
	else
		$context['pmx']['is_teased'] = 0;

	return $content;
}

/**
* get word count for post_teaser.
*/
function pmx_teasecountwords($text)
{
	global $pmx_transchr;
	$text = preg_replace_callback('/(\S)(<br[^>]*>)(\S)/', function($matches){return $matches[1] . $matches[2] .' '. $matches[3];}, $text);
	$text = preg_replace_callback('/([a-zA-Z0-9])(\/)(\S)/', function($matches){return $matches[1] .' '. $matches[2] . $matches[3];}, $text);
	return count(preg_split('/ /', preg_replace('/<[^>]*>/', '', $text)));
}

/**
* get charater cont for post_teaser.
*/
function pmx_teasecountchars($text)
{
	global $pmxcFunc, $pmx_transchr;

	return $pmxcFunc['strlen'](strtr(preg_replace('/<[^>]*>/', '', $text), $pmx_transchr));
}

/**
* get a shorten wordcount string for post_teaser.
*/
function pmx_teasegetwords($text, $wordcount)
{
	global $pmx_transchr;

	$text = preg_replace_callback('/(\S)(<br[^>]*>)(\S)/', function($matches){return $matches[1] . $matches[2] .' '. $matches[3];}, $text);
	$tags = pmx_tease_gettags($text);
	$text = preg_replace_callback('/([a-zA-Z0-9])(\/)(\S)/', function($matches){return $matches[1] .' '. $matches[2] . $matches[3];}, $text);
	$words = preg_split('/ /', preg_replace('/<[^>]*>/', '', $text));
	$addwords = 0;
	foreach($words as $i => $word)
	{
		$addwords += intval(trim(strtr($word, $pmx_transchr)) == '');
		if($i == $wordcount)
			break;
	}
	$wordcount += $addwords;
	$words = preg_split('/ /', $text, $wordcount+1);
	if(isset($words[$wordcount-1]))
		$words[$wordcount-1] = preg_replace('/<[^>]*>/', '', $words[$wordcount-1]);
	unset($words[$wordcount]);

	$text = '';
	foreach($words as $i => $word)
		$text .= isset($words[$i+1]) && !empty($words[$i+1]) && $words[$i+1]{0} == '/' ? $word : $word .' ';
	unset($words);

	$text = pmx_tease_settags(rtrim($text), $tags);
	$text = preg_replace_callback('/(\S)(<br[^>]*>)(" ")/', function($matches){return $matches[1] . $matches[2];}, $text);

	return $text;
}

/**
* get a shorten charcount string for post_teaser.
*/
function pmx_teasegetchars($text, $wordcount)
{
	global $pmxcFunc, $pmx_transchr;

	$tags = pmx_tease_gettags($text);
	if(!empty($tags))
	{
		if(preg_match_all('/<[0-9]+>/', utf8_decode(strtr($text, $pmx_transchr)), $repl, PREG_OFFSET_CAPTURE) > 0)
		{
			foreach($repl[0] as $nt)
				if($nt[1] < $wordcount) $wordcount += strlen($nt[0]); else break;
		}
		$text = pmx_tease_settags($pmxcFunc['substr']($text, 0, $wordcount), $tags);
	}

	return $text;
}

/**
* get tags in a post_teaser block.
*/
function pmx_tease_gettags(&$text)
{
	preg_match_all('~<[^>]*>~si', $text, $tags);
	foreach($tags[0] as $i => $tag)
		$text = substr_replace($text, '<'. $i .'>', strpos($text, $tag), strlen($tag));

	return $tags[0];
}

/**
* set tags in a post_teaser block.
*/
function pmx_tease_settags($text, $tags)
{
	$text = rtrim($text);
	foreach($tags as $i => $tag)
	{
		$repl = '<'. strval($i) .'>';
		if(strpos($text, $repl) === false)
			break;
		$text = substr_replace($text, $tag, strpos($text, $repl), strlen($repl));
	}

	return $text;
}

/**
* close open tags in a post_teaser block.
*/
function pmx_tease_closetags($text)
{
	global $pmx_voidtags;

	preg_match_all('~<(\w+)[^>]*>~s', $text, $open);
	preg_match_all('~<\/(\w+)[^>]*>~s', $text, $closed);
	foreach($open[1] as $i => $tag)
	{
		if(in_array(strtolower($tag),  $pmx_voidtags))
			unset($open[1][$i]);
		elseif(($fnd = array_search($tag, $closed[1])) !== false)
		{
			unset($closed[1][$fnd]);
			unset($open[1][$i]);
		}
	}

	foreach(array_reverse($open[1]) as $element)
		$text .= "</$element>";

	return $text;
}

/**
* remove all <br> and spaces tags from end of post_teaser block.
*/
function pmx_tease_cleanup($text)
{
	do {
			$l = strlen($text);
			$text = rtrim(preg_replace('/<.?br[^>]*>$/', '', $text));
	} while ($l > strlen($text));

	return $text;
}

/**
* convert smileys (PortaMx set)
**/
function PortaMx_BBCsmileys($content)
{
	global $modSettings, $context, $pmxCacheFunc, $pmxcFunc;

	if(!empty($content))
	{
		// smileys cached ?
		if(($data = $pmxCacheFunc['get']('smileys', false)) !== null)
		{
			$smileyPregSearch = $data['search'];
			$smileyPregReplace = $data['replace'];
		}
		else
		{
			$result = $pmxcFunc['db_query']('', '
				SELECT code, filename, description
				FROM {db_prefix}smileys
				WHERE hidden = 0
				ORDER BY LENGTH(code) DESC',
				array()
			);
			$smileyfrom = array();
			$smileyto = array();
			$smileydesc = array();
			while($row = $pmxcFunc['db_fetch_assoc']($result))
			{
				$smileyfrom[] = $row['code'];
				$smileyto[] = $pmxcFunc['htmlspecialchars']($row['filename']);
				$smileydesc[] = $row['description'];
			}
			$pmxcFunc['db_free_result']($result);

			$smileyPregReplace = array();
			$searchParts = array();
			$i = count($smileyfrom);
			while($i > 0)
			{
				$i--;
				$specialChars = $pmxcFunc['htmlspecialchars']($smileyfrom[$i], ENT_QUOTES);
				$smileyCode = '<img alt="' . strtr($specialChars, array(':' => '&#58;', '(' => '&#40;', '))' => '&#41;', ')' => '&#41;', '$' => '&#36;', '[' => '&#091;')). '" src="' . $modSettings['smileys_url'] . '/portamx/' . $smileyto[$i] . '" title="'. $smileydesc[$i] .'" class="smiley" />';

				$smileyPregReplace[$smileyfrom[$i]] = $smileyCode;
				$searchParts[] = preg_quote($smileyfrom[$i], '~');
				if($smileyfrom[$i] != $specialChars)
				{
					$smileyPregReplace[$specialChars] = $smileyCode;
					$searchParts[] = preg_quote($specialChars, '~');
				}
			}

			$non_breaking_space = $context['utf8'] ? '\x{A0}' : '\xA0';
			$smileyPregSearch = '~(?<=[>:\?\.\s'. $non_breaking_space .'[\]()*\\\;]|^)('. implode('|', $searchParts) .')(?=[^[:alpha:]0-9]|$)~u';

			// put to cache
			$data['search'] = $smileyPregSearch;
			$data['replace'] = $smileyPregReplace;
			$pmxCacheFunc['put']('smileys', $data, 86400, false);
		}

		// convert smileys
		$content = preg_replace_callback($smileyPregSearch, function($matches) use ($smileyPregReplace) {return $smileyPregReplace[$matches[1]];}, $content);
	}
	return $content;
}

/**
* convert smileys Images to BBC code (PortaMx set)
**/
function PortaMx_SmileyToBBC($content)
{
	global $modSettings;

	if(!empty($content))
	{
		$content = str_replace('&nbsp;', ' ', preg_replace('/<br[^>]*>/', '<br>', $content));
		if(preg_match_all('~<img.*'. preg_quote($modSettings['smileys_url']) .'[^>]*>~U', $content, $match) > 0)
		{
			foreach($match[0] as $idx => $img)
				if(preg_match('/alt?=?\"([^\"]*\")/', $img, $smcode))
					$content = str_replace($img, html_entity_decode(str_replace('"', '', $smcode[1])), $content);
		}
	}
	return $content;
}

/**
* Fatal Error redirect
**/
function pmx_fatalerror($redir, &$blockobjects)
{
	if(!empty($blockobjects))
	{
		if(isset($blockobjects['front']))
			unset($blockobjects['front']);
		if(isset($blockobjects['pages']))
			unset($blockobjects['pages']);
	}
	redirectexit('pmxerror='. $redir);
}

/**
 Get attachments (image && thumb) and check approved
 **/
function pmx_GetAttachments($posts)
{
	global $modSettings, $context, $user_info, $pmxcFunc;

	if(empty($user_info['is_admin']))
	{
		$request = $pmxcFunc['db_query']('', '
			SELECT id_group, permission 
			FROM {db_prefix}board_permissions
			WHERE id_group IN ({array_int:groups}) AND permission = {string:perm}',
			array(
				'groups' => $user_info['groups'],
				'perm' => 'view_attachments',
			)
		);
		$found = $pmxcFunc['db_num_rows']($request);
		$pmxcFunc['db_free_result']($request);

		if(empty($found))
			return array();
	}

	$messages = array();
	if(is_array($posts))
	{
		foreach($posts as $msg => $data)
		{
			if(is_array($data))
			{
				foreach($data as $img)
					$messages[$msg] = $msg;
			}
			else
				$messages[$msg] = $msg;
		}
	}

	$attaches = array();
	if(!empty($messages)&& is_array($messages))
	{
		$request = $pmxcFunc['db_query']('', '
			SELECT
				a.id_attach, a.id_msg, a.filename, a.approved, m.id_topic, a.attachment_type,
				a.width, a.height'. (empty($modSettings['attachmentShowImages']) || empty($modSettings['attachmentThumbnails']) ? '' : ',
				COALESCE(thumb.id_attach, 0) AS id_thumb, thumb.width AS thumb_width, thumb.height AS thumb_height') . '
			FROM {db_prefix}attachments AS a' . (empty($modSettings['attachmentShowImages']) || empty($modSettings['attachmentThumbnails']) ? '' : '
				LEFT JOIN {db_prefix}attachments AS thumb ON (thumb.id_attach = a.id_thumb)') .'
				LEFT JOIN {db_prefix}messages AS m ON (a.id_msg = m.id_msg)
			WHERE a.id_msg IN ({array_int:msg_list}) AND a.attachment_type = 0 AND a.mime_type like {string:mimetype}',
			array(
				'msg_list' => array_keys($messages),
				'is_approved' => 1,
				'mimetype' => 'image%'
			)
		);

		while($row = $pmxcFunc['db_fetch_assoc']($request))
		{
			if(in_array($row['id_msg'], $messages) && !is_array($posts[$row['id_msg']]) || (is_array($posts[$row['id_msg']]) && !in_array($row['id_attach'], $posts[$row['id_msg']])))
				$attaches[$row['id_msg']][$row['id_attach']] = $row;
		}
		$pmxcFunc['db_free_result']($request);
	}
	return $attaches;
}

/**
	Replace < and > with &lt and &gt in BBC-Content (exept in code / php areas)
	Used for code / PHP parts in BBC blocks and BBC article
**/
function prepare_bbc_content(&$content)
{
	$needchg = array();

	if(preg_match_all('/<[^>]*>/U', $content, $html, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE) !== false)
	{
		foreach($html[0] as $k => $val)
			if(!in_array($val[0], array('<br>', '<br/>', '<br />')))
				$needchg[$val[1]] = $val[0];

		if(count($needchg) > 0)
		{
			if(preg_match_all('/\[php\].*\[\/php\]/Us', $content, $php_matches, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE) !== false)
			{
				foreach($php_matches[0] as $test)
				{
					foreach($needchg as $o => $v)
						if($o >= $test[1] && $o <= $test[1] + strlen($test[0]))
							unset($needchg[$o]);
				}
			}
			if(preg_match_all('/\[code\].*\[\/code\]/Us', $content, $code_matches, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE) !== false)
			{
				foreach($php_matches[0] as $test)
				{
					foreach($needchg as $o => $v)
						if($o >= $test[1] && $o <= $test[1] + strlen($test[0]))
							unset($needchg[$o]);
				}
			}
			if(count($needchg) > 0)
			{
				$needchg = array_reverse($needchg, true);
				foreach($needchg as $p => $v)
				{
					$l = strlen($v);
					$v = str_replace(array('<', '>'), array('&lt;', '&gt;'), $v);
					$content = substr_replace($content, $v, $p, $l);
				}
			}
		}
	}
}

/**
* modify content for LightBox
*/
function pmx_ContentLightBox($content)
{
	global $context, $txt;

	// find the images they can zoomed
	if(preg_match_all('/<img.*(class?=?\"bbc_img\s+resized\")[^>]*>/U', $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) > 0)
	{
		foreach($matches as $data)
		{
			// setup ALT and TITLE, add lightbox code
			preg_match('/src?=?(\"[^\"]*\")/', $data[0][0], $srcstr);
			$altstr = strtolower(trim(substr($srcstr[1], 1+ strrpos($srcstr[1], '/')), '"'));
			$replace = $data[0][0];
			$fnd = array('~alt?=?\"[^?"]*\"~', '~title?=?\"[^\"]*\"~', '~class?=?\"[^\"]*\"~', '~\soncontextmenu?=?\"return false\"~');
			$replace = preg_replace($fnd, '', $replace);
			$replace = preg_replace('/<img/', '<img alt="'. $altstr .'"'. (isset($context['lbimage_data']['lightbox_id']) ? '' : ' oncontextmenu="return false"'), $replace);

			if(isset($context['lbimage_data']['lightbox_id']))
				$replace = '<a class="lb-link" href="" data-link='. $srcstr[1] .' title="'. $txt['lightbox_expand'] .'" data-lightbox="'. $context['lbimage_data']['lightbox_id'] .'" data-title="'. $altstr .'" oncontextmenu="return false">'. $replace .'</a>';

			$content = substr_replace($content, $replace, strpos($content, $data[0][0]), strlen($data[0][0]));
		}
	}

	// convert smileys to the users set..
	return convertSmileysToUser($content);
}

/**
* Convert smileys to the users Smiley set
*/
function convertSmileysToUser($content)
{
	global $modSettings, $user_info;

	if(preg_match_all('~<img.*'. preg_quote($modSettings['smileys_url'] .'/') .'(.*[^\/]*)\/[^>]*>~U', $content, $match, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) > 0)
	{
		while(($data = array_pop($match)) !== null)
		{
			if($data[1][0] !== $user_info['smiley_set'])
				$content = substr_replace($content, $user_info['smiley_set'], $data[1][1], strlen($data[1][0]));
		}
	}
	return $content;
}

/**
* modify the outbuffer
*/
function ob_portamx($buffer)
{
	global $context;

	// set ptop to admin links
	if(preg_match_all('~<a.*href?=.*\?action[=admin|=portal][^\"]*\"~imU', $buffer, $match))
	{
		foreach($match[0] as $data)
		{
			if((strpos($data, '=admin') !== false || strpos($data, '=portal') !== false) && strpos($data, 'adminlogoff') === false)
				$buffer = str_replace($data, str_replace($data, rtrim($data, '"') .'#ptop"', $data), $buffer);
		}
	}

	if(preg_match_all('~<form.*\?action[=admin|=portal][^\"]*\"~imU', $buffer, $match))
	{
		foreach($match[0] as $data)
		{
			if(strpos($data, 'search_form') === false && strpos($data, 'login2') === false)
				$buffer = str_replace($data, str_replace($data, rtrim($data, '"') .'#ptop"', $data), $buffer);
		}
	}

	return $buffer;
}

/**
* Check Group access.
*/
function allowPmxGroup($groupstr)
{
	global $user_info;

	$isAdmin = allowedTo('admin_forum');

	// get the groups and (we have) deny groups
	@list($groups, $denygrps) = Pmx_StrToArray($groupstr, ',', '=');
	$result = !empty($isAdmin) ? (true xor count(array_intersect($user_info['groups'], $denygrps)) > 0) : count(array_intersect($user_info['groups'], $denygrps)) == 0;
	return !empty($isAdmin) ? $result : ($result && count(array_intersect($user_info['groups'], array_diff($groups, $denygrps))) > 0);
}

/**
* Check access
*/
function allowPmx($permission, $hideAdmin = false)
{
	global $context, $user_info;

	$isAdmin = allowedTo('admin_forum');

	// Administrators
	if(empty($hideAdmin) && !empty($isAdmin))
		return true;

	if(empty($context['pmx']['permissions']))
		return false;

	// Check if they have access
	$perms = array();
	$permission = Pmx_StrToArray($permission);
	foreach($permission as $perm)
		$perms = (array_key_exists($perm, $context['pmx']['permissions']) ? array_merge($perms, $context['pmx']['permissions'][$perm]) : $perms);
	if(empty($perm) || !is_array($user_info['groups']))
		return (0 && empty($isAdmin));
	else
		return count(array_intersect(array_unique($perms), $user_info['groups'])) > 0 && empty($isAdmin);
}

/**
* formatted output stream (html) from any variable.
*/
function PortaMx_Printvar($vardata, $varname = '', $dept = 0)
{
	global $pmxcFunc;

	$result = '';
	$find_replace = array(
		'find' => array('&nbsp;', '&quot;', '&lt;', '&gt;', '&amp;'),
		'repl' => array(' ', '"', '<', '>', '&')
	);
	$format = array(
		'find' => array("\n", "\t"),
		'repl' => array('<br />', '&nbsp;&nbsp;')
	);

	if(is_array($vardata) || is_object($vardata))
	{
		if(!empty($dept))
			$varname = ($varname != '' ? ($varname{0} == '$' ? $varname : (is_string($varname) ? '\''. $varname .'\'' : strval($varname))) : $varname);

		if($varname != '')
			$result .= str_pad('', $dept*6, '&'.'nbsp;', STR_PAD_LEFT) . (empty($dept) ? '<b>'. $pmxcFunc['htmlspecialchars']($varname) .'</b>' : $pmxcFunc['htmlspecialchars']($varname)) .($dept > 0 ? ' => ' : ' = ') . (is_object($vardata) ? 'object(' : 'array(') .'<br />';
		else
			$result .= str_pad('', $dept*6, '&'.'nbsp;', STR_PAD_LEFT) . $pmxcFunc['htmlspecialchars']($varname) .($dept > 0 ? ' => ' : ' = '). (is_object($vardata) ? 'object(' : 'array(') .'<br />';

		$dept += 3;
		foreach($vardata as $key => $val)
		{
			if(is_array($val) || is_object($val))
				$result .= PortaMx_Printvar($val, $key, $dept);
			else
			{
				$val = (is_string($val) ? '\''. str_replace($format['find'], $format['repl'], $pmxcFunc['htmlspecialchars'](str_replace($find_replace['find'], $find_replace['repl'], $val), ENT_NOQUOTES) .'\'') : (is_bool($val) ? (!empty($val) ? 'true' : 'false') : strval($val)));
				$result .= str_pad('', $dept*6, '&'.'nbsp;', STR_PAD_LEFT) . (is_string($key) ? '\''. $pmxcFunc['htmlspecialchars']($key) .'\'' : strval($key)) .' => '. $val .',<br />';
			}
		}
		$result .= str_pad('', ($dept - 3)*6, '&'.'nbsp;', STR_PAD_LEFT) . ')'. ($dept - 3 > 0 ? ',' : '') .'<br />';
	}
	else
	{
		$vardata = (is_string($vardata) ? '\''. str_replace($format['find'], $format['repl'], $pmxcFunc['htmlspecialchars'](str_replace($find_replace['find'], $find_replace['repl'], $vardata), ENT_NOQUOTES) .'\'') : (is_bool($vardata) ? (!empty($vardata) ? 'true' : 'false') : $vardata));
		$varname = ($varname != '' ? ($varname{0} == '$' ? $varname : (is_string($varname) ? '\''. $varname .'\'' : strval($varname))) : $varname);
		$result .= str_pad('', $dept*6, '&'.'nbsp;', STR_PAD_LEFT) . $varname .' = '. $vardata .'<br />';
	}
	return $result;
}

/**
* create the header for PortaMx.
*/
function PortaMx_headers($action = '')
{
	global $context, $settings, $modSettings, $txt, $options, $boarddir, $boardurl, $user_info, $language, $cookiename;

	$panel_names = array_keys($txt['pmx_block_panels']);
	foreach($panel_names as $pname)
	{
		// set panel upshrink
		if(empty($context['pmx']['settings'][$pname .'_panel']['collapse']))
		{
			$cook = 'upshr'. $pname .'Panel';
			$cookval = get_cookie($cook);
			$options['collapse_'. $pname] = !empty($cookval);
		}
		else
			$options['collapse_'. $pname] = false;
	}

	// switch of xbarkeys on posting
	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : ''; 
	if($action == 'post')
		$context['pmx']['xbarkeys'] = 0;

	$context['Is_Admin'] = allowPmx('pmx_admin');
	addInlineJavascript('
	pmx_boardurl = \''. $boardurl .'/\';
	ttliconurl = \''. $context['pmx_Iconsurl'] .'\';
	var pmx_failed_image = \''. $context['pmx_imageurl'] .'missing_image.png\';
	var pmx_failed_image_text = \''. $txt['pmx_hs_noimage'] .'\';
	var pmx_onedit = false;
	var pmx_inAdmin = '. (!empty($context['pmx']['inAdmin']) ? 'true;' : 'false;') .'
	var pmx_blockOnOff_enabled = '. (!empty($context['In_Administration']) && !empty($context['pmx']['settings']['loadinactive']) ? 'true;' : 'false;') .'
	var curTop = 0;
	var oldHeigth = 0;
	var pmx_colwidth = '. (empty($context['pmx']['settings']['colminwidth']) ? 0 : $context['pmx']['settings']['colminwidth']) .';
	var isIE = '. (!$context['browser']['is_opera'] && !$context['browser']['is_firefox'] && !$context['browser']['is_chrome'] && !$context['browser']['is_safari'] && strpos($_SERVER['HTTP_USER_AGENT'], 'Edge/') === false ? 'true' : 'false') .';
	var isEdge = '. (strpos($_SERVER['HTTP_USER_AGENT'], 'Edge/') !== false ? 'true' : 'false') .';
	var isFireFox = '. ($context['browser']['is_firefox'] && !$context['browser']['is_chrome'] && !$context['browser']['is_safari'] && strpos($_SERVER['HTTP_USER_AGENT'], 'Edge/') === false ? 'true' : 'false') .';
	var isOpera = '. ($context['browser']['is_opera'] && strpos($_SERVER['HTTP_USER_AGENT'], 'Edge/') === false ? 'true' : 'false') .';
	var isChrome = '. ($context['browser']['is_chrome'] && strpos($_SERVER['HTTP_USER_AGENT'], 'Edge/') === false ? 'true' : 'false') .';
	var isWebKit = '. ($context['browser']['is_webkit'] !== false ? 'true' : 'false') .';
	$(window).keydown(function(e){xBarKeys(e);});
');

	loadCSSFile(PortaMx_loadCompressed('portal.css'), array('external' => true));

	// Find the current index.css to get windowbg2 to work
	$cssfile = file_exists($settings['theme_dir'] . '/css/index.css') ? $settings['theme_dir'] . '/css/index.css' : $settings['default_theme_dir'] . '/css/index.css';
	$css = file_get_contents($cssfile);
	preg_match('~'. preg_quote('.windowbg:nth-of-type(odd)') .'([^\{]*\{[^\}]*\})~', $css, $match);
	if(!empty($match[1]))
	{
		$css = trim(str_replace(array('{','}'), '', $match[1]));
		addInlineCss('
	.windowbg2:nth-of-type(even){'. $css .'}');
	}

	if(!empty($context['browser']['is_edge']))
		addInlineCss('
	.pwindiconBlk{top:-23px !important;}');
	elseif(!empty($context['browser']['is_ie11']))
		addInlineCss('
	.pwindiconBlk{top:-22px !important;}
	.ddImageBlk{top:-18px !important;}');
	if(!empty($context['browser']['is_firefox']))
		addInlineCss('
	.pmx_filter,.pmx_nofilter{margin-top:-21px;}
	.ddImageBlk{top:-24px !important;}');

	if($action == 'frontpage')
		addInlineCss('.bbc_code{max-height:12.7em;line-height:1.3em;font-size: 11px;}');

	$Xtemp = $context['pmx']['settings']['xbaroffset_top'];
	if(!empty($context['pmx']['settings']['head']['collapse']) || !empty($context['pmx']['xbar_head']) || $context['Is_Admin'])
	{
		addInlineCss('
	#xbarhead{top:'. $context['pmx']['settings']['xbaroffset_top'] .'px !important;}');
		$Xtemp = strval(intval($context['pmx']['settings']['xbaroffset_top'])+25);
	}
	if(!empty($context['pmx']['settings']['top']['collapse']) || !empty($context['pmx']['xbar_top']) || $context['Is_Admin'])
		addInlineCss('
	#xbartop{top:'. $Xtemp.'px !important;}');

	$Xtemp = $context['pmx']['settings']['xbaroffset_foot'];
	if(!empty($context['pmx']['collapse']['foot']['collapse']) || !empty($context['pmx']['xbar_foot']) || $context['Is_Admin'])
	{
		addInlineCSS('
	#xbarfoot{bottom:'. $context['pmx']['settings']['xbaroffset_foot'] .'px !important;}');
		$Xtemp = strval(intval($context['pmx']['settings']['xbaroffset_foot'])+25);
	}
	if(!empty($context['pmx']['collapse']['bottom']['collapse']) || !empty($context['pmx']['xbar_bottom']) || $context['Is_Admin'])
		addInlineCSS('
	#xbarbottom{bottom:'. $Xtemp .'px !important;}');

	// javascript for the footer part
	addInlineJavascript('
	document.oncontextmenu= function(e){var target = (typeof e !="undefined")? e.target : event.srcElement;
	if (target.tagName == "IMG" || (target.tagName == "A" && target.firstChild && target.firstChild.tagName == "IMG")) return false;}
	var pmx_xBarKeys = '. (!empty($context['pmx']['xbarkeys']) ? 'true' : 'false') .';
	var xBarKeys_Status = pmx_xBarKeys;
	var panel_text = new Object();', true);

	$tmp = '';
	foreach($txt['pmx_block_panels'] as $key => $val)
		$tmp .= '
	panel_text["'. $key .'"] = "'. htmlentities($val, ENT_QUOTES, $context['pmx']['encoding']) .'";';
	addInlineJavascript("\t". str_replace("\n", "\n\t", PortaMx_compressJS($tmp)), true);

	$tmp = '
	function setUpshrinkTitles() {if(this.opt.bToggleEnabled){ var panel = this.opt.aSwappableContainers[0].substring(8, this.opt.aSwappableContainers[0].length - 3).toLowerCase(); document.getElementById("xbar" + panel).setAttribute("title", (this.bCollapsed ? "'. htmlentities($txt['pmx_hidepanel'], ENT_QUOTES, $context['pmx']['encoding']) .'" : "'. htmlentities($txt['pmx_showpanel'], ENT_QUOTES, $context['pmx']['encoding']) .'") + panel_text[panel]);}}';
	addInlineJavascript("\t". str_replace("\n", "\n\t", PortaMx_compressJS($tmp)), true);

	foreach($panel_names as $pname)
	{
		if(empty($context['pmx']['settings'][$pname .'_panel']['collapse']) || $context['Is_Admin'])
		{
		$tmp = '
	var '. $pname .'Panel = new pmxc_Toggle({
	bToggleEnabled: '. (empty($context['pmx']['show_'. $pname .'panel']) ? 'false' : 'true') .',
	bCurrentlyCollapsed: '. (empty($options['collapse_'. $pname]) ? 'false' : 'true') .','. (!empty($context['pmx']['xbar_'. $pname]) ? '
	funcOnBeforeCollapse: setUpshrinkTitles,
	funcOnBeforeExpand: setUpshrinkTitles,' : '') .'
	aSwappableContainers: [
		\'upshrink'. ucfirst($pname) .'Bar\'
	],
		oCookieOptions: {
			bUseCookie: true,
			sCookieName: \'upshr'. $pname .'Panel\',
			sCookieValue: \''. $options['collapse_'. $pname] .'\'
		}
	});';
		addInlineJavascript("\t". str_replace("\n", "\n\t", PortaMx_compressJS($tmp)), true);
		}
	}
}
?>