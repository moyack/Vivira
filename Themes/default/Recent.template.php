<?php
/**
 * PortaMx Forum
 *
 * @package PortaMx
 * @author PortaMx & Simple Machines
 * @copyright 2018 PortaMx,  Simple Machines and individual contributors
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 1.41
 */

/**
 * Template for showing recent posts
 */
function template_recent()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="recent" class="main_section">
		<div class="cat_bar">
			<h3 class="catbg">
				<span class="xx"></span>',$txt['recent_posts'],'
			</h3>
		</div>
		<div class="pagesection" id="top">
			<a href="#pbot" class="topbottom floatleft">', $txt['go_down'], '</a>
			<div class="pagelinks floatleft">', $context['page_index'], '</div>
		</div>';

	if (empty($context['posts']))
	{
		echo '
			<div class="windowbg">', $txt['no_messages'], '</div>';
	}

	foreach ($context['posts'] as $post)
	{
		echo '
			<div class="', $post['css_class'] ,'">
					<div class="counter">', $post['counter'], '</div>
					<div class="topic_details">
						<h5>', $post['board']['link'], ' / ', $post['link'], '</h5>
						<span class="smalltext">', $txt['last_poster'], ' <strong>', $post['poster']['link'], ' </strong> - ', $post['time'], '</span>
					</div>
					<div class="list_posts">', $post['message'], '</div>';

		if ($post['can_reply'] || $post['can_quote'] || $post['can_delete'])
			echo '
					<ul class="quickbuttons">';

		// If they *can* reply?
		if ($post['can_reply'])
			echo '
						<li><a href="', $scripturl, '?action=post;topic=', $post['topic'], '.', $post['start'], '"><span class="generic_icons reply_button"></span>', $txt['reply'], '</a></li>';

		// If they *can* quote?
		if ($post['can_quote'])
			echo '
						<li><a href="', $scripturl, '?action=post;topic=', $post['topic'], '.', $post['start'], ';quote=', $post['id'], '"><span class="generic_icons quote"></span>', $txt['quote_action'], '</a></li>';

		// How about... even... remove it entirely?!
		if ($post['can_delete'])
			echo '
						<li><a href="', $scripturl, '?action=deletemsg;msg=', $post['id'], ';topic=', $post['topic'], ';recent;', $context['session_var'], '=', $context['session_id'], '" data-confirm="', $txt['remove_message'] ,'" class="you_sure"><span class="generic_icons remove_button"></span>', $txt['remove'], '</a></li>';

		if ($post['can_reply'] || $post['can_quote'] || $post['can_delete'])
			echo '
					</ul>';

		echo '
			</div>';

	}

	echo '
			<div class="pagesection bot" id="pbot">
				<a href="#recent" class="topbottom floatleft">', $txt['go_up'], '</a>
				<div class="pagelinks floatleft">', $context['page_index'], '</div>
			</div>
		</div>';
}

/**
 * Template for showing unread posts
 */
function template_unread()
{
	global $context, $settings, $txt, $scripturl, $modSettings;

	echo '
	<div id="recent" class="main_content">';

	if (!empty($context['topics']))
	{
		echo '
			<div class="pagesection">
				', $context['menu_separator'], '<a href="#pbot" class="topbottom floatleft">', $txt['go_down'], '</a>
				<div class="pagelinks floatleft">', $context['page_index'], '</div>
			</div>';

		echo '
			<div id="unread">
				<div id="topic_header" class="title_bar">
					<div class="board_icon"></div>
					<div class="info">
						<a href="', $scripturl, '?action=unread', $context['showing_all_topics'] ? ';all' : '', $context['querystring_board_limits'], ';sort=subject', $context['sort_by'] == 'subject' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['subject'], $context['sort_by'] == 'subject' ? ' <span class="generic_icons sort_' . $context['sort_direction'] . '"></span>' : '', '</a>
					</div>
					<div class="board_stats">
						<a href="', $scripturl, '?action=unread', $context['showing_all_topics'] ? ';all' : '', $context['querystring_board_limits'], ';sort=replies', $context['sort_by'] == 'replies' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['replies'], $context['sort_by'] == 'replies' ? ' <span class="generic_icons sort_' . $context['sort_direction'] . '"></span>' : '', '</a>
					</div>
					<div class="lastpost'. (!empty($modSettings['avatars_on_boardIndex']) ? '_ava' : '') .'">
						<a href="', $scripturl, '?action=unread', $context['showing_all_topics'] ? ';all' : '', $context['querystring_board_limits'], ';sort=last_post', $context['sort_by'] == 'last_post' && $context['sort_direction'] == 'up' ? ';desc' : '', '">', $txt['last_post'], $context['sort_by'] == 'last_post' ? ' <span class="generic_icons sort_' . $context['sort_direction'] . '"></span>' : '', '</a>
					</div>
					<div class="moderation">&nbsp;</div>
				</div>
				<div id="topic_container" class="recent_topics">';

		foreach ($context['topics'] as $topic)
		{
			echo '
					<div class="', $topic['css_class'], '">
						<div class="board_icon">
							<img src="', $topic['first_post']['icon_url'], '" alt="">
							', $topic['is_posted_in'] ? '<img class="posted" src="' . $settings['images_url'] . '/icons/profile_sm.png" alt="">' : '', '
						</div>
						<div class="info">';

			// Now we handle the icons
			echo '
							<div class="icons floatright">';
			if ($topic['is_locked'])
				echo '
								<span class="generic_icons lock"></span>';
			if ($topic['is_sticky'])
				echo '
								<span class="generic_icons sticky"></span>';
			if ($topic['is_poll'])
				echo '
								<span class="generic_icons poll"></span>';
			echo '
							</div>';

			echo '
							<div class="recent_title">
								<a href="', $topic['new_href'], '" id="newicon', $topic['first_post']['id'], '"><span class="new_posts">' . $txt['new'] . '</span></a>
								', $topic['is_sticky'] ? '<strong>' : '', '<span class="preview" title="', $topic[(!empty($modSettings['message_index_preview_first']) ? 'last_post' : 'first_post')]['preview'], '"><span id="msg_' . $topic['first_post']['id'] . '">', $topic['first_post']['link'], '</span></span>', $topic['is_sticky'] ? '</strong>' : '', '
							</div>
							<p class="floatleft">
								', $topic['first_post']['started_by'], '
							</p>';

			if(!empty($topic['pages']))
				echo '
								<small id="pages', $topic['first_post']['id'], '" class="topic_pages">&nbsp;', str_replace(';all"', ';all#ptop"', $topic['pages']), '</small>';

			echo '
							</div>
							<div class="board_stats">
								<p>
									', $txt['replies'], ': ', $topic['replies'], '
									<br>
									', $txt['views'], ': ', $topic['views'], '
								</p>
							</div>
							<div class="lastpost';

			if(isset($topic['last_post']['member']['avatar']['url']) && !empty($modSettings['avatars_on_boardIndex']))
				echo '_ava">
								<span class="avaspan"><img src="'. $topic['last_post']['member']['avatar']['url'].'" alt="avatar" class="brdidxava'. $topic['last_post']['member']['avatar']['class'] .'"></span>';
			else
				echo '">';

			echo '
								', sprintf($txt['last_post_topic'], '<a href="' . $topic['last_post']['href'] . '">' . $topic['last_post']['time'] . '</a>', $topic['last_post']['member']['link']), '
							</div>';

			showModeration($topic);

			echo '
						</div>';
		}

		echo '
				</div>
			</div>';

		echo '
			<div class="pagesection">
				', !empty($context['recent_buttons']) ? template_button_strip($context['recent_buttons'], 'right') : '', '
				<div class="floatleft">
					<a href="#recent" class="topbottom floatleft">', $txt['go_up'], '</a>
					<div class="pagelinks floatleft">', $context['page_index'], '</div>
				</div>
			</div>';
	}
	else
		echo '
			<div class="cat_bar">
				<h3 class="catbg">
					', $context['showing_all_topics'] ? $txt['unread_topics_all'] : $txt['unread_topics_visit'], '
				</h3>
			</div>
			<div class="roundframe" style="margin-top:0;border-top-left-radius:0;border-top-right-radius:0">
				', $context['showing_all_topics'] ? $txt['topic_alert_none'] : $txt['unread_topics_visit_none'], '
			</div>';

	echo '
	</div>';

	if (empty($context['no_topic_listing']))
		template_topic_legend();
}

/**
 * Template for showing unread replies (eg new replies to topics you've posted in)
 */
function template_replies()
{
	global $context, $settings, $txt, $scripturl, $modSettings;

	echo '
	<div id="recent">';

	if (!empty($context['topics']))
	{
		echo '

			<div class="pagesection" id="top">
				<a href="#pbot" class="topbottom floatleft">', $txt['go_down'], '</a>
				<div class="pagelinks floatleft">', $context['page_index'], '</div>
			</div>';

		echo '
			<div id="unreadreplies">
				<div id="topic_header" class="title_bar">
					<div class="board_icon"></div>
					<div class="info">
						<a href="', $scripturl, '?action=unreadreplies', $context['querystring_board_limits'], ';sort=subject', $context['sort_by'] === 'subject' && $context['sort_direction'] === 'up' ? ';desc' : '', '">', $txt['subject'], $context['sort_by'] === 'subject' ? ' <span class="generic_icons sort_' . $context['sort_direction'] . '"></span>' : '', '</a>
					</div>
					<div class="board_stats">
						<a href="', $scripturl, '?action=unreadreplies', $context['querystring_board_limits'], ';sort=replies', $context['sort_by'] === 'replies' && $context['sort_direction'] === 'up' ? ';desc' : '', '">', $txt['replies'], $context['sort_by'] === 'replies' ? ' <span class="generic_icons sort_' . $context['sort_direction'] . '"></span>' : '', '</a>
					</div>
					<div class="lastpost'. (!empty($modSettings['avatars_on_boardIndex']) ? '_ava' : '') .'">
						<a href="', $scripturl, '?action=unreadreplies', $context['querystring_board_limits'], ';sort=last_post', $context['sort_by'] === 'last_post' && $context['sort_direction'] === 'up' ? ';desc' : '', '">', $txt['last_post'], $context['sort_by'] === 'last_post' ? ' <span class="generic_icons sort_' . $context['sort_direction'] . '"></span>' : '', '</a>
					</div>
					<div class="moderation">&nbsp;</div>
				</div>
				<div id="topic_container">';

		foreach ($context['topics'] as $topic)
		{
			echo '
						<div class="', $topic['css_class'], '">
							<div class="board_icon">
								<img src="', $topic['first_post']['icon_url'], '" alt="">
								', $topic['is_posted_in'] ? '<img class="posted" src="' . $settings['images_url'] . '/icons/profile_sm.png" alt="">' : '','
							</div>
							<div class="info">';

			// Now we handle the icons
			echo '
								<div class="icons floatright">';
			if ($topic['is_locked'])
				echo '
									<span class="generic_icons lock"></span>';
			if ($topic['is_sticky'])
				echo '
									<span class="generic_icons sticky"></span>';
			if ($topic['is_poll'])
				echo '
									<span class="generic_icons poll"></span>';
			echo '
								</div>';

			$topic['new_href'] = str_replace('#new', '', $topic['new_href']) .'#ptop';
			echo '
								<div class="recent_title">
									<a href="', $topic['new_href'], '" id="newicon', $topic['first_post']['id'], '"><span class="new_posts">' . $txt['new'] . '</span></a>
									', $topic['is_sticky'] ? '<strong>' : '', '<span title="', $topic[(empty($modSettings['message_index_preview_first']) ? 'last_post' : 'first_post')]['preview'], '"><span id="msg_' . $topic['first_post']['id'] . '">', $topic['first_post']['link'], '</span></span>', $topic['is_sticky'] ? '</strong>' : '', '
								</div>
								<p class="floatleft">
									', $topic['first_post']['started_by'], '
								</p>';

			if(!empty($topic['pages']))
				echo '
								<small id="pages', $topic['first_post']['id'], '" class="topic_pages">&nbsp;', $topic['pages'], '</small>';

			echo '
							</div>
							<div class="board_stats">
								<p>
									', $txt['replies'], ': ', $topic['replies'], '
									<br>
									', $txt['views'], ': ', $topic['views'], '
								</p>
							</div>
							<div class="lastpost';

			if(isset($topic['last_post']['member']['avatar']['url']) && !empty($modSettings['avatars_on_boardIndex']))
				echo '_ava">
								<span class="avaspan"><img src="'. $topic['last_post']['member']['avatar']['url'].'" alt="avatar" class="brdidxava'. $topic['last_post']['member']['avatar']['class'] .'"></span>';
			else
				echo '">';
			
			echo '
								', sprintf($txt['last_post_topic'], '<a href="' . $topic['last_post']['href'] . '">' . $topic['last_post']['time'] . '</a>', $topic['last_post']['member']['link']), '
							</div>';

			showModeration($topic);

				echo '
						</div>';
		}

		echo '
					</div>
			</div>
			<div class="pagesection">
				', !empty($context['recent_buttons']) ? template_button_strip($context['recent_buttons'], 'right') : '', '
				', $context['menu_separator'], '<a href="#recent" class="topbottom bottom floatleft">', $txt['go_up'], '</a>
				<div class="pagelinks floatleft">', $context['page_index'], '</div>
			</div>';
	}
	else
	{
		echo '
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['unread_replies'], '
				</h3>
			</div>
			<div class="roundframe" style="margin-top:0;border-top-left-radius:0;border-top-right-radius:0">
				', $context['showing_all_topics'] ? $txt['topic_alert_none'] : $txt['unread_topics_updated_none'] , '
			</div>';
	}

	echo '
	</div>';

	if (empty($context['no_topic_listing']))
		template_topic_legend();
}

function showModeration($topic)
{
	global $scripturl, $context, $user_info, $txt;

//	if($user_info['is_guest'])
//		return;

	echo '
							<div class="moderation">';

	$boards_can = boardsAllowedTo(array('make_sticky', 'remove_any', 'remove_own', 'lock_any', 'lock_own'), true, false);
	$haveIco = false;

	if($user_info['is_admin'] || (in_array($topic['board']['id'], $boards_can['make_sticky']) && $user_info['id'] == $topic["first_post"]['member']['id']))
	{
		$haveIco = true;
		$_SESSION['came_from'] = $_SERVER['QUERY_STRING'];
		echo '
				<a href="', $scripturl, '?action=quickmod;board=', $topic['board']['id'], ';actions%5B', $topic['id'], '%5D=sticky;', $context['session_var'], '=', $context['session_id'], '" class="you_sure"><span class="generic_icons sticky" title="', $topic['is_sticky'] ? $txt['set_nonsticky'] : $txt['set_sticky'], '"></span></a>';
	}

	if($user_info['is_admin'] || (in_array($topic['board']['id'], $boards_can['lock_own']) && $user_info['id'] == $topic["first_post"]['member']['id']) || in_array($topic['board']['id'], $boards_can['lock_any']))
	{
		$haveIco = true;
		$_SESSION['came_from'] = $_SERVER['QUERY_STRING'];
		echo '
				<a href="', $scripturl, '?action=quickmod;board=', $topic['board']['id'], ';actions%5B', $topic['id'], '%5D=lock;', $context['session_var'], '=', $context['session_id'], '" class="you_sure"><span class="generic_icons lock" title="', $topic['is_locked'] ? $txt['set_unlock'] : $txt['set_lock'], '"></span></a>';
//				<div style="height:2px;"></div>';
	}

	if($user_info['is_admin'] || (in_array($topic['board']['id'], $boards_can['remove_own']) && $user_info['id'] == $topic["first_post"]['member']['id']) || in_array($topic['board']['id'], $boards_can['remove_any']))
	{
		$haveIco = true;
		$_SESSION['came_from'] = $_SERVER['QUERY_STRING'];
		echo '
				<a href="', $scripturl, '?action=quickmod;board=', $topic['board']['id'], ';actions%5B', $topic['id'], '%5D=remove;', $context['session_var'], '=', $context['session_id'], '" class="you_sure"><span class="generic_icons delete" title="', $txt['remove_topic'], '"></span></a>';
	}
	echo '
							</div>';
}
?>