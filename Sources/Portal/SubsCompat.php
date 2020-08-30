<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file SubsCompat.php
 * Compatibility & Subroutines for the Portal
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* Replacement for http_build_query
*/
function pmx_http_build_query($data, $prefix = '', $sep = ';')
{
	$ret = array();
	foreach ((array) $data as $k => $v)
	{
		$k = urlencode($k);
		if(is_int($k) && !empty($prefix))
			$k = $prefix . $k;
		if(is_array($v) || is_object($v))
			array_push($ret, pmx_http_build_query($v, '', $sep));
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
* Replacement for parse_url
*/
function pmx_parse_url($data, $component = '')
{
	return empty($component) ? parse_url($data) : parse_url($data, $component);
}

/**
* Read POST data with a specific key
* used for Blocks Pageindex
**/
function pmx_GetPostKey($postKey, &$result)
{
	global $modSettings;

	if(!empty($_POST[$postKey]))
	{
		if(!empty($modSettings['sef_enabled']))
			$_POST[$postKey] = pmxsef_query(str_replace('//', '/', $_POST[$postKey]));
		else
		{
			$tmp = $data = array();
			$_POST[$postKey] = explode(';', $_POST[$postKey]);
			while(list($key, $val) = pmx_each($_POST[$postKey]))
			{
				$tmp = explode('=', $val);
				$data[$tmp[0]] = isset($tmp[1]) ? $tmp[1] : '';
			}
			$_POST[$postKey] = $data;
		}
		$result = array_merge($result, $_POST[$postKey]);
		if(isset($result['pgkey']))
			unset($result['pgkey']);

		return $result;
	}
}

/**
* load customer CSS definitions.
* called from hook "integrate_pre_css_output"
**/
function PortaMx_loadCSS()
{
	global $context, $modSettings;

	if(!empty($context['pmx']['customCSS']))
	{
		$tmp = PortaMx_compressCSS($context['pmx']['customCSS']);
		if(isset($context['pmx']['customCSS']) && !empty($context['pmx']['customCSS']))
			echo '
	<style type="text/css">'."\n\t\t". PortaMx_compressCSS($context['pmx']['customCSS']) .'
	</style>';
	}
}

/**
* php syntax check
*/
function PortaMx_PHPsyntax($data)
{
	// convert and cleanup the PHP code
	$cleanstr = pack('H*', $data);
	$cleanstr = html_entity_decode($cleanstr, ENT_QUOTES | ENT_XML1, 'UTF-8');

	$trackErr = @ini_set('track_errors', true);
	$logErr = @ini_set('log_errors', false);
	$displayErr = @ini_set('display_errors', true);

	ob_end_clean();
	ob_start();
	eval($cleanstr);
	$result = ob_get_clean();

	@ini_set('track_errors', $trackErr);
	@ini_set('log_errors', $logErr);
	@ini_set('display_errors', $displayErr);

	if(strpos($result, 'Parse error: ') === false && strpos($result, 'syntax error,') === false)
		$result = 'No errors detected.';
	else
	{
		$result = substr($result, strpos($result, 'Parse error:'));
		if(strpos($result, 'expecting') !== false)
		{
			preg_match('~on\s+line\s+([0-9]+)~', strip_tags($result), $match);
			if(!empty($match[1]))
				$result = str_replace($match[1], strval(intval($match[1])-1), $result);
		}
	}
	return $result;
}
?>