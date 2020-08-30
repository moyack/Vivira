#### ATTENTION: You do not need to run or use this file!  The install.php script does everything for you!
#### Install script for MySQL

#
# Table structure for table `admin_info_files`
#

CREATE TABLE {$db_prefix}admin_info_files (
	id_file TINYINT(4) UNSIGNED AUTO_INCREMENT,
	filename VARCHAR(255) NOT NULL DEFAULT '',
	path VARCHAR(255) NOT NULL DEFAULT '',
	parameters VARCHAR(255) NOT NULL DEFAULT '',
	data TEXT NOT NULL,
	filetype VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (id_file),
	INDEX idx_filename (filename(30))
) ENGINE={$engine};

#
# Table structure for table `approval_queue`
#

CREATE TABLE {$db_prefix}approval_queue (
	id_msg INT(10) UNSIGNED NOT NULL DEFAULT '0',
	id_attach INT(10) UNSIGNED NOT NULL DEFAULT '0',
	id_event SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE={$engine};

#
# Table structure for table `attachments`
#

CREATE TABLE {$db_prefix}attachments (
	id_attach INT(10) UNSIGNED AUTO_INCREMENT,
	id_thumb INT(10) UNSIGNED NOT NULL DEFAULT '0',
	id_msg INT(10) UNSIGNED NOT NULL DEFAULT '0',
	id_member MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	id_folder TINYINT(3) NOT NULL DEFAULT '1',
	attachment_type TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	filename VARCHAR(255) NOT NULL DEFAULT '',
	file_hash VARCHAR(40) NOT NULL DEFAULT '',
	fileext VARCHAR(8) NOT NULL DEFAULT '',
	size INT(10) UNSIGNED NOT NULL DEFAULT '0',
	downloads MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	width MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	height MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	mime_type VARCHAR(20) NOT NULL DEFAULT '',
	approved TINYINT(3) NOT NULL DEFAULT '1',
	PRIMARY KEY (id_attach),
	UNIQUE idx_id_member (id_member, id_attach),
	INDEX idx_id_msg (id_msg),
	INDEX idx_attachment_type (attachment_type)
) ENGINE={$engine};

#
# Table structure for table `background_tasks`
#

CREATE TABLE {$db_prefix}background_tasks (
	id_task INT(10) UNSIGNED AUTO_INCREMENT,
	task_file VARCHAR(255) NOT NULL DEFAULT '',
	task_class VARCHAR(255) NOT NULL DEFAULT '',
	task_data MEDIUMTEXT NOT NULL,
	claimed_time INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (id_task)
) ENGINE={$engine};

#
# Table structure for table `ban_groups`
#

CREATE TABLE {$db_prefix}ban_groups (
	id_ban_group MEDIUMINT(8) UNSIGNED AUTO_INCREMENT,
	name VARCHAR(20) NOT NULL DEFAULT '',
	ban_time INT(10) UNSIGNED NOT NULL DEFAULT '0',
	expire_time INT(10) UNSIGNED,
	cannot_access TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	cannot_register TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	cannot_post TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	cannot_login TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	reason VARCHAR(255) NOT NULL DEFAULT '',
	notes TEXT NOT NULL,
	PRIMARY KEY (id_ban_group)
) ENGINE={$engine};

#
# Table structure for table `ban_items`
#

CREATE TABLE {$db_prefix}ban_items (
	id_ban MEDIUMINT(8) UNSIGNED AUTO_INCREMENT,
	id_ban_group SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	ip_low VARBINARY(16),
	ip_high VARBINARY(16),
	hostname VARCHAR(255) NOT NULL DEFAULT '',
	email_address VARCHAR(255) NOT NULL DEFAULT '',
	id_member MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	hits MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (id_ban),
	INDEX idx_id_ban_group (id_ban_group),
	INDEX idx_id_ban_ip (ip_low,ip_high)
) ENGINE={$engine};

#
# Table structure for table `board_permissions`
#

CREATE TABLE {$db_prefix}board_permissions (
	id_group SMALLINT(5) DEFAULT '0',
	id_profile SMALLINT(5) UNSIGNED DEFAULT '0',
	permission VARCHAR(30) DEFAULT '',
	add_deny TINYINT(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (id_group, id_profile, permission)
) ENGINE={$engine};

#
# Table structure for table `boards`
#

CREATE TABLE {$db_prefix}boards (
	id_board SMALLINT(5) UNSIGNED AUTO_INCREMENT,
	id_cat TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
	child_level TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
	id_parent SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	board_order SMALLINT(5) NOT NULL DEFAULT '0',
	id_last_msg INT(10) UNSIGNED NOT NULL DEFAULT '0',
	id_msg_updated INT(10) UNSIGNED NOT NULL DEFAULT '0',
	member_groups VARCHAR(255) NOT NULL DEFAULT '-1,0',
	id_profile SMALLINT(5) UNSIGNED NOT NULL DEFAULT '1',
	name VARCHAR(255) NOT NULL DEFAULT '',
	description TEXT NOT NULL,
	num_topics MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	num_posts MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	count_posts TINYINT(4) NOT NULL DEFAULT '0',
	id_theme TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
	override_theme TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
	unapproved_posts SMALLINT(5) NOT NULL DEFAULT '0',
	unapproved_topics SMALLINT(5) NOT NULL DEFAULT '0',
	redirect VARCHAR(255) NOT NULL DEFAULT '',
	deny_member_groups VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (id_board),
	UNIQUE idx_categories (id_cat, id_board),
	INDEX idx_id_parent (id_parent),
	INDEX idx_id_msg_updated (id_msg_updated),
	INDEX idx_member_groups (member_groups(48))
) ENGINE={$engine};

#
# Table structure for table `cache`
#

CREATE TABLE {$db_prefix}cache (
	cachekey VARCHAR(100) NOT NULL,
	value mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
	storedtm INT(11) NOT NULL,
	validtm INT(11) NOT NULL
) ENGINE={$engine} DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

#
# Index for table `cache`
#

ALTER TABLE {$db_prefix}cache
	ADD UNIQUE KEY idx_cachekey (cachekey);

#
# Table structure for table `calendar`
#

CREATE TABLE {$db_prefix}calendar (
	id_event SMALLINT UNSIGNED AUTO_INCREMENT,
	start_date date NOT NULL DEFAULT '0001-01-01',
	end_date date NOT NULL DEFAULT '0001-01-01',
	id_board SMALLINT UNSIGNED NOT NULL DEFAULT '0',
	id_topic MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	title VARCHAR(255) NOT NULL DEFAULT '',
	id_member MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
	start_time time,
	end_time time,
	timezone VARCHAR(80),
	location VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (id_event),
	INDEX idx_start_date (start_date),
	INDEX idx_end_date (end_date),
	INDEX idx_topic (id_topic, id_member)
) ENGINE={$engine};

#
# Table structure for table `calendar_holidays`
#

CREATE TABLE {$db_prefix}calendar_holidays (
	id_holiday SMALLINT(5) UNSIGNED AUTO_INCREMENT,
	event_date date NOT NULL DEFAULT '0001-01-01',
	title VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (id_holiday),
	INDEX idx_event_date (event_date)
) ENGINE={$engine};

#
# Table structure for table `categories`
#

CREATE TABLE {$db_prefix}categories (
	id_cat TINYINT(4) UNSIGNED AUTO_INCREMENT,
	cat_order TINYINT(4) NOT NULL DEFAULT '0',
	name VARCHAR(255) NOT NULL DEFAULT '',
	description TEXT NOT NULL,
	can_collapse TINYINT(1) NOT NULL DEFAULT '1',
	PRIMARY KEY (id_cat)
) ENGINE={$engine};

#
# Table structure for table `custom_fields`
#

CREATE TABLE {$db_prefix}custom_fields (
	id_field SMALLINT AUTO_INCREMENT,
	col_name VARCHAR(12) NOT NULL DEFAULT '',
	field_name VARCHAR(40) NOT NULL DEFAULT '',
	field_desc VARCHAR(255) NOT NULL DEFAULT '',
	field_type VARCHAR(8) NOT NULL DEFAULT 'text',
	field_length SMALLINT(5) NOT NULL DEFAULT '255',
	field_options TEXT NOT NULL,
	field_order TINYINT(3) NOT NULL DEFAULT '0',
	mask VARCHAR(255) NOT NULL DEFAULT '',
	show_reg TINYINT(3) NOT NULL DEFAULT '0',
	show_display TINYINT(3) NOT NULL DEFAULT '0',
	show_mlist TINYINT(3) NOT NULL DEFAULT '0',
	show_profile VARCHAR(20) NOT NULL DEFAULT 'forumprofile',
	private TINYINT(3) NOT NULL DEFAULT '0',
	active TINYINT(3) NOT NULL DEFAULT '1',
	bbc TINYINT(3) NOT NULL DEFAULT '0',
	can_search TINYINT(3) NOT NULL DEFAULT '0',
	DEFAULT_value VARCHAR(255) NOT NULL DEFAULT '',
	enclose text NOT NULL,
  placement TINYINT NOT NULL DEFAULT '0',
  PRIMARY KEY (id_field),
  UNIQUE idx_col_name (col_name)
) ENGINE={$engine};

#
# Table structure for table `group_moderators`
#

CREATE TABLE {$db_prefix}group_moderators (
	id_group SMALLINT(5) UNSIGNED DEFAULT '0',
	id_member MEDIUMINT(8) UNSIGNED DEFAULT '0',
	PRIMARY KEY (id_group, id_member)
) ENGINE={$engine};

#
# Table structure for table `log_actions`
#

CREATE TABLE {$db_prefix}log_actions (
	id_action INT(10) UNSIGNED AUTO_INCREMENT,
	id_log TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
	log_time INT(10) UNSIGNED NOT NULL DEFAULT '0',
	id_member MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	ip VARBINARY(16),
	action VARCHAR(30) NOT NULL DEFAULT '',
	id_board SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	id_topic MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	id_msg INT(10) UNSIGNED NOT NULL DEFAULT '0',
	extra TEXT NOT NULL,
	PRIMARY KEY (id_action),
	INDEX idx_id_log (id_log),
	INDEX idx_log_time (log_time),
	INDEX idx_id_member (id_member),
	INDEX idx_id_board (id_board),
	INDEX idx_id_msg (id_msg),
	INDEX idx_id_topic_id_log (id_topic, id_log)
) ENGINE={$engine};

#
# Table structure for table `log_activity`
#

CREATE TABLE {$db_prefix}log_activity (
	date DATE DEFAULT '0001-01-01',
	hits MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	topics SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	posts SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	registers SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	most_on SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (date)
) ENGINE={$engine};

#
# Table structure for table `log_banned`
#

CREATE TABLE {$db_prefix}log_banned (
	id_ban_log MEDIUMINT(8) UNSIGNED AUTO_INCREMENT,
	id_member MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	ip VARBINARY(16),
	email VARCHAR(255) NOT NULL DEFAULT '',
	log_time INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (id_ban_log),
	INDEX idx_log_time (log_time)
) ENGINE={$engine};

#
# Table structure for table `log_boards`
#

CREATE TABLE {$db_prefix}log_boards (
	id_member MEDIUMINT(8) UNSIGNED DEFAULT '0',
	id_board SMALLINT(5) UNSIGNED DEFAULT '0',
	id_msg INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (id_member, id_board)
) ENGINE={$engine};

#
# Table structure for table `log_comments`
#

CREATE TABLE {$db_prefix}log_comments (
	id_comment MEDIUMINT(8) UNSIGNED AUTO_INCREMENT,
	id_member MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	member_name VARCHAR(80) NOT NULL DEFAULT '',
	comment_type VARCHAR(8) NOT NULL DEFAULT 'warning',
	id_recipient MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	recipient_name VARCHAR(255) NOT NULL DEFAULT '',
	log_time INT(10) NOT NULL DEFAULT '0',
	id_notice MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	counter TINYINT(3) NOT NULL DEFAULT '0',
	body TEXT NOT NULL,
	PRIMARY KEY (id_comment),
	INDEX idx_id_recipient (id_recipient),
	INDEX idx_log_time (log_time),
	INDEX idx_comment_type (comment_type(8))
) ENGINE={$engine};

#
# Table structure for table `log_digest`
#

CREATE TABLE {$db_prefix}log_digest (
	id_topic MEDIUMINT(8) UNSIGNED NOT NULL,
	id_msg INT(10) UNSIGNED NOT NULL,
	note_type VARCHAR(10) NOT NULL DEFAULT 'post',
	daily TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	exclude MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE={$engine};

#
# Table structure for table `log_errors`
#

CREATE TABLE {$db_prefix}log_errors (
	id_error MEDIUMINT(8) UNSIGNED AUTO_INCREMENT,
	log_time INT(10) UNSIGNED NOT NULL DEFAULT '0',
	id_member MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	ip VARBINARY(16),
	url TEXT NOT NULL,
	message TEXT NOT NULL,
	session CHAR(64) NOT NULL DEFAULT '                                                                ',
	error_type CHAR(15) NOT NULL DEFAULT 'general',
	file VARCHAR(255) NOT NULL DEFAULT '',
	line MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (id_error),
	INDEX idx_log_time (log_time),
	INDEX idx_id_member (id_member),
	INDEX idx_ip (ip)
) ENGINE={$engine};

#
# Table structure for table `log_floodcontrol`
#

CREATE TABLE {$db_prefix}log_floodcontrol (
	ip VARBINARY(16),
	log_time INT(10) UNSIGNED NOT NULL DEFAULT '0',
	log_type VARCHAR(8) DEFAULT 'post',
	PRIMARY KEY (ip, log_type(8))
) ENGINE={$memory};

#
# Table structure for table `log_group_requests`
#

CREATE TABLE {$db_prefix}log_group_requests (
	id_request MEDIUMINT(8) UNSIGNED AUTO_INCREMENT,
	id_member MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	id_group SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	time_applied INT(10) UNSIGNED NOT NULL DEFAULT '0',
	reason TEXT NOT NULL,
	status TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	id_member_acted MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	member_name_acted VARCHAR(255) NOT NULL DEFAULT '',
	time_acted INT(10) UNSIGNED NOT NULL DEFAULT '0',
	act_reason TEXT NOT NULL,
	PRIMARY KEY (id_request),
	INDEX idx_id_member (id_member, id_group)
) ENGINE={$engine};

#
# Table structure for table `log_mark_read`
#

CREATE TABLE {$db_prefix}log_mark_read (
	id_member MEDIUMINT(8) UNSIGNED DEFAULT '0',
	id_board SMALLINT(5) UNSIGNED DEFAULT '0',
	id_msg INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (id_member, id_board)
) ENGINE={$engine};

#
# Table structure for table `log_member_notices`
#

CREATE TABLE {$db_prefix}log_member_notices (
	id_notice MEDIUMINT(8) UNSIGNED AUTO_INCREMENT,
	subject VARCHAR(255) NOT NULL DEFAULT '',
	body TEXT NOT NULL,
	PRIMARY KEY (id_notice)
) ENGINE={$engine};

#
# Table structure for table `log_notify`
#

CREATE TABLE {$db_prefix}log_notify (
	id_member MEDIUMINT(8) UNSIGNED DEFAULT '0',
	id_topic MEDIUMINT(8) UNSIGNED DEFAULT '0',
	id_board SMALLINT(5) UNSIGNED DEFAULT '0',
	sent TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (id_member, id_topic, id_board),
	INDEX idx_id_topic (id_topic, id_member)
) ENGINE={$engine};

#
# Table structure for table `log_online`
#

CREATE TABLE {$db_prefix}log_online (
	session VARCHAR(64) DEFAULT '',
	log_time INT(10) NOT NULL DEFAULT '0',
	id_member MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	id_spider SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	ip VARBINARY(16),
	url VARCHAR(1024) NOT NULL,
	PRIMARY KEY (session),
	INDEX idx_log_time (log_time),
	INDEX idx_id_member (id_member)
) ENGINE={$memory};

#
# Table structure for table `log_packages`
#

CREATE TABLE {$db_prefix}log_packages (
	id_install INT(10) AUTO_INCREMENT,
	filename VARCHAR(255) NOT NULL DEFAULT '',
	package_id VARCHAR(255) NOT NULL DEFAULT '',
	name VARCHAR(255) NOT NULL DEFAULT '',
	version VARCHAR(255) NOT NULL DEFAULT '',
	id_member_installed MEDIUMINT(8) NOT NULL DEFAULT '0',
	member_installed VARCHAR(255) NOT NULL DEFAULT '',
	time_installed INT(10) NOT NULL DEFAULT '0',
	id_member_removed MEDIUMINT(8) NOT NULL DEFAULT '0',
	member_removed VARCHAR(255) NOT NULL DEFAULT '',
	time_removed INT(10) NOT NULL DEFAULT '0',
	install_state TINYINT(3) NOT NULL DEFAULT '1',
	failed_steps TEXT NOT NULL,
	themes_installed VARCHAR(255) NOT NULL DEFAULT '',
	db_changes TEXT NOT NULL,
	credits VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (id_install),
	INDEX idx_filename (filename(15))
) ENGINE={$engine};

#
# Table structure for table `log_polls`
#

CREATE TABLE {$db_prefix}log_polls (
	id_poll MEDIUMINT(8) UNSIGNED DEFAULT '0',
	id_member MEDIUMINT(8) UNSIGNED DEFAULT '0',
	id_choice TINYINT(3) UNSIGNED DEFAULT '0',
	INDEX idx_id_poll (id_poll, id_member, id_choice)
) ENGINE={$engine};

#
# Table structure for table `log_reported`
#

CREATE TABLE {$db_prefix}log_reported (
	id_report MEDIUMINT(8) UNSIGNED AUTO_INCREMENT,
	id_msg INT(10) UNSIGNED NOT NULL DEFAULT '0',
	id_topic MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	id_board SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	id_member MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	membername VARCHAR(255) NOT NULL DEFAULT '',
	subject VARCHAR(255) NOT NULL DEFAULT '',
	body MEDIUMTEXT NOT NULL,
	time_started INT(10) NOT NULL DEFAULT '0',
	time_updated INT(10) NOT NULL DEFAULT '0',
	num_reports MEDIUMINT(6) NOT NULL DEFAULT '0',
	closed TINYINT(3) NOT NULL DEFAULT '0',
	ignore_all TINYINT(3) NOT NULL DEFAULT '0',
	PRIMARY KEY (id_report),
	INDEX idx_id_member (id_member),
	INDEX idx_id_topic (id_topic),
	INDEX idx_closed (closed),
	INDEX idx_time_started (time_started),
	INDEX idx_id_msg (id_msg)
) ENGINE={$engine};

#
# Table structure for table `log_reported_comments`
#

CREATE TABLE {$db_prefix}log_reported_comments (
	id_comment MEDIUMINT(8) UNSIGNED AUTO_INCREMENT,
	id_report MEDIUMINT(8) NOT NULL DEFAULT '0',
	id_member MEDIUMINT(8) NOT NULL,
	membername VARCHAR(255) NOT NULL DEFAULT '',
	member_ip VARBINARY(16),
	comment VARCHAR(255) NOT NULL DEFAULT '',
	time_sent INT(10) NOT NULL,
	PRIMARY KEY (id_comment),
	INDEX idx_id_report (id_report),
	INDEX idx_id_member (id_member),
	INDEX idx_time_sent (time_sent)
) ENGINE={$engine};

#
# Table structure for table `log_scheduled_tasks`
#

CREATE TABLE {$db_prefix}log_scheduled_tasks (
	id_log MEDIUMINT(8) AUTO_INCREMENT,
	id_task SMALLINT(5) NOT NULL DEFAULT '0',
	time_run INT(10) NOT NULL DEFAULT '0',
	time_taken float NOT NULL DEFAULT '0',
	PRIMARY KEY (id_log)
) ENGINE={$engine};

#
# Table structure for table `log_search_messages`
#

CREATE TABLE {$db_prefix}log_search_messages (
	id_search TINYINT(3) UNSIGNED DEFAULT '0',
	id_msg INT(10) UNSIGNED DEFAULT '0',
	PRIMARY KEY (id_search, id_msg)
) ENGINE={$engine};

#
# Table structure for table `log_search_results`
#

CREATE TABLE {$db_prefix}log_search_results (
	id_search TINYINT(3) UNSIGNED DEFAULT '0',
	id_topic MEDIUMINT(8) UNSIGNED DEFAULT '0',
	id_msg INT(10) UNSIGNED NOT NULL DEFAULT '0',
	relevance SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	num_matches SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (id_search, id_topic)
) ENGINE={$engine};

#
# Table structure for table `log_search_subjects`
#

CREATE TABLE {$db_prefix}log_search_subjects (
	word VARCHAR(20) DEFAULT '',
	id_topic MEDIUMINT(8) UNSIGNED DEFAULT '0',
	PRIMARY KEY (word, id_topic),
	INDEX idx_id_topic (id_topic)
) ENGINE={$engine};

#
# Table structure for table `log_search_topics`
#

CREATE TABLE {$db_prefix}log_search_topics (
	id_search TINYINT(3) UNSIGNED DEFAULT '0',
	id_topic MEDIUMINT(8) UNSIGNED DEFAULT '0',
	PRIMARY KEY (id_search, id_topic)
) ENGINE={$engine};

#
# Table structure for table `log_spider_hits`
#

CREATE TABLE {$db_prefix}log_spider_hits (
	id_hit INT(10) UNSIGNED AUTO_INCREMENT,
	id_spider SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	log_time INT(10) UNSIGNED NOT NULL DEFAULT '0',
	url VARCHAR(1024) NOT NULL DEFAULT '',
	processed TINYINT(3) NOT NULL DEFAULT '0',
	PRIMARY KEY (id_hit),
	INDEX idx_id_spider(id_spider),
	INDEX idx_log_time(log_time),
	INDEX idx_processed (processed)
) ENGINE={$engine};

#
# Table structure for table `log_spider_stats`
#

CREATE TABLE {$db_prefix}log_spider_stats (
	id_spider SMALLINT(5) UNSIGNED DEFAULT '0',
	page_hits SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	last_seen INT(10) UNSIGNED NOT NULL DEFAULT '0',
	stat_date DATE DEFAULT '0001-01-01',
	PRIMARY KEY (stat_date, id_spider)
) ENGINE={$engine};

#
# Table structure for table `log_subscribed`
#

CREATE TABLE {$db_prefix}log_subscribed (
	id_sublog INT(10) UNSIGNED AUTO_INCREMENT,
	id_subscribe MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	id_member INT(10) NOT NULL DEFAULT '0',
	old_id_group SMALLINT(5) NOT NULL DEFAULT '0',
	start_time INT(10) NOT NULL DEFAULT '0',
	end_time INT(10) NOT NULL DEFAULT '0',
	status TINYINT(3) NOT NULL DEFAULT '0',
	payments_pending TINYINT(3) NOT NULL DEFAULT '0',
	pending_details TEXT NOT NULL,
	reminder_sent TINYINT(3) NOT NULL DEFAULT '0',
	vendor_ref VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (id_sublog),
	UNIQUE KEY id_subscribe (id_subscribe, id_member),
	INDEX idx_end_time (end_time),
	INDEX idx_reminder_sent (reminder_sent),
	INDEX idx_payments_pending (payments_pending),
	INDEX idx_status (status),
	INDEX idx_id_member (id_member)
) ENGINE={$engine};

#
# Table structure for table `log_topics`
#

CREATE TABLE {$db_prefix}log_topics (
	id_member MEDIUMINT(8) UNSIGNED DEFAULT '0',
	id_topic MEDIUMINT(8) UNSIGNED DEFAULT '0',
	id_msg INT(10) UNSIGNED NOT NULL DEFAULT '0',
	unwatched TINYINT(3) NOT NULL DEFAULT '0',
	PRIMARY KEY (id_member, id_topic),
	INDEX idx_id_topic (id_topic)
) ENGINE={$engine};

#
# Table structure for table `mail_queue`
#

CREATE TABLE {$db_prefix}mail_queue (
	id_mail INT(10) UNSIGNED AUTO_INCREMENT,
	time_sent INT(10) NOT NULL DEFAULT '0',
	recipient VARCHAR(255) NOT NULL DEFAULT '',
	body MEDIUMTEXT NOT NULL,
	subject VARCHAR(255) NOT NULL DEFAULT '',
	headers TEXT NOT NULL,
	send_html TINYINT(3) NOT NULL DEFAULT '0',
	priority TINYINT(3) NOT NULL DEFAULT '1',
	private TINYINT(1) NOT NULL DEFAULT '0',
	PRIMARY KEY  (id_mail),
	INDEX idx_time_sent (time_sent),
	INDEX idx_mail_priority (priority, id_mail)
) ENGINE={$engine};

#
# Table structure for table `membergroups`
#

CREATE TABLE {$db_prefix}membergroups (
	id_group SMALLINT(5) UNSIGNED AUTO_INCREMENT,
	group_name VARCHAR(80) NOT NULL DEFAULT '',
	description TEXT NOT NULL,
	online_color VARCHAR(20) NOT NULL DEFAULT '',
	min_posts MEDIUMINT(9) NOT NULL DEFAULT '-1',
	max_messages SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	icons VARCHAR(255) NOT NULL DEFAULT '',
	group_type TINYINT(3) NOT NULL DEFAULT '0',
	hidden TINYINT(3) NOT NULL DEFAULT '0',
	id_parent SMALLINT(5) NOT NULL DEFAULT '-2',
	tfa_required TINYINT(3) NOT NULL DEFAULT '0',
	PRIMARY KEY (id_group),
	INDEX idx_min_posts (min_posts)
) ENGINE={$engine};

#
# Table structure for table `members`
#

CREATE TABLE {$db_prefix}members (
	id_member MEDIUMINT(8) UNSIGNED AUTO_INCREMENT,
	member_name VARCHAR(80) NOT NULL DEFAULT '',
	date_registered INT(10) UNSIGNED NOT NULL DEFAULT '0',
	posts MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	id_group SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	lngfile VARCHAR(255) NOT NULL DEFAULT '',
	last_login INT(10) UNSIGNED NOT NULL DEFAULT '0',
	real_name VARCHAR(255) NOT NULL DEFAULT '',
	instant_messages SMALLINT(5) NOT NULL DEFAULT 0,
	unread_messages SMALLINT(5) NOT NULL DEFAULT 0,
	new_pm TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	alerts INT(10) UNSIGNED NOT NULL DEFAULT '0',
	buddy_list TEXT NOT NULL,
	pm_ignore_list VARCHAR(255) NOT NULL DEFAULT '',
	pm_prefs MEDIUMINT(8) NOT NULL DEFAULT '0',
	mod_prefs VARCHAR(20) NOT NULL DEFAULT '',
	passwd VARCHAR(64) NOT NULL DEFAULT '',
	email_address VARCHAR(255) NOT NULL DEFAULT '',
	personal_text VARCHAR(255) NOT NULL DEFAULT '',
	birthdate date NOT NULL DEFAULT '0001-01-01',
	website_title VARCHAR(255) NOT NULL DEFAULT '',
	website_url VARCHAR(255) NOT NULL DEFAULT '',
	hide_email TINYINT(4) NOT NULL DEFAULT '0',
	show_online TINYINT(4) NOT NULL DEFAULT '1',
	signature TEXT NOT NULL,
	time_offset float NOT NULL DEFAULT '0',
	avatar VARCHAR(255) NOT NULL DEFAULT '',
	usertitle VARCHAR(255) NOT NULL DEFAULT '',
	member_ip VARBINARY(16),
	member_ip2 VARBINARY(16),
	secret_question VARCHAR(255) NOT NULL DEFAULT '',
	secret_answer VARCHAR(64) NOT NULL DEFAULT '',
	id_theme TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
	is_activated TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
	validation_code VARCHAR(10) NOT NULL DEFAULT '',
	id_msg_last_visit INT(10) UNSIGNED NOT NULL DEFAULT '0',
	additional_groups VARCHAR(255) NOT NULL DEFAULT '',
	smiley_set VARCHAR(48) NOT NULL DEFAULT '',
	id_post_group SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	total_time_logged_in INT(10) UNSIGNED NOT NULL DEFAULT '0',
	password_salt VARCHAR(255) NOT NULL DEFAULT '',
	ignore_boards TEXT NOT NULL,
	warning TINYINT(4) NOT NULL DEFAULT '0',
	passwd_flood VARCHAR(12) NOT NULL DEFAULT '',
	pm_receive_from TINYINT(4) UNSIGNED NOT NULL DEFAULT '1',
	timezone VARCHAR(80) NOT NULL DEFAULT 'UTC',
	location VARCHAR(80) NOT NULL DEFAULT '',
	gender TINYINT(2) NOT NULL DEFAULT '0',
	tmdisplay TINYINT(2) NOT NULL DEFAULT '-1',
	tfa_secret VARCHAR(24) NOT NULL DEFAULT '',
	tfa_backup VARCHAR(64) NOT NULL DEFAULT '',
	gdpr_date INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (id_member),
	INDEX idx_member_name (member_name),
	INDEX idx_real_name (real_name),
	INDEX idx_email_address (email_address),
	INDEX idx_date_registered (date_registered),
	INDEX idx_id_group (id_group),
	INDEX idx_birthdate (birthdate),
	INDEX idx_posts (posts),
	INDEX idx_last_login (last_login),
	INDEX idx_lngfile (lngfile(30)),
	INDEX idx_id_post_group (id_post_group),
	INDEX idx_warning (warning),
	INDEX idx_total_time_logged_in (total_time_logged_in),
	INDEX idx_id_theme (id_theme)
) ENGINE={$engine};

#
# Table structure for table `member_logins`
#

CREATE TABLE {$db_prefix}member_logins (
	id_login INT(10) AUTO_INCREMENT,
	id_member MEDIUMINT(8) NOT NULL DEFAULT '0',
	time INT(10) NOT NULL DEFAULT '0',
	ip VARBINARY(16),
	ip2 VARBINARY(16),
	PRIMARY KEY (id_login),
	INDEX idx_id_member (id_member),
	INDEX idx_time (time)
) ENGINE={$engine};

#
# Table structure for table `message_icons`
#

CREATE TABLE {$db_prefix}message_icons (
	id_icon SMALLINT(5) UNSIGNED AUTO_INCREMENT,
	title VARCHAR(80) NOT NULL DEFAULT '',
	filename VARCHAR(80) NOT NULL DEFAULT '',
	id_board SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	icon_order SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (id_icon),
	INDEX idx_id_board (id_board)
) ENGINE={$engine};

#
# Table structure for table `messages`
#

CREATE TABLE {$db_prefix}messages (
	id_msg INT(10) UNSIGNED AUTO_INCREMENT,
	id_topic MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	id_board SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	poster_time INT(10) UNSIGNED NOT NULL DEFAULT '0',
	id_member MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	id_msg_modified INT(10) UNSIGNED NOT NULL DEFAULT '0',
	subject VARCHAR(255) NOT NULL DEFAULT '',
	poster_name VARCHAR(255) NOT NULL DEFAULT '',
	poster_email VARCHAR(255) NOT NULL DEFAULT '',
	poster_ip VARBINARY(16),
	smileys_enabled TINYINT(4) NOT NULL DEFAULT '1',
	modified_time INT(10) UNSIGNED NOT NULL DEFAULT '0',
	modified_name VARCHAR(255) NOT NULL DEFAULT '',
	modified_reason VARCHAR(255) NOT NULL DEFAULT '',
	body TEXT NOT NULL,
	icon VARCHAR(16) NOT NULL DEFAULT 'xx',
	approved TINYINT(3) NOT NULL DEFAULT '1',
	likes SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (id_msg),
	UNIQUE idx_id_board (id_board, id_msg),
	UNIQUE idx_id_member (id_member, id_msg),
	INDEX idx_approved (approved),
	INDEX idx_ip_index (poster_ip, id_topic),
	INDEX idx_participation (id_member, id_topic),
	INDEX idx_show_posts (id_member, id_board),
	INDEX idx_id_member_msg (id_member, approved, id_msg),
	INDEX idx_current_topic (id_topic, id_msg, id_member, approved),
	INDEX idx_related_ip (id_member, poster_ip, id_msg)
) ENGINE={$engine};

#
# Table structure for table `moderators`
#

CREATE TABLE {$db_prefix}moderators (
	id_board SMALLINT(5) UNSIGNED DEFAULT '0',
	id_member MEDIUMINT(8) UNSIGNED DEFAULT '0',
	PRIMARY KEY (id_board, id_member)
) ENGINE={$engine};

#
# Table structure for table `moderator_groups`
#

CREATE TABLE {$db_prefix}moderator_groups (
	id_board SMALLINT(5) UNSIGNED DEFAULT '0',
	id_group SMALLINT(5) UNSIGNED DEFAULT '0',
	PRIMARY KEY (id_board, id_group)
) ENGINE={$engine};

#
# Table structure for table `package_servers`
#

CREATE TABLE {$db_prefix}package_servers (
	id_server SMALLINT(5) UNSIGNED AUTO_INCREMENT,
	name VARCHAR(255) NOT NULL DEFAULT '',
	url VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (id_server)
) ENGINE={$engine};

#
# Table structure for table `permission_profiles`
#

CREATE TABLE {$db_prefix}permission_profiles (
	id_profile SMALLINT(5) AUTO_INCREMENT,
	profile_name VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (id_profile)
) ENGINE={$engine};

#
# Table structure for table `permissions`
#

CREATE TABLE {$db_prefix}permissions (
	id_group SMALLINT(5) DEFAULT '0',
	permission VARCHAR(30) DEFAULT '',
	add_deny TINYINT(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (id_group, permission)
) ENGINE={$engine};

#
# Table structure for table `personal_messages`
#

CREATE TABLE {$db_prefix}personal_messages (
	id_pm INT(10) UNSIGNED AUTO_INCREMENT,
	id_pm_head INT(10) UNSIGNED NOT NULL DEFAULT '0',
	id_member_from MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	deleted_by_sender TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	from_name VARCHAR(255) NOT NULL DEFAULT '',
	msgtime INT(10) UNSIGNED NOT NULL DEFAULT '0',
	subject VARCHAR(255) NOT NULL DEFAULT '',
	body TEXT NOT NULL,
	PRIMARY KEY (id_pm),
	INDEX idx_id_member (id_member_from, deleted_by_sender),
	INDEX idx_msgtime (msgtime),
	INDEX idx_id_pm_head (id_pm_head)
) ENGINE={$engine};

#
# Table structure for table `pm_labels`
#

CREATE TABLE {$db_prefix}pm_labels (
	id_label INT(10) UNSIGNED AUTO_INCREMENT,
	id_member MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	name VARCHAR(30) NOT NULL DEFAULT '',
	PRIMARY KEY (id_label)
) ENGINE={$engine};

#
# Table structure for table `pm_labeled_messages`
#

CREATE TABLE {$db_prefix}pm_labeled_messages (
	id_label INT(10) UNSIGNED DEFAULT '0',
	id_pm INT(10) UNSIGNED DEFAULT '0',
	PRIMARY KEY (id_label, id_pm)
) ENGINE={$engine};

#
# Table structure for table `pm_recipients`
#

CREATE TABLE {$db_prefix}pm_recipients (
	id_pm INT(10) UNSIGNED DEFAULT '0',
	id_member MEDIUMINT(8) UNSIGNED DEFAULT '0',
	bcc TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	is_read TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	is_new TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	deleted TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	in_inbox TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
	PRIMARY KEY (id_pm, id_member),
	UNIQUE idx_id_member (id_member, deleted, id_pm)
) ENGINE={$engine};

#
# Table structure for table `pm_rules`
#

CREATE TABLE {$db_prefix}pm_rules (
	id_rule INT(10) UNSIGNED AUTO_INCREMENT,
	id_member INT(10) UNSIGNED NOT NULL DEFAULT '0',
	rule_name VARCHAR(60) NOT NULL,
	criteria TEXT NOT NULL,
	actions TEXT NOT NULL,
	delete_pm TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	is_or TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (id_rule),
	INDEX idx_id_member (id_member),
	INDEX idx_delete_pm (delete_pm)
) ENGINE={$engine};

#
# Table structure for table `polls`
#

CREATE TABLE {$db_prefix}polls (
	id_poll MEDIUMINT(8) UNSIGNED AUTO_INCREMENT,
	question VARCHAR(255) NOT NULL DEFAULT '',
	voting_locked TINYINT(1) NOT NULL DEFAULT '0',
	max_votes TINYINT(3) UNSIGNED NOT NULL DEFAULT '1',
	expire_time INT(10) UNSIGNED NOT NULL DEFAULT '0',
	hide_results TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	change_vote TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	guest_vote TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
	num_guest_voters INT(10) UNSIGNED NOT NULL DEFAULT '0',
	reset_poll INT(10) UNSIGNED NOT NULL DEFAULT '0',
	id_member MEDIUMINT(8) NOT NULL DEFAULT '0',
	poster_name VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (id_poll)
) ENGINE={$engine};

#
# Table structure for table `poll_choices`
#

CREATE TABLE {$db_prefix}poll_choices (
	id_poll MEDIUMINT(8) UNSIGNED DEFAULT '0',
	id_choice TINYINT(3) UNSIGNED DEFAULT '0',
	label VARCHAR(255) NOT NULL DEFAULT '',
	votes SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (id_poll, id_choice)
) ENGINE={$engine};

#
# Table structure for table `qanda`
#

CREATE TABLE {$db_prefix}qanda (
	id_question SMALLINT(5) UNSIGNED AUTO_INCREMENT,
	lngfile VARCHAR(255) NOT NULL DEFAULT '',
	question VARCHAR(255) NOT NULL DEFAULT '',
	answers TEXT NOT NULL,
	PRIMARY KEY (id_question),
	INDEX idx_lngfile (lngfile)
) ENGINE={$engine};

#
# Table structure for table `scheduled_tasks`
#

CREATE TABLE {$db_prefix}scheduled_tasks (
	id_task SMALLINT(5) AUTO_INCREMENT,
	next_time INT(10) NOT NULL DEFAULT '0',
	time_offset INT(10) NOT NULL DEFAULT '0',
	time_regularity SMALLINT(5) NOT NULL DEFAULT '0',
	time_unit VARCHAR(1) NOT NULL DEFAULT 'h',
	disabled TINYINT(3) NOT NULL DEFAULT '0',
	task VARCHAR(24) NOT NULL DEFAULT '',
	callable VARCHAR(60) NOT NULL DEFAULT '',
	PRIMARY KEY (id_task),
	INDEX idx_next_time (next_time),
	INDEX idx_disabled (disabled),
	UNIQUE idx_task (task)
) ENGINE={$engine};

#
# Table structure for table `settings`
#

CREATE TABLE {$db_prefix}settings (
	variable VARCHAR(255) DEFAULT '',
	value TEXT NOT NULL,
	PRIMARY KEY (variable(30))
) ENGINE={$engine};

#
# Table structure for table `sessions`
#

CREATE TABLE {$db_prefix}sessions (
	session_id CHAR(64),
	last_update INT(10) UNSIGNED NOT NULL,
	data TEXT NOT NULL,
	PRIMARY KEY (session_id)
) ENGINE={$engine};

#
# Table structure for table `smileys`
#

CREATE TABLE {$db_prefix}smileys (
	id_smiley SMALLINT(5) UNSIGNED AUTO_INCREMENT,
	code VARCHAR(30) NOT NULL DEFAULT '',
	filename VARCHAR(48) NOT NULL DEFAULT '',
	description VARCHAR(80) NOT NULL DEFAULT '',
	smiley_row TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
	smiley_order SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	hidden TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (id_smiley)
) ENGINE={$engine};

#
# Table structure for table `spiders`
#

CREATE TABLE {$db_prefix}spiders (
	id_spider SMALLINT(5) UNSIGNED AUTO_INCREMENT,
	spider_name VARCHAR(255) NOT NULL DEFAULT '',
	user_agent VARCHAR(255) NOT NULL DEFAULT '',
	ip_info VARCHAR(255) NOT NULL DEFAULT '',
	forbidden TINYINT(4) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY id_spider(id_spider)
) ENGINE={$engine};

#
# Table structure for table `subscriptions`
#

CREATE TABLE {$db_prefix}subscriptions(
	id_subscribe MEDIUMINT(8) UNSIGNED AUTO_INCREMENT,
	name VARCHAR(60) NOT NULL DEFAULT '',
	description VARCHAR(255) NOT NULL DEFAULT '',
	cost TEXT NOT NULL,
	length VARCHAR(6) NOT NULL DEFAULT '',
	id_group SMALLINT(5) NOT NULL DEFAULT '0',
	add_groups VARCHAR(40) NOT NULL DEFAULT '',
	active TINYINT(3) NOT NULL DEFAULT '1',
	repeatable TINYINT(3) NOT NULL DEFAULT '0',
	allow_partial TINYINT(3) NOT NULL DEFAULT '0',
	reminder TINYINT(3) NOT NULL DEFAULT '0',
	email_complete TEXT NOT NULL,
	PRIMARY KEY (id_subscribe),
	INDEX idx_active (active)
) ENGINE={$engine};

#
# Table structure for table `themes`
#

CREATE TABLE {$db_prefix}themes (
	id_member MEDIUMINT(8) DEFAULT '0',
	id_theme TINYINT(4) UNSIGNED DEFAULT '1',
	variable VARCHAR(255) DEFAULT '',
	value TEXT NOT NULL,
	PRIMARY KEY (id_theme, id_member, variable(30)),
	INDEX idx_id_member (id_member)
) ENGINE={$engine};

#
# Table structure for table `topics`
#

CREATE TABLE {$db_prefix}topics (
	id_topic MEDIUMINT(8) UNSIGNED AUTO_INCREMENT,
	is_sticky TINYINT(4) NOT NULL DEFAULT '0',
	id_board SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	id_first_msg INT(10) UNSIGNED NOT NULL DEFAULT '0',
	id_last_msg INT(10) UNSIGNED NOT NULL DEFAULT '0',
	id_member_started MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	id_member_updated MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	id_poll MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	id_previous_board SMALLINT(5) NOT NULL DEFAULT '0',
	id_previous_topic MEDIUMINT(8) NOT NULL DEFAULT '0',
	num_replies INT(10) UNSIGNED NOT NULL DEFAULT '0',
	num_views INT(10) UNSIGNED NOT NULL DEFAULT '0',
	locked TINYINT(4) NOT NULL DEFAULT '0',
	redirect_expires INT(10) UNSIGNED NOT NULL DEFAULT '0',
	id_redirect_topic MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	unapproved_posts SMALLINT(5) NOT NULL DEFAULT '0',
	approved TINYINT(3) NOT NULL DEFAULT '1',
	PRIMARY KEY (id_topic),
	UNIQUE idx_last_message (id_last_msg, id_board),
	UNIQUE idx_first_message (id_first_msg, id_board),
	UNIQUE idx_poll (id_poll, id_topic),
	INDEX idx_is_sticky (is_sticky),
	INDEX idx_approved (approved),
	INDEX idx_member_started (id_member_started, id_board),
	INDEX idx_last_message_sticky (id_board, is_sticky, id_last_msg),
	INDEX idx_board_news (id_board, id_first_msg)
) ENGINE={$engine};

#
# Table structure for table `user_alerts`
#

CREATE TABLE {$db_prefix}user_alerts (
	id_alert INT(10) UNSIGNED AUTO_INCREMENT,
	alert_time INT(10) UNSIGNED NOT NULL DEFAULT '0',
	id_member MEDIUMINT(10) UNSIGNED NOT NULL DEFAULT '0',
	id_member_started MEDIUMINT(10) UNSIGNED NOT NULL DEFAULT '0',
	member_name VARCHAR(255) NOT NULL DEFAULT '',
	content_type VARCHAR(255) NOT NULL DEFAULT '',
	content_id INT(10) UNSIGNED NOT NULL DEFAULT '0',
	content_action VARCHAR(255) NOT NULL DEFAULT '',
	is_read INT(10) UNSIGNED NOT NULL DEFAULT '0',
	extra TEXT NOT NULL,
	PRIMARY KEY (id_alert),
	INDEX idx_id_member (id_member),
	INDEX idx_alert_time (alert_time)
) ENGINE={$engine};

#
# Table structure for table `user_alerts_prefs`
#

CREATE TABLE {$db_prefix}user_alerts_prefs (
	id_member MEDIUMINT(8) UNSIGNED DEFAULT '0',
	alert_pref VARCHAR(32) DEFAULT '',
	alert_value TINYINT(3) NOT NULL DEFAULT '0',
	PRIMARY KEY (id_member, alert_pref)
) ENGINE={$engine};

#
# Table structure for table `user_drafts`
#

CREATE TABLE {$db_prefix}user_drafts (
	id_draft INT(10) UNSIGNED AUTO_INCREMENT,
	id_topic MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	id_board SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
	id_reply INT(10) UNSIGNED NOT NULL DEFAULT '0',
	type TINYINT(4) NOT NULL DEFAULT '0',
	poster_time INT(10) UNSIGNED NOT NULL DEFAULT '0',
	id_member MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
	subject VARCHAR(255) NOT NULL DEFAULT '',
	smileys_enabled TINYINT(4) NOT NULL DEFAULT '1',
	body MEDIUMTEXT NOT NULL,
	icon VARCHAR(16) NOT NULL DEFAULT 'xx',
	locked TINYINT(4) NOT NULL DEFAULT '0',
	is_sticky TINYINT(4) NOT NULL DEFAULT '0',
	to_list VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (id_draft),
	UNIQUE idx_id_member (id_member, id_draft, type)
) ENGINE={$engine};

#
# Table structure for table `user_likes`
#

CREATE TABLE {$db_prefix}user_likes (
	id_member MEDIUMINT(8) UNSIGNED DEFAULT '0',
	content_type CHAR(6) DEFAULT '',
	content_id INT(10) UNSIGNED DEFAULT '0',
	like_time INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (content_id, content_type, id_member),
	INDEX content (content_id, content_type),
	INDEX liker (id_member)
) ENGINE={$engine};

#
# Table structure for table `mentions`
#

CREATE TABLE {$db_prefix}mentions (
	content_id INT DEFAULT '0',
	content_type VARCHAR(10) DEFAULT '',
	id_mentioned INT DEFAULT 0,
	id_member INT NOT NULL DEFAULT 0,
	`time` INT NOT NULL DEFAULT 0,
	PRIMARY KEY (content_id, content_type, id_mentioned),
	INDEX content (content_id, content_type),
	INDEX mentionee (id_member)
) ENGINE={$engine};

#
# Table structure for table `portal_settings`
#

CREATE TABLE {$db_prefix}portal_settings (
	varname VARCHAR(80) NOT NULL DEFAULT '',
	config TEXT NOT NULL,
	PRIMARY KEY (varname),
	UNIQUE uidx (varname)
) ENGINE={$engine};

#
# Table structure for table `portal_blocks`
#

CREATE TABLE {$db_prefix}portal_blocks (
	id INT(10) UNSIGNED AUTO_INCREMENT,
	side VARCHAR(10) NOT NULL DEFAULT '',
	pos SMALLINT(6) UNSIGNED NOT NULL DEFAULT 0,
	active SMALLINT(6) UNSIGNED NOT NULL DEFAULT 0,
	cache SMALLINT(6) UNSIGNED NOT NULL DEFAULT 0,
	blocktype VARCHAR(30) NOT NULL DEFAULT '',
	acsgrp VARCHAR(200) NOT NULL DEFAULT '',
	config TEXT NOT NULL DEFAULT '',
	content TEXT NOT NULL DEFAULT '',
	PRIMARY KEY (id),
	INDEX sidepos (side, pos),
	INDEX blocktype (blocktype)
) ENGINE={$engine};

#
# Table structure for table `portal_categories`
#

CREATE TABLE {$db_prefix}portal_categories (
	id INT(10) UNSIGNED NOT NULL,
	name VARCHAR(80) NOT NULL DEFAULT '',
	parent INT UNSIGNED NOT NULL DEFAULT 0,
	level SMALLINT(6) UNSIGNED NOT NULL DEFAULT 0,
	catorder INT UNSIGNED NOT NULL DEFAULT 0,
	acsgrp VARCHAR(200) NOT NULL DEFAULT '',
	artsort VARCHAR(30) NOT NULL DEFAULT '',
	config TEXT NOT NULL DEFAULT '',
	PRIMARY KEY (id),
	INDEX name (name),
	INDEX catorder (catorder)
) ENGINE={$engine};

#
# Table structure for table `portal_articles`
#

CREATE TABLE {$db_prefix}portal_articles (
	id INT(10) UNSIGNED NOT NULL,
	name VARCHAR(80) NOT NULL DEFAULT '',
	catid INT(10) UNSIGNED NOT NULL DEFAULT 0,
	acsgrp VARCHAR(200) NOT NULL DEFAULT '',
	ctype VARCHAR(10) NOT NULL DEFAULT '',
	active SMALLINT(6) UNSIGNED NOT NULL DEFAULT 0,
	owner INT(10) UNSIGNED NOT NULL DEFAULT 0,
	created INT UNSIGNED NOT NULL DEFAULT 0,
	approved INT UNSIGNED NOT NULL DEFAULT 0,
	approvedby INT UNSIGNED NOT NULL DEFAULT 0,
	updated INT UNSIGNED NOT NULL DEFAULT 0,
	updatedby INT UNSIGNED NOT NULL DEFAULT 0,
	config TEXT NOT NULL DEFAULT '',
	content TEXT NOT NULL DEFAULT '',
	INDEX id (id),
	INDEX name (name),
	INDEX catid (catid),
	INDEX actapp (active, approved)
) ENGINE={$engine};


# Transactions for the win - only used if we have InnoDB available...
START TRANSACTION;

#
# Dumping data for table `portal_settings`
#

INSERT INTO {$db_prefix}portal_settings
	(varname, config)
VALUES
	('areas', 'pmx_center,pmx_settings,pmx_blocks,pmx_categories,pmx_articles'),
	('cache', '{"default":{"settings_time":86400,"acsgroup_time":691200,"trigger":"$ret = false;if(isset($_REQUEST[\\"action\\"])){if($_REQUEST[\\"action\\"] == \\"profile\\" && isset($_REQUEST[\\"area\\"]) && $_REQUEST[\\"area\\"] == \\"showposts\\" && !empty($_REQUEST[\\"delete\\"]))$ret = array(\\"action\\" => $_REQUEST[\\"action\\"]);elseif(in_array($_REQUEST[\\"action\\"], array(\\"markasread\\", \\"post2\\", \\"editpoll2\\", \\"removepoll\\", \\"deletemsg\\", \\"movetopic2\\", \\"removetopic2\\", \\"quickmod\\")))$ret = array(\\"action\\" => $_REQUEST[\\"action\\"]);elseif($_REQUEST[\\"action\\"] == \\"mergetopics\\" && isset($_REQUEST[\\"sa\\"]) && $_REQUEST[\\"sa\\"] == \\"done\\")$ret = array(\\"action\\" => $_REQUEST[\\"action\\"]);elseif($_REQUEST[\\"action\\"] == \\"splittopics\\" && isset($_REQUEST[\\"sa\\"]) && $_REQUEST[\\"sa\\"] == \\"execute\\")$ret = array(\\"action\\" => $_REQUEST[\\"action\\"]);}elseif(isset($_REQUEST[\\"topic\\"])){if(isset($_REQUEST[\\"start\\"]))$ret = array(\\"topic\\" => $_REQUEST[\\"topic\\"], \\"msg\\" => substr($_REQUEST[\\"start\\"], 3));else$ret = array(\\"topic\\" => $_REQUEST[\\"topic\\"]);}return $ret;"},"blocks":{"mini_calendar":{"time":86400,"mode":false,"trigger":""},"article":{"time":86400,"mode":true,"trigger":""},"boardnews":{"mode":true,"time":86400,"trigger":"default"},"boardnewsmult":{"mode":true,"time":86400,"trigger":"default"},"category":{"time":86400,"mode":true,"trigger":""},"cbt_navigator":{"mode":true,"time":86400,"trigger":"default"},"fader":{"time":86400,"mode":false,"trigger":""},"newposts":{"time":86400,"mode":true,"trigger":"default"},"promotedposts":{"time":86400,"mode":true,"trigger":"default"},"polls":{"time":86400,"mode":true,"trigger":"$ret = false;if(isset($_REQUEST[\\"action\\"])){if(in_array($_REQUEST[\\"action\\"], array(\\"vote\\", \\"lockvoting\\", \\"removepoll\\", \\"editpoll2\\", \\"post2\\", \\"deletemsg\\", \\"movetopic2\\", \\"removetopic2\\")))$ret = array(\\"action\\" => $_REQUEST[\\"action\\"]);elseif(in_array($_REQUEST[\\"action\\"], array(\\"mergetopics\\", \\"splittopics\\")) && isset($_REQUEST[\\"sa\\"]) && $_REQUEST[\\"sa\\"] == \\"execute\\")$ret = array(\\"action\\" => $_REQUEST[\\"action\\"]);}return $ret;"},"recent_posts":{"time":86400,"mode":true,"trigger":"default"},"recent_topics":{"time":86400,"mode":true,"trigger":"default"},"rss_reader":{"time":3600,"mode":false,"trigger":""},"shoutbox":{"time":86400,"mode":false,"trigger":"$ret = false;if(isset($_REQUEST[\\"action\\"]) && $_REQUEST[\\"action\\"] == \\"profile\\" && isset($_REQUEST[\\"area\\"]) && $_REQUEST[\\"area\\"] == \\"deleteaccount\\" && isset($_REQUEST[\\"save\\"]))$ret = array(\\"action\\" => $_REQUEST[\\"action\\"]);return $ret;"},"statistics":{"time":300,"mode":false,"trigger":"$ret = false;if(isset($_REQUEST[\\"action\\"]) && in_array($_REQUEST[\\"action\\"] ,array(\\"login2\\", \\"logout\\")))$ret = array(\\"action\\" => $_REQUEST[\\"action\\"]);return $ret;"}}}'),
	('permissions', '{"pmx_promote":[],"pmx_create":[],"pmx_articles":[],"pmx_blocks":[],"pmx_admin":[]}'),
	('promotes', '{}'),
	('registerblocks', 'return array("mini_calendar" => array("description" => $txt["pmx_mini_calendar_description"],"blocktype" => $txt["pmx_sysblock"],"icon" => "calendar"),"article" => array("description" => $txt["pmx_article_description"],"blocktype" => $txt["pmx_sysblock"],"icon" => "article"),"category" => array("description" => $txt["pmx_category_description"],"blocktype" => $txt["pmx_sysblock"],"icon" => "category"),"bbc_script" => array("description" => $txt["pmx_bbc_script_description"],"blocktype" => $txt["pmx_sysblock"],"icon" => "bbc"),"boardnews" => array("description" => $txt["pmx_boardnews_description"],"blocktype" => $txt["pmx_sysblock"],"icon" => "system"),"cbt_navigator" => array("description" => $txt["pmx_cbt_navigator_description"],"blocktype" => $txt["pmx_sysblock"],"icon" => "system"),"download" => array("description" => $txt["pmx_download_description"],"blocktype" => $txt["pmx_sysblock"],"icon" => "system"),"fader" => array("description" => $txt["pmx_fader_description"],"blocktype" => $txt["pmx_sysblock"],"icon" => "system"),"html" => array("description" => $txt["pmx_html_description"],"blocktype" => $txt["pmx_sysblock"],"icon" => "html"),"boardnewsmult" => array("description" => $txt["pmx_boardnewsmult_description"],"blocktype" => $txt["pmx_sysblock"],"icon" => "system"),"newposts" => array("description" => $txt["pmx_newposts_description"],"blocktype" => $txt["pmx_sysblock"],"icon" => "system"),"php" => array("description" => $txt["pmx_php_description"],"blocktype" => $txt["pmx_sysblock"],"icon" => "php"),"promotedposts" => array("description" => $txt["pmx_promotedposts_description"],"blocktype" => $txt["pmx_sysblock"],"icon" => "system"),"polls" => array("description" => $txt["pmx_polls_description"],"blocktype" => $txt["pmx_sysblock"],"icon" => "system"),"recent_posts" => array("description" => $txt["pmx_recent_post_description"],"blocktype" => $txt["pmx_sysblock"],"icon" => "system"),"recent_topics" => array("description" => $txt["pmx_recent_topics_description"],"blocktype" => $txt["pmx_sysblock"],"icon" => "system"),"rss_reader" => array("description" => $txt["pmx_rss_reader_description"],"blocktype" => $txt["pmx_sysblock"],"icon" => "rss"),"script" => array("description" => $txt["pmx_script_description"],"blocktype" => $txt["pmx_sysblock"],"icon" => "code"),"shoutbox" => array("description" => $txt["pmx_shoutbox_description"],"blocktype" => $txt["pmx_sysblock"],"icon" => "system"),"statistics" => array("description" => $txt["pmx_statistics_description"],"blocktype" => $txt["pmx_sysblock"],"icon" => "system"),"user_login" => array("description" => $txt["pmx_user_login_description"],"blocktype" => $txt["pmx_sysblock"],"icon" => "system"));'),
	('settings', '{"download":"0","dl_action":"","other_actions":"","panelpad":"6","restoretop":"0","teasermode":"0","postcountacs":"0","xbarkeys":"1","xbars":["","left"],"xbaroffset_top":"35","xbaroffset_foot":"5","frontpage":"centered","indexfront":"1","hidefrontonpages":"","head_panel":{"size":"0","collapse":"0","overflow":"","custom_hide":"","device":"0"},"manager":{"collape_visibility":"1","follow":"0","qedit":"1","promote":"0","artpage":"10"},"top_panel":{"size":"","collapse":"0","overflow":"","custom_hide":"","device":"0"},"left_panel":{"size":"170","collapse":"0","custom_hide":"","device":"2"},"right_panel":{"size":"170","collapse":"0","custom_hide":"","device":"2"},"bottom_panel":{"size":"0","collapse":"0","collapse_init":"0","overflow":"","custom_hide":"","device":"0"},"foot_panel":{"size":"","collapse":"0","overflow":"","custom_hide":"","device":"0"},"dl_access":"","restorespeed":"350"}');
# --------------------------------------------------------

#
# Dumping data for table `portal_blocks`
#

INSERT INTO {$db_prefix}portal_blocks
	(id, side, pos, active, cache, blocktype, acsgrp, config, content)
VALUES
	(1, 'front', 1, 1, 0, 'html', '-1=1,0=1', '{"title":{"english":"Welcome"},"title_align":"center","title_icon":"page.png","pagename":"","settings":{"teaser":"1","printing":"0","disableHSimg":"0"},"show_sitemap":"0","check_ecl":"0","can_moderate":"0","check_eclbots":"0","collapse":"1","collapse_state":"0","overflow":"","innerpad":"4","visuals":{"header":"catbg notrnd","frame":"pmxborder","body":"windowbg","bodytext":"smalltext"},"cssfile":"","ext_opts":{"pmxcust":"","device":"0"},"maintenance_mode":"0","frontmode":"","frontview":"","frontplace":"hide","created":1475156953}', '
<div style="padding:15px 0pt;text-align:center;font-size:35px;">Welcome to PortaMx-Forum</div>
<div style="text-align: center; padding-bottom: 5px; font-family: Tahoma; font-size: small;">This Frontpage demo give you a inspiration of the PortaMx-Forum functions.</div>
<hr class="pmx_hr" />
<div style="padding-top:4px;font-family:Tahoma;font-size:small;">What is PortaMx-Forum ?
<ul class="list_plus">
	<li>PortaMx-Forum is a free and powerful Forum with a integrated Portal and Search Engine Friendly URL\'s (SEF)</li>
	<li>PortaMX-Forum have full support for mobile devices</li>
	<li>PortaMx-Forum is full compatible with the <strong><a class="bbc_link" href="https://gdpr-info.eu/" target="_blank">General Data Protection Regulation (GDPR)</a></strong></li>
</ul>
The integrated Portal expands the forum with panels (head, top, left, right, bottom, foot) and a frontpage.<br />
In each panel you can have a unlimited number of blocks and you can hide the panels and Blocks on many situations.<br />
Each block have a Admin part for settings, a Load part (called on load) and a View part (called from template), to present his content.<br />
All settings are stored as JSON-Array in the database, so we can add new settings without any change on the database tables.<br />
PortaMx-Forum have a integrated Cache System. This will reduce the database querys and the page is loaded faster.<br />
PortaMx-Forum is full compatible with PHP 7.2 and Opcode precompiling.<br />
<div style="page-break-after: always"><span style="display: none;">&nbsp;</span></div>
<div style="padding-top: 10px;">Default settings for each Portal Block:
<ul class="list_go">
	<li>Titles for all existing languages</li>
	<li>Title icons</li>
	<li>Pagenames (only in Single Pages and Articles)</li>
	<li>Styles from the template or a custom CSS file</li>
	<li>Style settings for header, frame, body and bodytext</li>
	<li>Visibility settings for usergroups</li>
	<li>Dynamic visibility settings, based on actions, boards, languages, pages, categories, articles and device type (Mobile / Desctop)</li>
	<li>Content cache settings</li>
</ul>
A Portal Block can have more settings, this is dependent on block type.
<div style="padding-top: 10px;">Currently available blocktypes:
<ul class="list_red">
	<li>System blocks like Recent posts, Recent topics, User, Statistic, RSS Reader and mutch more</li>
	<li>Html (uses the CKeditor) with php code inside</li>
	<li>Php (with init and content part)</li>
	<li>Script (for html, Javascript) with php code inside</li>
</ul>
</div>
</div>
Please visit the <a class="bbc_link" href="https://www.portamx.com" target="_blank">PortaMx support site</a> to find news and updates.</div>'),
	(2, 'left', 1, 1, 300, 'statistics', '-1=1,0=1', '{"title":{"english":"Statistic"},"title_align":"center","title_icon":"chart_bar.png","pagename":"","settings":{"stat_member":"1","stat_stats":"1","stat_users":"1","stat_spider":"1","stat_olheight":"10"},"show_sitemap":"0","check_ecl":"0","can_moderate":"0","check_eclbots":"0","collapse":"1","collapse_state":"0","overflow":"","innerpad":"4","visuals":{"header":"catbg notrnd","frame":"pmxborder","body":"windowbg","bodytext":"smalltext","stats_text":"normaltext"},"cssfile":"","ext_opts":{"pmxcust":"","device":"0"},"maintenance_mode":"0","frontmode":"","frontview":"","created":1475156995}', '');
# --------------------------------------------------------

#
# Dumping data for table `admin_info_files`
#

INSERT INTO {$db_prefix}admin_info_files
	(id_file, filename, path, parameters, filetype, data)
VALUES
	(1, 'current-version.js', 'infofiles/', '', 'text/javascript', ''),
	(2, 'detailed-version.js', 'infofiles/', '%1$s/', 'text/javascript', ''),
	(3, 'latest-news.js', 'infofiles/', '%1$s/', 'text/javascript', ''),
	(4, 'latest-versions.txt', 'infofiles/', '', 'text/plain', '');
# --------------------------------------------------------

#
# Dumping data for table `board_permissions`
#

INSERT INTO {$db_prefix}board_permissions
	(id_group, id_profile, permission)
VALUES (-1, 1, 'poll_view'),
	(0, 1, 'remove_own'),
	(0, 1, 'lock_own'),
	(0, 1, 'modify_own'),
	(0, 1, 'poll_add_own'),
	(0, 1, 'poll_edit_own'),
	(0, 1, 'poll_lock_own'),
	(0, 1, 'poll_post'),
	(0, 1, 'poll_view'),
	(0, 1, 'poll_vote'),
	(0, 1, 'post_attachment'),
	(0, 1, 'post_new'),
	(0, 1, 'post_draft'),
	(0, 1, 'post_reply_any'),
	(0, 1, 'post_reply_own'),
	(0, 1, 'post_unapproved_topics'),
	(0, 1, 'post_unapproved_replies_any'),
	(0, 1, 'post_unapproved_replies_own'),
	(0, 1, 'post_unapproved_attachments'),
	(0, 1, 'delete_own'),
	(0, 1, 'report_any'),
	(0, 1, 'view_attachments'),
	(2, 1, 'moderate_board'),
	(2, 1, 'post_new'),
	(2, 1, 'post_draft'),
	(2, 1, 'post_reply_own'),
	(2, 1, 'post_reply_any'),
	(2, 1, 'post_unapproved_topics'),
	(2, 1, 'post_unapproved_replies_any'),
	(2, 1, 'post_unapproved_replies_own'),
	(2, 1, 'post_unapproved_attachments'),
	(2, 1, 'poll_post'),
	(2, 1, 'poll_add_any'),
	(2, 1, 'poll_remove_any'),
	(2, 1, 'poll_view'),
	(2, 1, 'poll_vote'),
	(2, 1, 'poll_lock_any'),
	(2, 1, 'poll_edit_any'),
	(2, 1, 'report_any'),
	(2, 1, 'lock_own'),
	(2, 1, 'delete_own'),
	(2, 1, 'modify_own'),
	(2, 1, 'make_sticky'),
	(2, 1, 'lock_any'),
	(2, 1, 'remove_any'),
	(2, 1, 'move_any'),
	(2, 1, 'merge_any'),
	(2, 1, 'split_any'),
	(2, 1, 'delete_any'),
	(2, 1, 'modify_any'),
	(2, 1, 'approve_posts'),
	(2, 1, 'post_attachment'),
	(2, 1, 'view_attachments'),
	(3, 1, 'moderate_board'),
	(3, 1, 'post_new'),
	(3, 1, 'post_draft'),
	(3, 1, 'post_reply_own'),
	(3, 1, 'post_reply_any'),
	(3, 1, 'post_unapproved_topics'),
	(3, 1, 'post_unapproved_replies_any'),
	(3, 1, 'post_unapproved_replies_own'),
	(3, 1, 'post_unapproved_attachments'),
	(3, 1, 'poll_post'),
	(3, 1, 'poll_add_any'),
	(3, 1, 'poll_remove_any'),
	(3, 1, 'poll_view'),
	(3, 1, 'poll_vote'),
	(3, 1, 'poll_lock_any'),
	(3, 1, 'poll_edit_any'),
	(3, 1, 'report_any'),
	(3, 1, 'lock_own'),
	(3, 1, 'delete_own'),
	(3, 1, 'modify_own'),
	(3, 1, 'make_sticky'),
	(3, 1, 'lock_any'),
	(3, 1, 'remove_any'),
	(3, 1, 'move_any'),
	(3, 1, 'merge_any'),
	(3, 1, 'split_any'),
	(3, 1, 'delete_any'),
	(3, 1, 'modify_any'),
	(3, 1, 'approve_posts'),
	(3, 1, 'post_attachment'),
	(3, 1, 'view_attachments'),
	(-1, 2, 'poll_view'),
	(0, 2, 'remove_own'),
	(0, 2, 'lock_own'),
	(0, 2, 'modify_own'),
	(0, 2, 'poll_view'),
	(0, 2, 'poll_vote'),
	(0, 2, 'post_attachment'),
	(0, 2, 'post_new'),
	(0, 2, 'post_draft'),
	(0, 2, 'post_reply_any'),
	(0, 2, 'post_reply_own'),
	(0, 2, 'post_unapproved_topics'),
	(0, 2, 'post_unapproved_replies_any'),
	(0, 2, 'post_unapproved_replies_own'),
	(0, 2, 'post_unapproved_attachments'),
	(0, 2, 'delete_own'),
	(0, 2, 'report_any'),
	(0, 2, 'view_attachments'),
	(2, 2, 'moderate_board'),
	(2, 2, 'post_new'),
	(2, 2, 'post_draft'),
	(2, 2, 'post_reply_own'),
	(2, 2, 'post_reply_any'),
	(2, 2, 'post_unapproved_topics'),
	(2, 2, 'post_unapproved_replies_any'),
	(2, 2, 'post_unapproved_replies_own'),
	(2, 2, 'post_unapproved_attachments'),
	(2, 2, 'poll_post'),
	(2, 2, 'poll_add_any'),
	(2, 2, 'poll_remove_any'),
	(2, 2, 'poll_view'),
	(2, 2, 'poll_vote'),
	(2, 2, 'poll_lock_any'),
	(2, 2, 'poll_edit_any'),
	(2, 2, 'report_any'),
	(2, 2, 'lock_own'),
	(2, 2, 'delete_own'),
	(2, 2, 'modify_own'),
	(2, 2, 'make_sticky'),
	(2, 2, 'lock_any'),
	(2, 2, 'remove_any'),
	(2, 2, 'move_any'),
	(2, 2, 'merge_any'),
	(2, 2, 'split_any'),
	(2, 2, 'delete_any'),
	(2, 2, 'modify_any'),
	(2, 2, 'approve_posts'),
	(2, 2, 'post_attachment'),
	(2, 2, 'view_attachments'),
	(3, 2, 'moderate_board'),
	(3, 2, 'post_new'),
	(3, 2, 'post_draft'),
	(3, 2, 'post_reply_own'),
	(3, 2, 'post_reply_any'),
	(3, 2, 'post_unapproved_topics'),
	(3, 2, 'post_unapproved_replies_any'),
	(3, 2, 'post_unapproved_replies_own'),
	(3, 2, 'post_unapproved_attachments'),
	(3, 2, 'poll_post'),
	(3, 2, 'poll_add_any'),
	(3, 2, 'poll_remove_any'),
	(3, 2, 'poll_view'),
	(3, 2, 'poll_vote'),
	(3, 2, 'poll_lock_any'),
	(3, 2, 'poll_edit_any'),
	(3, 2, 'report_any'),
	(3, 2, 'lock_own'),
	(3, 2, 'delete_own'),
	(3, 2, 'modify_own'),
	(3, 2, 'make_sticky'),
	(3, 2, 'lock_any'),
	(3, 2, 'remove_any'),
	(3, 2, 'move_any'),
	(3, 2, 'merge_any'),
	(3, 2, 'split_any'),
	(3, 2, 'delete_any'),
	(3, 2, 'modify_any'),
	(3, 2, 'approve_posts'),
	(3, 2, 'post_attachment'),
	(3, 2, 'view_attachments'),
	(-1, 3, 'poll_view'),
	(0, 3, 'remove_own'),
	(0, 3, 'lock_own'),
	(0, 3, 'modify_own'),
	(0, 3, 'poll_view'),
	(0, 3, 'poll_vote'),
	(0, 3, 'post_attachment'),
	(0, 3, 'post_reply_any'),
	(0, 3, 'post_reply_own'),
	(0, 3, 'post_unapproved_replies_any'),
	(0, 3, 'post_unapproved_replies_own'),
	(0, 3, 'post_unapproved_attachments'),
	(0, 3, 'delete_own'),
	(0, 3, 'report_any'),
	(0, 3, 'view_attachments'),
	(2, 3, 'moderate_board'),
	(2, 3, 'post_new'),
	(2, 3, 'post_draft'),
	(2, 3, 'post_reply_own'),
	(2, 3, 'post_reply_any'),
	(2, 3, 'post_unapproved_topics'),
	(2, 3, 'post_unapproved_replies_any'),
	(2, 3, 'post_unapproved_replies_own'),
	(2, 3, 'post_unapproved_attachments'),
	(2, 3, 'poll_post'),
	(2, 3, 'poll_add_any'),
	(2, 3, 'poll_remove_any'),
	(2, 3, 'poll_view'),
	(2, 3, 'poll_vote'),
	(2, 3, 'poll_lock_any'),
	(2, 3, 'poll_edit_any'),
	(2, 3, 'report_any'),
	(2, 3, 'lock_own'),
	(2, 3, 'delete_own'),
	(2, 3, 'modify_own'),
	(2, 3, 'make_sticky'),
	(2, 3, 'lock_any'),
	(2, 3, 'remove_any'),
	(2, 3, 'move_any'),
	(2, 3, 'merge_any'),
	(2, 3, 'split_any'),
	(2, 3, 'delete_any'),
	(2, 3, 'modify_any'),
	(2, 3, 'approve_posts'),
	(2, 3, 'post_attachment'),
	(2, 3, 'view_attachments'),
	(3, 3, 'moderate_board'),
	(3, 3, 'post_new'),
	(3, 3, 'post_draft'),
	(3, 3, 'post_reply_own'),
	(3, 3, 'post_reply_any'),
	(3, 3, 'post_unapproved_topics'),
	(3, 3, 'post_unapproved_replies_any'),
	(3, 3, 'post_unapproved_replies_own'),
	(3, 3, 'post_unapproved_attachments'),
	(3, 3, 'poll_post'),
	(3, 3, 'poll_add_any'),
	(3, 3, 'poll_remove_any'),
	(3, 3, 'poll_view'),
	(3, 3, 'poll_vote'),
	(3, 3, 'poll_lock_any'),
	(3, 3, 'poll_edit_any'),
	(3, 3, 'report_any'),
	(3, 3, 'lock_own'),
	(3, 3, 'delete_own'),
	(3, 3, 'modify_own'),
	(3, 3, 'make_sticky'),
	(3, 3, 'lock_any'),
	(3, 3, 'remove_any'),
	(3, 3, 'move_any'),
	(3, 3, 'merge_any'),
	(3, 3, 'split_any'),
	(3, 3, 'delete_any'),
	(3, 3, 'modify_any'),
	(3, 3, 'approve_posts'),
	(3, 3, 'post_attachment'),
	(3, 3, 'view_attachments'),
	(-1, 4, 'poll_view'),
	(0, 4, 'poll_view'),
	(0, 4, 'poll_vote'),
	(0, 4, 'report_any'),
	(0, 4, 'view_attachments'),
	(2, 4, 'moderate_board'),
	(2, 4, 'post_new'),
	(2, 4, 'post_draft'),
	(2, 4, 'post_reply_own'),
	(2, 4, 'post_reply_any'),
	(2, 4, 'post_unapproved_topics'),
	(2, 4, 'post_unapproved_replies_any'),
	(2, 4, 'post_unapproved_replies_own'),
	(2, 4, 'post_unapproved_attachments'),
	(2, 4, 'poll_post'),
	(2, 4, 'poll_add_any'),
	(2, 4, 'poll_remove_any'),
	(2, 4, 'poll_view'),
	(2, 4, 'poll_vote'),
	(2, 4, 'poll_lock_any'),
	(2, 4, 'poll_edit_any'),
	(2, 4, 'report_any'),
	(2, 4, 'lock_own'),
	(2, 4, 'delete_own'),
	(2, 4, 'modify_own'),
	(2, 4, 'make_sticky'),
	(2, 4, 'lock_any'),
	(2, 4, 'remove_any'),
	(2, 4, 'move_any'),
	(2, 4, 'merge_any'),
	(2, 4, 'split_any'),
	(2, 4, 'delete_any'),
	(2, 4, 'modify_any'),
	(2, 4, 'approve_posts'),
	(2, 4, 'post_attachment'),
	(2, 4, 'view_attachments'),
	(3, 4, 'moderate_board'),
	(3, 4, 'post_new'),
	(3, 4, 'post_draft'),
	(3, 4, 'post_reply_own'),
	(3, 4, 'post_reply_any'),
	(3, 4, 'post_unapproved_topics'),
	(3, 4, 'post_unapproved_replies_any'),
	(3, 4, 'post_unapproved_replies_own'),
	(3, 4, 'post_unapproved_attachments'),
	(3, 4, 'poll_post'),
	(3, 4, 'poll_add_any'),
	(3, 4, 'poll_remove_any'),
	(3, 4, 'poll_view'),
	(3, 4, 'poll_vote'),
	(3, 4, 'poll_lock_any'),
	(3, 4, 'poll_edit_any'),
	(3, 4, 'report_any'),
	(3, 4, 'lock_own'),
	(3, 4, 'delete_own'),
	(3, 4, 'modify_own'),
	(3, 4, 'make_sticky'),
	(3, 4, 'lock_any'),
	(3, 4, 'remove_any'),
	(3, 4, 'move_any'),
	(3, 4, 'merge_any'),
	(3, 4, 'split_any'),
	(3, 4, 'delete_any'),
	(3, 4, 'modify_any'),
	(3, 4, 'approve_posts'),
	(3, 4, 'post_attachment'),
	(3, 4, 'view_attachments');
# --------------------------------------------------------

#
# Dumping data for table `boards`
#

INSERT INTO {$db_prefix}boards
	(id_board, id_cat, board_order, id_last_msg, id_msg_updated, name, description, num_topics, num_posts, member_groups)
VALUES (1, 1, 1, 1, 1, '{$default_board_name}', '{$default_board_description}', 1, 1, '-1,0,2');
# --------------------------------------------------------

#
# Dumping data for table `calendar_holidays`
#

INSERT INTO {$db_prefix}calendar_holidays
	(event_date, title)
VALUES ('0004-01-01', 'New Year''s (en)'),
('0004-12-25', 'Christmas (en)'),
('0004-02-14', 'Valentine''s Day (en)'),
('0004-03-17', 'St. Patrick''s Day (en)'),
('0004-04-01', 'April Fools (en)'),
('0004-04-22', 'Earth Day (en)'),
('0004-10-24', 'United Nations Day (en)'),
('0004-10-31', 'Halloween (en)'),
('0004-07-04', 'Independence Day (en)'),
('0004-05-05', 'Cinco de Mayo (en)'),
('0004-06-14', 'Flag Day (en)'),
('0004-11-11', 'Veterans Day (en)'),
('0004-02-02', 'Groundhog Day (en)'),
('0004-06-06', 'D-Day (en)'),
('0004-10-02', 'Wedding day'),
('0004-01-06', 'Hl. 3 Könige (de)'),
('0004-05-01', 'Maifeiertag (de)'),
('0004-08-15', 'Mariä Himmelfahrt (de)'),
('0004-10-03', 'Tag d. Dt. Einheit (de)'),
('0004-10-31', 'Reformationstag (de)'),
('0004-11-01', 'Allerheiligen (de)'),
('0004-12-24', 'Heiliger Abend (de)'),
('0004-12-25', '1. Weihnachtstag (de)'),
('0004-12-26', '2. Weihnachtstag (de)'),
('0004-12-31', 'Sylvester (de)'),
('0004-01-01', 'Neujahr (de)'),
('2004-05-09', 'Mother''s Day (en)'),
('2005-05-08', 'Mother''s Day (en)'),
('2006-05-14', 'Mother''s Day (en)'),
('2007-05-13', 'Mother''s Day (en)'),
('2008-05-11', 'Mother''s Day (en)'),
('2009-05-10', 'Mother''s Day (en)'),
('2010-05-09', 'Mother''s Day (en)'),
('2011-05-08', 'Mother''s Day (en)'),
('2012-05-13', 'Mother''s Day (en)'),
('2013-05-12', 'Mother''s Day (en)'),
('2014-05-11', 'Mother''s Day (en)'),
('2015-05-10', 'Mother''s Day (en)'),
('2016-05-08', 'Mother''s Day (en)'),
('2017-05-14', 'Mother''s Day (en)'),
('2018-05-13', 'Mother''s Day (en)'),
('2019-05-12', 'Mother''s Day (en)'),
('2020-05-10', 'Mother''s Day (en)'),
('2004-06-20', 'Father''s Day (en)'),
('2005-06-19', 'Father''s Day (en)'),
('2006-06-18', 'Father''s Day (en)'),
('2007-06-17', 'Father''s Day (en)'),
('2008-06-15', 'Father''s Day (en)'),
('2009-06-21', 'Father''s Day (en)'),
('2010-06-20', 'Father''s Day (en)'),
('2011-06-19', 'Father''s Day (en)'),
('2012-06-17', 'Father''s Day (en)'),
('2013-06-16', 'Father''s Day (en)'),
('2014-06-15', 'Father''s Day (en)'),
('2015-06-21', 'Father''s Day (en)'),
('2016-06-19', 'Father''s Day (en)'),
('2017-06-18', 'Father''s Day (en)'),
('2018-06-17', 'Father''s Day (en)'),
('2019-06-16', 'Father''s Day (en)'),
('2020-06-21', 'Father''s Day (en)'),
('2004-06-20', 'Summer Solstice (en)'),
('2005-06-20', 'Summer Solstice (en)'),
('2006-06-21', 'Summer Solstice (en)'),
('2007-06-21', 'Summer Solstice (en)'),
('2008-06-20', 'Summer Solstice (en)'),
('2009-06-20', 'Summer Solstice (en)'),
('2010-06-21', 'Summer Solstice (en)'),
('2011-06-21', 'Summer Solstice (en)'),
('2012-06-20', 'Summer Solstice (en)'),
('2013-06-21', 'Summer Solstice (en)'),
('2014-06-21', 'Summer Solstice (en)'),
('2015-06-21', 'Summer Solstice (en)'),
('2016-06-20', 'Summer Solstice (en)'),
('2017-06-20', 'Summer Solstice (en)'),
('2018-06-21', 'Summer Solstice (en)'),
('2019-06-21', 'Summer Solstice (en)'),
('2020-06-20', 'Summer Solstice (en)'),
('2004-03-19', 'Vernal Equinox (en)'),
('2005-03-20', 'Vernal Equinox (en)'),
('2006-03-20', 'Vernal Equinox (en)'),
('2007-03-20', 'Vernal Equinox (en)'),
('2008-03-19', 'Vernal Equinox (en)'),
('2009-03-20', 'Vernal Equinox (en)'),
('2010-03-20', 'Vernal Equinox (en)'),
('2011-03-20', 'Vernal Equinox (en)'),
('2012-03-20', 'Vernal Equinox (en)'),
('2013-03-20', 'Vernal Equinox (en)'),
('2014-03-20', 'Vernal Equinox (en)'),
('2015-03-20', 'Vernal Equinox (en)'),
('2016-03-19', 'Vernal Equinox (en)'),
('2017-03-20', 'Vernal Equinox (en)'),
('2018-03-20', 'Vernal Equinox (en)'),
('2019-03-20', 'Vernal Equinox (en)'),
('2020-03-19', 'Vernal Equinox (en)'),
('2004-12-21', 'Winter Solstice (en)'),
('2005-12-21', 'Winter Solstice (en)'),
('2006-12-22', 'Winter Solstice (en)'),
('2007-12-22', 'Winter Solstice (en)'),
('2008-12-21', 'Winter Solstice (en)'),
('2009-12-21', 'Winter Solstice (en)'),
('2010-12-21', 'Winter Solstice (en)'),
('2011-12-22', 'Winter Solstice (en)'),
('2012-12-21', 'Winter Solstice (en)'),
('2013-12-21', 'Winter Solstice (en)'),
('2014-12-21', 'Winter Solstice (en)'),
('2015-12-21', 'Winter Solstice (en)'),
('2016-12-21', 'Winter Solstice (en)'),
('2017-12-21', 'Winter Solstice (en)'),
('2018-12-21', 'Winter Solstice (en)'),
('2019-12-21', 'Winter Solstice (en)'),
('2020-12-21', 'Winter Solstice (en)'),
('2004-09-22', 'Autumnal Equinox (en)'),
('2005-09-22', 'Autumnal Equinox (en)'),
('2006-09-22', 'Autumnal Equinox (en)'),
('2007-09-23', 'Autumnal Equinox (en)'),
('2008-09-22', 'Autumnal Equinox (en)'),
('2009-09-22', 'Autumnal Equinox (en)'),
('2010-09-22', 'Autumnal Equinox (en)'),
('2011-09-23', 'Autumnal Equinox (en)'),
('2012-09-22', 'Autumnal Equinox (en)'),
('2013-09-22', 'Autumnal Equinox (en)'),
('2014-09-22', 'Autumnal Equinox (en)'),
('2015-09-23', 'Autumnal Equinox (en)'),
('2016-09-22', 'Autumnal Equinox (en)'),
('2017-09-22', 'Autumnal Equinox (en)'),
('2018-09-22', 'Autumnal Equinox (en)'),
('2019-09-23', 'Autumnal Equinox (en)'),
('2020-09-22', 'Autumnal Equinox (en)'),
('2004-11-25', 'Thanksgiving (en)'),
('2005-11-24', 'Thanksgiving (en)'),
('2006-11-23', 'Thanksgiving (en)'),
('2007-11-22', 'Thanksgiving (en)'),
('2008-11-27', 'Thanksgiving (en)'),
('2009-11-26', 'Thanksgiving (en)'),
('2010-11-25', 'Thanksgiving (en)'),
('2011-11-24', 'Thanksgiving (en)'),
('2012-11-22', 'Thanksgiving (en)'),
('2013-11-21', 'Thanksgiving (en)'),
('2014-11-20', 'Thanksgiving (en)'),
('2015-11-26', 'Thanksgiving (en)'),
('2016-11-24', 'Thanksgiving (en)'),
('2017-11-23', 'Thanksgiving (en)'),
('2018-11-22', 'Thanksgiving (en)'),
('2019-11-21', 'Thanksgiving (en)'),
('2020-11-26', 'Thanksgiving (en)'),
('2004-05-31', 'Memorial Day (en)'),
('2005-05-30', 'Memorial Day (en)'),
('2006-05-29', 'Memorial Day (en)'),
('2007-05-28', 'Memorial Day (en)'),
('2008-05-26', 'Memorial Day (en)'),
('2009-05-25', 'Memorial Day (en)'),
('2010-05-31', 'Memorial Day (en)'),
('2011-05-30', 'Memorial Day (en)'),
('2012-05-28', 'Memorial Day (en)'),
('2013-05-27', 'Memorial Day (en)'),
('2014-05-26', 'Memorial Day (en)'),
('2015-05-25', 'Memorial Day (en)'),
('2016-05-30', 'Memorial Day (en)'),
('2017-05-29', 'Memorial Day (en)'),
('2018-05-28', 'Memorial Day (en)'),
('2019-05-27', 'Memorial Day (en)'),
('2020-05-25', 'Memorial Day (en)'),
('2004-09-06', 'Labor Day (en)'),
('2005-09-05', 'Labor Day (en)'),
('2006-09-04', 'Labor Day (en)'),
('2007-09-03', 'Labor Day (en)'),
('2008-09-01', 'Labor Day (en)'),
('2009-09-07', 'Labor Day (en)'),
('2010-09-06', 'Labor Day (en)'),
('2011-09-05', 'Labor Day (en)'),
('2012-09-03', 'Labor Day (en)'),
('2013-09-09', 'Labor Day (en)'),
('2014-09-08', 'Labor Day (en)'),
('2015-09-07', 'Labor Day (en)'),
('2016-09-05', 'Labor Day (en)'),
('2017-09-04', 'Labor Day (en)'),
('2018-09-03', 'Labor Day (en)'),
('2019-09-09', 'Labor Day (en)'),
('2020-09-07', 'Labor Day (en)');

INSERT INTO {$db_prefix}calendar_holidays
	(event_date, title)
VALUES ('2004-02-25', 'Aschermittwoch (de)'),
('2005-02-09', 'Aschermittwoch (de)'),
('2006-03-01', 'Aschermittwoch (de)'),
('2007-02-21', 'Aschermittwoch (de)'),
('2008-02-06', 'Aschermittwoch (de)'),
('2009-02-25', 'Aschermittwoch (de)'),
('2010-02-17', 'Aschermittwoch (de)'),
('2011-03-09', 'Aschermittwoch (de)'),
('2012-02-22', 'Aschermittwoch (de)'),
('2013-02-13', 'Aschermittwoch (de)'),
('2014-03-05', 'Aschermittwoch (de)'),
('2015-02-18', 'Aschermittwoch (de)'),
('2016-02-10', 'Aschermittwoch (de)'),
('2017-03-01', 'Aschermittwoch (de)'),
('2018-02-14', 'Aschermittwoch (de)'),
('2019-03-06', 'Aschermittwoch (de)'),
('2020-02-26', 'Aschermittwoch (de)'),
('2021-02-17', 'Aschermittwoch (de)'),
('2022-03-02', 'Aschermittwoch (de)'),
('2004-11-17', 'Buß- und Bettag (de)'),
('2005-11-16', 'Buß- und Bettag (de)'),
('2006-11-22', 'Buß- und Bettag (de)'),
('2007-11-21', 'Buß- und Bettag (de)'),
('2008-11-19', 'Buß- und Bettag (de)'),
('2009-11-18', 'Buß- und Bettag (de)'),
('2010-11-17', 'Buß- und Bettag (de)'),
('2011-11-16', 'Buß- und Bettag (de)'),
('2012-11-21', 'Buß- und Bettag (de)'),
('2013-11-20', 'Buß- und Bettag (de)'),
('2014-11-19', 'Buß- und Bettag (de)'),
('2015-11-18', 'Buß- und Bettag (de)'),
('2016-11-16', 'Buß- und Bettag (de)'),
('2017-11-22', 'Buß- und Bettag (de)'),
('2018-11-21', 'Buß- und Bettag (de)'),
('2019-11-20', 'Buß- und Bettag (de)'),
('2020-11-18', 'Buß- und Bettag (de)'),
('2021-11-17', 'Buß- und Bettag (de)'),
('2022-11-16', 'Buß- und Bettag (de)'),
('2004-02-24', 'Fastnacht (de)'),
('2005-02-08', 'Fastnacht (de)'),
('2006-02-28', 'Fastnacht (de)'),
('2007-02-20', 'Fastnacht (de)'),
('2008-02-05', 'Fastnacht (de)'),
('2009-02-24', 'Fastnacht (de)'),
('2010-02-16', 'Fastnacht (de)'),
('2011-03-08', 'Fastnacht (de)'),
('2012-02-21', 'Fastnacht (de)'),
('2013-02-12', 'Fastnacht (de)'),
('2014-03-04', 'Fastnacht (de)'),
('2015-02-17', 'Fastnacht (de)'),
('2016-02-09', 'Fastnacht (de)'),
('2017-02-28', 'Fastnacht (de)'),
('2018-02-13', 'Fastnacht (de)'),
('2019-03-05', 'Fastnacht (de)'),
('2020-02-25', 'Fastnacht (de)'),
('2021-02-16', 'Fastnacht (de)'),
('2022-03-01', 'Fastnacht (de)'),
('2004-06-10', 'Fronleichnam (de)'),
('2005-05-26', 'Fronleichnam (de)'),
('2006-06-15', 'Fronleichnam (de)'),
('2007-06-07', 'Fronleichnam (de)'),
('2008-05-22', 'Fronleichnam (de)'),
('2009-06-11', 'Fronleichnam (de)'),
('2010-06-03', 'Fronleichnam (de)'),
('2011-06-23', 'Fronleichnam (de)'),
('2012-06-07', 'Fronleichnam (de)'),
('2013-05-30', 'Fronleichnam (de)'),
('2014-06-19', 'Fronleichnam (de)'),
('2015-06-04', 'Fronleichnam (de)'),
('2016-05-26', 'Fronleichnam (de)'),
('2017-06-15', 'Fronleichnam (de)'),
('2018-05-31', 'Fronleichnam (de)'),
('2019-06-20', 'Fronleichnam (de)'),
('2020-06-11', 'Fronleichnam (de)'),
('2021-06-03', 'Fronleichnam (de)'),
('2022-06-16', 'Fronleichnam (de)'),
('2004-04-12', 'Ostermontag (de)'),
('2005-03-28', 'Ostermontag (de)'),
('2006-04-17', 'Ostermontag (de)'),
('2007-04-09', 'Ostermontag (de)'),
('2008-03-24', 'Ostermontag (de)'),
('2009-04-13', 'Ostermontag (de)'),
('2010-04-05', 'Ostermontag (de)'),
('2011-04-25', 'Ostermontag (de)'),
('2012-04-09', 'Ostermontag (de)'),
('2013-04-01', 'Ostermontag (de)'),
('2014-04-21', 'Ostermontag (de)'),
('2015-04-06', 'Ostermontag (de)'),
('2016-03-28', 'Ostermontag (de)'),
('2017-04-17', 'Ostermontag (de)'),
('2018-04-02', 'Ostermontag (de)'),
('2019-04-22', 'Ostermontag (de)'),
('2020-04-13', 'Ostermontag (de)'),
('2021-04-05', 'Ostermontag (de)'),
('2022-04-18', 'Ostermontag (de)'),
('2004-04-11', 'Ostersonntag (de)'),
('2005-03-27', 'Ostersonntag (de)'),
('2006-04-16', 'Ostersonntag (de)'),
('2007-04-08', 'Ostersonntag (de)'),
('2008-03-23', 'Ostersonntag (de)'),
('2009-04-12', 'Ostersonntag (de)'),
('2010-04-04', 'Ostersonntag (de)'),
('2011-04-24', 'Ostersonntag (de)'),
('2012-04-08', 'Ostersonntag (de)'),
('2013-03-31', 'Ostersonntag (de)'),
('2014-04-20', 'Ostersonntag (de)'),
('2015-04-05', 'Ostersonntag (de)'),
('2016-03-27', 'Ostersonntag (de)'),
('2017-04-16', 'Ostersonntag (de)'),
('2018-04-01', 'Ostersonntag (de)'),
('2019-04-21', 'Ostersonntag (de)'),
('2020-04-12', 'Ostersonntag (de)'),
('2021-04-04', 'Ostersonntag (de)'),
('2022-04-17', 'Ostersonntag (de)'),
('2004-05-31', 'Pfingstmontag (de)'),
('2005-05-16', 'Pfingstmontag (de)'),
('2006-06-05', 'Pfingstmontag (de)'),
('2007-05-28', 'Pfingstmontag (de)'),
('2008-05-12', 'Pfingstmontag (de)'),
('2009-06-01', 'Pfingstmontag (de)'),
('2010-05-24', 'Pfingstmontag (de)'),
('2011-06-13', 'Pfingstmontag (de)'),
('2012-05-28', 'Pfingstmontag (de)'),
('2013-05-20', 'Pfingstmontag (de)'),
('2014-06-09', 'Pfingstmontag (de)'),
('2015-05-25', 'Pfingstmontag (de)'),
('2016-05-16', 'Pfingstmontag (de)'),
('2017-06-05', 'Pfingstmontag (de)'),
('2018-05-21', 'Pfingstmontag (de)'),
('2019-06-10', 'Pfingstmontag (de)'),
('2020-06-01', 'Pfingstmontag (de)'),
('2021-05-24', 'Pfingstmontag (de)'),
('2022-06-06', 'Pfingstmontag (de)'),
('2004-05-30', 'Pfingstsonntag (de)'),
('2005-05-15', 'Pfingstsonntag (de)'),
('2006-06-04', 'Pfingstsonntag (de)'),
('2007-05-27', 'Pfingstsonntag (de)'),
('2008-05-11', 'Pfingstsonntag (de)'),
('2009-05-31', 'Pfingstsonntag (de)'),
('2010-05-23', 'Pfingstsonntag (de)'),
('2011-06-12', 'Pfingstsonntag (de)'),
('2012-05-27', 'Pfingstsonntag (de)'),
('2013-05-19', 'Pfingstsonntag (de)'),
('2014-06-08', 'Pfingstsonntag (de)'),
('2015-05-24', 'Pfingstsonntag (de)'),
('2016-05-15', 'Pfingstsonntag (de)'),
('2017-06-04', 'Pfingstsonntag (de)'),
('2018-05-20', 'Pfingstsonntag (de)'),
('2019-06-09', 'Pfingstsonntag (de)'),
('2020-05-31', 'Pfingstsonntag (de)'),
('2021-05-23', 'Pfingstsonntag (de)'),
('2022-06-05', 'Pfingstsonntag (de)'),
('2004-02-23', 'Rosenmontag (de)'),
('2005-02-07', 'Rosenmontag (de)'),
('2006-02-27', 'Rosenmontag (de)'),
('2007-02-19', 'Rosenmontag (de)'),
('2008-02-04', 'Rosenmontag (de)'),
('2009-02-23', 'Rosenmontag (de)'),
('2010-02-15', 'Rosenmontag (de)'),
('2011-03-07', 'Rosenmontag (de)'),
('2012-02-20', 'Rosenmontag (de)'),
('2013-02-11', 'Rosenmontag (de)'),
('2014-03-03', 'Rosenmontag (de)'),
('2015-02-16', 'Rosenmontag (de)'),
('2016-02-08', 'Rosenmontag (de)'),
('2017-02-27', 'Rosenmontag (de)'),
('2018-02-12', 'Rosenmontag (de)'),
('2019-03-04', 'Rosenmontag (de)'),
('2020-02-24', 'Rosenmontag (de)'),
('2021-02-15', 'Rosenmontag (de)'),
('2022-02-28', 'Rosenmontag (de)'),
('2005-11-27', '1. Advent (de)'),
('2006-12-03', '1. Advent (de)'),
('2007-12-02', '1. Advent (de)'),
('2008-11-30', '1. Advent (de)'),
('2009-11-29', '1. Advent (de)'),
('2010-11-28', '1. Advent (de)'),
('2011-11-27', '1. Advent (de)'),
('2012-12-02', '1. Advent (de)'),
('2013-12-01', '1. Advent (de)'),
('2014-11-30', '1. Advent (de)'),
('2015-11-29', '1. Advent (de)'),
('2016-11-27', '1. Advent (de)'),
('2017-12-03', '1. Advent (de)'),
('2018-12-02', '1. Advent (de)'),
('2019-12-01', '1. Advent (de)'),
('2020-11-29', '1. Advent (de)'),
('2021-11-28', '1. Advent (de)'),
('2022-11-27', '1. Advent (de)'),
('2004-12-05', '2. Advent (de)'),
('2005-12-04', '2. Advent (de)'),
('2006-12-10', '2. Advent (de)'),
('2007-12-09', '2. Advent (de)'),
('2008-12-07', '2. Advent (de)'),
('2009-12-06', '2. Advent (de)'),
('2010-12-05', '2. Advent (de)'),
('2011-12-04', '2. Advent (de)'),
('2012-12-09', '2. Advent (de)'),
('2013-12-08', '2. Advent (de)'),
('2014-12-07', '2. Advent (de)'),
('2015-12-06', '2. Advent (de)'),
('2016-12-04', '2. Advent (de)'),
('2017-12-10', '2. Advent (de)'),
('2018-12-09', '2. Advent (de)'),
('2019-12-08', '2. Advent (de)'),
('2020-12-06', '2. Advent (de)'),
('2021-12-05', '2. Advent (de)'),
('2022-12-04', '2. Advent (de)'),
('2004-12-12', '3. Advent (de)'),
('2005-12-11', '3. Advent (de)'),
('2006-12-17', '3. Advent (de)'),
('2007-12-16', '3. Advent (de)'),
('2008-12-14', '3. Advent (de)'),
('2009-12-13', '3. Advent (de)'),
('2010-12-12', '3. Advent (de)'),
('2011-12-11', '3. Advent (de)'),
('2012-12-16', '3. Advent (de)'),
('2013-12-15', '3. Advent (de)'),
('2014-12-14', '3. Advent (de)'),
('2015-12-13', '3. Advent (de)'),
('2016-12-11', '3. Advent (de)'),
('2017-12-17', '3. Advent (de)'),
('2018-12-16', '3. Advent (de)'),
('2019-12-15', '3. Advent (de)'),
('2020-12-13', '3. Advent (de)'),
('2021-12-12', '3. Advent (de)'),
('2022-12-11', '3. Advent (de)'),
('2004-12-19', '4. Advent (de)'),
('2005-12-18', '4. Advent (de)'),
('2006-12-24', '4. Advent (de)'),
('2007-12-23', '4. Advent (de)'),
('2008-12-21', '4. Advent (de)'),
('2009-12-20', '4. Advent (de)'),
('2010-12-19', '4. Advent (de)'),
('2011-12-18', '4. Advent (de)'),
('2012-12-23', '4. Advent (de)'),
('2013-12-22', '4. Advent (de)'),
('2014-12-21', '4. Advent (de)'),
('2015-12-20', '4. Advent (de)'),
('2016-12-18', '4. Advent (de)'),
('2017-12-24', '4. Advent (de)'),
('2018-12-23', '4. Advent (de)'),
('2019-12-22', '4. Advent (de)'),
('2020-12-20', '4. Advent (de)'),
('2021-12-19', '4. Advent (de)'),
('2022-12-18', '4. Advent (de)'),
('2004-05-20', 'Christi Himmelfahrt (de)'),
('2005-05-05', 'Christi Himmelfahrt (de)'),
('2006-05-25', 'Christi Himmelfahrt (de)'),
('2007-05-17', 'Christi Himmelfahrt (de)'),
('2008-05-01', 'Christi Himmelfahrt (de)'),
('2009-05-21', 'Christi Himmelfahrt (de)'),
('2010-05-13', 'Christi Himmelfahrt (de)'),
('2011-06-02', 'Christi Himmelfahrt (de)'),
('2012-05-17', 'Christi Himmelfahrt (de)'),
('2013-05-09', 'Christi Himmelfahrt (de)'),
('2014-05-29', 'Christi Himmelfahrt (de)'),
('2015-05-14', 'Christi Himmelfahrt (de)'),
('2016-05-05', 'Christi Himmelfahrt (de)'),
('2017-05-25', 'Christi Himmelfahrt (de)'),
('2018-05-10', 'Christi Himmelfahrt (de)'),
('2019-05-30', 'Christi Himmelfahrt (de)'),
('2020-05-21', 'Christi Himmelfahrt (de)'),
('2021-05-13', 'Christi Himmelfahrt (de)'),
('2022-05-26', 'Christi Himmelfahrt (de)'),
('2004-04-08', 'Gründonnerstag (de)'),
('2005-03-24', 'Gründonnerstag (de)'),
('2006-04-13', 'Gründonnerstag (de)'),
('2007-04-05', 'Gründonnerstag (de)'),
('2008-03-20', 'Gründonnerstag (de)'),
('2009-04-09', 'Gründonnerstag (de)'),
('2010-04-01', 'Gründonnerstag (de)'),
('2011-04-21', 'Gründonnerstag (de)'),
('2012-04-05', 'Gründonnerstag (de)'),
('2013-03-28', 'Gründonnerstag (de)'),
('2014-04-17', 'Gründonnerstag (de)'),
('2015-04-02', 'Gründonnerstag (de)'),
('2016-03-24', 'Gründonnerstag (de)'),
('2017-04-13', 'Gründonnerstag (de)'),
('2018-03-29', 'Gründonnerstag (de)'),
('2019-04-18', 'Gründonnerstag (de)'),
('2020-04-09', 'Gründonnerstag (de)'),
('2021-04-01', 'Gründonnerstag (de)'),
('2022-04-14', 'Gründonnerstag (de)'),
('2004-04-09', 'Karfreitag (de)'),
('2005-03-25', 'Karfreitag (de)'),
('2006-04-14', 'Karfreitag (de)'),
('2007-04-06', 'Karfreitag (de)'),
('2008-03-21', 'Karfreitag (de)'),
('2009-04-10', 'Karfreitag (de)'),
('2010-04-02', 'Karfreitag (de)'),
('2011-04-22', 'Karfreitag (de)'),
('2012-04-06', 'Karfreitag (de)'),
('2013-03-29', 'Karfreitag (de)'),
('2014-04-18', 'Karfreitag (de)'),
('2015-04-03', 'Karfreitag (de)'),
('2016-03-25', 'Karfreitag (de)'),
('2017-04-14', 'Karfreitag (de)'),
('2018-03-30', 'Karfreitag (de)'),
('2019-04-19', 'Karfreitag (de)'),
('2020-04-10', 'Karfreitag (de)'),
('2021-04-02', 'Karfreitag (de)'),
('2022-04-15', 'Karfreitag (de)');

# --------------------------------------------------------

#
# Dumping data for table `categories`
#

INSERT INTO {$db_prefix}categories
VALUES (1, 0, '{$default_category_name}', '', 1);
# --------------------------------------------------------

#
# Dumping data for table `custom_fields`
#

INSERT INTO {$db_prefix}custom_fields
	(`col_name`, `field_name`, `field_desc`, `field_type`, `field_length`, `field_options`, `field_order`, `mask`, `show_reg`, `show_display`, `show_mlist`, `show_profile`, `private`, `active`, `bbc`, `can_search`, `default_value`, `enclose`, `placement`)
VALUES ('cust_skype', 'Skype', 'Your Skype name', 'text', 32, '', 1, 'nohtml', 0, 1, 0, 'forumprofile', 0, 1, 0, 0, '', '<a href="skype:{INPUT}?call"><img src="{DEFAULT_IMAGES_URL}/skype.png" alt="{INPUT}" title="{INPUT}" /></a>', 1),
	('cust_gplus', 'Google+', 'Your Google+ account', 'text', 50, '', 2, 'nohtml', 0, 1, 1, 'forumprofile', 0, 1, 0, 0, '', '<a class="gplus" href="{INPUT}" target="_blank" title="Google+"><img src="{DEFAULT_IMAGES_URL}/gplus.png" alt="Google+" style="padding-left:1px;"></a>', 1),
	('cust_fbook', 'Facebook', 'Your Facebook account', 'text', 50, '', 3, 'nohtml', 0, 1, 1, 'forumprofile', 0, 1, 0, 0, '', '<a class="facebook" href="{INPUT}" target="_blank" title="Facebook"><img src="{DEFAULT_IMAGES_URL}/facebook.png" alt="Facebook" style="padding-left:1px;"></a>', 1),
	('cust_twitter', 'Twitter', 'Your Twitter account', 'text', 50, '', 4, 'nohtml', 0, 1, 1, 'forumprofile', 0, 1, 0, 0, '', '<a class="titter" href="{INPUT}" target="_blank" title="Twitter"><img src="{DEFAULT_IMAGES_URL}/twitter.png" alt="Titter" style="padding-left:1px;"></a>', 1),
	('cust_github', 'Github', 'Your Github account', 'text', 50, '', 5, 'nohtml', 0, 1, 1, 'forumprofile', 0, 1, 0, 0, '', '<a class="github" href="{INPUT}" target="_blank" title="Github"><img src="{IMAGES_URL}/Github.png" alt="Github" style="padding-left:1px;"></a>', 1);

# --------------------------------------------------------

#
# Dumping data for table `membergroups`
#

INSERT INTO {$db_prefix}membergroups
	(id_group, group_name, description, online_color, min_posts, icons, group_type)
VALUES (1, '{$default_administrator_group}', '', '#FF0000', -1, '5#iconadmin.png', 1),
	(2, '{$default_global_moderator_group}', '', '#0000FF', -1, '5#icongmod.png', 0),
	(3, '{$default_moderator_group}', '', '', -1, '5#iconmod.png', 0),
	(4, '{$default_newbie_group}', '', '', 0, '1#icon.png', 0),
	(5, '{$default_junior_group}', '', '', 50, '2#icon.png', 0),
	(6, '{$default_full_group}', '', '', 100, '3#icon.png', 0),
	(7, '{$default_senior_group}', '', '', 250, '4#icon.png', 0),
	(8, '{$default_hero_group}', '', '', 500, '5#icon.png', 0);
# --------------------------------------------------------

#
# Dumping data for table `message_icons`
#

INSERT INTO {$db_prefix}message_icons
	(filename, title, icon_order)
VALUES ('xx', 'Standard', '0'),
	('thumbup', 'Thumb Up', '1'),
	('thumbdown', 'Thumb Down', '2'),
	('exclamation', 'Exclamation point', '3'),
	('question', 'Question mark', '4'),
	('lamp', 'Lamp', '5'),
	('smiley', 'Smiley', '6'),
	('angry', 'Angry', '7'),
	('cheesy', 'Cheesy', '8'),
	('grin', 'Grin', '9'),
	('sad', 'Sad', '10'),
	('wink', 'Wink', '11'),
	('poll', 'Poll', '12'),
	('clip', 'Clip', '13'),
	('last_post', 'Last post', '14'),
	('moved', 'Moved', '15'),
	('recycled', 'Recycled', '16'),
	('wireless', 'Wireless', '17'),
	('solved', 'Solved', '18'),
	('important', 'Important', '19'),
	('cup', 'Cup', '20'),
	('bug', 'Bug', '21');
# --------------------------------------------------------

#
# Dumping data for table `messages`
#

INSERT INTO {$db_prefix}messages
	(id_msg, id_msg_modified, id_topic, id_board, poster_time, subject, poster_name, poster_email, poster_ip, modified_name, body, icon)
VALUES (1, 1, 1, 1, UNIX_TIMESTAMP(), '{$default_topic_subject}', 'PortaMx', 'support@portamx.com', '127.0.0.1', '', '{$default_topic_message}', 'xx');
# --------------------------------------------------------

#
# Dumping data for table `package_servers`
#

INSERT INTO {$db_prefix}package_servers
	(name, url)
VALUES ('PortaMx File Server', 'https://docserver.portamx.com');
# --------------------------------------------------------

#
# Dumping data for table `permission_profiles`
#

INSERT INTO {$db_prefix}permission_profiles
	(id_profile, profile_name)
VALUES (1, 'default'), (2, 'no_polls'), (3, 'reply_only'), (4, 'read_only');
# --------------------------------------------------------

#
# Dumping data for table `permissions`
#

INSERT INTO {$db_prefix}permissions
	(id_group, permission)
VALUES (-1, 'search_posts'),
	(-1, 'calendar_view'),
	(-1, 'view_stats'),
	(0, 'view_mlist'),
	(0, 'search_posts'),
	(0, 'profile_view'),
	(0, 'pm_read'),
	(0, 'pm_send'),
	(0, 'pm_draft'),
	(0, 'calendar_view'),
	(0, 'view_stats'),
	(0, 'who_view'),
	(0, 'profile_identity_own'),
	(0, 'profile_password_own'),
	(0, 'profile_blurb_own'),
	(0, 'profile_displayed_name_own'),
	(0, 'profile_signature_own'),
	(0, 'profile_other_own'),
	(0, 'profile_forum_own'),
	(0, 'profile_extra_own'),
	(0, 'profile_remove_own'),
	(0, 'profile_server_avatar'),
	(0, 'profile_upload_avatar'),
	(0, 'profile_remote_avatar'),
	(0, 'send_email_to_members'),
	(2, 'view_mlist'),
	(2, 'search_posts'),
	(2, 'profile_view'),
	(2, 'pm_read'),
	(2, 'pm_send'),
	(2, 'pm_draft'),
	(2, 'calendar_view'),
	(2, 'view_stats'),
	(2, 'who_view'),
	(2, 'profile_identity_own'),
	(2, 'profile_password_own'),
	(2, 'profile_blurb_own'),
	(2, 'profile_displayed_name_own'),
	(2, 'profile_signature_own'),
	(2, 'profile_other_own'),
	(2, 'profile_forum_own'),
	(2, 'profile_extra_own'),
	(2, 'profile_remove_own'),
	(2, 'profile_server_avatar'),
	(2, 'profile_upload_avatar'),
	(2, 'profile_remote_avatar'),
	(2, 'send_email_to_members'),
	(2, 'profile_title_own'),
	(2, 'calendar_post'),
	(2, 'calendar_edit_any'),
	(2, 'access_mod_center');
# --------------------------------------------------------

#
# Dumping data for table `scheduled_tasks`
#

INSERT INTO {$db_prefix}scheduled_tasks
	(id_task, next_time, time_offset, time_regularity, time_unit, disabled, task, callable)
VALUES
	(1, 0, 0, 2, 'h', 0, 'approval_notification', ''),
	(3, 0, 60, 1, 'd', 0, 'daily_maintenance', ''),
	(5, 0, 0, 1, 'd', 0, 'daily_digest', ''),
	(6, 0, 0, 1, 'w', 0, 'weekly_digest', ''),
	(7, 0, {$sched_task_offset}, 1, 'd', 0, 'fetchPMXfiles', ''),
	(8, 0, 0, 1, 'd', 1, 'birthdayemails', ''),
	(9, 0, 0, 1, 'w', 0, 'weekly_maintenance', ''),
	(10, 0, 120, 1, 'd', 1, 'paid_subscriptions', ''),
	(11, 0, 120, 1, 'd', 0, 'remove_temp_attachments', ''),
	(12, 0, 180, 1, 'd', 0, 'remove_topic_redirect', ''),
	(13, 0, 240, 1, 'd', 0, 'remove_old_drafts', '');

# --------------------------------------------------------

#
# Dumping data for table `settings`
#

INSERT INTO {$db_prefix}settings
	(variable, value)
VALUES ('pmxVersion', '{$pmx_version}'),
	('news', '{$default_news}'),
	('compactTopicPagesContiguous', '5'),
	('compactTopicPagesEnable', '1'),
	('todayMod', '1'),
	('enablePreviousNext', '1'),
	('pollMode', '1'),
	('enableCompressedOutput', '{$enableCompressedOutput}'),
	('attachmentSizeLimit', '128'),
	('attachmentPostLimit', '192'),
	('attachmentNumPerPostLimit', '4'),
	('attachmentDirSizeLimit', '10240'),
	('attachmentDirFileLimit', '1000'),
	('attachmentUploadDir', '{$attachdir}'),
	('attachmentExtensions', 'doc,gif,jpg,mpg,pdf,png,txt,zip'),
	('attachmentCheckExtensions', '0'),
	('attachmentShowImages', '1'),
	('attachmentEnable', '1'),
	('attachmentThumbnails', '1'),
	('attachmentThumbWidth', '150'),
	('attachmentThumbHeight', '150'),
	('use_subdirectories_for_attachments', '1'),
	('currentAttachmentUploadDir', 1),
	('censorIgnoreCase', '1'),
	('mostOnline', '1'),
	('mostOnlineToday', '1'),
	('mostDate', UNIX_TIMESTAMP()),
	('allow_disableAnnounce', '1'),
	('trackStats', '1'),
	('userLanguage', '1'),
	('titlesEnable', '1'),
	('topicSummaryPosts', '15'),
	('enableErrorLogging', '1'),
	('log_ban_hits', '1'),
	('max_image_width', '0'),
	('max_image_height', '0'),
	('onlineEnable', '0'),
	('cal_enabled', '0'),
	('cal_showInTopic', '1'),
	('cal_maxyear', '2020'),
	('cal_minyear', '2008'),
	('cal_daysaslink', '0'),
	('cal_defaultboard', ''),
	('cal_showholidays', '1'),
	('cal_showbdays', '1'),
	('cal_showevents', '1'),
	('cal_maxspan', '7'),
	('cal_highlight_events', '3'),
	('cal_highlight_holidays', '3'),
	('cal_highlight_birthdays', '3'),
	('cal_disable_prev_next', '0'),
	('cal_display_type', '0'),
	('cal_week_links', '2'),
	('cal_prev_next_links', '1'),
	('cal_short_days', '0'),
	('cal_short_months', '0'),
	('smtp_host', ''),
	('smtp_port', '25'),
	('smtp_username', ''),
	('smtp_password', ''),
	('mail_type', '0'),
	('timeLoadPageEnable', '0'),
	('totalMembers', '0'),
	('totalTopics', '1'),
	('totalMessages', '1'),
	('censor_vulgar', ''),
	('censor_proper', ''),
	('enablePostHTML', '0'),
	('theme_allow', '1'),
	('theme_default', '1'),
	('theme_guests', '1'),
	('enableEmbeddedFlash', '0'),
	('xmlnews_enable', '1'),
	('xmlnews_maxlen', '255'),
	('registration_method', '{$registration_method}'),
	('send_validation_onChange', '0'),
	('send_welcomeEmail', '1'),
	('allow_editDisplayName', '1'),
	('allow_hideOnline', '1'),
	('spamWaitTime', '5'),
	('pm_spam_settings', '10,5,20'),
	('reserveWord', '0'),
	('reserveCase', '1'),
	('reserveUser', '1'),
	('reserveName', '1'),
	('reserveNames', '{$default_reserved_names}'),
	('autoLinkUrls', '1'),
	('banLastUpdated', '0'),
	('smileys_dir', '{$boarddir}/Smileys'),
	('smileys_url', '{$boardurl}/Smileys'),
	('custom_avatar_dir', '{$boarddir}/custom_avatar'),
	('custom_avatar_url', '{$boardurl}/custom_avatar'),
	('avatar_directory', '{$boarddir}/avatars'),
	('avatar_url', '{$boardurl}/avatars'),
	('avatar_max_height_external', '65'),
	('avatar_max_width_external', '65'),
	('avatar_action_too_large', 'option_css_resize'),
	('avatar_max_height_upload', '65'),
	('avatar_max_width_upload', '65'),
	('avatar_resize_upload', '1'),
	('avatar_download_png', '1'),
	('failed_login_threshold', '3'),
	('oldTopicDays', '120'),
	('edit_wait_time', '90'),
	('edit_disable_time', '0'),
	('autoFixDatabase', '1'),
	('allow_guestAccess', '1'),
	('number_format', '1234.00'),
	('enableBBC', '1'),
	('max_messageLength', '20000'),
	('signature_settings', '1,300,0,0,0,0,0,0:'),
	('defaultMaxMessages', '15'),
	('defaultMaxTopics', '20'),
	('defaultMaxMembers', '30'),
	('enableParticipation', '1'),
	('recycle_enable', '0'),
	('recycle_board', '0'),
	('maxMsgID', '1'),
	('enableAllMessages', '0'),
	('knownThemes', '1'),
	('enableThemes', '1'),
	('who_enabled', '1'),
	('time_offset', '0'),
	('cookieTime', '60'),
	('lastActive', '15'),
	('smiley_sets_known', 'default,aaron,akyhne,fugue,portamx'),
	('smiley_sets_names', '{$default_default_smileyset_name}
{$default_aaron_smileyset_name}
{$default_akyhne_smileyset_name}
{$default_fugue_smileyset_name}
{$default_portamx_smileyset_name}'),
	('smiley_sets_default', 'portamx'),
	('cal_days_for_index', '7'),
	('requireAgreement', '1'),
	('unapprovedMembers', '0'),
	('default_personal_text', ''),
	('package_make_backups', '1'),
	('databaseSession_enable', '{$databaseSession_enable}'),
	('databaseSession_loose', '1'),
	('databaseSession_lifetime', '2880'),
	('displayFields', '[{"col_name":"cust_skype","title":"Skype","type":"text","order":"1","bbc":"0","placement":"1","enclose":"\u003Ca href=\"skype:{INPUT}?call\"\u003E\u003Cimg src=\"{DEFAULT_IMAGES_URL}\/skype.png\" alt=\"{INPUT}\" title=\"{INPUT}\" \/\u003E\u003C\/a\u003E","mlist":"0"},{"col_name":"cust_gplus","title":"Google+","type":"text","order":"2","bbc":"0","placement":"1","enclose":"\u003Ca class=\"gplus\" href=\"{INPUT}\" target=\"_blank\" title=\"Google+\"\u003E\u003Cimg src=\"{DEFAULT_IMAGES_URL}\/gplus.png\" alt=\"Google+\" style=\"padding-left:1px;\"\u003E\u003C\/a\u003E","mlist":"1"},{"col_name":"cust_fbook","title":"Facebook","type":"text","order":"3","bbc":"0","placement":"1","enclose":"\u003Ca class=\"facebook\" href=\"{INPUT}\" target=\"_blank\" title=\"Facebook\"\u003E\u003Cimg src=\"{DEFAULT_IMAGES_URL}\/facebook.png\" alt=\"Facebook\" style=\"padding-left:1px;\"\u003E\u003C\/a\u003E","mlist":"1"},{"col_name":"cust_twitter","title":"Twitter","type":"text","order":"4","bbc":"0","placement":"1","enclose":"\u003Ca class=\"titter\" href=\"{INPUT}\" target=\"_blank\" title=\"Twitter\"\u003E\u003Cimg src=\"{DEFAULT_IMAGES_URL}\/twitter.png\" alt=\"Titter\" style=\"padding-left:1px;\"\u003E\u003C\/a\u003E","mlist":"1"},{"col_name":"cust_github","title":"Github","type":"text","order":"5","bbc":"0","placement":"1","enclose":"\u003Ca class=\"github\" href=\"{INPUT}\" target=\"_blank\" title=\"Github\"\u003E\u003Cimg src=\"{IMAGES_URL}\/Github.png\" alt=\"Github\" style=\"padding-left:1px;\"\u003E\u003C\/a\u003E","mlist":"1"}]'),
	('search_cache_size', '50'),
	('search_results_per_page', '30'),
	('search_weight_frequency', '30'),
	('search_weight_age', '25'),
	('search_weight_length', '20'),
	('search_weight_subject', '15'),
	('search_weight_first_message', '10'),
	('search_max_results', '1200'),
	('search_floodcontrol_time', '5'),
	('permission_enable_deny', '0'),
	('permission_enable_postgroups', '0'),
	('mail_next_send', '0'),
	('mail_recent', '0000000000|0'),
	('settings_updated', '0'),
	('next_task_time', '1'),
	('warning_settings', '1,20,0'),
	('warning_watch', '10'),
	('warning_moderate', '35'),
	('warning_mute', '60'),
	('last_mod_report_action', '0'),
	('pruningOptions', '30,180,180,180,30,0'),
	('modlog_enabled', '1'),
	('adminlog_enabled', '1'),
	('cache_enable', '1'),
	('reg_verification', '1'),
	('visual_verification_type', '3'),
	('enable_buddylist', '1'),
	('birthday_email', 'happy_birthday'),
	('dont_repeat_theme_core', '1'),
	('dont_repeat_smileys_20', '1'),
	('dont_repeat_buddylists', '1'),
	('attachment_image_reencode', '1'),
	('attachment_image_paranoid', '0'),
	('attachment_thumb_png', '1'),
	('avatar_reencode', '1'),
	('avatar_paranoid', '0'),
	('drafts_post_enabled', '1'),
	('drafts_pm_enabled', '1'),
	('drafts_autosave_enabled', '1'),
	('drafts_show_saved_enabled', '1'),
	('drafts_keep_days', '7'),
	('topic_move_any', '0'),
	('browser_cache', '?rc1'),
	('mail_limit', '5'),
	('mail_quantity', '5'),
	('messageIconChecks_enable', '1'),
	('additional_options_collapsable', '1'),
	('show_modify', '1'),
	('show_user_images', '1'),
	('show_blurb', '1'),
	('show_profile_buttons', '1'),
	('enable_ajax_alerts', '1'),
	('gravatarEnabled', '1'),
	('gravatarOverride', '0'),
	('gravatarAllowExtraEmail', '1'),
	('gravatarMaxRating', 'PG'),
	('defaultMaxListItems', '15'),
	('loginHistoryDays', '7'),
	('httponlyCookies', '1'),
	('tfa_mode', '1'),
	('allow_expire_redirect', '1'),
	('json_done', '1'),
	('minimize_files', '0'),
	('pmx_docserver', 'https://docserver.portamx.com/pmxforum/'),
	('portal_enabled', '0'),
	('ecl_topofs', '40'),
	('sef_actions', 'about:mozilla,about:unknown,activate,announce,attachapprove,buddy,calendar,clock,collapse,community,coppa,credits,deletemsg,display,dlattach,editpoll,editpoll2,emailuser,findmember,groups,help,helpadmin,im,imprint,jseditor,jsmodify,jsoption,keepalive,language,lock,lockvoting,login,login2,logout,logintfa,markasread,mergetopics,mlist,moderate,modifycat,modifykarma,movetopic,movetopic2,notify,notifyboard,openidreturn,pm,post,post2,printpage,profile,promote,quotefast,quickmod,quickmod2,recent,register,register2,reminder,removepoll,removetopic2,reporttm,requestmembers,restoretopic,search,search2,sendtopic,signup,signup2,suggest,spellcheck,splittopics,stats,sticky,themes,trackip,unread,unreadreplies,verificationcode,viewprofile,vote,viewquery,viewpmxfile,who,.xml,xmlhttp,notifytopic,likes,loadeditorlocale,xml,sitemap'),
	('sef_autosave', '0'),
	('sef_enabled', '0'),
	('sef_ignoreactions', ''),
	('sef_lowercase', '1'),
	('sef_spacechar', '-'),
	('sef_stripchars', '&<>~!@#$%^&*()=+.,;:\'"/?\\|[]`´°§µ€'),
	('gdpr_enabled', '0'),
	('gdpr_last_update', '0'),
	('gdpr_owner_email', ''),
	('gdpr_owner_hoster', ''),
	('gdpr_owner_location', ''),
	('gdpr_owner_country', ''),
	('gdpr_owner_name', ''),
	('gdpr_owner_street', ''),
	('google_site_verification', ''),
	('lang_autodetect', '0'),
	('geoip_enabled', '0'),
	('geoip_sslkey', ''),
	('geoip_log', '0'),
	('webkit_scrollbars', '0'),
	('add_favicon_to_links', '0'),
	('dont_use_lightbox', '0'),
	('image_download', '0'),
	('image_addwatermark', '0'),
	('watermark_image', '');
# --------------------------------------------------------

#
# Dumping data for table `smileys`
#

INSERT INTO {$db_prefix}smileys
	(code, filename, description, smiley_order, hidden)
VALUES (':)', 'smiley.gif', '{$default_smiley_smiley}', 0, 0),
	(';)', 'wink.gif', '{$default_wink_smiley}', 1, 0),
	(':D', 'cheesy.gif', '{$default_cheesy_smiley}', 2, 0),
	(';D', 'grin.gif', '{$default_grin_smiley}', 3, 0),
	(':>(', 'angry.gif', '{$default_angry_smiley}', 4, 0),
	(':(', 'sad.gif', '{$default_sad_smiley}', 5, 0),
	(':o', 'shocked.gif', '{$default_shocked_smiley}', 6, 0),
	('8)', 'cool.gif', '{$default_cool_smiley}', 7, 0),
	('???', 'huh.gif', '{$default_huh_smiley}', 8, 0),
	('::)', 'rolleyes.gif', '{$default_roll_eyes_smiley}', 9, 0),
	(':P', 'tongue.gif', '{$default_tongue_smiley}', 10, 0),
	(':-[', 'embarrassed.gif', '{$default_embarrassed_smiley}', 11, 0),
	(':-X', 'lipsrsealed.gif', '{$default_lips_sealed_smiley}', 12, 0),
	(':-\\', 'undecided.gif', '{$default_undecided_smiley}', 13, 0),
	(':-*', 'kiss.gif', '{$default_kiss_smiley}', 14, 0),
	(':''(', 'cry.gif', '{$default_cry_smiley}', 15, 0),
	(':>D', 'evil.gif', '{$default_evil_smiley}', 16, 0),
	('^-^', 'azn.gif', '{$default_azn_smiley}', 17, 0),
	('O0', 'afro.gif', '{$default_afro_smiley}', 18, 0),
	(':|)', 'laugh.gif', '{$default_laugh_smiley}', 19, 0);
# --------------------------------------------------------

#
# Dumping data for table `spiders`
#

INSERT INTO {$db_prefix}spiders
	(spider_name, user_agent, ip_info, forbidden)
VALUES ('PortaMx Spider', 'PortaMx Spider', '', 0);
#---------------------------------------------------------

#
# Dumping data for table `themes`
#

INSERT INTO {$db_prefix}themes
	(id_theme, variable, value)
VALUES (1, 'name', '{$default_theme_name}'),
	(1, 'theme_url', '{$boardurl}/Themes/default'),
	(1, 'images_url', '{$boardurl}/Themes/default/images'),
	(1, 'theme_dir', '{$boarddir}/Themes/default'),
	(1, 'show_latest_member', '1'),
	(1, 'show_newsfader', '0'),
	(1, 'number_recent_posts', '0'),
	(1, 'show_stats_index', '1'),
	(1, 'newsfader_time', '3000'),
	(1, 'use_image_buttons', '1'),
	(1, 'enable_news', '1'),
	(1, 'drafts_show_saved_enabled', '1');

INSERT INTO {$db_prefix}themes
	(id_member, id_theme, variable, value)
VALUES (-1, 1, 'posts_apply_ignore_list', '1'),
	(-1, 1, 'return_to_post', '1');
# --------------------------------------------------------

#
# Dumping data for table `topics`
#

INSERT INTO {$db_prefix}topics
	(id_topic, id_board, id_first_msg, id_last_msg, id_member_started, id_member_updated)
VALUES (1, 1, 1, 1, 0, 0);
# --------------------------------------------------------

#
# Dumping data for table `user_alerts_prefs`
#

INSERT INTO {$db_prefix}user_alerts_prefs
	(id_member, alert_pref, alert_value)
VALUES (0, 'member_group_request', 1),
	(0, 'member_register', 1),
	(0, 'msg_like', 1),
	(0, 'msg_report', 1),
	(0, 'msg_report_reply', 1),
	(0, 'unapproved_reply', 3),
	(0, 'topic_notify', 1),
	(0, 'board_notify', 1),
	(0, 'msg_mention', 1),
	(0, 'msg_quote', 1),
	(0, 'pm_new', 1),
	(0, 'pm_reply', 1),
	(0, 'groupr_approved', 3),
	(0, 'groupr_rejected', 3),
	(0, 'member_report_reply', 3),
	(0, 'birthday', 2),
	(0, '_announcements', 2),
	(0, 'member_report', 3),
	(0, 'unapproved_post', 1),
	(0, 'buddy_request', 1),
	(0, 'warn_any', 1),
	(0, 'request_group', 1);
# --------------------------------------------------------

COMMIT;
