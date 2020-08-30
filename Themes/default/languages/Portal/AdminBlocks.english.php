<?php
// Version: 1.41; AdminBlocks

/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * Language file AdminBlocks.english
 */

// Block overview
$txt['addblock'] = 'Add a new ';
$txt['pmx_add_sideblock'] = 'Click to add a block to %s';
$txt['pmx_edit_sideblock'] = 'Click to edit this block';
$txt['pmx_clone_sideblock'] = 'Click to clone this block';
$txt['pmx_move_sideblock'] = 'Click to move this block';
$txt['pmx_delete_sideblock'] = 'Click to delete this block';
$txt['pmx_confirm_blockdelete'] = 'Are you sure you want to delete this Block?';
$txt['pmx_chg_blockaccess'] = 'Click to change visibility access';
$txt['pmx_delete_block'] = 'Delete block';

$txt['pmx_admBlk_order'] = 'Order';
$txt['pmx_admBlk_adj'] = 'Adjust';
$txt['pmx_admBlk_type'] = 'Block type';
$txt['pmx_admBlk_options'] = 'Setting options';

$txt['pmx_moveto'] = 'Move to:&nbsp;';
$txt['pmx_cloneto'] = 'Clone to:&nbsp;';
$txt['pmx_clonechoice'] = 'Select side:';
$txt['pmx_chgAccess'] = 'Block visibility access';

$txt['pmx_have_groupaccess'] = 'Block has visibility access';
$txt['pmx_have_modaccess'] = 'Block has moderate access';
$txt['pmx_have_dynamics'] = 'Block has dynamic visibility options';
$txt['pmx_have_cssfile'] = 'Block has own style sheet file';
$txt['pmx_have_caching'] = 'Block caching: ';

$txt['pmx_edit_type'] = 'Block type:';
$txt['pmx_edit_cache'] = 'Enable cache:';
$txt['pmx_edit_cachetime'] = 'Time:';
$txt['pmx_edit_cachetimemin'] = 'Min';
$txt['pmx_edit_cachetimesec'] = ' Sec';
$txt['pmx_edit_nocachehelp'] = 'Caching is not possible for this block type.';

$txt['pmx_edit_frontplacing'] = 'Placement on Single Pages, Categories or Articles';
$txt['pmx_edit_frontplacing_hide'] = 'Hide block:';
$txt['pmx_edit_frontplacing_before'] = 'Place before:';
$txt['pmx_edit_frontplacing_after'] = 'Place after:';
$txt['pmx_edit_groups'] = 'Block visibility access settings';
$txt['pmx_edit_ext_opts'] = 'Dynamic visibility options';
$txt['pmx_edit_ext_opts_action'] = 'Visibility on action';
$txt['pmx_edit_ext_opts_custaction'] = 'Visibility on custom action';
$txt['pmx_edit_ext_opts_boards'] = 'Visibility on board';
$txt['pmx_edit_ext_opts_languages'] = 'Visibility on language';
$txt['pmx_edit_ext_opts_themes'] = 'Visibility on theme';
$txt['pmx_edit_ext_maintenance'] = 'Hide on Maintenance mode:';
$txt['pmx_block_other_settings'] = 'Other Block settings';
$txt['pmx_block_moderate'] = 'Enable Block moderation:';
$txt['pmx_check_elcmode'] = 'Hide the block until ECL is accepted:';
$txt['pmx_check_elcbots'] = 'Hide the block for spider:';
$txt['pmx_edit_ext_opts_help'] = 'If you choose any of these dynamic visibility options, the block will show <strong>just</strong> on these, nowhere else.
	To display the block without any dynamic visibility, leave <strong>all unselected</strong>.';
$txt['pmx_edit_ext_opts_morehelp'] = 'To select or unselect one or more options, hold down the <strong>Ctrl Key</strong> and <strong>click</strong> on the items.
	To toggle between <strong>Show and Hide</strong>, hold down the <strong>Ctrl Key</strong> and take a <strong>double click</strong> on the item.
	If a item set to <strong>Hide</strong> the symbol <strong>^</strong> is shown at the front.<br>
	<u><strong>How does it work?</strong></u><br>
	<strong>Show</strong>: If you want to indicate the block only with to one or more actions, boards or languages, then select these.<br>
	<strong>Hide</strong>: If you want to always indicate the block, only with one or more actions, boards or languages not, then select these with a double click (you see a <strong>^</strong> at the front).<br>
	<strong>Examples</strong>:<br>Select the actions "Admin" and "Calendar".. The block shows only on Admin and Calendar.<br>
	Select the action "<strong>^</strong>Admin" .. The block shows always but not on Admin.';

$txt['pmx_rowmove_title'] = 'Set new Block position';
$txt['pmx_block_rowmove'] = 'Move Block';
$txt['pmx_blockmove_place'] = 'to the position';
$txt['pmx_blockmove_to'] = 'Block';
$txt['rowmove_before'] = 'before';
$txt['rowmove_after'] = 'after';
$txt['row_move_updown'] = 'Click to move the block position';

$txt['pmx_clone_move_side'] = 'Select the destination:';
$txt['pmx_clone_move_title'] = '';
$txt['pmx_text_clone'] = 'Clone block';
$txt['pmx_text_move'] = 'Move block';
$txt['pmx_text_block'] = 'Block:';
$txt['pmx_blocks_settings_title'] = '%s block settings';
$txt['pmx_clone_move_toarticles'] = 'Articles Manager';
$txt['pmx_promote_all'] = '[ all posts ]';

/* Blocktype specific text */
// cbt_navigator
$txt['pmx_cbtnavnum'] = 'Max number of topics in each board:';
$txt['pmx_cbtnavexpand'] = 'Expand all boards initially:';
$txt['pmx_cbtnavexpandnew'] = 'Expand boards with new posts initially:';
$txt['pmx_cbtnavboards'] = 'Choose the boards to show in the Navigator block';

// download
$txt['pmx_download_board'] = 'Choose the Board to download from:';
$txt['pmx_download_groups'] = 'Choose groups they have download access:';

// fader
$txt['pmx_fader_uptime'] = 'Uptime:';
$txt['pmx_fader_downtime'] = 'Downtime:';
$txt['pmx_fader_holdtime'] = 'Holdtime:';
$txt['pmx_fader_changetime'] = 'Changetime:';
$txt['pmx_fader_units'] = 'seconds';
$txt['pmx_fader_content'] = 'Enter the Fader content:';

// polls
$txt['pmx_select_polls'] = 'Select polls to show in the Poll block:';
$txt['pmx_no_polls'] = 'No polls found';

// recent_posts/topics
$txt['pmx_recentpostnum'] = 'Number of posts to show:';
$txt['pmx_recentsplit'] = 'Show Name and Time in one line:';
$txt['pmx_recenttopicnum'] = 'Number of topics to show:';
$txt['pmx_recent_boards'] = 'Choose the boards to show in the Recent block';
$txt['pmx_recent_showboard'] = 'Show boardname:';

// statistics
$txt['pmx_admstat_member'] = 'Show Member statistics:';
$txt['pmx_admstat_stats'] = 'Show Post and Online statistics: ';
$txt['pmx_admstat_users'] = 'Show User statistics: ';
$txt['pmx_admstat_spider'] = 'Show Spider in the User stats: ';
$txt['pmx_admstat_olheight'] = 'Entries in the Userlist before scroll: ';

// user_login
$txt['show_avatar'] = 'Show Avatar:';
$txt['show_pm'] = 'Show Personal Messages:';
$txt['show_posts'] = 'Show unread replies/posts:';
$txt['show_logtime'] = 'Show total logged in time:';
$txt['show_login'] = 'Show login for Guests:';
$txt['show_langsel'] = 'Show language selector:';
$txt['show_logout'] = 'Show logout Button:';
$txt['show_time'] = 'Show current time:';
$txt['show_realtime'] = 'Show the current time as real time:';

// boardnews/newposts/promoted posts
$txt['pmx_promoted_selposts'] = 'Show posts selected by Messages:';
$txt['pmx_promoted_selboards'] = 'Show posts selected by Boards:';
$txt['pmx_promoted_posts'] = 'Choose the posts to show:';
$txt['pmx_boardnews_boards'] = 'Choose the Board from which to show boardnews:';
$txt['pmx_postnews_boards'] = 'Choose the Boards from which to show posts:';
$txt['pmx_multbonews'] = 'Max number of posts in each board:';
$txt['pmx_boponews_total'] = 'Max number of posts to show:';
$txt['pmx_boponews_split'] = 'Show the posts in two columns:';
$txt['pmx_boponews_rescale'] = 'Rescale inline images:';
$txt['pmx_boponews_showthumbs'] = 'Show Image attachments under the posts:';
$txt['pmx_boponews_hidethumbs'] = 'Collapse the attachments area:';
$txt['pmx_boponews_thumbcnt'] = 'Number of attachments to show:';
$txt['pmx_boponews_thumbsize'] = 'Max width, height of attachments:';
$txt['pmx_boponews_disableHSimage'] = 'Disable Lightbox for Images:';
$txt['pmx_boponews_page'] = 'Number of posts on page:';
$txt['pmx_boponews_equal'] = 'Set columns in a row to the same height:';
$txt['pmx_boponews_postinfo'] = 'Show Postheader (Posted by, Board):';
$txt['pmx_boponews_postviews'] = 'Add Views/Replies to Postheader:';

// rss_reader
$txt['pmx_rssreader_url'] = 'Enter the full url for the feed:';
$txt['pmx_rssreader_timeout'] = 'Feed response timeout (sec):';
$txt['pmx_rssreader_usettl'] = 'Set the cache time automatic from TTL:';
$txt['pmx_rssreader_maxitems'] = 'Max items to show:';
$txt['pmx_rssreader_cont_encode'] = 'Use "content:encoded" if send:';
$txt['pmx_rssreader_split'] = 'Show the posts in two columns:';
$txt['pmx_rssreader_showhead'] = 'Show the feed header:';
$txt['pmx_rssreader_name'] = 'Site name:';
$txt['pmx_rssreader_link'] = 'Site link:';
$txt['pmx_rssreader_desc'] = 'Description:';
$txt['pmx_rssreader_delimages'] = 'Remove inline images:';
$txt['pmx_rssreader_maxitems'] = 'Max items to show:';
$txt['pmx_rssreader_page'] = 'Number of article on page:';

// shoutbox
$txt['pmx_shoutbox_maxlen'] = 'Max number of characters in shout:';
$txt['pmx_shoutbox_maxshouts'] = 'Number of shouts to save:';
$txt['pmx_shoutbox_maxheight'] = 'Max height of shout box (pixel):';
$txt['pmx_shoutbox_allowedit'] = 'User can edit and delete own shouts:';
$txt['pmx_shoutbox_canshout'] = 'Select the user groups they can shout';

// Category
$txt['pmx_catblock_cats'] = 'Choose the Category:';
$txt['pmx_catblock_blockframe'] = 'Use Titlebar and Frame from the Block:';
$txt['pmx_catblock_catframe'] = 'Use the Titlebar and Frame from the Category:';
$txt['pmx_catblock_inherit'] = 'Inherit Block access to the Category:';

// Article
$txt['pmx_artblock_arts'] = 'Choose the Article:';
$txt['pmx_artblock_blockframe'] = 'Use Titlebar and Frame from the Block:';
$txt['pmx_artblock_artframe'] = 'Use the Titlebar and Frame from the Article';
$txt['pmx_artblock_inherit'] = 'Inherit Block access to the Article:';

// mini calendar
$txt['pmx_minical_firstday'] = 'First day of the week:';
$txt['pmx_minical_firstdays'] = array(
	0 => 'Sunday',
	1 => 'Monday',
	6 => 'Saturday');
$txt['pmx_minical_birthdays'] = 'Show birthdays:';
$txt['pmx_minical_holidays'] = 'Show holidays:';
$txt['pmx_minical_events'] = 'Show events:';
$txt['pmx_minical_bdays_before_after'] = 'Days before<strong> - </strong>after:';

// common for teaser
$txt['pmx_adm_teaser'] = 'Number of %s before tease:';

// common for pages
$txt['pmx_pageindex_pagetop'] = 'Show page index also on top:';
?>