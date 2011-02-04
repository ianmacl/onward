<?php
/**
 * @version		$Id $
 * @package		Onward
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Prototype adapter class for the Onward Importer package.
 *
 * @package	Onward	
 * @subpackage	joomla15_users
 */
class plgOnwardJoomla15_Users extends OnwardImporterAdapter
{
	/**
	 * The context is the name of the entity that the plugin imports. This should
	 * unique and will generally follow the table and might include version.
	 * For example, it might be com_content_1_5
	 *
	 * @var		string		The plugin identifier.
	 */
	protected $context = 'jos_users';

	/**
	 * Method to get the SQL query used to retrieve the list of content items.
	 *
	 * @param	mixed		A JDatabaseQuery object or null.
	 * @return	object		A JDatabaseQuery object.
	 */
	protected function getListQuery($sql = null)
	{
		// Check if we can use the supplied SQL query.
		$sql = is_a($sql, 'JDatabaseQuery') ? $sql : new JDatabaseQuery();
		$sql->select('a.*');
		$sql->from('#__users AS a');

		return $sql;
	}

	/**
	 * Method to import an item.
	 *
	 * @param	object		The item to import.
	 * @return	boolean		True on success.
	 * @throws	Exception on database error.
	 */
	protected function import($oldUser)
	{
		$db = JFactory::getDBO();
		
		//$userObject = JUser::getInstance(0);
		$userObject = JTable::getInstance('user');

		$userObject->id = 0;
		$userObject->name = $oldUser->name;
		$userObject->username = $oldUser->username;
		$userObject->email = $oldUser->email;
		$userObject->password = $oldUser->password;
		$userObject->usertype = 'deprecated';
		$userObject->block = $oldUser->block;
		$userObject->sendEmail = $oldUser->sendEmail;
		$userObject->registerDate = $oldUser->registerDate;
		$userObject->lastVisitDate = $oldUser->lastvisitDate;
		$userObject->activation = $oldUser->activation;
		$userObject->params = $oldUser->params;

		$result = $userObject->store();

		if ($result) {
			$newId = $userObject->id;
			$db->setQuery('INSERT INTO #__onward_data_map (site_id, asset, original_id, new_id) VALUES (13, \'jos_users\', '.$oldUser->id.', '.$userObject->id.')');
			$db->query();
		}
	}
}
