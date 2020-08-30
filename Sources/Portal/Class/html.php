<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file html.php
 * Systemblock HTML
 *
 * @version 1.41
 */

if(!defined('PMX'))
	die('This file can\'t be run without PortaMx-Forum');

/**
* @class pmxc_html
* Systemblock HTML
* @see html.php
*/
class pmxc_html extends PortaMxC_SystemBlock
{
	/**
	* ShowContent
	* Check for PHP inside, prepare the content and save in $this->cfg['content'].
	* Also check for tease the content.
	*/
	function pmxc_ShowContent()
	{
		global $context, $modSettings, $scripturl, $user_info, $txt;

		$printdir = 'ltr';
		$printChars = $context['character_set'];
		if(!empty($user_info['possibly_robot']) || !empty($user_info['is_guest']))
		{
			if(!empty($user_info['possibly_robot']))
				$noLB = true;

			// replace YT-Player html
			if(preg_match_all('~<div.*[^<].*<iframe.*<\/div>~Ums', $this->cfg['content'], $matches) > 0)
			{
				foreach($matches[0] as $embed)
					if(preg_match('/\/embed([^\?]*\?)/U', $embed, $ytid) > 0 && isset($ytid[1]))
						$this->cfg['content'] = str_replace($embed, '<div class="videodisabled"><a href="https://www.youtube.com/watch?v='. trim($ytid[1], '/?') .'" target="_blank" rel="noopener" class="bbc_link" title="'. $txt['play_on_youtube'] .'">Youtube Video</a></div>', $this->cfg['content']);
			}
		}
		else
			$noLB = !empty($modSettings['dont_use_lightbox']) || !empty($this->cfg['config']['settings']['disableHSimg']);

		$context['lbimage_data'] = array('lightbox_id' => (empty($noLB) ? $this->cfg['blocktype'] .'-'. $this->cfg['id'] : null));

		$this->cfg['content'] = '<div class="htmlblock">'. $this->cfg['content'] .'</div>';

		// remove or add lightbox code
		$this->cfg['content'] = pmx_ContentLightBox($this->cfg['content']);

		// check for tease
		if(!empty($this->cfg['config']['settings']['teaser']) && preg_match('/(<span|<div)\s+style=\"page-break-after\:/is', $this->cfg['content']) > 0)
		{
			$statID = 'blk'. $this->cfg['id'];
			$tmp = '
				<div id="short_'. $statID .'">'.
				PortaMx_Tease_posts($this->cfg['content'], -1, '<div class="smalltext" style="text-align:right;"><a id="href_short_'. $statID .'" href="'.$scripturl .'" style="padding: 0 5px;" onclick="ShowHTML(\''. $statID .'\')">'. $txt['pmx_readmore'] .'</a></div>') .'
				</div>';

			if(!empty($context['pmx']['is_teased']))
			{
				$this->cfg['content'] = preg_replace('~<div style="page-break-after\:(.*)<\/div>~i', '', $this->cfg['content']);
				$this->cfg['content'] = '
				<div id="full_'. $statID .'" style="display:none;">'. (!empty($this->cfg['config']['settings']['printing']) ? '
					<img class="pmx_printimg" src="'. $context['pmx_imageurl'] .'Print.png" alt="Print" title="'. $txt['pmx_text_printing'] .'" onclick="PmxPrintPage(\''. $printdir .'\', \''. $this->cfg['id'] .'\', \''. $printChars .'\', \''. $this->getUserTitle() .'\', \''. $txt['lightbox_help'] .'\', \''. $txt['lightbox_label'] .'\')" />
					<div id="print'. $this->cfg['id'] .'">'.
						$this->cfg['content'] .'
					</div>' : $this->cfg['content']) .'
					<div class="smalltext" style="text-align:right;">
						<a id="href_full_'. $statID .'" href="'.$scripturl .'" style="padding: 0 5px;;" onclick="ShowHTML(\''. $statID .'\')">'. $txt['pmx_readclose'] .'</a>
					</div>
				</div>'. $tmp;
				unset($tmp);
			}
		}

		if(empty($context['pmx']['is_teased']) && !empty($this->cfg['config']['settings']['printing']))
			$this->cfg['content'] = '
			<img class="pmx_printimg" src="'. $context['pmx_imageurl'] .'Print.png" alt="Print" title="'. $txt['pmx_text_printing'] .'" onclick="PmxPrintPage(\''. $printdir .'\', \''. $this->cfg['id'] .'\', \''. $printChars .'\', \''. $this->getUserTitle() .'\', \''. $txt['lightbox_help'] .'\', \''. $txt['lightbox_label'] .'\')" />
			<div id="print'. $this->cfg['id'] .'">'.
				preg_replace('~<div style="page-break-after\:(.*)<\/div>~i', '', $this->cfg['content']) .'
			</div>';

		// check for inside php code and write out the content
		if(PortaMx_GetInsidePHP($this->cfg['content']))
			eval($this->cfg['content']);
		else
			echo $this->cfg['content'];
	}
}
?>