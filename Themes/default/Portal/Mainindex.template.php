<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * file Mainindex.template.php
 * Template for the Maininxdex (Forumpage).
 *
 * @version 1.41
 */

/**
* The sub template above the mainframe.
*/
function template_portal_above()
{
	global $context, $options, $modSettings, $txt;

	echo '
			<input type="hidden" id="pmx_topset" name="pmx_topset" value="" style="height:0px;" />';

	// head panel
	if(!empty($context['pmx']['show_headpanel']))
	{
		if(!empty($context['pmx']['xbar_head']))
			echo '
			<div id="xbarhead" title="'. (empty($options['collapse_head']) ? $txt['pmx_hidepanel'] : $txt['pmx_showpanel']) . $txt['pmx_block_panels']['head'] .'" onclick="headPanel.toggle()"'. (!empty($context['pmx']['settings']['head_panel']['collapse']) || !empty($context['In_Administration']) ? ' style="display:none"' : '') .'></div>';

		echo '
			<div id="pmx_head_panel" style="display:'. (empty($context['pmx']['settings']['head_panel']['collapse']) && !empty($options['collapse_head']) ? ':none;' : 'block;') .'">';

		echo '
				<div id="upshrinkHeadBar" style="';

		// Height / overflow set?
		if(!empty($context['pmx']['settings']['head_panel']['size']))
		{
			echo 'max-height:'. $context['pmx']['settings']['head_panel']['size'] .'px;';
			if(!empty($context['pmx']['settings']['head_panel']['overflow']))
				echo 'overflow:'. $context['pmx']['settings']['head_panel']['overflow'] .';';
		}

		echo 'display:'. (!empty($options['collapse_head']) ? 'none;' : 'block;') .'">';

		PortaMx_ShowBlocks('head', 1);

		echo '
				</div>
			</div>';
	}

	echo '
			<div class="pmx_table">
				<div class="pmx_tbl_tr">';

	// Left panel
	if(!empty($context['pmx']['show_leftpanel']))
	{
		if(!empty($context['Is_Admin']))
			$options['collapse_left'] = get_cookie('upshrleftPanel');

		echo '
					<div class="pmx_tbl_td">';

		if(!empty($context['pmx']['xbar_left']))
			echo '
						<div id="xbarleft" title="'. (empty($options['collapse_left']) ? $txt['pmx_hidepanel'] : $txt['pmx_showpanel']) . $txt['pmx_block_panels']['left'] .'" onclick="leftPanel.toggle()"'. (!empty($context['pmx']['settings']['left_panel']['collapse']) ? ' style="display:none"' : '') .'></div>';

			Show_Block('Left');

		echo '
					</div>';
	}

		echo '
					<div class="pmx_tbl_td">';

	// Top panel
	if(!empty($context['pmx']['show_toppanel']))
	{
		if(!empty($context['pmx']['xbar_top']))
			echo '
						<div id="xbartop" title="'. (empty($options['collapse_top']) ? $txt['pmx_hidepanel'] : $txt['pmx_showpanel']) . $txt['pmx_block_panels']['top'] .'" onclick="topPanel.toggle()"'. (!empty($context['pmx']['settings']['top_panel']['collapse']) || !empty($context['In_Administration']) ? ' style="display:none"' : '') .'></div>';

		echo '
						<div id="pmx_top_panel" style="display:'. (empty($context['pmx']['settings']['top_panel']['collapse']) && !empty($options['collapse_top']) ? ':none;' : 'block;') .'">';

		echo '
							<div id="upshrinkTopBar" style="';

		// Height / overflow set?
		if(!empty($context['pmx']['settings']['top_panel']['size']))
		{
			echo ' max-height:'. $context['pmx']['settings']['top_panel']['size'] .'px;';

			if(!empty($context['pmx']['settings']['top_panel']['overflow']))
				echo ' overflow:'. $context['pmx']['settings']['top_panel']['overflow'] .';';
		}

		echo 'display:'. (!empty($options['collapse_top']) ? 'none;' : 'block;') .'">';

		PortaMx_ShowBlocks('top', 1);

		echo '
							</div>
						</div>';
	}

	echo '
						<div id="portal_main">';
}

/**
* The sub template below the mainframe.
*/
function template_portal_below()
{
	global $context, $options, $txt;

	echo '
						</div>';

	// Bottom panel
	if(!empty($context['pmx']['show_bottompanel']))
	{
		if(!empty($context['pmx']['xbar_bottom']))
			echo '
						<div id="xbarbottom" title="'. (empty($options['collapse_top']) ? $txt['pmx_hidepanel'] : $txt['pmx_showpanel']) . $txt['pmx_block_panels']['bottom'] .'" onclick="bottomPanel.toggle()"'. (!empty($context['pmx']['xbar_bottom_hide']) || !empty($context['pmx']['settings']['bottom_panel']['collapse']) || !empty($context['In_Administration']) ? ' style="display:none"' : '') .'></div>';

		echo '
						<div id="upshrinkBottomBar" style="';

		// Height / overflow set?
		if(!empty($context['pmx']['settings']['bottom_panel']['size']))
		{
			echo 'max-height:'. $context['pmx']['settings']['bottom_panel']['size'] .'px;';
			if(!empty($context['pmx']['settings']['bottom_panel']['overflow']))
				echo 'overflow:'. $context['pmx']['settings']['bottom_panel']['overflow'] .';';
		}

		echo 'display:'. (!empty($options['collapse_bottom']) ? 'none;' : 'block;') .'">';

		PortaMx_ShowBlocks('bottom', 1);

		echo '
						</div>';
	}

	echo '
					</div>';

	// Right panel
	if(!empty($context['pmx']['show_rightpanel']))
	{
		if(!empty($context['Is_Admin']))
			$options['collapse_left'] = get_cookie('upshrrightPanel');

		echo '
					<div class="pmx_tbl_td">';

		if(!empty($context['pmx']['xbar_right']))
			echo '
						<div id="xbarright" title="'. (empty($options['collapse_right']) ? $txt['pmx_hidepanel'] : $txt['pmx_showpanel']) . $txt['pmx_block_panels']['right'] .'" onclick="rightPanel.toggle()"'. (!empty($context['pmx']['settings']['right_panel']['collapse']) ? ' style="display:none"' : '') .'></div>';

		Show_Block('Right');

		echo '
					</div>';
	}

	echo '
				</div>
			</div>';


	// Foot panel
	if(!empty($context['pmx']['show_footpanel']))
	{
		echo '
			<div>';

		if(!empty($context['pmx']['xbar_foot']))
			echo '
				<div id="xbarfoot" title="'. (empty($options['collapse_foot']) ? $txt['pmx_hidepanel'] : $txt['pmx_showpanel']) . $txt['pmx_block_panels']['foot'] .'" onclick="footPanel.toggle()"'. (!empty($context['pmx']['xbar_foot_hide']) || !empty($context['pmx']['settings']['foot_panel']['collapse']) || !empty($context['In_Administration']) ? ' style="display:none"' : '') .'></div>';

		echo '
				<div id="pmx_foot_panel">
					<div id="upshrinkFootBar" style="';

		// Height / overflow set?
		if(!empty($context['pmx']['settings']['foot_panel']['size']))
		{
			echo ' max-height:'. $context['pmx']['settings']['foot_panel']['size'] .'px;';
			if(!empty($context['pmx']['settings']['foot_panel']['overflow']))
				echo ' overflow:'. $context['pmx']['settings']['foot_panel']['overflow'] .';';
		}

		echo 'display:'. (!empty($options['collapse_foot']) ? 'none;' : 'block;') .'">';

		PortaMx_ShowBlocks('foot', 1);

		echo '
					</div>
				</div>
			</div>';
	}

	echo '
			<div id="xbartxt"></div>';
}

/**
* write out a side block (left or right).
*/
function Show_Block($side)
{
	global $context, $options;

	$lcside = strtolower($side);
	echo '
						<div id="upshrink'. $side .'Bar" style="max-width:'. strval(intval($context['pmx']['settings'][$lcside .'_panel']['size']) + intval($context['pmx']['settings']['panelpad'])) .'px;display:' .(!empty($options['collapse_'. $lcside]) ? 'none;' : 'block;') .'">';

	PortaMx_ShowBlocks($lcside);

	echo '
						</div>';
}
?>