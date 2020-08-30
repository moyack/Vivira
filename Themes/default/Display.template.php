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
 * This tempate handles displaying a topic
 */
function template_main()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	// Let them know, if their report was a success!
	if ($context['report_sent'])
	{
		echo '
			<div class="infobox">
				', $txt['report_sent'], '
			</div>';
	}

	// Let them know why their message became unapproved.
	if ($context['becomesUnapproved'])
	{
		echo '
			<div class="noticebox">
				', $txt['post_becomesUnapproved'], '
			</div>';
	}

	// Show new topic info here?
	echo '
		<div id="display_head" class="information_top">
			<h2 class="display_title">
				<span id="top_subject">', $context['subject'], '</span>', ($context['is_locked']) ? ' <span class="generic_icons lock"></span>' : '', ($context['is_sticky']) ? ' <span class="generic_icons sticky"></span>' : '', '
			</h2>
		</div>
		<div id="display_head2" class="information">
			<p>',$txt['started_by'],' ', $context['topic_poster_name'],', ', $context['topic_started_time'],'</p>';

	// Next - Prev
	echo '
			<span class="nextlinks floatright">', $context['previous_next'], '</span>';

	if (!empty($settings['display_who_viewing']))
	{
		echo '
			<p>';

		// Show just numbers...?
		if ($settings['display_who_viewing'] == 1)
				echo count($context['view_members']), ' ', count($context['view_members']) == 1 ? $txt['who_member'] : $txt['members'];
		// Or show the actual people viewing the topic?
		else
			echo empty($context['view_members_list']) ? '0 ' . $txt['members'] : implode(', ', $context['view_members_list']) . ((empty($context['view_num_hidden']) || $context['can_moderate_forum']) ? '' : ' (+ ' . $context['view_num_hidden'] . ' ' . $txt['hidden'] . ')');

		// Now show how many guests are here too.
		echo $txt['who_and'], $context['view_num_guests'], ' ', $context['view_num_guests'] == 1 ? $txt['guest'] : $txt['guests'], $txt['who_viewing_topic'], '
			</p>';
	}

	// Show the anchor for the top and for the first message. If the first message is new, say so.
	echo '
		</div>
			<a id="msg', $context['first_message'], '"></a>', $context['first_new_message'] ? '<a id="new"></a>' : '';

	// Is this topic also a poll?
	if ($context['is_poll'])
	{
		echo '
			<div id="poll">
				<div class="cat_bar">
					<h3 class="catbg">
						<span class="generic_icons poll"></span>', $context['poll']['is_locked'] ? '<span class="generic_icons lock"></span>' : '' ,' ', $context['poll']['question'], '
					</h3>
				</div>
				<div class="windowbg">
					<div id="poll_options">';

		// Are they not allowed to vote but allowed to view the options?
		if ($context['poll']['show_results'] || !$context['allow_vote'])
		{
			echo '
					<dl class="options">';

			// Show each option with its corresponding percentage bar.
			foreach ($context['poll']['options'] as $option)
			{
				echo '
						<dt class="', $option['voted_this'] ? ' voted' : '', '">', $option['option'], '</dt>
						<dd class="statsbar', $option['voted_this'] ? ' voted' : '', '">';

				if ($context['allow_results_view'])
					echo '
							', $option['bar_ndt'], '
							<span class="percentage">', $option['votes'], ' (', $option['percent'], '%)</span>';

				echo '
						</dd>';
			}

			echo '
					</dl>';

			if ($context['allow_results_view'])
				echo '
						<p><strong>', $txt['poll_total_voters'], ':</strong> ', $context['poll']['total_votes'], '</p>';
		}
		// They are allowed to vote! Go to it!
		else
		{
			echo '
						<form action="', $scripturl, '?action=vote;topic=', $context['current_topic'], '.', $context['start'], ';poll=', $context['poll']['id'], '" method="post" accept-charset="', $context['character_set'], '">';

			// Show a warning if they are allowed more than one option.
			if ($context['poll']['allowed_warning'])
				echo '
							<p class="smallpadding">', $context['poll']['allowed_warning'], '</p>';

			echo '
							<ul class="options">';

			// Show each option with its button - a radio likely.
			foreach ($context['poll']['options'] as $option)
				echo '
								<li>', $option['vote_button'], ' <label for="', $option['id'], '">', $option['option'], '</label></li>';

			echo '
							</ul>
							<div class="submitbutton">
								<input type="submit" value="', $txt['poll_vote'], '" class="button_submit">
								<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
							</div>
						</form>';
		}

		// Is the clock ticking?
		if (!empty($context['poll']['expire_time']))
			echo '
						<p><strong>', ($context['poll']['is_expired'] ? $txt['poll_expired_on'] : $txt['poll_expires_on']), ':</strong> ', $context['poll']['expire_time'], '</p>';

		echo '
					</div>
				</div>
			</div>
			<div id="pollmoderation" class="floatright">';

		template_button_strip($context['poll_buttons']);

		echo '
			</div>';
	}

	// Does this topic have some events linked to it?
	if (!empty($context['linked_calendar_events']))
	{
		echo '
			<div class="title_bar">
				<h3 class="titlebg">', $txt['calendar_linked_events'], '</h3>
			</div>
			<div class="information">
				<ul>';

		foreach ($context['linked_calendar_events'] as $event)
			echo '
					<li>
						', ($event['can_edit'] ? '<a href="' . $event['modify_href'] . '"><span class="generic_icons calendar_modify"></span></a> ' : ''), '<strong>', $event['title'], '</strong>: ', $event['start_date'], ($event['start_date'] != $event['end_date'] ? ' - ' . $event['end_date'] : ''), '
					</li>';


		echo '
				</ul>
			</div>';
	}

	// Show the page index... "Pages: [1]".
	$haveButtons = template_button_strip($context['normal_buttons'], 'right', array(), true);
	if(!empty($haveButtons))
		echo '
			<div class="pagesection top">
				', $haveButtons, '
				', $context['menu_separator'], '
				<div class="fixpagesbar">
					<a href="#pbot" class="topbottom floatleft">', $txt['go_down'], '</a>
					<div class="pagelinks floatleft">
						', $context['page_index'], '
					</div>
				</div>
			</div>';
	else
		echo '
			<div class="pagesection top">
				<div class="fixpagesbar">
					<a href="#pbot" class="topbottom floatleft">', $txt['go_down'], '</a>
					<div class="pagelinks floatleft">
						', $context['page_index'], '
					</div>
				</div>
			</div>';

	// Mobile action - moderation buttons (top)
	echo '
			<div class="mobile_buttons floatright">
				', (!empty($haveButtons) ? '<a class="button mobile_act">'. $txt['mobile_action'] .'</a>' : ''), '
				', ($context['can_moderate_forum'] || $context['user']['is_mod'] ? '<a class="button mobile_mod">'. $txt['mobile_moderation'] .'</a>' : ''),'
			</div>';

	// Show the topic information - icon, subject, etc.
	echo '
			<div id="forumposts">';

	echo '
				<form action="', $scripturl, '?action=quickmod2;topic=', $context['current_topic'], '.', $context['start'], '" method="post" accept-charset="', $context['character_set'], '" name="quickModForm" id="quickModForm" onsubmit="return oQuickModify.bInEditMode ? oQuickModify.modifySave(\'' . $context['session_id'] . '\', \'' . $context['session_var'] . '\') : false">';

	$context['ignoredMsgs'] = array();
	$context['removableMessageIDs'] = array();

	// get Max count for poster Icons
	$maxCnt = $modSettings['isMobile'] ? 4 : 7;
	if($modSettings['isMobile'])
	{
		$width = get_cookie('screen');
		$width = !empty($width) ? explode('-', $width) : '';
		if(isset($width[1]))
			$maxCnt = intval($width[1]) > 800 || intval($width[1]) < 560 ? 7 : 4;
	}

	// Get all the messages...
	while ($message = $context['get_message']())
	{
		$message['max_icon_count'] = $maxCnt;
		template_single_post($message);
	}

	echo '
				</form>
			</div>';

	// Show the page index... "Pages: [1]".
	if(!empty($haveButtons))
	echo '
			<div class="pagesection bot" id="pbot">
				', $haveButtons, '
				', $context['menu_separator'], '
				<div class="fixpagesbar">
					<a href="#ptop" class="topbottom floatleft">', $txt['go_up'], '</a>
					<div class="pagelinks floatleft">
						', $context['page_index'], '
					</div>
				</div>
			</div>';
	else
		echo '
			<div class="pagesection bot" id="pbot">
				<div class="fixpagesbar">
					<a href="#ptop" class="topbottom floatleft">', $txt['go_up'], '</a>
					<div class="pagelinks floatleft">
						', $context['page_index'], '
					</div>
				</div>
			</div>';
	// Mobile action - moderation buttons (bottom)
	echo '
			<div class="mobile_buttons floatright">
				', (!empty($haveButtons) ? '<a class="button mobile_act">'. $txt['mobile_action'] .'</a>' : ''), '
				', ($context['can_moderate_forum'] || $context['user']['is_mod'] ? '<a class="button mobile_mod">'. $txt['mobile_moderation'] .'</a>' : ''),'
			</div>';

	// Moderation buttons
	echo '
			<div id="moderationbuttons">
				', template_button_strip($context['mod_buttons'], 'bottom', array('id' => 'moderationbuttons_strip')), '
			</div>';

	// Show the jumpto box, or actually...let Javascript do it.
	echo '
			<div id="display_jump_to">&nbsp;</div>';

	// User action pop on mobile screen (or actually small screen), this uses responsive css does not check mobile device.
	echo '
			<div id="mobile_action" class="popup_container">
				<div class="popup_window description">
					<div class="popup_heading">', $txt['mobile_action'],'
						<a href="javascript:void(0);" class="generic_icons hide_popup"></a>
					</div>
					', template_button_strip($context['normal_buttons']), '
				</div>
			</div>';

	// Show the moderation button & pop only if user can moderate
	if ($context['can_moderate_forum'] || $context['user']['is_mod'])
		echo '
			<div id="mobile_moderation" class="popup_container">
				<div class="popup_window description">
					<div class="popup_heading">', $txt['mobile_moderation'],'
						<a href="javascript:void(0);" class="generic_icons hide_popup"></a>
					</div>
						', template_button_strip($context['mod_buttons'], 'bottom', array('id' => 'moderationbuttons_strip_mobile')), '
				</div>
			</div>';

	echo '
			<script>';

	if($context['current_board'] != $modSettings['recycle_board'] && !empty($context['pmx']['can_promote']))
		echo '
			function toggle_promote(elm)
			{
				ajax_indicator(true);
				newstate = pmxXMLpost("'. $scripturl .'?action=promote", "message="+elm.id);
				newstate = newstate.split(",");
				document.getElementById("pmx_promo_img_"+ newstate[0]).className = "pmx_"+ newstate[1] +"_promote";
				document.getElementById("pmx_promo_txt_"+ newstate[0]).innerText = newstate[2];
				ajax_indicator(false);
			}';

	echo '
			if (\'XMLHttpRequest\' in window)
			{
				aIconLists[aIconLists.length] = new IconList({
					sBackReference: "aIconLists[" + aIconLists.length + "]",
					sIconIdPrefix: "msg_icon_",
					sScriptUrl: pmx_scripturl,
					bShowModify: ', !empty($modSettings['show_modify']) ? 'true' : 'false', ',
					iBoardId: ', $context['current_board'], ',
					iTopicId: ', $context['current_topic'], ',
					sSessionId: pmx_session_id,
					sSessionVar: pmx_session_var,
					sLabelIconList: "', $txt['message_icon'], '",
					sBoxBackground: "transparent",
					sBoxBackgroundHover: "#ffffff",
					iBoxBorderWidthHover: 1,
					sBoxBorderColorHover: "#adadad" ,
					sContainerBackground: "#ffffff",
					sContainerBorder: "1px solid #adadad",
					sItemBorder: "1px solid #ffffff",
					sItemBorderHover: "1px dotted gray",
					sItemBackground: "transparent",
					sItemBackgroundHover: "#e0e0f0"
				});
			}';

	if (!empty($context['ignoredMsgs']))
		echo '
				ignore_toggles([', implode(', ', $context['ignoredMsgs']), '], ', JavaScriptEscape($txt['show_ignore_user_post']), ');';

	echo '
			</script>';
}

/**
 * Template for displaying a single post.
 *
 * @param array $message An array of information about the message to display. Should have 'id' and 'member'. Can also have 'first_new', 'is_ignored' and 'css_class'.
 */
function template_single_post($message)
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;

	$ignoring = false;

	if ($message['can_remove'])
		$context['removableMessageIDs'][] = $message['id'];

	// Are we ignoring this message?
	if (!empty($message['is_ignored']))
	{
		$ignoring = true;
		$context['ignoredMsgs'][] = $message['id'];
	}

	// Show the message anchor
	echo '
				<div class="', $message['css_class'] ,'">
					<a class="msg_id_link" id="msg' . $message['id'] . '"></a>
					<div class="post_wrapper">';

	// Show information about the poster of this message.
	echo '
						<div class="poster">
							<div>';

	// Are there any custom fields above the member name?
	if (!empty($message['custom_fields']['above_member']))
	{
		echo '
								<div class="custom_fields_above_member">
									<ul class="nolist">';

		foreach ($message['custom_fields']['above_member'] as $custom)
			echo '
										<li class="custom ', $custom['col_name'] ,'">', $custom['value'], '</li>';

		echo '
									</ul>
								</div>';
	}

	echo '
								<h4>';

	// Show online and offline buttons?
	if (!empty($modSettings['onlineEnable']) && !$message['member']['is_guest'])
		echo '
									<span class="' . ($message['member']['online']['is_online'] == 1 ? 'on' : 'off') . '" title="' . $message['member']['online']['text'] . '"></span>';

	// Show a link to the member's profile.
	echo '
									', $message['member']['link'], '
								</h4>';

	echo '
								<ul class="user_info">';


	// Show the user's avatar.
	if (!empty($modSettings['show_user_images']))
	{
		if(isset($message['member']['avatar']['class']))
			$message['member']['avatar']['image'] = preg_replace('~class\=\"[^\"]*\"~', 'class="avatar '. $message['member']['avatar']['class'] .'"', $message['member']['avatar']['image']);

		echo '
									<li class="avatar">
										'. (isset($message['member']['is_guest']) ? $message['member']['avatar']['image'] : '<a href="'. $message['member']['href'] .'" >'. $message['member']['avatar']['image'] .'</a>') .'
									</li>';
	}

	// Are there any custom fields below the avatar?
	if (!empty($message['custom_fields']['below_avatar']))
		foreach ($message['custom_fields']['below_avatar'] as $custom)
			echo '
									<li class="custom ', $custom['col_name'] ,'">', $custom['value'], '</li>';

	// Show the post group icons, but not for guests.
	if (!$message['member']['is_guest'])
		echo '
									<li class="icons">', $message['member']['group_icons'], '</li>';

	// Show the member's primary group (like 'Administrator') if they have one.
	if (!empty($message['member']['group']))
		echo '
									<li class="membergroup">', $message['member']['group'], '</li>';

	// Show the member's custom title, if they have one.
	if (!empty($message['member']['title']))
		echo '
									<li class="title">', $message['member']['title'], '</li>';

	// Don't show these things for guests.
	if (!$message['member']['is_guest'])
	{

		// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
		if ((empty($modSettings['hide_post_group']) || $message['member']['group'] == '') && $message['member']['post_group'] != '')
			echo '
									<li class="postgroup">', $message['member']['post_group'], '</li>';

		// Show how many posts they have made.
		if (!isset($context['disabled_fields']['posts']))
			echo '
									<li class="postcount">', $txt['member_postcount'], ': ', $message['member']['posts'], '</li>';

		// Show their personal text?
		if (!empty($modSettings['show_blurb']) && !empty($message['member']['blurb']))
			echo '
									<li class="blurb">', $message['member']['blurb'], '</li>';

		// Any custom fields for standard placement?
		if (!empty($message['custom_fields']['standard']))
		{
			echo '
									<li class="cust_standard">
										<ol>';

			foreach ($message['custom_fields']['standard'] as $custom)
				echo '
											<li class="custom ', $custom['col_name'] ,'">', $custom['col_name'], ': ', $custom['value'], '</li>';

			echo '
										<ol>
									</li>';
		}

		// Show the website and email address buttons.
		if ($message['member']['show_profile_buttons'])
		{
			echo '
									<li class="profile">
										<ol class="profile_icons">';

			// Don't show an icon if they haven't specified a website.
			$cnt = 0;
			if (!empty($message['member']['website']['url']) && !isset($context['disabled_fields']['website']))
				echo '
											<li><a href="', $message['member']['website']['url'], '" title="' . $message['member']['website']['title'] . '" target="_blank" rel="noopener" class="new_win">', ($settings['use_image_buttons'] ? '<span class="generic_icons www centericon" title="' . $message['member']['website']['title'] . '"></span>' : $txt['www']), '</a></li>';

			// Since we know this person isn't a guest, you *can* message them.
			if ($context['can_send_pm'])
				echo '
											<li><a href="', $scripturl, '?action=pm;sa=send;u=', $message['member']['id'], '" title="', $message['member']['online']['is_online'] ? $txt['pm_online'] : $txt['pm_offline'], '">', $settings['use_image_buttons'] ? '<span class="generic_icons im_' . ($message['member']['online']['is_online'] ? 'on' : 'off') . ' centericon" title="' . ($message['member']['online']['is_online'] ? $txt['pm_online'] : $txt['pm_offline']) . '"></span> ' : ($message['member']['online']['is_online'] ? $txt['pm_online'] : $txt['pm_offline']), '</a></li>';

			// Show the email if necessary
			if (!empty($message['member']['email']) && $message['member']['show_email'] && !$context['user']['is_guest'])
				echo '
											<li class="email"><a href="mailto:' . $message['member']['email'] . '" rel="nofollow">', ($settings['use_image_buttons'] ? '<span class="generic_icons mail centericon" title="' . $txt['email'] . '"></span>' : $txt['email']), '</a></li>';

			// Show the Gender icon
			if(isset($message['member']['gender']) && !isset($context['disabled_fields']['gender']) && !$context['user']['is_guest']) 
				echo '
											<li class="gender_post'. $message['member']['gender'] .'"><span class="generic_icons gender_'. $message['member']['gender'] .'" title="'. $txt['cust_gender']['field_name'] .': '. $txt['gender_type'][$message['member']['gender']] .'"></span></li>';

			// custom fileds "width icon"
			if (isset($message['custom_fields']['icons']))
			{
				foreach ($message['custom_fields']['icons'] as $custom)
				{
					if(!isset($context['disabled_fields'][$custom['col_name']]))
						echo '
											<li class="custom ', $custom['col_name'] ,'">', $custom['value'], '</li>';
				}
			}

			echo '
										</ol>
									</li>';

			// Show location (if exists)
			if(!isset($context['disabled_fields']['location'])) 
			{
				if(!empty($message['member']['location']) && !$context['user']['is_guest'])
				{
					$cnt++;
					echo '
									<li>'. $txt['cust_loca']['field_name'] .': '. $message['member']['location'] .'</li>';
				}
			}
		}

		// Any custom fields for standard placement?
		if (!empty($message['custom_fields']['standard']))
		{
			foreach ($message['custom_fields']['standard'] as $custom)
			{
				if($cnt > 5)
				{
					echo '<br />';
					$cnt = 1;
				}
				echo '
									<li class="custom ', $custom['col_name'] ,'">', $custom['col_name'], ': ', $custom['value'], '</li>';
			}
		}
	}
	// Otherwise, show the guest's email.
	elseif (!empty($message['member']['email']) && $message['member']['show_email'] && !$context['user']['is_guest'])
		echo '
									<li class="email"><a href="mailto:' . $message['member']['email'] . '" rel="nofollow">', ($settings['use_image_buttons'] ? '<span class="generic_icons mail centericon" title="' . $txt['email'] . '"></span>' : $txt['email']), '</a></li>';

	// Show the IP to this user for this post - because you can moderate?
	if (!empty($context['can_moderate_forum']) && !empty($message['member']['ip']))
		echo '
									<li class="poster_ip"><a href="', $scripturl, '?action=', !$context['user']['is_guest'] ? 'trackip' : 'profile;area=tracking;sa=ip;u=' . $message['member']['id'], ';searchip=', $message['member']['ip'], '">', $message['member']['ip'], '</a> <a href="', $scripturl, '?action=helpadmin;help=see_admin_ip" onclick="return reqOverlayDiv(this.href);" class="help">(?)</a></li>';

	// Or, should we show it because this is you?
	elseif ($message['can_see_ip'])
		echo '
									<li class="poster_ip"><a href="', $scripturl, '?action=helpadmin;help=see_member_ip" onclick="return reqOverlayDiv(this.href);" class="help">', $message['member']['ip'], '</a></li>';

	// Okay, are you at least logged in? Then we can show something about why IPs are logged...
	elseif (!$context['user']['is_guest'])
		echo '
									<li class="poster_ip"><a href="', $scripturl, '?action=helpadmin;help=see_member_ip" onclick="return reqOverlayDiv(this.href);" class="help">', $txt['logged'], '</a></li>';

	// Are we showing the warning status?
	// Don't show these things for guests.
	if (!$message['member']['is_guest'] && $message['member']['can_see_warning'])
		echo '
									<li class="warning">', $context['can_issue_warning'] ? '<a href="' . $scripturl . '?action=profile;area=issuewarning;u=' . $message['member']['id'] . '">' : '', '<span class="generic_icons warning_', $message['member']['warning_status'], '"></span> ', $context['can_issue_warning'] ? '</a>' : '', '<span class="warn_', $message['member']['warning_status'], '">', $txt['warn_' . $message['member']['warning_status']], '</span></li>';

	// Are there any custom fields to show at the bottom of the poster info?
	if (!empty($message['custom_fields']['bottom_poster']))
		foreach ($message['custom_fields']['bottom_poster'] as $custom)
			echo '
									<li class="custom ', $custom['col_name'] ,'">', $custom['value'], '</li>';

	// Poster info ends.
	echo '
								</ul>';
	echo '
							</div>
						</div>
						<div class="postarea">
							<div class="keyinfo">
								<div class="messageicon">
									<img src="', $message['icon_url'] . '" alt="*"', $message['can_modify'] ? ' id="msg_icon_' . $message['id'] . '"' : '', '>
								</div>';

	//Some people don't want subject ... The div is still required or quick edit breaks...
	echo '
								<div id="subject_', $message['id'], '" class="subject_title">', (empty($modSettings['subject_toggle']) ? '' : '<a href="' . $message['href'] . '" rel="nofollow">' . $message['subject'] . '</a>'), '</div>';

	echo '
								<div class="page_number floatright">
									', empty($message['modified']['name']) && !empty($message['counter']) ? ' #' . $message['counter'] : '', ' ', '
								</div>
								<h5>
									<a href="', $message['href'], '" rel="nofollow" title="', !empty($message['counter']) ? sprintf($txt['reply_number'], $message['counter'], ' - ') : '', $message['subject'], '" class="smalltext">', $message['time'], '</a>';

	// Show "<< Last Edit: Time by Person >>" if this post was edited. But we need the div even if it wasn't modified!
	// Because we insert into it through AJAX and we don't want to stop themers moving it around if they so wish so they can put it where they want it.
	echo '
									<span class="smalltext modified" id="modified_', $message['id'], '">';

	if (!empty($modSettings['show_modify']) && !empty($message['modified']['name']))
		echo
										$message['modified']['last_edit_text'];

	echo '
									</span>
									<script>window.addEventListener("resize",Title'. $message['id'] .'Resize);function Title'. $message['id'] .'Resize(){if(!document.getElementById("edreason'. $message['id'] .'"))document.getElementById("modified_'. $message['id'] .'").style.marginTop = "-2px"; else document.getElementById("subject_'. $message['id'] .'").style.paddingRight=document.getElementById("modified_'. $message['id'] .'").offsetWidth+"px";}Title'. $message['id'] .'Resize();</script>
								</h5>
								<div id="msg_', $message['id'], '_quick_mod"', $ignoring ? ' style="display:none;"' : '', '></div>
							</div>';

	// Ignoring this user? Hide the post.
	if ($ignoring)
		echo '
							<div id="msg_', $message['id'], '_ignored_prompt">
								', $txt['ignoring_user'], '
								<a href="#" id="msg_', $message['id'], '_ignored_link" style="display: none;">', $txt['show_ignore_user_post'], '</a>
							</div>';

	// Show the post itself, finally!
	echo '
							<div class="post">';

	if (!$message['approved'] && $message['member']['id'] != 0 && $message['member']['id'] == $context['user']['id'])
		echo '
								<div class="approve_post">
									', $txt['post_awaiting_approval'], '
								</div>';
	echo '
								<div class="inner" data-msgid="', $message['id'], '" id="msg_', $message['id'], '"', $ignoring ? ' style="display:none;"' : '', '>', $message['body'], '</div>
							</div>';

	// Assuming there are attachments...
	if (!empty($message['attachment']))
	{
		$last_approved_state = 1;
		$attachments_per_line = 5;
		$i = 0;
		// Don't output the div unless we actually have something to show...
		$div_output = false;

		foreach ($message['attachment'] as $attachment)
		{
			// Do we want this attachment to not be showed here?
			if (!empty($modSettings['dont_show_attach_under_post']) && !empty($context['show_attach_under_post'][$attachment['id']]))
				continue;
			elseif (!$div_output)
			{
				$div_output = true;

				echo '
							<div id="msg_', $message['id'], '_footer" class="attachments"', $ignoring ? ' style="display:none;"' : '', '>';
			}

			// Show a special box for unapproved attachments...
			if ($attachment['is_approved'] != $last_approved_state)
			{
				$last_approved_state = 0;
				echo '
								<fieldset>
									<legend>', $txt['attach_awaiting_approve'];

				if ($context['can_approve'])
					echo '
										&nbsp;[<a href="', $scripturl, '?action=attachapprove;sa=all;mid=', $message['id'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['approve_all'], '</a>]';

				echo '
									</legend>';
			}

			echo '
									<div class="floatleft attached">';

			if ($attachment['is_image'])
			{
				echo '
										<div class="attachments_top">'. (isset($context['lbimage_data']['lightbox_id']) ? '
											<a class="lb-link" href="" data-link="' . $attachment['href'] . ';image" title="'. $txt['lightbox_expand'] .'" data-lightbox="' . $context['lbimage_data']['lightbox_id'] . '" data-title="' . $attachment['name'] . '">' : '');

				if ($attachment['thumbnail']['has_thumb'])
					echo '
												<img src="' . $attachment['thumbnail']['href'] . '" alt="*" oncontextmenu="return false">';
				else
					echo '
												<img src="' . $attachment['href'] . ';image" alt="*" oncontextmenu="return false"' . ($attachment['width'] >= $attachment['height'] ? ' width="150"' : ' height="150"') . '>';

				echo '
											'. (isset($context['lbimage_data']['lightbox_id']) ? '</a>' : '') .'
										</div>';
			}

			echo '
										<div class="attachments_bot">';

			if(!$attachment['is_image'])
			{
				if(allowedTo('download_attach_file'))
					echo '
											<a href="' . $attachment['href'] . ';filedl" title="'. $txt['download_fileattach'] .'"><img src="' . $settings['images_url'] . '/icons/down.png" class="centericon" alt="*">' . $attachment['name'] . '</a><br>';
				else
					echo $attachment['name'] .'<br />';
			}

			if (!$attachment['is_approved'] && $context['can_approve'])
				echo '
											[<a href="', $scripturl, '?action=attachapprove;sa=approve;aid=', $attachment['id'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['approve'], '</a>]&nbsp;|&nbsp;[<a href="', $scripturl, '?action=attachapprove;sa=reject;aid=', $attachment['id'], ';', $context['session_var'], '=', $context['session_id'], '">', $txt['delete'], '</a>] ';
			else
			{
				if($attachment['is_image'])
				{
					if(!empty($modSettings['image_download']) && allowedTo('download_attach_image'))
						echo '
											<a href="'. str_replace(';image', ';imagedl', $attachment['href']) .';" title="'. $txt['download_imageattach'] .'"><img src="' . $settings['images_url'] . '/icons/down.png" class="centericon" alt="*">' . $attachment['name'] . '</a><br>' . $attachment['real_width'] . ' x ' . $attachment['real_height'] .', '. $txt['filesize'] ,': ', $attachment['size'] .'<br>' . sprintf($txt['attach_downloaded'], $attachment['downloads']);
					else
						echo '
											', $attachment['name'] . '<br>' . $attachment['size'], ($attachment['is_image'] ? ', ' . $attachment['real_width'] . ' x ' . $attachment['real_height'] : '<br>' . sprintf($txt['attach_downloaded'], $attachment['downloads']));
				}
				else
					echo '
											', $txt['filesize'] , ': ', $attachment['size'], '<br>', sprintf($txt['attach_downloaded'], $attachment['downloads']);
				echo '
										</div>';
			}
			echo '
									</div>';

			// Next attachment line ?
			if (++$i % $attachments_per_line === 0)
				echo '
									<br>';
		}

		// If we had unapproved attachments clean up.
		if ($last_approved_state == 0)
			echo '
								</fieldset>';

		// Only do this if we output a div above - otherwise it'll break things
		if ($div_output)
			echo '
							</div>';
	}

	// And stuff below the attachments.
	if($context['can_report_moderator'] || (!empty($modSettings['enable_likes']) && !empty($context['can_see_likes']) && !empty($message['likes']['count'])) || $message['can_approve'] || $message['can_unapprove'] || $context['can_reply'] || $message['can_modify'] || $message['can_remove'] || $context['can_split'] || $context['can_restore_msg'] || $context['can_quote'])
	{
		echo '
							<div class="under_message">';

		// Maybe they want to report this post to the moderator(s)?
		if ($context['can_report_moderator'])
			echo '
								<ul class="floatright smalltext">
									<li class="report_link"><a href="', $scripturl, '?action=reporttm;topic=', $context['current_topic'], '.', $message['counter'], ';msg=', $message['id'], '">', $txt['report_to_mod'], '</a></li>
								</ul>';

		// What about likes?
		if (!empty($modSettings['enable_likes']) && !empty($context['can_see_likes']))
		{
			echo '
								<ul class="floatleft">';

			if (!empty($message['likes']['can_like']))
				echo '
									<li class="like_button" id="msg_', $message['id'], '_likes"', $ignoring ? ' style="display:none;"' : '', '><a href="', $scripturl, '?action=likes;ltype=msg;sa=like;like=', $message['id'], ';', $context['session_var'], '=', $context['session_id'], '" class="msg_like"><span class="generic_icons ', $message['likes']['you'] ? 'unlike' : 'like', '"></span> ', $message['likes']['you'] ? $txt['unlike'] : $txt['like'], '</a></li>';

			if (!empty($message['likes']['count']))
			{
				$LikeMsg = getLikeCount($message['id']);
				if($LikeMsg !== array() && !empty($LikeMsg['count']))
				{
					$context['likers'][$message['member']['id']] = array('timestamp' => $LikeMsg['time']);
					$context['some_likes'] = true;
					$count = $LikeMsg['count'];
					$base = 'likes_';
					if ($message['likes']['you'])
					{
						$base = 'you_' . $base;
						$count--;
					}
					$base .= (isset($txt[$base . $count])) ? $count : 'n';

					echo '
									<li class="like_count smalltext">', sprintf($txt[$base], $scripturl . '?action=likes;sa=view;ltype=msg;like=' . $message['id'] .';'. $context['session_var'] .'='. $context['session_id'], comma_format($count)), '</li>';
				}
			}

			echo '
								</ul>';
		}

		// Show the quickbuttons, for various operations on posts.
		if ($message['can_approve'] || $message['can_unapprove'] || $context['can_reply'] || $message['can_modify'] || $message['can_remove'] || $context['can_split'] || $context['can_restore_msg'] || $context['can_quote'])
		{
			if($context['current_board'] != $modSettings['recycle_board'] && !empty($context['pmx']['can_promote']))
				$promo = in_array($message['id'], $context['pmx']['promotes']) ? 'unset' : 'set';
			else
				$promo = '';

			echo '
								<div class="under_msgbuttons">
									<ul class="quickbuttons">';

			if(!empty($promo))
				echo '
										<li><a id="pmx_promo_img_'. $message['id'] .'" class="pmx_'. $promo .'_promote" href="javascript:void(0)" onclick="toggle_promote(this)"><span id="pmx_promo_txt_'. $message['id'] .'" class="pmx_promote_txt">'. $txt['pmx_'. $promo .'_promote'] .'</span></a></li>';

			// Can they quote? if so they can select and quote as well!
			if ($context['can_quote'])
				echo '
										<li><a href="', $scripturl, '?action=post;quote=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], ';last_msg=', $context['topic_last_message'], '"><span class="generic_icons quote"></span>', $txt['quote_action'], '</a></li>';

			// Can the user modify the contents of this post? Show the modify inline image.
			if ($message['can_modify'])
				echo '
										<li><a href="', $scripturl, '?action=post;msg=', $message['id'], ';topic=', $context['current_topic'], '.', $context['start'], '"><span class="generic_icons modify_button"></span>', $txt['modify'], '</a></li>';

			if ($message['can_approve'] || $message['can_unapprove'] || $message['can_modify'] || $message['can_remove'] || $context['can_split'] || $context['can_restore_msg'])
				echo '
										<li class="post_options">', $txt['post_options'];

			$wecan = ($context['can_delete'] && ($context['topic_first_message'] == $message['id']) 
				|| $message['can_remove'] && ($context['topic_first_message'] != $message['id'])
				|| $context['can_split'] && !empty($context['real_num_replies'])
				|| $context['can_issue_warning'] && !$message['is_message_author'] && !$message['member']['is_guest']
				|| $context['can_restore_msg'] || $message['can_approve'] || $message['can_unapprove']);

			if($wecan)
				echo '
											<ul class="is_last_button">';

			// How about... even... remove it entirely?!
			if ($context['can_delete'] && ($context['topic_first_message'] == $message['id']))
				echo '
												<li><a href="', $scripturl, '?action=removetopic2;topic=', $context['current_topic'], '.', $context['start'], ';', $context['session_var'], '=', $context['session_id'], '" data-confirm="', $txt['are_sure_remove_topic'], '" class="you_sure"><span class="generic_icons remove_button"></span>', $txt['remove_topic'],'</a></li>';
			elseif ($message['can_remove'] && ($context['topic_first_message'] != $message['id']))
				echo '
												<li><a href="', $scripturl, '?action=deletemsg;topic=', $context['current_topic'], '.', $context['start'], ';msg=', $message['id'], ';', $context['session_var'], '=', $context['session_id'], '" data-confirm="', $txt['remove_message_question'] ,'" class="you_sure"><span class="generic_icons remove_button"></span>', $txt['remove'], '</a></li>';

			// What about splitting it off the rest of the topic?
			if ($context['can_split'] && !empty($context['real_num_replies']))
				echo '
												<li><a href="', $scripturl, '?action=splittopics;topic=', $context['current_topic'], '.0;at=', $message['id'], '"><span class="generic_icons split_button"></span>', $txt['split'], '</a></li>';

			// Can we issue a warning because of this post? Remember, we can't give guests warnings.
			if ($context['can_issue_warning'] && !$message['is_message_author'] && !$message['member']['is_guest'])
				echo '
												<li><a href="', $scripturl, '?action=profile;area=issuewarning;u=', $message['member']['id'], ';msg=', $message['id'], '"><span class="generic_icons warn_button"></span>', $txt['issue_warning'], '</a></li>';

			// Can we restore topics?
			if ($context['can_restore_msg'])
				echo '
												<li><a href="', $scripturl, '?action=restoretopic;msgs=', $message['id'], ';', $context['session_var'], '=', $context['session_id'], '"><span class="generic_icons restore_button"></span>', $txt['restore_message'], '</a></li>';

			// Maybe we can approve it, maybe we should?
			if ($message['can_approve'])
				echo '
												<li><a href="', $scripturl, '?action=moderate;area=postmod;sa=approve;topic=', $context['current_topic'], '.', $context['start'], ';msg=', $message['id'], ';', $context['session_var'], '=', $context['session_id'], '"><span class="generic_icons approve_button"></span>', $txt['approve'], '</a></li>';

			// Maybe we can unapprove it?
			if ($message['can_unapprove'])
				echo '
												<li><a href="', $scripturl, '?action=moderate;area=postmod;sa=approve;topic=', $context['current_topic'], '.', $context['start'], ';msg=', $message['id'], ';', $context['session_var'], '=', $context['session_id'], '"><span class="generic_icons unapprove_button"></span>', $txt['unapprove'], '</a></li>';

			if($wecan)
				echo '
											</ul>';

			echo '
										</li>';

			// Show a checkbox for quick moderation?
			if (!empty($options['display_quick_mod']) && $message['can_remove'])
				echo '
										<li style="display: none;" id="in_topic_mod_check_', $message['id'], '"></li>';

			if ($message['can_approve'] || $context['can_reply'] || $message['can_modify'] || $message['can_remove'] || $context['can_split'] || $context['can_restore_msg'])
				echo '
									</ul>
								</div>';
		}


		echo '
							</div>';
	}

	echo '
						</div>
						<div class="moderatorbar">';

	// Are there any custom profile fields for above the signature?
	if (!empty($message['custom_fields']['above_signature']))
	{
		echo '
							<div class="custom_fields_above_signature">
								<ul class="nolist">';

		foreach ($message['custom_fields']['above_signature'] as $custom)
			echo '
									<li class="custom ', $custom['col_name'] ,'">', $custom['value'], '</li>';

		echo '
								</ul>
							</div>';
	}

	// Show the member's signature?
	if (!empty($message['member']['signature']) && empty($options['show_no_signatures']) && $context['signature_enabled'])
		echo '
							<div class="signature" id="msg_', $message['id'], '_signature"', $ignoring ? ' style="display:none;"' : '', '>', $message['member']['signature'], '</div>';


	// Are there any custom profile fields for below the signature?
	if (!empty($message['custom_fields']['below_signature']))
	{
		echo '
							<div class="custom_fields_below_signature">
								<ul class="nolist">';

		foreach ($message['custom_fields']['below_signature'] as $custom)
			echo '
									<li class="custom ', $custom['col_name'] ,'">', $custom['value'], '</li>';

		echo '
								</ul>
							</div>';
	}

	echo '
						</div>
					</div>
				</div>';
}
?>
