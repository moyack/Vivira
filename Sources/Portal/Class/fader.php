<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file fader.php
 * Systemblock FADER
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_fader
* Systemblock FADER
* @see fader.php
*/
class pmxc_fader extends PortaMxC_SystemBlock
{
	var $faderdata;

	/**
	* InitContent.
	* Checks the cache status and create the content.
	*/
	function pmxc_InitContent()
	{
		global $pmxCacheFunc, $modSettings;

		// if visible init the content
		if($this->visible)
		{
			if($this->cfg['cache'] > 0)
			{
				// check the block cache
				if(($this->faderdata = $pmxCacheFunc['get']($this->cache_key, $this->cache_mode)) === null)
				{
					$this->getFaderData();
					$pmxCacheFunc['put']($this->cache_key, $this->faderdata, $this->cache_time, $this->cache_mode);
				}
			}
			else
				$this->getFaderData();

			$this->faderdata['iniheight'] = 0;
			$fcsr = get_cookie('oFader'. $this->cfg['id']);
			if($fcsr !== null && $fcsr !== '' && strpos($fcsr, '-') === false)
				$this->faderdata['cursor'] = intval($fcsr);
			else
			{
				$this->faderdata['cursor'] = 0;
				set_cookie('oFader'. $this->cfg['id'], 0);
			}

			if(empty($modSettings['pmxFaderLoaded']))
			{
				loadJavascriptFile(PortaMx_loadCompressed('PortalFader.js'), array('external' => true));
				$modSettings['pmxFaderLoaded'] = true;
			}

			addInlineJavascript('
	document.onreadystatechange=function(){if(document.readyState==\'interactive\'){NewFaderHeight();}}
	window.addEventListener(\'resize\', NewFaderHeight, true);');
		}
		// return the visibility flag (true/false)
		return $this->visible;
	}

	/**
	* Get Fader Data.
	*/
	function getFaderData()
	{
		$this->faderdata = array(
			'lines' => '',
			'up' => '',
			'down' => '',
			'hold' => ''
		);
		preg_match_all('~\{(.*)((\}.?=.?\(([0-9\.\,\s]+)\))|\})(\s+|\t+|\r|\n|$|\r|\n|$)~Ums', str_replace("'", "\'", $this->cfg['content']), $faderlines, PREG_PATTERN_ORDER);
		if(isset($faderlines[1]) && !empty($faderlines[1]))
		{
			foreach($faderlines[1] as $i => $value)
			{
				$fdata = trim(preg_replace(array('~>\s+<~', '~\s+~', '~\n+~', '~\r+~', '~\t+~'), array('><', ' ', '', '', ''), $value));
				if(!empty($fdata))
				{
					$this->faderdata['lines'] .= "\n".'\''. $fdata .'\',';
					$fdt = array();
					if(!empty($faderlines[4][$i]))
					{
						$fdt = explode(',', $faderlines[4][$i]);
						array_walk($fdt, function(&$v,$k){$v = floatval(trim($v));});
					}
					$this->faderdata['up'] .= (!empty($fdt[0]) ? $fdt[0] * 1000 : $this->cfg['config']['settings']['uptime'] * 1000) .',';
					$this->faderdata['down'] .= (!empty($fdt[1]) ? $fdt[1] * 1000 : $this->cfg['config']['settings']['downtime'] * 1000) .',';
					$this->faderdata['hold'] .= (!empty($fdt[2]) ? $fdt[2] * 1000 : $this->cfg['config']['settings']['holdtime'] * 1000) .',';
				}
			}
			$this->faderdata['lines'] = '['. rtrim($this->faderdata['lines'], ',') .']';
			$this->faderdata['up'] = '['. rtrim($this->faderdata['up'], ',') .']';
			$this->faderdata['down'] = '['. rtrim($this->faderdata['down'], ',') .']';
			$this->faderdata['hold'] = '['. rtrim($this->faderdata['hold'], ',') .']';
		}
	}

	/**
	* ShowContent
	* Create the fader object and output the content.
	*/
	function pmxc_ShowContent()
	{
		global $context, $txt;

		if(!empty($this->faderdata))
		{
			echo '
				<div id="oFader'. $this->cfg['id'] .'" style="overflow-y:hidden;"></div>
				<script>
				var oFader'. $this->cfg['id'] .' = new PmxOpacFader({
					fadeCsr: '. $this->faderdata['cursor'] .',
					fadeIniHeight:'. $this->faderdata['iniheight'] .',
					fadeName: \'oFader'. $this->cfg['id'] .'\',
					fadeUptime: '. $this->faderdata['up'] .',
					fadeDowntime: '. $this->faderdata['down'] .',
					fadeHoldtime: '. $this->faderdata['hold'] .',
					fadeContId: \'oFader'. $this->cfg['id'] .'\',
					fadeData: '. $this->faderdata['lines'] .'
				});
				</script>';
		}
	}
}
?>