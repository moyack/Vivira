<?php
// Version: 1.41; PortalHelp

/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * Language file PortalHelp.english
 */
global $context;

$txt['close_window'] = 'Close window';
$txt['pmx_edit_titlehelp'] = 'Enter a title for each language you have.';
$txt['pmx_html_teasehelp'] = 'You can insert a teaser mark in the html content. To do this, set the cursor to a suitable tease position, then click on the <strong>Pagebreak</strong> icon <img src="'. $context['pmx_imageurl'] .'pgbreak.png" alt="*" title="pagebreak" style="vertical-align:-5px;" /> in the editor.';
$txt['pmx_blocks_deviceshelp'] = 'Select the type of device on which the block is to be displayed.';
$txt['pmx_settings_deviceshelp'] = 'Select the type of device on which the panel is to be displayed.';
$txt['pmx_article_teasehelp'] = 'Leave this empty or Enter 0 for no tease';
$txt['pmx_article_footerhelp'] = 'If checked, the Article author, date created and last update is show below the article';
$txt['pmx_article_moderatehelp'] = 'If checked, all member in the Article Moderator Group can edit, delete and approve this article.';
$txt['pmx_article_groupshelp'] = 'Choose your membergroups that will able to see this article.<br>
	You can also use <strong>deny group</strong>. This is useful when a user is in more than one group, but one of the groups should not see the block.<br>
	To toggle between deny groups and access groups, hold down the <strong>Ctrl Key</strong> and <strong>double click</strong> on the item.
	If the group a deny group,  you see the deny symbol <strong>^</strong> before the group name.';
$txt['pmx_art_eclcheckhelp'] = 'If you have the ECL mode enabled and the article is visible for Guests, he is not shown until ECL is accepted.
	If ECL mode not enabled, this settings is ignored.';
$txt['pmx_art_eclcheckbotshelp'] = 'If the article visible for Guests, he is also visible for spider (like Google) even is the ECL mode enabled.
	To hide the article for spiders, enable these option.';
$txt['pmx_cat_eclcheckhelp'] = 'If you have the ECL mode enabled and the category is visibe for Guests, he is not shown until ECL is accepted.
	If ECL mode not enabled, this settings is ignored.';
$txt['pmx_cat_eclcheckbotshelp'] = 'If the category visible for Guests, he is also visible for spider (like Google) even is the ECL mode enabled.
	To hide the category for spiders, enable these option.';
$txt['pmx_categories_groupshelp'] = 'Choose your membergroups that will able to see this category.<br>
	You can also use <strong>deny group</strong>. This is useful when a user is in more than one group, but one of the groups should not see the category.<br>
	To toggle between deny groups and access groups, hold down the <strong>Ctrl Key</strong> and <strong>double click</strong> on the item.
	If the group a deny group,  you see the deny symbol <strong>^</strong> before the group name.';
$txt['pmx_categories_sorthelp'] = 'You can sort the articles in this category with variable values.
	If you choice more then one sort option, these are logically XOR-ed (the result is true, if <strong>one</strong> option true, else the result is false).<br>
	To select more then one sort option, hold down the <strong>Ctrl Key</strong> and click on the items.
	To toggle between ascending and descending sort, hold down the <strong>Ctrl Key</strong> and <strong>double click</strong> the item.
	For a descending sort the symbol <strong>^</strong> is shown before the sort option.';
$txt['pmx_categories_inherithelp'] = 'if checked, the category permissions is inherit to the article.
	This is done even, if the permission on the article are higher as on the category.';
$txt['pmx_categories_gloablcathelp'] = 'If you disable a category for global use, the category is invisible for Members in the Article Writer and the Article Moderator group.';
$txt['pmx_categorie_requesthelp'] = 'If you check this option, only a Forum Admin and a Portal Admin can request these category.';
$txt['pmx_edit_pagenamehelp'] = 'You can use any name with the chars <strong>a-z, A-Z, 0-9</strong>, underscore(<strong>_</strong>), dot(<strong>.</strong>) and hyphen(<strong>-</strong>).';
$txt['pmx_article_teasehelp'] = 'Leave this empty or Enter 0 for no tease';
$txt['pmx_settings_panelpadding_help'] = 'Space between the panels and the Forum area, and clearance between the individual portal elements.';
$txt['pmx_settings_hidehelp'] = 'To hide the panel, select or unselect one or more options by hold down the <strong>Ctrl Key</strong> and <strong>click</strong> on the items.<br>
	To toggle between <strong>Show and Hide</strong>, hold down the <strong>Ctrl Key</strong> and take a <strong>double click</strong> (IE needs three clicks!) on the item.
	If a item set to <strong>Hide</strong> the symbol <strong>^</strong> is shown at the front.<br>
	<strong>Select example</strong>: On "Admin" the panel is hidden only on <i>Admin</i>, on "^Admin" the panel is always hidden, but not on <i>Admin</i>';
$txt['pmx_settings_index_front_help'] = 'If checked, the Frontpage content can be indexed by spiders like google.';
$txt['pmx_settings_restoretop_help'] = 'The browser vertically page position is restored on change the page on Frontpage blocks like category, article and other they have more then one page.';
$txt['pmx_settings_loadinactive_help'] = 'If enabled, not active blocks on the <strong>top, head, left, right, bottom and foot</strong> panel are loaded but not shown on the blocks manager overview.
	So you can see the result immediate if you enable not active blocks.<br>
	If <strong>not checked</strong>, you must reload the page to see the result after enable not active blocks.';
$txt['pmx_settings_colminwidth_help'] = 'Enter the minimum width of the <strong>Screen width</strong> to show two-column frontpage blocks (like boardnews, promoted posts) also on <strong>mobile devices</strong> as two-column blocks.
	If the width of the screen less than the specified value, two-column blocks are displayed as one-column blocks.
	A value of <strong>640</strong> or greater is a good choice to display these on smaller devices as single-column blocks. Enter <strong>0</strong> or leave this empty to disable this feature.<br>
	Note that <strong>Caching</strong> must be activated, if you have the <strong>ECL</strong> function enabled else <strong>this function don\'t work!</strong>';
$txt['pmx_access_promote_help'] = 'Members in the selected groups can promote posts in the forum.<br>
	<strong>Granted rights:</strong> <i>Add and remove promote to posts</i>';
$txt['pmx_access_articlecreate_help'] = 'Members in the selected groups can create articles, edit or delete his own articles.
	Articles the created by this membergroups must be approved by a Article Moderator or Administrator.<br>
	<strong>Granted rights:</strong> <i>Create article, edit, clone, delete, activate/deactivate own articles</i>';
$txt['pmx_access_articlemoderator_help'] = 'Members in the selected groups can create, edit, delete and approve articles they enabled for <strong>Moderate Article</strong>.
	This is always given, if a article created by the Article create groups.<br>
	<strong>Granted rights:</strong> <i>Create article, edit, clone, delete, activate/deactivate, approve/unapprove</i>';
$txt['pmx_access_blocksmoderator_help'] = 'Members in the selected groups can edit blocks they enabled for <strong>Moderate Blocks</strong>.
	The access to the blocks is limited by the enabled panels (see Manager settings).<br>
	<strong>Granted rights:</strong> <i>Edit the content, access, title, css settings, activate/deactivate</i>';
$txt['pmx_access_pmxadmin_help'] = 'Members in the selected groups have <strong>full access</strong> to all parts of the entire Portal.
	The Members have the same rights as a Forum Admin, but limited to the Portal. <strong>Handle this with care !</strong>';
$txt['pmx_frontpage_help'] = 'Select the Frontpage, which you use.<br>
	Note, that the full size Frontpage normally have <strong>no</strong> Menubar, but you can enable a small Menubar.<br>
	Single pages are always displayed, even if the Frontpage set to "no Frontpage".<br>
	If you need a additional CSS for the full size Frontpage, create a CSS file (<strong>frontpage.css</strong>) and save it to the directory of the theme.';
$txt['pmx_settings_adminpageshelp'] = 'Members in the <strong>Portal Moderator group</strong> can change the settings on the Blocks Manager overview and edit the content of the blocks they enabled for moderate.
	<strong>Handle this option with care!</strong>';
$txt['pmx_settings_xbars_help'] = 'Select the panels, they you can collapse or expand with the xBars.
	<strong>xBars</strong> are narrow strips on the left, right, up and down edge of the browser area, they shown once you move the mouse over the area.
	The xBars also work with mobile devices.';
$txt['pmx_settings_collapse_vishelp'] = 'The panel is used in Block settings. You can collapse that initially, but it\'s shown always if the Block have dynamic visibility options.';
$txt['pmx_settings_xbarkeys_help'] = 'If checked, you can collapse/expand the panels with the <strong>Alt</strong> key and a <strong>Numpad</strong> key (<strong>4=left, 6=right, 9=head, 8=top, 2=bottom, 3=foot</strong>). Note that the <strong>xBarKeys</strong> are disabled if the editor loaded.';
$txt['pmx_settings_panel_custhelp'] = 'Here you can enter any other actions.
	For <strong>Single pages, Articles and Categories</strong> we use a prefix (<strong>p:</strong> for Single pages, <strong>a:</strong> for Articles and <strong>c:</strong> for Categories).
	Enter the prefix before the page, article or category name, as example <strong>p:mypage</strong>.
	You can use names with the wildcards <strong>*</strong> and <strong>?</strong>. In this case the panel is invisible, whose name matched.
	Furthermore you can also use subaction, these starts alway with a ampersand (<strong>&amp;</strong>) like <strong>&amp;subactionname=value</strong>.
	For more detailed informations about the customer actions read our documentation.';
$txt['pmx_settings_downloadhelp'] = 'If checked, a <strong>Download</strong> button is shown next to the <strong>Community</strong> button.';
$txt['pmx_settings_dl_actionhelp'] = 'Define the action which the download button to be assigned.<br>
	You can use any name with the character (<strong>a-z, A-Z, 0-9, -, _</strong>).<br>For Single pages, Articles and Categories you have to add a prefix before the name
	(<strong>p:</strong> for Single pages, <strong>a:</strong> for Articles and <strong>c:</strong> for Categories) as example <strong>p:download</strong>';
$txt['pmx_settings_other_actionshelp'] = 'Enter one or more request names (separated by comma) they are handled as Forum requests.
	You can enter <strong>name=value</strong> pairs like <strong>project=1</strong> for the Project tool.';
$txt['pmx_settings_quickedithelp'] = 'You can enable a direct link to the Manager <strong>edit function</strong>.
	The links is associated to the <strong>title</strong> and is active only for Admins and Portal Admins.';
$txt['pmx_settings_pages_help'] = 'Enter names for Singe Pages, Categories and Articles (separated by comma), for which you will hide the Frontpage blocks.
	Use the prefix <strong>p:</strong> for Single pages, <strong>a:</strong> for Articles and <strong>c:</strong> for Categories.
	You can use names with the wildcards <strong>*</strong> and <strong>?</strong><br>
	Leave this empty, if you want to place Frontpage block individually with the block settings.';
$txt['pmx_settings_article_on_pagehelp'] = 'Enter the number of Articles you will see in the Article Manager overview page';
$txt['pmx_settings_postcountacshelp'] = 'Use the Forum Post count based groups for the block access, additional to the Regular groups.';
$txt['pmx_settings_pmxteasecnthelp'] = 'In different blocks a <i>Post teaser</i> is used.
	Here you can set, as the teaser is supposed to work.
	For languages that do not use spaces between words, the setting, <strong>Count characters</strong> is suggest.';
$txt['pmx_settings_promote_messages_help'] = 'You see all promoted message id\'s and you can add or remove message id\'s. Note that each id is separated by a comma.';
$txt['pmx_settings_enable_promote_help'] = 'If checked the Promote function is enabled and you see a <strong>Set Promote</strong> link belove each message. If the message already promoted, the link is show as <strong>Clear Promote</strong>.';
$txt['pmx_edit_cachehelp'] = 'If enabled, the content is saved and refreshed after the given time.<br>
	You can use the multiplicator "*" like "24*60" for one day.';
$txt['pmx_edit_pmxcachehelp'] = 'Caching will save many Database requests, so the page is loaded faster.<br>
	Please do not change the cache time, until you known what you do. A bad value can slow down your server!<br >
	To restore the default value, disable the cache and enable it again.';
$txt['pmx_used_stylehelp'] = 'Select the styles you will use. <strong>HEADER</strong> contains the title, a title icon and the collapse icon,
	<strong>FRAME</strong> is the border around the content, <strong>BODY</strong> is the backgrond for the content, <strong>BODYTEXT</strong> is the size of the body text.<br><hr />
	<strong>POSTHEADER</strong>, <strong>POSTFRAME</strong> and <strong>POSTBODY</strong> is <strong>only used</strong> on block like <strong>Promoted Posts</strong> and contains the styles for the content-header, content-frame and content-body for these blocks.';
$txt['pmx_used_style2help'] = 'Select the styles you will use. <strong>HEADER</strong> contains the title, a title icon and the collapse icon,
	<strong>FRAME</strong> is the border around the content, <strong>BODY</strong> is the backgrond for the content, <strong>BODYTEXT</strong> is the size of the body text.';

$txt['pmx_edit_frontplacinghelp'] = 'Choice the placement for this block if a Singe page, Category or Article requested.';
$txt['pmx_edit_groups_help'] = 'Choose your membergroups that will able to see this block.<br>
	You can also use <strong>deny group</strong>. This is useful when a user is in more than one group, but one of the groups should not see the block.<br>
	To toggle between deny groups and access groups, hold down the <strong>Ctrl Key</strong> and <strong>double click</strong> on the item.
	If the group a deny group,  you see the deny symbol <strong>^</strong> before the group name.';
$txt['pmx_edit_ext_opts_custhelp'] = 'Here you can enter any other actions.
	For <strong>Single pages, Articles and Categories</strong> we use a prefix (<strong>p:</strong> for Single pages, <strong>a:</strong> for Articles and <strong>c:</strong> for Categories).
	Enter the prefix before the page, article or category name, as example <strong>p:mypage</strong>.
	You can use names with the wildcards <strong>*</strong> and <strong>?</strong>. To <strong>Hide</strong> the Block on a entry, enter the symbol <strong>^</strong> before the name or the Prefix.
	Furthermore you can also use subaction, these starts alway with a ampersand (<strong>&amp;</strong>) like <strong>&amp;subactionname=value</strong>.
	More detailed informations about the customer actions you can find in our documentation or in the Support Forum.';
$txt['pmx_edit_ext_opts_selnote'] = 'To show or hide the block, hold down the <strong>Ctrl Key</strong> and <strong>click</strong> on the items.
	To toggle between <strong>Show and Hide</strong>, hold down the <strong>Ctrl Key</strong> and take a <strong>double click</strong> (IE needs three clicks!) on the item.
	If a item set to <strong>Hide</strong> the symbol <strong>^</strong> is shown at the front.';
$txt['pmx_block_moderatehelp'] = 'If checked, all member in the Block Moderator Group can edit this block.';
$txt['pmx_block_eclcheckhelp'] = 'If you have the ECL mode enabled and the Block is visible for Guests, he is only shown if ECL accepted.
	Use this setting for blocks they create cookies, such as Google Ads. If ECL mode not enabled, this settings is ignored.';
$txt['pmx_block_eclcheckbotshelp'] = 'If the Block visible for Guests, he is also visible for spider (like Google) even is the ECL mode enabled.
	To hide the block for spiders, enable these option.';
$txt['pmx_fader_timehelp'] = 'All times must be entered as #.#### seconds. The given value is internal converted to milliseconds (#.#### * 1000)';
$txt['pmx_custom_css_filehelp'] = 'You can create your own CSS file to give your blocks, articles or categories ar a different look.
	An example can be found in the file:<br><strong>Themes/default/Portal/BlockCss/custom.mpt</strong><br>where an explanation of the parameters is included.';

$_convertTypes = 'HTML => SCRIPT, BBC SCRIPT<br>BBC SCRIPT => SCRIPT, HTML<br>SCRIPT => BBC SCRIPT, HTML';
$txt['pmx_block_select_help'] = 'Select the block type you will use.<br>
	You can also convert the content between the following block types:<br>'. $_convertTypes .'<br>
	<strong>On all other converting, the Content is LOST !</strong>';
$txt['pmx_article_select_help'] = 'Select the article type you will use.<br>
	You can also convert the content between the following block types:<br />'. $_convertTypes;
$txt['pmx_disable_lightbox_help'] = 'If checked, attached and inserted images cannot expand with the Lightbox viewer.';

// do not reformat these !
$txt['pmx_fader_content_help'] = 'You can use any html code in the fader.
	Each entry must enclosed in curly brackets <strong>{ .. }</strong>.
	Line breaks, carriage returns, tabs and spaces will be removed on runtime.
	You can overwrite the time values for each entry by adding a <strong>=(uptime,downtime,holdtime)</strong> immediate after the closed curly brackets <strong>}</strong>.
	All time values must be define in seconds.
	You can also overwrite a singe value like <strong>=(,,5.0)</strong> which will change the holdtime only.<br>
	<strong>Example</strong>: <pre>{A simple text&lt;br /&gt;
break in two lines.}
{a image &lt;img src="smile.gif" alt="*" title="smile" /&gt;}
{&lt;a href="url.tld" target="_blank" rel="noopener"&gt;This is a link&lt;/a&gt;}=(1.5,1.5,4.0)</pre>';

$txt['pmx_boponews_rescalehelp'] = 'Inline images can be rescaled or removed.
 Enter the <strong>width, height</strong> (pixel), <strong>width%, height%</strong> (percent) or <strong>0</strong> to remove inline images.
	You can enter <strong>on</strong> value, as example <strong>50</strong> or <strong>,50</strong>. In this case, the width/height is accordingly set to the specified value.
	If you want to view the images in their original size, type in a comma (<strong>,</strong>) nothing else. In this case, any existing value for height or width is removed.
	If you don\'t change the images, leave this empty.<br>
	<strong>Hints:</strong> If you set the width to <strong>99.5%</strong>, bigger Images are exactly passed to the post area. <strong>Smaller</strong> Images are not changed.
	Also you can use different value types (percent/pixel) for width and height, as example <strong>99.5%,300</strong>.';
$txt['pmx_boponews_thumbsizehelp'] = 'Enter <strong>width, height</strong> as pixel to rescale images in the attachments area or leave this empty to use the original size.
	You can also enter one value, like <strong>width</strong> or <strong>,height</strong>. In this case the image is rescaled according his original size.<br>
	Use this, if you not have thumnais or the thumbnail are to big for this area.';
$txt['pmx_rssreader_urlhelp'] = 'For SMF Forums you can use follow:<br>'.
	'<strong>forumurl?action=.xml;<i>options</i></strong><br>'.
	'<i>Options:</i> &nbsp;type=s;sa=s;boards=n;limit=n;<br>'.
	'&nbsp; type: <strong>rss</strong> | <strong>rss2</strong> | <strong>rdf</strong> | <strong>atom</strong><br>'.
	'&nbsp; sa: <strong>recent</strong> | <strong>news</strong> | <strong>members</strong><br>'.
	'&nbsp; boards: <strong>#[,#,#]</strong> (# is the board id)<br>'.
	'&nbsp; limit: <strong>#</strong> (# is a number, 1 to n)<br>'.
	'<i>Defaults: </i>sa=recent';
$txt['pmx_rssreader_timeouthelp'] = 'The reader stops the reading after response timeout, if no data received. (Default: <strong>5</strong> seconds)';
$txt['pmx_rssreader_usettlhelp'] = 'If checked, a received TTL (Time To Life) enabled the cache and set the cache time automatic to the received value.';
$txt['pmx_rssmaxitems_help'] = 'Specify the maximum number of contributions or leave blank to see all posts by this option.';
$txt['pmx_rssreader_cont_encodehelp'] = 'If you enable this option and the feed send a "encoded" content (many feeds do that), you see a longer content with images and other elements.';
$txt['pmx_rssreader_help'] = 'The follow settings used only, if not feed header send.';
$txt['pmx_rssreader_delimagehelp'] = 'If enabled, inline images and objects are removed.';
$txt['pmx_rssmaxitems_help'] = 'Enter the number of max item you will see or leave empty to see all received articles.';
$txt['pmx_rsspage index_help'] = 'Enter the number of articles you will see on a page.';
$txt['pmx_recentsplit_help'] = 'If this activated, it\'s possible that the line is shorten and the Time is not full visible. This is dependent on the Panel width.';
$txt['pmx_recent_boards_help'] = 'Select the boards to show or select nothing to show all boards.';
$txt['pmx_admstat_olheight_help'] = 'Enter 0 to disable the User online list';
$txt['pmx_boponews_hidethumbshelp'] = 'If checked, the attachments area is collapsed and can expand manually for each post.';
$txt['pmx_boponews_thumbcnthelp'] = 'Enter the number of max attachments to show or leave this empty to show all.';
$txt['pmx_boponews_disableYoutubehelp'] = 'If checked, embedded Youtube videos show only as a <strong>link to Youtube</strong>.';

$txt['permissionhelp_manage_portamx'] = 'With this permission any members of this group can access the Portal Block Manager moderation.';
$txt['pmx_shoutbox_maxshouthelp'] = 'Enter the number of shout to save. Older shouts will be automatically removed, if this value overflow.';
$txt['pmx_catblock_inherithelp'] = 'If checked, the block permissions is inherit to the category.
	This is done even, if the permission on the category are higher as on the block.<br>
	Note that the access to the articles in the category in NOT inherit from the block,
	the article access is given by the article or is inherit from the category.';
$txt['pmx_artblock_inherithelp'] = 'if checked, the block permissions is inherit to the article.
	This is done even, if the permission on the article are higher as on the block.';
$txt['pmx_adm_teasehelp'] = 'Leave this empty or Enter 0 for no tease';;
$txt['pmx_pageindex_help'] = 'Enter the number of posts you will see on a page.
	If the number of posts to show bigger as this value, the page index is show.
	Leave this empty (or set to 0) to disable the pagination.';
$txt['pmx_pageindex_tophelp'] = 'If checked, the page index is show on top and bottom.
	If disabled the page index is show only on bottom.';
$txt['pmx_articlecat_help'] = 'Select a category in wich you will see this article or <strong>none</strong> if you not link this article to a category.';
$txt['pmx_cat_to_art_design_help'] = 'If checked, the category style setting for the title, frame, body, bodytext and the custum css is inherit to the article, so both have the same style.'; 
$txt['pmx_polls_hint'] ='If you select more then one poll, a "multiple" pollblock with a select bar at the bottom is created.';
?>