<?php
// Version: 1.41; Admin

/**
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx
 * @copyright 2018 PortaMx
 *
 * Language file Admin.english
 */

// Portal Center
$txt['pmx_admin_center'] = 'Portal Center';
$txt['pmx_admin_main_welcome'] = 'This is your "'. $txt['pmx_admin_center'] .'".
	From here, you can maintain your settings, blocks, articles and categories.
	You may also find answers to your questions by clicking the symbols %s for more information on the related functions.';
$txt['pmx_admin_main_custom'] = 'This is your "'. $txt['pmx_admin_center'] .'".
	From here, you can maintain your asigned settings.
	You may also find answers to your questions by clicking the symbols %s for more information on the related functions.';

$txt['pmx_center_mansettings'] = 'Settings Manager';
$txt['pmx_center_mansettings_desc'] = 'Change or set options for the Panels, the Frontpage and for the Block Manager.';
$txt['pmx_center_manblocks'] = 'Block Manager';
$txt['pmx_center_manblocks_desc'] = 'Change or set options for the Blocks, create new, move, edit and delete.';
$txt['pmx_center_mancategories'] = 'Category Manager';
$txt['pmx_center_mancategories_desc'] = 'Change or set options for Categories, create new, move, edit and delete.';
$txt['pmx_center_manarticles'] = 'Article Manager';
$txt['pmx_center_manarticles_desc'] = 'Change or set options for Articles, create new, move, edit and delete.';

$txt['pmx_allpanels'] = '<strong>Mobile Menu "Portal Panels"</strong>';
$txt['pmx_allpanelSetting'] = '<strong>Mobile Menu "Portal Panel Setting"</strong>';
$txt['pmx_allsettings'] = '<strong>Mobile Menu "Portal Settings"</strong>';
$txt['mobile_portal_blocks'] = 'Blocks Manager';
$txt['mobile_portal_settings'] = 'Settings Manager';

// AdminBlocks
$txt['pmx_admBlk_desc'] = 'Manage your blocks with edit, move, clone, create or delete.<br>
	On the overview you have also a lot of quick functions to edit the elements.';
$txt['pmx_blocks_mod'] = 'Portal Block Manager moderation';
$txt['pmx_admBlk_sides'] = array(
	'front' => 'Frontpage',
	'head' => 'Head Panel',
	'top' => 'Top Panel',
	'left' => 'Left Panel',
	'right' => 'Right Panel',
	'bottom' => 'Bottom Panel',
	'foot' => 'Foot Panel',
	'pages' => 'Single Pages',
);

// default for access
$txt['pmx_guest'] = 'Guests';
$txt['pmx_ungroupedmembers'] = 'Regular Members';

// panel / block overflow actions
$txt['pmx_overflow_actions'] = array(
	'' => 'none',
	'auto' => 'Let\'s do the Browser',
	'hidden' => 'Clip at frame');

// actions for panels and blocks
$txt['pmx_cache_autoclear'] = 'Cleaned on day change';
$txt['pmx_action_names'] = array(
	'frontpage' => 'Frontpage',
	'pages' => 'Pages',
	'articles' => 'Articles',
	'categories' => 'Categories',
	'community' => 'Community',
	'boards' => 'Boards',
	'topics' => 'Topics',
	'admin' => 'Admin',
	'calendar' => 'Calendar',
	'login,login2,reminder' => 'Login',
	'logout' => 'Logout',
	'moderate' => 'Moderate',
	'mlist' => 'Memberlist',
	'pm' => 'Pers. Messages',
	'post' => 'Post Topic',
	'profile' => 'Profile',
	'recent' => 'Recent posts',
	'register,register2' => 'Register',
	'stats' => 'Show stats',
	'search,search2' => 'Search',
	'unread' => 'Unread posts',
	'unreadreplies' => 'Unread replies',
	'who' => 'Who',
);

// Device settings
$txt['pmx_blocks_devices'] = 'Visibility on device types';
$txt['pmx_settings_devices'] = 'Panel visibility on device types:';
$txt['pmx_devices']['all'] = 'Show on all devices';
$txt['pmx_devices']['desk'] = 'Show only on Desktop devices';
$txt['pmx_devices']['mobil'] = 'Show only on Mobile devices';

// default settings
$txt['settings_saved'] = 'The settings were successfully saved';
$txt['pmx_defaultsettings'] = 'This block type have no settings.';
$txt['pmx_default_header_none'] = 'hide';
$txt['pmx_default_header_catbg'] = 'round';
$txt['pmx_default_header_titlebg'] = 'bar';
$txt['pmx_default_header_asbody'] = 'as body';
$txt['pmx_default_none'] = 'none';

$txt['pmx_information_icon'] = 'Click for more Information';
$txt['pmx_actionfault'] = 'Illegal request reached!';
$txt['pmx_sysblock'] = 'Systemblock';
$txt['pmx_userblock'] = 'Userblock';

// popups
$txt['pmx_category_popup'] = 'Category settings';
$txt['pmx_article_information'] = 'Detailed informations for article id %s';
$txt['pmx_click_edit_ttl'] = 'Click to edit the title';
$txt['pmx_edit_titles'] = 'Edit title settings';
$txt['pmx_cat_clone'] = 'Clone category';
$txt['pmx_confirm_catclone'] = 'Are you sure you want to clone this category?';
$txt['pmx_cat_delete'] = 'Delete category';
$txt['pmx_confirm_catdelete'] = 'Are you sure you want to delete this category?';
$txt['pmx_art_delete'] = 'Delete article';
$txt['pmx_confirm_artdelete'] = 'Are you sure you want to delete this article?';
$txt['pmx_art_clone'] = 'Clone article';
$txt['pmx_confirm_artclone'] = 'Are you sure you want to clone this article?';

// Buttons
$txt['pmx_save'] = 'Save';
$txt['pmx_create'] = 'Create';
$txt['pmx_save_exit'] = 'Save &amp; Exit';
$txt['pmx_save_cont'] = 'Save &amp; Continue';
$txt['pmx_savechanges'] = 'Save changes';
$txt['pmx_cancel'] = 'Cancel';
$txt['pmx_update_save'] = 'Update';
$txt['pmx_update_all'] =  'Update ALL';
$txt['pmx_delete_button'] = 'Yes, i\'m sure';

// Article Manager
$txt['pmx_articles_desc'] = 'Manage your articles with create, edit, clone or delete.<br>
	On the overview you have also a lot of quick functions to edit the elements.';
$txt['pmx_articles_disableHSimage'] = 'Disable Lightbox for Images:';

// overview
$txt['pmx_articles_overview'] = 'Article overview';
$txt['pmx_articles_add'] = 'Add new article';
$txt['pmx_articles_title'] = 'Title';
$txt['pmx_articles_type'] = 'Type';
$txt['pmx_articles_catname'] = 'Category';
$txt['pmx_edit_article'] = 'Click to edit this article';
$txt['pmx_articles_info'] = 'Click for detailed informations';
$txt['pmx_chg_articlnocats'] = 'No category';
$txt['pmx_chg_articlcats'] = ' - click to change';
$txt['pmx_status_activ'] = 'Active';
$txt['pmx_status_inactiv'] = 'Not active';
$txt['pmx_status_change'] = 'Click to change';
$txt['pmx_article_order'] = 'Order';
$txt['pmx_have_ecl_settings'] = 'Block is hidden until ECL is accepted';
$txt['pmx_have_artecl_settings'] = 'Article is hidden until ECL is accepted';
$txt['pmx_have_catecl_settings'] = 'Category is hidden until ECL is accepted';

$txt['pmx_rowmove_title'] = 'Set new Article position';
$txt['pmx_rowmove'] = 'Move Article';
$txt['pmx_rowmove_to'] = 'Article';
$txt['pmx_rowmove_place'] = 'to the position';
$txt['pmx_rowmove_before'] = 'before';
$txt['pmx_rowmove_after'] = 'after';
$txt['pmx_rowmove_updown'] = 'Click to move Article position';
$txt['pmx_rowmove_error'] = 'You can\'t move the article to itself !';

$txt['pmx_chg_articleaccess'] = 'Click to change the article access';
$txt['pmx_clone_article'] = 'Click to clone this article';
$txt['pmx_delete_article'] = 'Click to delete this article';
$txt['pmx_article_groupaccess'] = 'Article have access settings';
$txt['pmx_article_modaccess'] = 'Article have moderate access';
$txt['pmx_article_cssfile'] = 'Article have own style sheet file';
$txt['pmx_article_approved'] = 'Approved';
$txt['pmx_article_not_approved'] = 'Not approved';

$txt['pmx_article_filter'] = 'Click to set a article filter';
$txt['pmx_article_setfilter'] = 'Setup article filter';
$txt['pmx_article_filter_category'] = 'Show category(s):';
$txt['pmx_article_filter_categoryClr'] = 'Clear filter';
$txt['pmx_article_filter_approved'] = 'Show not approved articles:';
$txt['pmx_article_filter_active'] = 'Show not active articles:';
$txt['pmx_article_filter_myown'] = 'Show my own articles:';
$txt['pmx_article_filter_member'] = 'Show articles from:';
$txt['pmx_article_filter_membername'] = '(Member name)';
$txt['set_article_filter'] = 'Apply';

// Article types
// do not change the keys of this array !!
$txt['pmx_articles_types'] = array(
	'html' => 'HTML',
	'script' => 'Script',
	'bbc_script' => 'BBC Script',
	'php' => 'PHP',
);

// edit
$txt['pmx_article_edit'] = 'Edit article';
$txt['pmx_article_edit_new'] = 'Create article';
$txt['pmx_article_title'] = 'Article title:';
$txt['pmx_article_type'] = 'Article type:';
$txt['pmx_article_cats'] = 'Category:';
$txt['pmx_article_name'] = 'Article name:';
$txt['pmx_article_settings_title'] = 'Article settings';
$txt['pmx_article_groups'] = 'Access settings';
$txt['pmx_article_moderate_title'] = 'Other Article settings';
$txt['pmx_article_moderate'] = 'Enable article moderation:';
$txt['pmx_article_teaser'] = 'Number of %s before tease:';
$txt['pmx_article_footer'] = 'Show Author, Date, last Update:';
$txt['pmx_settings_restorespeed_time'] = ' milliseconds';

// access popup
$txt['pmx_acs_repl'] = 'Set';
$txt['pmx_acs_add'] = 'Add';
$txt['pmx_acs_rem'] = 'Remove';

// Categories Manager
$txt['pmx_categories_desc'] = 'Manage your categories with create, edit, move, clone or delete.<br>
	On the overview you have also a lot of quick functions to edit the elements.';

// overview
$txt['pmx_categories_overview'] = 'Category overview';
$txt['pmx_categories_add'] = 'Add new category';
$txt['pmx_categories_name'] = 'Name';
$txt['pmx_categories_order'] = 'Order';
$txt['pmx_categories_level'] = 'Level';
$txt['pmx_edit_categories'] = 'Click to edit this category';
$txt['pmx_clone_categories'] = 'Click to clone this category';
$txt['pmx_move_categories'] = 'Click to move this category';
$txt['pmx_editname_categories'] = ' - Click to edit the name';
$txt['pmx_categories_showarts'] = 'Articles in the category';
$txt['pmx_delete_categories'] = 'Click to delete this category';
$txt['pmx_chg_categoriesaccess'] = 'Click to change category access';
$txt['pmx_confirm_categoriesdelete'] = 'Are you sure you want to delete this category?';
$txt['pmx_confirm_categoriesclone'] = 'You want to clone this category?';
$txt['pmx_categories_groupaccess'] = 'Category have access settings';
$txt['pmx_categories_cssfile'] = 'Category have own style sheet file';
$txt['pmx_categories_articles'] = 'Category have %s article(s)';
$txt['pmx_categories_none'] = '[none]';
$txt['pmx_categories_setname'] = 'Change category name';
$txt['pmx_update_all'] =  'Update ALL';

// popups
$txt['pmx_categories_popup'] = 'Category settings';
$txt['pmx_categories_movecat'] = 'Set new Category position';
$txt['pmx_categories_move'] = 'Move Category';
$txt['pmx_categories_moveplace'] = 'to the position';
$txt['pmx_categories_tomovecat'] = 'Category';
$txt['pmx_categories_move_error'] = 'You can\'t move a category to itself !';

// cat infos
$txt['pmx_categories_root'] = 'Root category';
$txt['pmx_categories_rootchild'] = 'Root category with child\'s';
$txt['pmx_categories_childchild'] = 'Child of category &quot;%s&quot; with child\'s';
$txt['pmx_categories_child'] = 'Child of category &quot;%s&quot;';

// Category placement
// do not change the keys of this array !!
$txt['pmx_categories_places'] = array(
	'before' => 'before',
	'after' => 'after',
	'child' => 'as child of',
);

// showmodes
$txt['pmx_categories_modsidebar'] = 'Show the first article and all other in a sidebar:';
$txt['pmx_categories_modpage'] = 'Show all articles in one page:';
$txt['pmx_categories_modpage_count'] = 'Number of articles in a page:';
$txt['pmx_categories_modpage_pageindex'] = 'Show page index always:';
$txt['pmx_categories_addsubcats'] = 'Add subcategories to the sidebar:';
$txt['pmx_categories_showsubcats'] = 'Show subcategories in a sidebar:';
$txt['pmx_categories_sidebarwith'] = 'Width of sidebar (Pixel):';
$txt['pmx_categorie_inherit'] = 'Inherit Category access to Articles:';
$txt['pmx_categorie_articlesort'] = 'Article sorting:';
$txt['pmx_categories_sidebaralign'] = 'Sidebar align:';
// do not change the keys of this array !!
$txt['pmx_categories_sbalign'] = array(
	0 => 'Left',
	1 => 'Right',
);

// Articles sort mode
// do not change the keys of this array !!
$txt['pmx_categories_artsort'] = array(
	'id' => 'Article ID',
	'name' => 'Article name',
	'created' => 'Date created',
	'updated' => 'Date updated',
	'approved' => 'Date approved',
	'owner' => 'Article owner',
);
$txt['pmx_artsort'] = array(
	0 => ' (descending)',
	1 => ' (ascending)'
);

// Categories/Articles show mode
// do not change the keys of this array !!
$txt['pmx_categories_showmode'] = array(
	'both' => 'Show Titlebar/Frame for Category and Articles:',
	'article' => 'Hide Titlebar/Frame for Category, show Titlebar/Frame for Articles:',
	'category' => 'Show Titlebar/Frame for Category, hide Titlebar/Frame for Articles:',
	'none' => 'Hide Titlebar/Frame for Category and Articles:',
);
$txt['pmx_categories_visual'] = 'Use Titelbar/Frame settings also for Articles:';

// edit
$txt['pmx_categories_edit'] = 'Edit category';
$txt['pmx_categories_edit_new'] = 'Create new category';
$txt['pmx_categories_title'] = 'Category title:';
$txt['pmx_categories_type'] = 'Place category:';
$txt['pmx_categories_cats'] = 'Category:';
$txt['pmx_categories_settings_title'] = 'Category settings';
$txt['pmx_categories_groups'] = 'Access settings';
$txt['pmx_categories_globalcat'] = 'Global category access';
$txt['pmx_categorie_global'] = 'Disable global use:';
$txt['pmx_categorie_request'] = 'Disable category request:';

$txt['pmx_check_catelcmode'] = 'Hide the category until ECL is accepted:';
$txt['pmx_check_catelcbots'] = 'Hide the category for spider:';
$txt['pmx_check_artelcmode'] = 'Hide the article until ECL is accepted:';
$txt['pmx_check_artelcbots'] = 'Hide the article for spider:';

// common edit for Blocks, Articles, Categories
$txt['pmx_edit_title_helpalign'] = 'Click to set Title align ';
$txt['pmx_editblock'] = 'Edit Block ';
$txt['pmx_editblock_new'] = 'Create Block ';
$txt['pmx_edit_title'] = 'Title:';
$txt['pmx_edit_title_lang'] = 'Language:';
$txt['pmx_edit_title_align'] = 'Align:';
$txt['pmx_edit_pagename'] = 'Page name:';
$txt['pmx_edit_titleicon'] = 'Title icon:';
$txt['pmx_edit_no_icon'] = 'no icon';
$txt['pmx_edit_content'] = 'Create or edit the content';
$txt['pmx_settings_deviceshelp'] = 'Select the type of device on which the panel is to be displayed.';
$txt['pmx_boponews_disableYoutube'] = 'Show Youtube videos only as link:';

$txt['pmx_block_move_error'] = 'You can\'t move the block to itself !';
$txt['namefielderror'] = 'The input field for "%s" is empty !';
$txt['pmx_edit_title_align_types'] = array(
	'left' => 'Left',
	'center' => 'Center',
	'right' => 'Right'
);

$txt['pmx_title'] = 'Title';
$txt['pmx_status'] = 'Status';
$txt['pmx_options'] = 'Options';
$txt['pmx_functions'] = 'Functions';
$txt['pmx_edit_titles'] = 'Edit title settings';
$txt['pmx_toggle_language'] = 'Click to toggle between languages';

$txt['pmx_edit_visuals'] = 'Visual settings and CSS classes';
$txt['pmx_edit_cancollapse'] = 'Can collapse:';
$txt['pmx_edit_overflow'] = 'Overflow action:';
$txt['pmx_pixel_blank'] = ' Pixel or leave blank';

$txt['pmx_edit_height'] = 'Fixed block height as:';
$txt['pmx_edit_height_mode'] = array(
	'max-height' => 'max height',
	'height' => 'height',
	'min-height' => 'min height');

$txt['pmx_edit_collapse_state'] = 'Entry block state:';
$txt['pmx_collapse_mode'] = array(
	0 => 'default',
	1 => 'collapsed',
	2 => 'expanded');

$txt['pmx_edit_cssfilename'] = 'Custom CSS File:';
$txt['pmx_edit_usedclass_type'] = 'Type name';
$txt['pmx_edit_usedclass_style'] = 'Assigned style class';
$txt['pmx_edit_canhavecssfile'] = 'Select a custom cssfile or leave blank';
$txt['pmx_edit_nocss_class'] = '[not defined for Theme %s]';

$txt['pmx_edit_innerpad'] = 'Inner padding:';
$txt['pmx_pixel'] = ' Pixel';

$txt['pmx_htmlsettings_title'] = 'Html block settings';
$txt['pmx_html_teaser'] = 'Enable the html teaser:';

$txt['pmx_teasemode'] = array(
	0 => 'words',
	1 => 'characters'
);

$txt['pmx_add_new_blocktype'] = 'Add new block on %s';
$txt['pmx_blocks_blocktype'] = 'Select the Blocktype:';
$txt['pmx_add_new_articletype'] = 'Add new Article';
$txt['pmx_articles_articletype'] = 'Select the Articletype:';
$txt['pmx_content_print'] = 'Enable content printing:';
$txt['close_window'] = 'Close window';
?>