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
 * This handles the Who's Online page
 */
function template_main()
{
	global $context, $settings, $scripturl, $txt;

	// Display the table header and linktree.
	echo '
	<div class="main_section" id="whos_online">
		<form action="', $scripturl, '?action=who" method="post" id="whoFilter" accept-charset="', $context['character_set'], '">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['who_title'], '</h3>
			</div>
			<div id="mlist">
				</div>
				<table class="table_grid">
					<thead>
						<tr class="title_bar">
							<th scope="col" class="lefttext" style="width: 35%;"><a href="', $scripturl, '?action=who;start=', $context['start'], ';show=', $context['show_by'], ';sort=user', $context['sort_direction'] != 'down' && $context['sort_by'] == 'user' ? '' : ';asc', '" rel="nofollow">', $txt['who_user'], $context['sort_by'] == 'user' ? '<span class="generic_icons sort_' . $context['sort_direction'] . '"></span>' : '', '</a></th>
							<th scope="col" class="lefttext time" style="width: 23%;min-width: 170px;"><a href="', $scripturl, '?action=who;start=', $context['start'], ';show=', $context['show_by'], ';sort=time', $context['sort_direction'] == 'down' && $context['sort_by'] == 'time' ? ';asc' : '', '" rel="nofollow">', $txt['who_time'], $context['sort_by'] == 'time' ? '<span class="generic_icons sort_' . $context['sort_direction'] . '"></span>' : '', '</a></th>
							<th scope="col" class="lefttext half_table" style="width:42%;">', $txt['who_action'], '</th>
						</tr>
					</thead>
					<tbody>';

	foreach ($context['members'] as $member)
	{
		echo '
						<tr class="windowbg">
							<td>';

		// Guests can't be messaged.
		if (!$member['is_guest'])
		{
			echo '
								<span class="contact_info floatleft">
									', $context['can_send_pm'] ? '<a href="' . $member['online']['href'] . '" title="' . $member['online']['text'] . '">' : '', $settings['use_image_buttons'] ? '<span class="' . ($member['online']['is_online'] == 1 ? 'on' : 'off') . '" title="' . $member['online']['text'] . '" style="margin-right:5px;vertical-align:-2px;"></span>' : $member['online']['label'], $context['can_send_pm'] ? '</a>' : '', '
								</span>';
		}

		echo '
								<span class="member', $member['is_hidden'] ? ' hidden' : '', '">
									', $member['is_guest'] ? $member['name'] : '<a href="' . $member['href'] . '" title="' . $txt['profile_of'] . ' ' . $member['name'] . '"' . (empty($member['color']) ? '' : ' style="color: ' . $member['color'] . '"') . '>' . $member['name'] . '</a>', '
								</span>';

		if (!empty($member['ip']))
			echo '
								(<a href="' . $scripturl . '?action=', ($member['is_guest'] ? 'trackip' : 'profile;area=tracking;sa=ip;u=' . $member['id']), ';searchip=' . $member['ip'] . '">' . $member['ip'] . '</a>)';

		echo '
							</td>
							<td class="time">', $member['time'], '</td>
							<td>', $member['action'], '</td>
						</tr>';
	}

	// No members?
	if (empty($context['members']))
	{
		echo '
						<tr class="windowbg">
							<td colspan="3">
							', $txt['who_no_online_' . ($context['show_by'] == 'guests' || $context['show_by'] == 'spiders' ? $context['show_by'] : 'members')], '
							</td>
						</tr>';
	}

	echo '
					</tbody>
				</table>
				<div class="pagesection who_lower">
					<div class="pagelinks floatleft">', $context['page_index'], '</div>';

	echo '
					<div class="who_selectbox floatright"><span>', $txt['who_show1'], '</span>
						<select name="show" onchange="document.forms.whoFilter.submit();">';

	foreach ($context['show_methods'] as $value => $label)
		echo '
							<option value="', $value, '" ', $value == $context['show_by'] ? ' selected' : '', '>', $label, '</option>';
	echo '
						</select>
						<noscript>
							<input type="submit" value="', $txt['go'], '" class="button_submit">
						</noscript>
				</div>
			</div>
		</form>
	</div>';
}

/**
 * This displays a nice credits page
 */
function template_credits()
{
	global $context, $txt;

	// The most important part - the credits :P.
	echo '
	<div class="main_section" id="credits">
		<div class="cat_bar">
			<h3 class="catbg">', $txt['credits'], '</h3>
		</div>';

	foreach ($context['credits'] as $section)
	{
		if (isset($section['pretext']))
		echo '
		<div class="windowbg">
			<p>', $section['pretext'], '</p>
		</div>';

		if (isset($section['title']))
		echo '
		<div class="cat_bar">
			<h3 class="catbg">', $section['title'], '</h3>
		</div>';

		echo '
		<div class="windowbg2">
			<dl>';

		$i = 0;
		foreach ($section['groups'] as $group)
		{
			if (isset($group['title']))
				echo '
				<dt>'. ($i > 0 ? '<br />' : '') .'
					<strong>', $group['title'], '</strong>
				</dt>
				<dd>';

			// Try to make this read nicely.
			if (count($group['members']) <= 1)
				echo implode($group['members']);
			else
			{
				$last_peep = array_pop($group['members']);
				echo implode(', ', $group['members']), ' ', $txt['credits_and'], ' ', $last_peep;
			}

			echo '
				</dd>';
			$i++;
		}

		echo '
			</dl>';

		if (isset($section['posttext']))
			echo '
				<p class="posttext">', $section['posttext'], '</p>';

		echo '
		</div>';
	}

	// Other software and graphics
	if (!empty($context['credits_software_graphics']))
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['credits_software_graphics'], '</h3>
		</div>
		<div class="windowbg">';

		if (!empty($context['credits_software_graphics']['graphics']))
			echo '
			<dl>
				<dt><strong>', $txt['credits_graphics'], '</strong></dt>
				<dd>', implode('</dd><dd>', $context['credits_software_graphics']['graphics']), '</dd>
			</dl>';

		if (!empty($context['credits_software_graphics']['software']))
			echo '
			<dl>
				<dt><strong>', $txt['credits_software'], '</strong></dt>
				<dd>', implode('</dd><dd>', $context['credits_software_graphics']['software']), '</dd>
			</dl>';

		if (!empty($context['credits_software_graphics']['fonts']))
			echo '
			<dl>
				<dt><strong>', $txt['credits_fonts'], '</strong></dt>
				<dd>', implode('</dd><dd>', $context['credits_software_graphics']['fonts']), '</dd>
			</dl>';
		echo '
		</div>';
	}

	// How about Modifications, we all love em
	if (!empty($context['credits_modifications']) || !empty($context['copyrights']['mods']))
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['credits_modifications'], '</h3>
		</div>
		<div class="windowbg">';

		// Display the credits.
		if (!empty($context['credits_modifications']))
			echo '
			', implode('
			<br>', $context['credits_modifications']);

		// Legacy.
		if (!empty($context['copyrights']['mods']))
			echo (empty($context['credits_modifications']) ? '<br>' : ''),
			implode('
			<br>', $context['copyrights']['mods']);

		echo '
		</div>';
	}

	// PMX itself
	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['credits_forum'],' ', $txt['credits_copyright'], '</h3>
		</div>
		<div class="windowbg">
			', $context['copyrights']['portamx'], '
		</div>
	</div>';
}

?>
