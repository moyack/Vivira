<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file php.php
 * Systemblock PHP
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_php
* Systemblock PHP
* @see php.php
*/
class pmxc_php extends PortaMxC_SystemBlock
{
	var $php_content;
	var $php_vars;

	/**
	* InitContent.
	* Check we have a init part
	*/
	function pmxc_InitContent()
	{
		if(preg_match('~\[\?pmx_initphp(.*)pmx_initphp\?\]~is', $this->cfg['content'], $match))
		eval($match[1]);

		return $this->visible;
	}

	/**
	* ShowContent
	* Output the content.
	*/
	function pmxc_ShowContent()
	{
		global $context, $txt;

		if(!empty($this->cfg['config']['settings']['printing']))
		{
			$printdir = 'ltr';
			$printChars = $context['character_set'];

			echo '
			<img class="pmx_printimg" src="'. $context['pmx_imageurl'] .'Print.png" alt="Print" title="'. $txt['pmx_text_printing'] .'" onclick="PmxPrintPage(\''. $printdir .'\', \''. $this->cfg['id'] .'\', \''. $printChars .'\', \''. $this->getUserTitle() .'\', \''. $txt['lightbox_help'] .'\', \''. $txt['lightbox_label'] .'\')" />
			<div id="print'. $this->cfg['id'] .'">';
		}

		// Check we have a show part
		if(preg_match('~\[\?pmx_showphp(.*)pmx_showphp\?\]~is', $this->cfg['content'], $match))
			eval($match[1]);

		// else write out the content
		else
			eval($this->cfg['content']);

		if(!empty($this->cfg['config']['settings']['printing']))
			echo '
			</div>';
	}
}
?>