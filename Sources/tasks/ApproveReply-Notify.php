<?php
/**
 * This file contains background notification code for any create post action
 *
 * PortaMx Forum
 * @package PortaMx
 * @author PortaMx & Simple Machines
 * @copyright 2018 PortaMx,  Simple Machines and individual contributors
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 1.41
 */

/**
 * Class ApproveReply_Notify_Background
 */
class ApproveReply_Notify_Background extends PMX_BackgroundTask
{
	/**
	 * This executes the task - loads up the information, puts the email in the queue and inserts alerts.
	 * @return bool Always returns true.
	 */
	public function execute()
	{
		global $pmxcFunc, $sourcedir, $scripturl, $modSettings, $context, $language;

		if(!defined('is_sheduled_task'))
			define('is_sheduled_task', true);

		$msgOptions = $this->_details['msgOptions'];
		$topicOptions = $this->_details['topicOptions'];
		$posterOptions = $this->_details['posterOptions'];

		$members = array();
		$alert_rows = array();

		$request = $pmxcFunc['db_query']('', '
			SELECT id_member, email_address, lngfile
			FROM {db_prefix}topics AS t
				INNER JOIN {db_prefix}members AS mem ON (mem.id_member = t.id_member_started)
			WHERE id_topic = {int:topic}',
			array(
				'topic' => $topicOptions['id'],
			)
		);

		$watched = array();
		while ($row = $pmxcFunc['db_fetch_assoc']($request))
		{
			$members[] = $row['id_member'];
			$watched[$row['id_member']] = $row;
		}
		$pmxcFunc['db_free_result']($request);

		require_once($sourcedir . '/Subs-Notify.php');
		$prefs = getNotifyPrefs($members, 'unapproved_reply', true);
		foreach ($watched as $member => $data)
		{
			$pref = !empty($prefs[$member]['unapproved_reply']) ? $prefs[$member]['unapproved_reply'] : 0;

			if ($pref & 0x02)
			{
				// Emails are a bit complicated. We have to do language stuff.
				require_once($sourcedir . '/Subs-Post.php');
				require_once($sourcedir . '/ScheduledTasks.php');
				loadEssentialThemeData();

				$replacements = array(
					'SUBJECT' => $msgOptions['subject'],
					'LINK' => $scripturl . '?topic=' . $topicOptions['id'] . '.new#new',
					'POSTERNAME' => un_htmlspecialchars($posterOptions['name']),
				);

				$emaildata = loadEmailTemplate('alert_unapproved_reply', $replacements, empty($data['lngfile']) ? $language : $data['lngfile']);
				sendmail($data['email_address'], $emaildata['subject'], $emaildata['body'], null, 'm' . $topicOptions['id'], $emaildata['is_html']);
			}

			if ($pref & 0x01)
			{
				$alert_rows[] = array(
					'alert_time' => time(),
					'id_member' => $member,
					'id_member_started' => $posterOptions['id'],
					'member_name' => $posterOptions['name'],
					'content_type' => 'unapproved',
					'content_id' => $topicOptions['id'],
					'content_action' => 'reply',
					'is_read' => 0,
					'extra' => json_encode(array(
						'topic' => $topicOptions['id'],
						'board' => $topicOptions['board'],
						'content_subject' => $msgOptions['subject'],
						'content_link' => $scripturl . '?topic=' . $topicOptions['id'] . '.new;topicseen#new',
					), true),
				);
				updateMemberData($member, array('alerts' => '+'));
			}
		}

		// Insert the alerts if any
		if (!empty($alert_rows))
			$pmxcFunc['db_insert']('',
				'{db_prefix}user_alerts',
				array('alert_time' => 'int', 'id_member' => 'int', 'id_member_started' => 'int', 'member_name' => 'string',
					'content_type' => 'string', 'content_id' => 'int', 'content_action' => 'string', 'is_read' => 'int', 'extra' => 'string'),
				$alert_rows,
				array()
			);

		return true;
	}
}

?>