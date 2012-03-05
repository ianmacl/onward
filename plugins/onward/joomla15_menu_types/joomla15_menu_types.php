<?php
/**
 * @version		$Id $
 * @package		Onward
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla 1.5 Sections Importer Plugin
 *
 * @package		Onward
 * @subpackage	jos_sections
 */
class plgOnwardJoomla15_Menu_Types extends OnwardImporterAdapter
{
	/**
	 * The context is the name of the entity that the plugin imports. This should
	 * unique and will generally follow the table and might include version.
	 * For example, it might be com_content_1_5
	 *
	 * @var		string		The plugin identifier.
	 */
	protected $context = 'jos_menu_types';

	/**
	 * Method to get the SQL query used to retrieve the list of content items.
	 *
	 * @param	mixed		A JDatabaseQuery object or null.
	 * @return	object		A JDatabaseQuery object.
	 */
	protected function getListQuery($sql = null)
	{
		// Check if we can use the supplied SQL query.
		$sql = is_a($sql, 'JDatabaseQuery') ? $sql : JFactory::getDbo()->getQuery(true);
		$sql->select('a.*');
		$sql->from('#__menu_types AS a');

		return $sql;
	}

	/**
	 * Method to import an item.
	 *
	 * @param	object		The item to import.
	 * @return	boolean		True on success.
	 * @throws	Exception on database error.
	 */
	protected function import($oldMenutype)
	{

		$mtObject = JTable::getInstance('menutype');

		$mtObject->id = 0;
		$mtObject->menutype = $oldMenutype->menutype;
		$mtObject->title = $oldMenutype->title;
		$mtObject->description = $oldMenutype->description;

		$result = $mtObject->store();

		if ($result) {
			OnwardImporter::map($this->context, (int)$oldMenutype->id, $mtObject->id);
			return true;
		} else {
			return false;
		}
	}
}
