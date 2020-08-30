<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file PortaMx.template.php
 * Main template for the Frontpage.
 *
 * @version 1.41
 */

/**
* The main template for frontpage and Page blocks.
*/
function template_main()
{
	global $context;

	if(!empty($context['pmx']['show_pagespanel']))
	{
		$placed = 0;
		if(isset($context['pmx']['viewblocks']['front']))
		{
			$spacer = intval(!empty($context['pmx']['show_pagespanel']));
			$placed = PortaMx_ShowBlocks('front', $spacer, 'before');

			$spacer = intval(count($context['pmx']['viewblocks']['front'])) >= $placed;
			PortaMx_ShowBlocks('pages', $spacer);

			PortaMx_ShowBlocks('front', 0, 'after');
		}
		else
			PortaMx_ShowBlocks('pages');
	}
	else
		PortaMx_ShowBlocks('front');
}
?>