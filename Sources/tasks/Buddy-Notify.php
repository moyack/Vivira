<?php

/**
 * This task handles notifying someone that an user has added him/her as his/her buddy.
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
 * Class Buddy_Notify_Background
 */
class Buddy_Notify_Background extends PMX_BackgroundTask
{
	/**
		 * This executes the task - loads up the info, sets the alerts and loads up the email queue.
	 * @return bool Always returns true
	 */
	public function execute()
	{
		global $pmxcFunc, $context, $sourcedir;

		if(!defined('is_sheduled_task'))
			define('is_sheduled_task', true);

		// Figure out if the user wants to be notified.
		require_once($sourcedir . '/Subs-Notify.php');
		$prefs = getNotifyPrefs($this->_details['receiver_id'], 'buddy_request', true);

		if ($prefs[$this->_details['receiver_id']]['buddy_request'])
		{
			$alert_row = array(
				'alert_time' => $this->_details['time'],
				'id_member' => $this->_details['receiver_id'],
				'id_member_started' => $this->_details['id_member'],
				'member_name' => $this->_details['member_name'],
				'content_type' => 'buddy',
				'content_id' => 0,
				'content_action' => 'buddy_request',
				'is_read' => 0,
				'extra' => '',
			);

			$pmxcFunc['db_insert']('insert', '{db_prefix}user_alerts',
				array('alert_time' => 'int', 'id_member' => 'int', 'id_member_started' => 'int', 'member_name' => 'string',
				'content_type' => 'string', 'content_id' => 'int', 'content_action' => 'string', 'is_read' => 'int', 'extra' => 'string'),
				$alert_row, array()
			);

			updateMemberData($this->_details['receiver_id'], array('alerts' => '+'));
		}

		return true;
	}
}
?>