<?php
/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx & Simple Machines
 * @copyright 2018 PortaMx,  Simple Machines and individual contributors
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 1.41
 */

/**
 * This contains the HTML for the menu bar at the top of the admin center.
 */
function template_generic_menu_dropdown_above()
{
	global $context, $modSettings, $txt;

	// Which menu are we rendering?
	$context['cur_menu_id'] = isset($context['cur_menu_id']) ? $context['cur_menu_id'] + 1 : 1;
	$menu_context = &$context['menu_data_' . $context['cur_menu_id']];

	$isPortal = !empty($modSettings['portal_enabled']) && isset($_REQUEST['area']) && substr($_REQUEST['area'], 0, 4) == 'pmx_';
	if(!empty($isPortal))
	{
		if($_REQUEST['area'] == 'pmx_settings')
		{
			$infoText = $txt['pmx_admSet_desc_'. (isset($_REQUEST['sa']) ? $_REQUEST['sa'] : 'globals')];
			$titleText = $txt['pmx_adm_settings'];
		}
		elseif($_REQUEST['area'] == 'pmx_blocks')
		{
			$infoText = $txt['pmx_admBlk_desc'];
			$titleText = $txt['pmx_adm_blocks'];
		}
		elseif($_REQUEST['area'] == 'pmx_articles')
		{
			$infoText = $txt['pmx_articles_desc'];
			$titleText = $txt['pmx_adm_articles'];
		}
		elseif($_REQUEST['area'] == 'pmx_categories')
		{
			$infoText = $txt['pmx_categories_desc'];
			$titleText = $txt['pmx_adm_categories'];
		}
		else
		{
			$infoText = '<b>'. $txt['hello_guest'] .' '. $context['user']['name'] .'!</b> '. sprintf($txt['pmx_admin_main_welcome'] ,'<span class="generic_icons help" title="'. $txt['help'] .'"></span>');
			$titleText = $txt['pmx_admin_center'];
		}
	}

	// Load the menu
	template_generic_menu($menu_context);
	template_generic_menu_mobile($menu_context);

	if(!empty($isPortal))
	{
		$curAct = '';
		if(!isset($context['pmx']['subaction']))
			$context['pmx']['subaction'] = '';

		if(isset($context['pmx']['function']) && ($context['pmx']['function'] == 'edit' || $context['pmx']['function'] == 'editnew'))
			$curAct = $context['pmx']['function'];
		elseif(isset($context['pmx']['subaction']) && ($context['pmx']['subaction'] == 'edit' || $context['pmx']['subaction'] == 'editnew')) 
			$curAct = $context['pmx']['subaction'];
		elseif($context['pmx']['subaction'])
			$curAct = $context['pmx']['subaction'];

		if(!in_array($curAct,  array('edit', 'editnew'))) 
			echo '
				<div class="cat_bar"><h3 class="catbg">'. $titleText .'</h3></div>
				<p class="information">'. $infoText .'</p>';
	}

	// This is the main table - we need it so we can keep the content to the right of it.
	echo '
				<div id="admin_content">';

	// It's possible that some pages have their own tabs they wanna force...
	if(empty($curAct) || (!empty($curAct) && !in_array($curAct,  array('edit', 'editnew'))))
	{
		template_generic_menu_tabs($menu_context);
		template_generic_menu_tabs_mobile($menu_context, true);
	}
}

/**
 * Part of the admin layer - used with generic_menu_dropdown_above to close the admin content div.
 */
function template_generic_menu_dropdown_below()
{
	echo '
				</div>';
}

function template_generic_menu_mobile(&$menu_context)
{
	global $context, $txt;

	$HeadText = $txt['mobile_user_menu'];
	if(isset($_REQUEST['action']))
	{
		if($_REQUEST['action'] == 'admin')
			$HeadText = $txt['mobile_admin_menu'];
		else if($_REQUEST['action'] == 'pm')
			$HeadText = $txt['pm_head_text'];
		else if($_REQUEST['action'] == 'profile')
			$HeadText = $txt['profile_submenu_title'];
	}

	// Load mobile menu here
	echo '
		<a class="menu_icon mobile_generic_menu_', $context['cur_menu_id'], '"></a>
		<div id="mobile_generic_menu_', $context['cur_menu_id'], '" class="popup_container">
			<div class="popup_window description">
				<div class="popup_heading">', $HeadText ,'
				<a href="javascript:void(0);" class="generic_icons hide_popup"></a></div>
				', template_generic_menu($menu_context), '
			</div>
		</div>
		<script>
			$( ".mobile_generic_menu_', $context['cur_menu_id'], '" ).click(function() {
				$( "#mobile_generic_menu_', $context['cur_menu_id'], '" ).show();
				});
			$( ".hide_popup" ).click(function() {
				$( "#mobile_generic_menu_', $context['cur_menu_id'], '" ).hide();
			});
		</script>';
}

function template_generic_menu (&$menu_context)
{
	global $context;

	echo '
				<div class="generic_menu">';

	echo '
					<ul class="dropmenu dropdown_menu_', $context['cur_menu_id'], '">';

	// Main areas first.
	foreach ($menu_context['sections'] as $section)
	{
		echo '
						<li ', !empty($section['areas']) ? 'class="subsections"' : '', '><a class="', !empty($section['selected']) ? 'active ' : '', '" href="', $section['url'], $menu_context['extra_parameters'], '">', $section['title'] , '</a>
							<ul>';

		// For every area of this section show a link to that area (bold if it's currently selected.)
		// @todo Code for additional_items class was deprecated and has been removed. Suggest following up in Sources if required.
		foreach ($section['areas'] as $i => $area)
		{
			// Not supposed to be printed?
			if (empty($area['label']) || strpos($area['icon_class'], 'pmx_center') !== false)
				continue;

			echo '
								<li', !empty($area['subsections']) ? ' class="subsections"' : '', '>';

			echo '
									<a class="', $area['icon_class'], !empty($area['selected']) ? ' chosen ' : '', '" href="', (isset($area['url']) ? $area['url'] : $menu_context['base_url'] . ';area=' . $i), $menu_context['extra_parameters'], '">', $area['icon'], $area['label'], '</a>';

			// Is this the current area, or just some area?
			if (!empty($area['selected']) && empty($context['tabs']))
					$context['tabs'] = isset($area['subsections']) ? $area['subsections'] : array();

			// Are there any subsections?
			if (!empty($area['subsections']))
			{
				echo '
									<ul>';

				foreach ($area['subsections'] as $sa => $sub)
				{
					if (!empty($sub['disabled']))
						continue;

					$url = isset($sub['url']) ? $sub['url'] : (isset($area['url']) ? $area['url'] : $menu_context['base_url'] . ';area=' . $i) . ';sa=' . $sa;

					echo '
										<li>
											<a ', !empty($sub['selected']) ? 'class="chosen" ' : '', ' href="', $url, $menu_context['extra_parameters'], '">', $sub['label'], '</a>
										</li>';
				}

				echo '
									</ul>';
			}

			echo '
								</li>';
		}
		echo '
							</ul>
						</li>';
	}

	echo '
					</ul>
				</div>';
}

/**
 * The code for displaying the menu
 *
 * @param array $menu_context An array of menu context data
 */
function template_generic_menu_tabs_mobile(&$menu_context)
{
	global $context, $txt;

	if(isset($context['menuTabsID']))
		$context['menuTabsID']++;
	else
		$context['menuTabsID'] = 1;

	// Load mobile menu here
	if(!empty($menu_context['tab_data']['title']))
		$headerText = $menu_context['tab_data']['title'];
	else if(isset($menu_context['current_area']) && $menu_context['current_area'] == 'pmx_blocks')
		$headerText = $txt['mobile_portal_blocks'];
	else if(isset($menu_context['current_area']) && $menu_context['current_area'] == 'pmx_settings')
		$headerText = $txt['mobile_portal_settings'];
	else
		$headerText = '';

	if(!empty($menu_context['tab_data']['tabs']))
		echo '
		<a class="menu_icon mobile_generic_menu_tabs', $context['menuTabsID'] ,'"></a>
		<div id="mobile_generic_menu_tabs', $context['menuTabsID'] ,'" class="popup_container">
			<div class="popup_window description">
				<div class="popup_heading"><div class="popup_head_title">'. sprintf($txt['mobile_head_submenu'], $headerText) .'</div>
				<a href="javascript:void(0);" class="generic_icons hide_popup"></a></div>
				', template_generic_menu_tabs($menu_context, true), '
			</div>
		</div>
		<script>
			$( ".mobile_generic_menu_tabs', $context['menuTabsID'] ,'" ).click(function() {
				$( "#mobile_generic_menu_tabs', $context['menuTabsID'] ,'" ).show();
				});
			$( ".hide_popup" ).click(function() {
				$( "#mobile_generic_menu_tabs', $context['menuTabsID'] ,'" ).hide();
			});
		</script>';
}

function template_generic_menu_tabs(&$menu_context, $mode = false)
{
	global $context, $settings, $scripturl, $txt;

	// Handy shortcut.
	$tab_context = &$menu_context['tab_data'];

	if (empty($mode) && (!empty($tab_context['title']) || $menu_context['current_section'] == 'portal'))
	{
		if($menu_context['current_section'] != 'portal')
			echo '
					<div class="cat_bar"', (getREQcnt('sengines,logs') == 2 ? ' style="margin-top:5px;"' : ''), '>
							<h3 class="catbg">';

		// Exactly how many tabs do we have?
		if (!empty($context['tabs']))
		{
			foreach ($context['tabs'] as $id => $tab)
			{
				// Can this not be accessed?
				if (!empty($tab['disabled']))
				{
					$tab_context['tabs'][$id]['disabled'] = true;
					continue;
				}
				// Did this not even exist - or do we not have a label?
				if (!isset($tab_context['tabs'][$id]))
					$tab_context['tabs'][$id] = array('label' => $tab['label']);
				elseif (!isset($tab_context['tabs'][$id]['label']))
					$tab_context['tabs'][$id]['label'] = $tab['label'];

				// Has a custom URL defined in the main admin structure?
				if (isset($tab['url']) && !isset($tab_context['tabs'][$id]['url']))
					$tab_context['tabs'][$id]['url'] = $tab['url'];

				// Any additional paramaters for the url?
				if (isset($tab['add_params']) && !isset($tab_context['tabs'][$id]['add_params']))
					$tab_context['tabs'][$id]['add_params'] = $tab['add_params'];

				// Has it been deemed selected?
				if (!empty($tab['is_selected']))
					$tab_context['tabs'][$id]['is_selected'] = true;

				// Does it have its own help?
				if (!empty($tab['help']))
					$tab_context['tabs'][$id]['help'] = $tab['help'];

				// Is this the last one?
				if (!empty($tab['is_last']) && !isset($tab_context['override_last']))
					$tab_context['tabs'][$id]['is_last'] = true;
			}

			// Find the selected tab
			foreach ($tab_context['tabs'] as $sa => $tab)
			{
				if (!empty($tab['is_selected']) || (isset($menu_context['current_subsection']) && $menu_context['current_subsection'] == $sa))
				{
					$selected_tab = $tab;
					$tab_context['tabs'][$sa]['is_selected'] = true;
				}
			}
		}

		// Show an icon and/or a help item?
		$notshow = getREQcnt('theme,admin');
		if (!empty($selected_tab['icon_class']) || !empty($tab_context['icon_class']) || !empty($selected_tab['icon']) || !empty($tab_context['icon']) || !empty($selected_tab['help']) || !empty($tab_context['help']))
		{
			if (!empty($selected_tab['icon_class']) || !empty($tab_context['icon_class']))
				echo '<span class="', !empty($selected_tab['icon_class']) ? $selected_tab['icon_class'] : $tab_context['icon_class'], ' icon"></span>';
			elseif (!empty($selected_tab['icon']) || !empty($tab_context['icon']))
				echo '<img src="', $settings['images_url'], '/icons/', !empty($selected_tab['icon']) ? $selected_tab['icon'] : $tab_context['icon'], '" alt="" class="icon">';

			if (!empty($selected_tab['help']) || !empty($tab_context['help']))
				echo '<a href="', $scripturl, '?action=helpadmin;help=', !empty($selected_tab['help']) ? $selected_tab['help'] : $tab_context['help'], '" onclick="return reqOverlayDiv(this.href);" class="help"><span class="generic_icons help" title="', $txt['help'],'"></span></a>&nbsp;';

			if($menu_context['current_section'] != 'portal')
				echo $tab_context['title'];
		}
		else
		{
			if(!empty($mode) && $menu_context['current_area'] == 'pmx_blocks')
				echo sprintf($txt['mobile_subMenu_var'], 'Portal Blocks');
			else if(isset($tab_context['title']))
				echo $tab_context['title']; 
		}

		if($menu_context['current_section'] != 'portal')
			echo '
						</h3>
				</div>';
	}

	// Shall we use the tabs? Yes, it's the only known way!
	$noHeadInfo = (getREQcnt('sengines,logs,errorlog,adminlog,modlog') == 2);
	if(empty($mode)&& empty($noHeadInfo))
	{
		if (!empty($selected_tab['description']) || !empty($tab_context['description']))
			echo '
						<p class="information">
							', !empty($selected_tab['description']) ? $selected_tab['description'] : $tab_context['description'], '
						</p>';
	}
	if(getREQcnt('sengines,logs') == 2)
		echo '
						<p class="information">', $txt['search_engines_description'] ,'</p>';

	if(getREQcnt('logs') == 1)
		echo '
						<div class="noinfobar"></div>';

	// Print out all the items in this tab (if any).
	if (!empty($context['tabs']))
	{
		if(empty($mode))
			echo '
				<div class="generic_menu">';

		// The admin tabs.
		echo '
						<ul class="dropmenu">';

		foreach ($context['tabs'] as $sa => $tab)
		{
			if (!empty($tab['disabled']))
				continue;

			if(isset($menu_context['current_subsection']) && $menu_context['current_subsection'] == $sa)
				$tab['is_selected'] = true;

			else if(isset($context['pmx']['subaction']) && $sa == $context['pmx']['subaction']) 
				$tab['is_selected'] = true;

			if (!empty($tab['is_selected']) && $menu_context['current_area'] != 'pmx_center')
				echo '
							<li>
								<a class="active" href="', isset($tab['url']) ? $tab['url'] : $menu_context['base_url'] . ';area=' . $menu_context['current_area'] . ';sa=' . $sa, $menu_context['extra_parameters'], isset($tab['add_params']) ? $tab['add_params'] : '', '">', $tab['label'], '</a>
							</li>';
			else
				echo '
							<li>
								<a href="', isset($tab['url']) ? $tab['url'] : $menu_context['base_url'] . ';area=' . $menu_context['current_area'] . ';sa=' . $sa, $menu_context['extra_parameters'], isset($tab['add_params']) ? $tab['add_params'] : '', '">', $tab['label'], '</a>
							</li>';
		}

		// the end of tabs
		echo '
						</ul>';

		if(empty($mode))
			echo '
				</div>';
	}
}

?>
