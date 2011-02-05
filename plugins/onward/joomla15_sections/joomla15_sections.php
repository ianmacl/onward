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
class plgOnwardJoomla15_Sections extends OnwardImporterAdapter
{
	/**
	 * The context is the name of the entity that the plugin imports. This should
	 * unique and will generally follow the table and might include version.
	 * For example, it might be com_content_1_5
	 *
	 * @var		string		The plugin identifier.
	 */
	protected $context = 'jos_sections';

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
		$sql->from('#__sections AS a');

		return $sql;
	}

	/**
	 * Method to import an item.
	 *
	 * @param	object		The item to import.
	 * @return	boolean		True on success.
	 * @throws	Exception on database error.
	 */
	protected function import($oldSection)
	{
		$catObject = JTable::getInstance('category');

		$catObject->id = 0;
		$catObject->parent_id = 1;
		$catObject->setLocation(1, 'last-child');
		$catObject->level = 1;
		$catObject->path = $oldSection->alias;
		$catObject->extension = 'com_content';
		$catObject->title = $oldSection->title;
		$catObject->alias = $oldSection->alias;
		$catObject->published = $oldSection->published;
		$catObject->note = '';
		$catObject->description = $oldSection->description;
		$catObject->checked_out = 0;
		$catObject->checked_out_time = '0000-00-00 00:00:00';
		$catObject->access = $oldSection->access + 1;  // TODO figure out mapping
		$catObject->params = $oldSection->params;
		$catObject->metadesc = '';
		$catObject->metakey = '';
		$catObject->metadata = '';
		$catObject->created_user_id = 0;
		$catObject->created_time = '0000-00-00 00:00:00';
		$catObject->modified_user_id = 0;
		$catObject->modified_time = '0000-00-00 00:00:00';
		$catObject->hits = $oldSection->count; 	// TODO check what count does
		$catObject->language = '*';

		$result = $catObject->store();

		if ($result) {
			OnwardImporter::map($this->context, $oldSection->id, $catObject->id);
			return true;
		} else {
			return false;
		}
	}
}
