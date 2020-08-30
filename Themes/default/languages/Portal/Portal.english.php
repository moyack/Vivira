<?php
// Version: 1.41; Portal

/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * Language file Portal.english
 */

$txt['forum'] = 'Community';
$txt['pmx_button'] = 'Portal';
$txt['pmx_managers'] = 'Your Portal';
$txt['pmx_expand'] = 'Expand ';
$txt['pmx_collapse'] = 'Collapse ';
$txt['pmx_hidepanel'] = 'Hide the ';
$txt['pmx_showpanel'] = 'Show the ';
$txt['pmx_expand_index'] = 'Expand';
$txt['pmx_show_index'] = 'Show';

// do not change the array keys !
$txt['pmx_block_panels'] = array(
	'head' => 'head Panel',
	'top' => 'top Panel',
	'left' => 'left Panel',
	'right' => 'right Panel',
	'bottom' => 'bottom Panel',
	'foot' => 'foot Panel',
);

// do not change the array keys !
$txt['pmx_block_sides'] = array(
	'front' => 'Frontpage',
	'head' => 'Head Panel',
	'top' => 'Top Panel',
	'left' => 'Left Panel',
	'right' => 'Right Panel',
	'bottom' => 'Bottom Panel',
	'foot' => 'Foot Panel',
	'pages' => 'Pages',
);

// Admin and dropdown menue
$txt['pmx_admBlk_panels'] = array(
	'all' => 'Panel overview',
	'front' => 'Frontpage',
	'head' => 'Head Panel',
	'top' => 'Top Panel',
	'left' => 'Left Panel',
	'right' => 'Right Panel',
	'bottom' => 'Bottom Panel',
	'foot' => 'Foot Panel',
	'pages' => 'Single Pages',
);

// Block description
$txt['pmx_boardnews_description'] = 'Board News';
$txt['pmx_download_description'] = 'Download';
$txt['pmx_mini_calendar_description'] = 'Mini Calendar';
$txt['pmx_html_description'] = 'HTML';
$txt['pmx_newposts_description'] = 'New Posts';
$txt['pmx_php_description'] = 'PHP';
$txt['pmx_recent_post_description'] = 'Recent Post';
$txt['pmx_recent_topics_description'] = 'Recent Topics';
$txt['pmx_script_description'] = 'Script';
$txt['pmx_statistics_description'] = 'Statistics';
$txt['pmx_user_login_description'] = 'User Login';
$txt['pmx_cbt_navigator_description'] = 'CBT Navigator';
$txt['pmx_bbc_script_description'] = 'BBC Script';
$txt['pmx_rss_reader_description'] = 'RSS Reader';
$txt['pmx_shoutbox_description'] = 'Shout box';
$txt['pmx_polls_description'] = 'Polls';
$txt['pmx_boardnewsmult_description'] = 'Multiple Board News';
$txt['pmx_article_description'] = 'Static Article';
$txt['pmx_category_description'] = 'Static Category';
$txt['pmx_promotedposts_description'] = 'Promoted Posts';
$txt["pmx_fader_description"] = 'Opaque Fader';

$txt['pmx_admSet_globals'] = 'Global settings';
$txt['pmx_admSet_panels'] = 'Panel settings';
$txt['pmx_admSet_front'] = 'Frontpage settings';
$txt['pmx_admSet_control'] = 'Manager settings';
$txt['pmx_admSet_access'] = 'Access settings';
$txt['cache_clear_ok'] = 'The Porta cache was cleared successfully.';

$txt['pmx_admSet_desc_globals'] = 'Configure the global settings.';
$txt['pmx_admSet_desc_panels'] = 'Configure the panel setting.';
$txt['pmx_admSet_desc_control'] = 'Configure the Manager settings.';
$txt['pmx_admSet_desc_access'] = 'Configure the Portal Admin, Portal Moderator and the Article writer access settings.';

$txt['pmx_extension'] = 'Portal';
$txt['pmx_ext_center'] = 'Portal Center';
$txt['pmx_settings'] = 'Settings Manager';
$txt['pmx_blocks'] = 'Block Manager';
$txt['pmx_adm_settings'] = 'Portal Settings Manager';
$txt['pmx_adm_blocks'] = 'Portal Block Manager';
$txt['permissionname_manage_portamx'] = 'Moderate the Portal Block Manager';

$txt['pmx_categories'] = 'Category Manager';
$txt['pmx_articles'] = 'Article Manager';
$txt['pmx_adm_categories'] = 'Portal Category Manager';
$txt['pmx_adm_articles'] = 'Portal Article Manager';
$txt['pmx_mobile_tab_blocks'] = 'Portal Blocks Manager';

// teaser
$txt['pmx_readmore'] = '<strong>Read the whole article</strong>';
$txt['pmx_readclose'] = '<strong>Collapse the article</strong>';
$txt['pmx_teaserinfo'] = array(
	0 => ' title="Truncation: %s of %s Words"',
	1 => ' title="Truncation: %s of %s Character"',
);

// LightBox
$txt['pmx_hs_expand'] = 'Click to expand';
$txt['pmx_hs_noimage'] = 'Image not exists';
$txt['pmx_hs_imagename'] = 'Name: ';
$txt['pmx_hs_helpttext'] = 'Click here or outside this window to close.';
$txt['pmx_hs_albumLabel'] = 'Image %1 of %2';

// special PHP type blocks/articles
$txt['pmx_edit_content_init'] = ' (INIT PART)';
$txt['pmx_edit_content_show'] = ' (SHOW PART)';
$txt['pmx_php_partblock'] = 'init part editor';
$txt['pmx_php_partblock_note'] = '<strong>Second editor for special PHP blocks</strong>';
$txt['pmx_php_partblock_help'] = '<hr class="hr" style="margin:7px 0 3px 0;" />
	You can create special PHP blocks with a <strong>show part</strong> (executed from template) in the above editor and a <strong>init part</strong> (executed on load time) in the <strong>second editor</strong>.
	The PHP block have two variables (<strong>$this->php_content</strong> and <strong>$this->php_vars</strong>) for common use and transfer values between both parts, as example:<br>
	<i>Code in the init part: <strong>$this->php_content = \'Hello world!\';</strong><br>
	Code in show part like: <strong>echo $this->php_content;</strong></i>';

// error messages
$txt['pmx_acces_error'] = 'You are not allowed to access this section';
$txt['feed_response_error'] = "fsockopen(%s) failed.\nError: Response timeout (%s seconds).";
$txt['page_reqerror_title'] = 'Page request Error';
$txt['page_reqerror_msg'] = 'The page you have requested does not exist or you have no access rights.';
$txt['article_reqerror_title'] = 'Article request Error';
$txt['article_reqerror_msg'] = 'The article you have requested does not exist or you have no access rights.';
$txt['category_reqerror_title'] = 'Category request Error';
$txt['category_reqerror_msg'] = 'The category you have requested does not exist or you have no access rights.';
$txt['download_error_title'] = 'Download Error';
$txt['download_acces_error'] = 'You have not enough rights to proceed the requested download.';
$txt['download_notfound_error'] = 'The requested download is not available and can not proceed.';
$txt['download_unknown_error'] = 'Illegal request reached, the download can not proceed.';
$txt['front_reqerror_title'] = 'Request Error';
$txt['front_reqerror_msg'] = 'The request can\'t processed because the Frontpage is locked.';
$txt['unknown_reqerror_title'] = 'Request Error';
$txt['unknown_reqerror_msg'] = 'The requested item is not available or can not proceed.';;
$txt['page_reqerror_button'] = 'Back';

$txt['pmxelc_failed_request'] = 'You can\'t perform the current request without accept the Cookie storage!';
$txt['pmxelc_failed_access'] = 'You have not enough rights to perform the current request!';
$txt['pmxelc_failed_art'] = 'You can\'t request the article without accept the Cookie storage!';
$txt['pmxelc_failed_cat'] = 'You can\'t request the category without accept the Cookie storage!';

// who display
$txt['pmx_who_frontpage'] = 'Viewing the front page';
$txt['pmx_who_spage'] = 'Viewing the page %s';
$txt['pmx_who_art'] = 'Viewing the article %s';
$txt['pmx_who_cat'] = 'Viewing the category %s';

$txt['whoadmin_pmx_center'] = 'Viewing the Portal Center.';
$txt['whoadmin_pmx_blocks'] = 'Editing the Portal Blocks.';
$txt['whoadmin_pmx_settings'] = 'Editing the Portal Settings.';
$txt['whoadmin_pmx_articles'] = 'Editing the Portal Articles.';
$txt['whoadmin_pmx_categories'] = 'Editing the Portal Categories.';

// category/article display
$txt['pmx_openSidebar'] = 'Click for more Articles';
$txt['pmx_clickclose'] = 'Click to close';
$txt['pmx_more_articles'] = 'Articles in the category';
$txt['pmx_main_category'] = 'Main category';
$txt['pmx_more_categories'] = 'Sub categories';

/* Blocktype specific text */
// cbt_navigator
$txt['pmx_cbt_colexp'] = 'Collapse/Expand: ';
$txt['pmx_cbt_expandall'] = 'Expand';
$txt['pmx_cbt_collapseall'] = 'Collapse';
$txt['pmx_cbt_sticky'] = 'Sticky';
$txt['pmx_cbt_locked'] = 'Locked';

// download
$txt['download'] = 'Download';
$txt['pmx_download_empty'] = '<strong>No downloads available</strong>';
$txt['pmx_kb_downloads'] = 'Kb, Downloads: ';
$txt['pmx_dl_noboard'] = 'None';

// polls
$txt['pmx_poll_novote_opt'] = 'You didn\'t select a vote option.';
$txt['pmx_pollmultiview'] = 'Choose a poll to show:';
$txt['pmx_poll_closed'] = 'Voting closed.';
$txt['pmx_poll_select_locked'] = ' [Locked]';
$txt['pmx_poll_select_expired'] = ' [Expired]';
$txt['pmx_poll_results'] = 'View results';

// rss reader
$txt['pmx_rssreader_postat'] = 'Posted: ';
$txt['pmx_rssreader_error'] = 'Response timeout error, can\'t read the feed.';
$txt['pmx_rssreader_timeout'] = 'Timeout while waiting for data.';

// shoutbox
$txt['pmx_shoutbox_toggle'] = 'Toggle edit mode';
$txt['pmx_shoutbox_shoutdelete'] = 'Delete this shout';
$txt['pmx_shoutbox_shoutconfirm'] = 'Are you sure you want to delete this shout?';
$txt['pmx_shoutbox_shoutedit'] = 'Edit this shout';
$txt['pmx_shoutbox_button_open'] = 'New Shout';
$txt['pmx_shoutbox_button'] = 'Send Shout';
$txt['pmx_shoutbox_button_title'] = 'Enter a new Shout!';
$txt['pmx_shoutbox_send_title'] = 'Send your Shout!';
$txt['pmx_shoutbox_bbc_code'] = 'Toggle BBC Display';
$txt['pmx_shoutbbc_b'] = 'Bold';
$txt['pmx_shoutbbc_i'] = 'Italic';
$txt['pmx_shoutbbc_u'] = 'Underline';
$txt['pmx_shoutbbc_center'] = 'Center text';
$txt['pmx_shoutbbc_hr'] = 'Horizontal Rule';
$txt['pmx_shoutbbc_sub'] = 'Subscript';
$txt['pmx_shoutbbc_sup'] = 'Superscript';
$txt['pmx_shoutbbc_changecolor'] = 'Change color';
$txt['pmx_shoutbbc_colorBlack'] = 'Black';
$txt['pmx_shoutbbc_colorRed'] = 'Red';
$txt['pmx_shoutbbc_colorYellow'] = 'Yellow';
$txt['pmx_shoutbbc_colorPink'] = 'Pink';
$txt['pmx_shoutbbc_colorGreen'] = 'Green';
$txt['pmx_shoutbbc_colorOrange'] = 'Orange';
$txt['pmx_shoutbbc_colorPurple'] = 'Purple';
$txt['pmx_shoutbbc_colorBlue'] = 'Blue';
$txt['pmx_shoutbbc_colorBeige'] = 'Beige';
$txt['pmx_shoutbbc_colorBrown'] = 'Brown';
$txt['pmx_shoutbbc_colorTeal'] = 'Teal';
$txt['pmx_shoutbbc_colorNavy'] = 'Navy';
$txt['pmx_shoutbbc_colorMaroon'] = 'Maroon';
$txt['pmx_shoutbbc_colorLimeGreen'] = 'Lime Green';
$txt['pmx_shoutbbc_colorWhite'] = 'White';

// statistics
$txt['pmx_stat_member'] = 'Members';
$txt['pmx_stat_totalmember'] = 'Total Members';
$txt['pmx_stat_lastmember'] = 'Latest';
$txt['pmx_stat_stats'] = 'Stats';
$txt['pmx_stat_stats_post'] = 'Total Posts';
$txt['pmx_stat_stats_topic'] = 'Total Topics';
$txt['pmx_stat_stats_ol_today'] = 'Online Today';
$txt['pmx_stat_stats_ol_ever'] = 'Most Online';
$txt['pmx_stat_users'] = 'Users online';
$txt['pmx_stat_users_reg'] = 'Users';
$txt['pmx_stat_users_guest'] = 'Guests';
$txt['pmx_stat_users_spider'] = 'Spiders';
$txt['pmx_stat_users_total'] = 'Total';
$txt['pmx_memberlist_icon'] = 'memberlist';
$txt['pmx_statistics_icon'] = 'statistics';
$txt['pmx_online_user_icon'] = 'online user';

// user_login
$txt['pmx_hello'] = 'Hello ';
$txt['login_dec'] = 'Login with username, password and session length';
$txt['pmx_pm'] = 'My Messages';
$txt['pmx_unread'] = 'Show unread posts';
$txt['pmx_replies'] = 'Show updated topics';
$txt['pmx_showownposts'] = 'Show my posts';
$txt['pmx_unapproved_members'] = 'Unapproved member:';
$txt['pmx_maintenace'] = 'Maintenance mode';
$txt['pmx_loggedintime'] = 'Logged in';
$txt['pmx_Ldays'] = 'd';
$txt['pmx_Lhours'] = 'h';
$txt['pmx_Lminutes'] = 'm';
$txt['pmx_langsel'] = 'Select language:';

// mini_calendar
$txt['pmx_cal_birthdays'] = 'Birthdays';
$txt['pmx_cal_holidays'] = 'Holidays';
$txt['pmx_cal_events'] = 'Events';
/* Birthday, Holiday, Event date format chars:
%M = Month (Jan - Dec)
%m = Month (01 - 12)
%d = Day (01 - 31)
%j = Day (1 - 31) */
$txt['pmx_minical_dateform'] = array(
	'%M %d',			// single date
	'%M %d',			// start-date same month
	' - %d',			// end-date same month
	'%M %d',			// start-date not same month
	' - %M %d'		// end-date not same month
);

// common use
$txt['pmx_text_category'] = 'Category: ';
$txt['pmx_text_board'] = 'Board: ';
$txt['pmx_text_topic'] = 'Topic: ';
$txt['pmx_text_post'] = 'Post: ';
$txt['pmx_text_postby'] = 'By: ';
$txt['pmx_text_replies'] = ' Replies: ';
$txt['pmx_text_views'] = 'Views: ';
$txt['pmx_text_createdby'] = 'By: ';
$txt['pmx_text_updated'] = 'Last update: ';
$txt['pmx_text_readmore'] = '<strong>Read more</strong>';
$txt['pmx_text_show_attach'] = '<strong>Show attaches</strong>';
$txt['pmx_text_hide_attach'] = '<strong>Hide attaches</strong>';
$txt['pmx_text_printing'] = 'Print the content';
$txt['pmx_user_unknown'] = 'Unknown';
$txt['pmx_set_promote'] = 'Set Promote';
$txt['pmx_unset_promote'] = 'Clear Promote';
$txt['pmx_check_phpsyntax'] = 'Check the PHP Syntax';
?>