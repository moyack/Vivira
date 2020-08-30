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

/*	This template is, perhaps, the most important template in the theme. It
	contains the main template layer that displays the header and footer of
	the forum, namely with main_above and main_below. It also contains the
	menu sub template, which appropriately displays the menu; the init sub
	template, which is there to set the theme up; (init can be missing.) and
	the linktree sub template, which sorts out the link tree.

	The init sub template should load any data and set any hardcoded options.

	The main_above sub template is what is shown above the main content, and
	should contain anything that should be shown up there.

	The main_below sub template, conversely, is shown after the main content.
	It should probably contain the copyright statement and some other things.

	The linktree sub template should display the link tree, using the data
	in the $context['linktree'] variable.

	The menu sub template should display all the relevant buttons the user
	wants and or needs.

	For more information on the templating system, please see the site at:
	https://www.portamx.com
*/

/**
 * Initialize the template... mainly little settings.
 */
function template_init()
{
	global $settings, $txt;

	/* $context, $options and $txt may be available for use, but may not be fully populated yet. */

	// The version this template/theme is for. This should probably be the version of PMX it was created for.
	$settings['theme_version'] = '1.4';

	// Use plain buttons - as opposed to text buttons?
	$settings['use_buttons'] = true;

	// Set the following variable to true if this theme requires the optional theme strings file to be loaded.
	$settings['require_theme_strings'] = false;

	// This defines the formatting for the page indexes used throughout the forum.
	$settings['page_index'] = array(
		'extra_before' => '<span class="pages">' . $txt['pages'] . '</span>',
		'previous_page' => '<span class="previous_page">&#9660;</span>',
		'current_page' => '<span class="current_page">%1$d</span> ',
		'page' => '<a class="navPages" href="{URL}#ptop">%2$s</a> ',
		'expand_pages' => '<span class="expand_pages"> ... </span>',
		'next_page' => '<span class="next_page">&#9660;</span>',
		'extra_after' => '',
	);

	// Allow css/js files to be disable for this specific theme.
	// Add the identifier as an array key. IE array('pmx_script'); Some external files might not add identifiers, on those cases PMX uses its filename as reference.
	if (!isset($settings['disable_files']))
		$settings['disable_files'] = array();
}

/**
 * The main sub template above the content.
 */
function template_html_above()
{
	global $context, $settings, $scripturl, $txt, $modSettings, $mbname;

	// Show right to left, the language code, and the character set for ease of translating.
	echo '<!DOCTYPE html>
<html', $context['right_to_left'] ? ' dir="rtl"' : '', !empty($txt['lang_locale']) ? ' lang="' . str_replace("_", "-", substr($txt['lang_locale'], 0, strcspn($txt['lang_locale'], "."))) . '"' : '' , '>
<head>
	<meta charset="', $context['character_set'], '" />';

	if(!empty($modSettings['google_site_verification']))
		echo '
	<meta name="google-site-verification" content="'. $modSettings['google_site_verification'] .'" />';

	if(!empty($modSettings['isMobile']))
		echo '
	<meta name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=1">';

	// load in any css from mods or themes so they can overwrite if wanted
	template_css();

	if(!checkECL_Cookie() && empty($modSettings['isMobile']))
		echo '
	<style>.dropmenu{margin-right:200px;}</style>';

	// load in any javascript from mods and themes
	template_javascript();

	echo '
	<title>', $context['page_title_html_safe'], '</title>';

	// Content related meta tags, like description, keywords, Open Graph stuff, etc...
	foreach ($context['meta_tags'] as $meta_tag)
	{
		echo '
	<meta';
		foreach ($meta_tag as $meta_key => $meta_value)
			echo ' ', $meta_key, '="', $meta_value, '"';
		echo '>';
	}

	/* What is your Lollipop's color?
	Theme Authors you can change here to make sure your theme's main color got visible on tab */
	echo '
	<meta name="theme-color" content="#557EA0">';

	// Please don't index these Mr Robot.
	if (!empty($context['robot_no_index']))
		echo '
	<meta name="robots" content="noindex">';

	// Present a canonical url for search engines to prevent duplicate content in their indices.
	if (!empty($context['canonical_url']))
		echo '
	<link rel="canonical" href="', $context['canonical_url'], '">';

	// Show all the relative links, such as help, search, contents, and the like.
	echo '
	<link rel="help" href="', $scripturl, '?action=help">
	<link rel="contents" href="', $scripturl, '">', ($context['allow_search'] ? '
	<link rel="search" href="' . $scripturl . '?action=search">' : '');

	// If RSS feeds are enabled, advertise the presence of one.
	if (!empty($modSettings['xmlnews_enable']) && (!empty($modSettings['allow_guestAccess']) || $context['user']['is_logged']))
		echo '
	<link rel="alternate" type="application/rss+xml" title="', $context['forum_name_html_safe'], ' - ', $txt['rss'], '" href="', $scripturl, '?type=rss2;action=.xml">
	<link rel="alternate" type="application/rss+xml" title="', $context['forum_name_html_safe'], ' - ', $txt['atom'], '" href="', $scripturl, '?type=atom;action=.xml">';

	// If we're viewing a topic, these should be the previous and next topics, respectively.
	if (!empty($context['links']['next']))
		echo '
	<link rel="next" href="', $context['links']['next'], '">';

	if (!empty($context['links']['prev']))
		echo '
	<link rel="prev" href="', $context['links']['prev'], '">';

	// If we're in a board, or a topic for that matter, the index will be the board's index.
	if (!empty($context['current_board']))
		echo '
	<link rel="index" href="', $scripturl, '?board=', $context['current_board'], '.0">';

	// Output any remaining HTML headers. (from mods, maybe?)
	echo $context['html_headers'];

	echo '
</head>
<body id="', (!empty($context['browser_body_id']) ? $context['browser_body_id'] : 'unknown'), '" class="action_', !empty($context['current_action']) ? $context['current_action'] : (!empty($context['current_board']) ?
		'messageindex' : (!empty($context['current_topic']) ? 'display' : 'boardindex')), !empty($context['current_board']) ? ' board_' . $context['current_board'] : '', '">';
}

/**
 * The upper part of the main template layer. This is the stuff that shows above the main forum content.
 */
function template_body_above()
{
	global $context, $settings, $scripturl, $txt, $modSettings, $user_info, $maintenance;

	// Wrapper div now echoes permanently for better layout options. h1 a is now target for "Go up" links.
	echo '
	<div id="top_section">
		<span id="head"></span>';

	// If the user is logged in, display some things that might be useful.
	if ($context['user']['is_logged'])
	{
		// Firstly, the user's menu
		echo '
		<ul class="floatleft" id="top_info">
			<li>
				<a href="', $scripturl, '?action=profile"', !empty($context['self_profile']) ? ' class="active"' : '', ' id="profile_menu_top" rel="nofollow" onclick="return false;">';
					if (!empty($context['user']['avatar']))
						echo $context['user']['avatar']['image'];
					echo $context['user']['name'], '</a>
				<div id="profile_menu" class="top_menu"></div>
			</li>';

		// Secondly, PMs if we're doing them
		if ($context['allow_pm'] && !show_gdpr_agreement())
		{
			echo '
			<li>
				<a href="', $scripturl, '?action=pm"', !empty($context['self_pm']) ? ' class="active"' : '', ' id="pm_menu_top" rel="nofollow">', $txt['pm_short'], !empty($context['user']['unread_messages']) ? ' <span class="amt">' . $context['user']['unread_messages'] . '</span>' : '', '</a>
				<div id="pm_menu" class="top_menu scrollable"></div>
			</li>';
		}

		// Thirdly, alerts
		echo '
			<li>
				<a href="', $scripturl, '?action=profile;area=showalerts;u=', $context['user']['id'] ,'"', !empty($context['self_alerts']) ? ' class="active"' : '', ' id="alerts_menu_top" rel="nofollow">', $txt['alerts'], !empty($context['user']['alerts']) ? ' <span class="amt">' . $context['user']['alerts'] . '</span>' : '', '</a>
				<div id="alerts_menu" class="top_menu scrollable"></div>
			</li>';

		// And now we're done.
		echo '
		</ul>';
	}
	// Otherwise they're a guest. Ask them to either register or login.
	else
		echo '
		<ul class="floatleft welcome">
			<li>', sprintf($txt[$context['can_register'] ? 'welcome_guest_register' : 'welcome_guest'], $txt['guest_title'], $context['forum_name_html_safe'], $scripturl . (empty($maintenance) ? '?action=login' : ''), (empty($maintenance) ? ('return reqOverlayDiv(this.href, ' . JavaScriptEscape($txt['login']) . ');') : ''), $scripturl . '?action=signup'), '</li>
		</ul>';

	if (!empty($context['languages']) && count($context['languages']) > 1)
	{
		echo '
		<form id="languages_form" method="post" class="floatright">
			<select id="language_select" name="headlangsel" onchange="this.form.submit()">';

		foreach ($context['languages'] as $language)
			echo '
				<option value="', $language['filename'], '"', isset($context['user']['language']) && $context['user']['language'] == $language['filename'] ? ' selected="selected"' : '', '>', str_replace('-utf8', '', $language['name']), '</option>';

		echo '
			</select>
		</form>';
	}

	// disable the search here if this a guest and search capcha enabled
	$head_search = ($context['user']['is_logged'] || (empty($context['user']['is_logged']) && empty($modSettings['search_enable_captcha'])));
	if ($context['allow_search'] && $head_search)
	{
		echo '
		<form id="search_form" class="floatright" action="', $scripturl, '?action=search2" method="post" accept-charset="', $context['character_set'], '">
			<input type="search" name="search" value="" class="input_text">&nbsp;';

		// Using the quick search dropdown?
		$selected = !empty($context['current_topic']) ? 'current_topic' : (!empty($context['current_board']) ? 'current_board' : 'all');

		echo '
			<select name="search_selection">
				<option value="all"', ($selected == 'all' ? ' selected' : ''), '>', $txt['search_entireforum'], ' </option>';

		// Can't limit it to a specific topic if we are not in one
		if (!empty($context['current_topic']))
			echo '
				<option value="topic"', ($selected == 'current_topic' ? ' selected' : ''), '>', $txt['search_thistopic'], '</option>';

		// Can't limit it to a specific board if we are not in one
		if (!empty($context['current_board']))
			echo '
				<option value="board"', ($selected == 'current_board' ? ' selected' : ''), '>', $txt['search_thisbrd'], '</option>';

		// Can't search for members if we can't see the memberlist
		if (!empty($context['allow_memberlist']))
			echo '
				<option value="members"', ($selected == 'members' ? ' selected' : ''), '>', $txt['search_members'], ' </option>';

		echo '
			</select>';

		// Search within current topic?
		if (!empty($context['current_topic']))
			echo '
			<input type="hidden" name="sd_topic" value="', $context['current_topic'], '">';
		// If we're on a certain board, limit it to this board ;).
		elseif (!empty($context['current_board']))
			echo '
			<input type="hidden" name="sd_brd" value="', $context['current_board'], '">';

		echo '
			<input type="submit" name="search2" value="', $txt['search'], '" class="button_submit">
			<input type="hidden" name="advanced" value="0">
		</form>';
	}

	echo '
	</div>';

	echo '
	<div id="header">
		<h1 class="forumtitle">
			<a href="', $scripturl, '">', empty($context['header_logo_url_html_safe']) ? $context['forum_name_html_safe'] : '<img src="' . $context['header_logo_url_html_safe'] . '" alt="' . $context['forum_name_html_safe'] . '">', '</a>
		</h1>';

	echo '
		', empty($settings['site_slogan']) ? '<img id="pmxlogo" src="' . $settings['images_url'] . '/portamxlogo.png" alt="PortaMx Forum" title="PortaMx Forum">' : '<div id="siteslogan" class="floatright">' . $settings['site_slogan'] . '</div>', '';

	// show the current date and time
	$context['current_time'] = $txt['days'][date_format(date_create('now'), 'w')] .', '. $context['current_time'];

	echo'
	</div>
	<div id="wrapper">
		<div id="upper_section">
			<div id="inner_section">';

	// Add small custum logo if ecl not accepted
	if(!checkECL_Cookie() && !empty($context['header_logo_url_html_safe']))
	{
		$finfo = @getimagesize($context['header_logo_url_html_safe']);
		if($finfo !== false && substr($finfo['mime'], 0, 5) == 'image')
			echo '
				<a class="lb-link" href="'. $scripturl .'" style="float:right;transform:none;" title="'. $context['forum_name_html_safe'] .'">
					<img src="'. $context['header_logo_url_html_safe'] .'" alt="'. $context['forum_name_html_safe'] .'" style="height:30px;margin-top:-8px;margin-right:0;">
				</a>';
	}

	echo '
				<div id="inner_wrap">
					<div class="user"'. (!empty($modSettings['isMobile']) && $user_info['is_guest'] ? ' style="display:block;"' : '') .'>
						', $context['current_time'], '
					</div>
					<div class="news">';

	if ($context['user']['is_logged'])
	echo '
						<a href="', $scripturl, '?action=unread" title="', $txt['unread_since_visit'], '">', $txt['view_unread_category'], '</a> &nbsp;&nbsp;
						<a href="', $scripturl, '?action=unreadreplies" title="', $txt['show_unread_replies'], '">', $txt['unread_replies'], '</a>';

	echo '	</div>
					<hr class="clear">
				</div>';

	// Load mobile menu here
	echo '
				<a class="menu_icon mobile_user_menu"></a>
				<div id="mobile_user_menu" class="popup_container">
					<div class="popup_window description">
						<div class="popup_heading">', $txt['mobile_user_menu'],'
						<a href="javascript:void(0);" class="generic_icons hide_popup"></a></div>
						', template_menu(), '
					</div>
				</div>';

	// Show the menu here, according to the menu sub template, followed by the navigation tree.
	echo '
				<div id="main_menu">';

	if(!checkECL_Cookie() && !empty($modSettings['isMobile']) && isset($modSettings['pmx_show_logo']) && !empty($modSettings['pmx_show_logo']))
		echo '
					<a class="lb-link" href="'. $scripturl .'" style="height:28px;float:right;transform:none;" title="Home">
						<img src="'. $settings['theme_url'] .'/images/portamxlogo.png" alt="PortaMx Forum" style="margin-top:-5px;">
					</a>';

	template_menu();

	echo '
				</div>';

	theme_linktree();

	echo '
			</div>
		</div>';

	// The main content should go here.
	echo '
		<div id="content_section">
			<div id="main_content_section">
				<script>fSetContentHeight();</script>';

	if(empty($modSettings['portal_enabled']))
		echo '
				<div id="portal_main">';
}

/**
 * The stuff shown immediately below the main content, including the footer
 */
function template_body_below()
{
	global $context, $txt, $scripturl, $boardurl, $modSettings, $user_info, $pmxCache;

	if(empty($modSettings['portal_enabled']))
		echo '
				</div>';

	echo '
			</div>
		</div>
		<div id="footer">';

	// There is the copyright, a disclaimer link (if set), the Rules and a global "Go to top" link at the right.
	$copyright = theme_copyright(true);
	echo '
			<div>
				<span class="footer_link">', preg_replace('~class[a-zA-Z0-9\_\-\.\"\=\s]+~', '', $copyright), '</span>';

	echo '
				<span class="footer_link floatright">'. (!empty($modSettings['requireAgreement']) ? '<a href="'. $scripturl. '?action=help;sa=rules">'. $txt['terms_and_rules']. '</a>&thinsp; | ' : '');

	echo '<a class="notxtdec" href="#head">&thinsp;', $txt['footgo_up'], '</a></span>
			</div>';

	// Show the cache status if enabled and also the cache is enabled
	if (!empty($modSettings['showCacheStatus']) && !empty($pmxCache['vals']['enabled']))
	{
		// The admin or a portal admin can clear the cache here !!
		if($user_info['is_admin'] || (!empty($modSettings['portal_enabled']) && allowPmx('pmx_admin', true)))
			echo '
			<span class="footer_info"><span class="footer_link_href" onclick="pmxCookie(\'clr\', \'\', \'\', \'cache\');" title="'. $txt['footer_clear_cache'] .'">'. trim($txt['cache']) .'</span>';
		else
			echo '
			<span class="footer_info">'. $txt['cache'];

		echo '&nbsp;<span class="footer_link" id="cachevals">';

		$values = $pmxCache['vals'];
		$values['time'] = number_format(floatval($values['time'] * 1000), $txt['numforms'][0], $txt['numforms'][1], $txt['numforms'][2]). $txt['cache_msec'];

		foreach($txt['cachestats'] as $key => $keytxt)
			echo $keytxt . (in_array($key, array('loaded', 'saved')) ? number_format(floatval($values[$key] / 1024), $txt['numforms'][0], $txt['numforms'][1], $txt['numforms'][2]) . $txt['cache_kb'] : $values[$key]);

		echo '</span></span>';
	}

	if (!empty($context['show_load_time']))
		echo '
			<span class="footer_info">', sprintf($txt['page_created_full'], number_format(floatval(str_replace(',', '.', $context['load_time'])), $txt['numforms'][0], $txt['numforms'][1], $txt['numforms'][2]), (intval($context['load_queries']) - $pmxCache['vals']['dbacs'])), '</span>';

	echo '
		</div>
	</div>';
}

/**
 * This shows any deferred JavaScript and closes out the HTML
 */
function template_html_below()
{
	global $context;

	// load in any javascipt that could be deferred to the end of the page
	template_javascript(true);

	// Output any remaining HTML footers. (from mods, maybe?)
	if(!empty($context['html_footers']))
		echo $context['html_footers'];

	echo '
</body>
</html>';
}

/**
 * Show a linktree. This is that thing that shows "My Community | General Category | General Discussion"..
 *
 * @param bool $force_show Whether to force showing it even if settings say otherwise
 */
function theme_linktree($force_show = false)
{
	global $context, $shown_linktree, $scripturl, $txt;

	// If linktree is empty, just return - also allow an override.
	if (empty($context['linktree']) || (!empty($context['dont_default_linktree']) && !$force_show))
		return;

	if(!isset($context['linktree_first_call']))
	{
		echo '
				<div class="navigate_section">
					<ul id="ptop">';

		$context['linktree_first_call'] = true;
	}
	else
		echo '
				<div class="navigate_section">
					<ul>';

	// Each tree item has a URL and name. Some may have extra_before and extra_after.
	foreach ($context['linktree'] as $link_num => $tree)
	{
		echo '
						<li', ($link_num == count($context['linktree']) - 1) ? ' class="last"' : '', '>';

		// Don't show a separator for the first one.
		// Better here. Always points to the next level when the linktree breaks to a second line.
		// Picked a better looking HTML entity, and added support for RTL plus a span for styling.
		if ($link_num != 0)
			echo '
							<span class="dividers">', $context['right_to_left'] ? ' &#9668; ' : ' &#9658; ', '</span>';

		// Show something before the link?
		if (isset($tree['extra_before']))
			echo $tree['extra_before'], ' ';

		// Show the link, including a URL if it should have one.
		if (isset($tree['url']))
			echo '
					<a href="'. $tree['url'] .'"><span>'. $tree['name'] .'</span></a>';
		else
			echo '
					<span>' . $tree['name'] . '</span>';

		// Show something after the link...?
		if (isset($tree['extra_after']))
			echo ' ', $tree['extra_after'];

		echo '
						</li>';
	}

	echo '
					</ul>
				</div>';

	$shown_linktree = true;
}

/**
 * Show the menu up top. Something like [home] [help] [profile] [logout]...
 */
function template_menu()
{
	global $context;

	echo '
					<ul class="dropmenu menu_nav">';

	// Note: Menu markup has been cleaned up to remove unnecessary spans and classes.
	foreach ($context['menu_buttons'] as $act => $button)
	{
		echo '
						<li class="button_', $act, '', !empty($button['sub_buttons']) ? ' subsections"' :'"', '>
							<a', $button['active_button'] ? ' class="active"' : '', ' href="', $button['href'], '"', (isset($button['target']) ? ' target="' . $button['target'] . '"' : ''), (strpos($button['href'], 'signup') !== false ? ' rel="nofollow"' : ''), '>
								', $button['icon'],'<span class="textmenu">', $button['title'], '</span>
							</a>';

		if (!empty($button['sub_buttons']))
		{
			echo '
							<ul>';

			foreach ($button['sub_buttons'] as $childbutton)
			{
				echo '
								<li', !empty($childbutton['sub_buttons']) ? ' class="subsections"' :'', '>
									<a href="', $childbutton['href'], '"' , isset($childbutton['target']) ? ' target="' . $childbutton['target'] . '"' : '', '>
										', $childbutton['title'], '
									</a>';
				// 3rd level menus :)
				if (!empty($childbutton['sub_buttons']))
				{
					echo '
									<ul>';

					foreach ($childbutton['sub_buttons'] as $grandchildbutton)
						echo '
										<li>
											<a href="', $grandchildbutton['href'], '"' , isset($grandchildbutton['target']) ? ' target="' . $grandchildbutton['target'] . '"' : '', '>
												', $grandchildbutton['title'], '
											</a>
										</li>';

					echo '
									</ul>';
				}

				echo '
								</li>';
			}
				echo '
							</ul>';
		}
		echo '
						</li>';
	}

	echo '
					</ul>';
}

/**
 * Generate a strip of buttons.
 *
 * @param array $button_strip An array with info for displaying the strip
 * @param string $direction The direction
 * @param array $strip_options Options for the button strip
 */
function template_button_strip($button_strip, $direction = '', $strip_options = array(), $getResult = false)
{
	global $context, $txt;

	if (!is_array($strip_options))
		$strip_options = array();

	// Create the buttons...
	$buttons = array();
	foreach ($button_strip as $key => $value)
	{
		// @todo this check here doesn't make much sense now (from 2.1 on), it should be moved to where the button array is generated
		// Kept for backward compatibility
		if (!isset($value['test']) || !empty($context[$value['test']]))
		{
			if (!isset($value['id']))
				$value['id'] = $key;

			$button = '
				<a class="button button_strip_' . $key . (!empty($value['active']) ? ' active' : '') . (isset($value['class']) ? ' '. $value['class'] : '') . '" ' . (!empty($value['url']) ? 'href="'. $value['url'] .'"' : '') . ' ' . (isset($value['custom']) ? ' ' . $value['custom'] : '') . '>' . $txt[$value['text']] . '</a>';

			if (!empty($value['sub_buttons']))
			{
				$button .= '
					<div class="top_menu dropmenu ' . $key . '_dropdown">
						<div class="viewport">
							<div class="overview">';
				foreach ($value['sub_buttons'] as $element)
				{
					if (isset($element['test']) && empty($context[$element['test']]))
						continue;

					$button .= '
								<a href="' . $element['url'] . '"><strong>' . $txt[$element['text']] . '</strong>';
					if (isset($txt[$element['text'] . '_desc']))
						$button .= '<br /><span>' . $txt[$element['text'] . '_desc'] . '</span>';
					$button .= '</a>';
				}
				$button .= '
							</div>
						</div>
					</div>';
			}

			$buttons[] = $button;
		}
	}

	// No buttons? No button strip either.
	if (empty($buttons))
		return;

	$output = '
		<div class="buttonlist'. (!empty($direction) ? ' float' . $direction : ''). '"'. (empty($buttons) ? ' style="display: none;"' : ''). (!empty($strip_options['id']) ? ' id="' . $strip_options['id'] . '"': ''). '>
			'. implode('', $buttons). '
		</div>';

	if(empty($getResult))
		echo $output;
	else
		return $output;
}

/**
 * The upper part of the maintenance warning box
 */
function template_maint_warning_above()
{
	global $txt, $context, $scripturl;

	echo '
	<div class="errorbox" id="errors">
		<dl>
			<dt>
				<strong id="error_serious">', $txt['forum_in_maintenance'], '</strong>
			</dt>
			<dd class="error" id="error_list">
				', sprintf($txt['maintenance_page'], $scripturl . '?action=admin;area=serversettings;' . $context['session_var'] . '=' . $context['session_id']), '
			</dd>
		</dl>
	</div>';
}

/**
 * The lower part of the maintenance warning box.
 */
function template_maint_warning_below()
{
}

?>