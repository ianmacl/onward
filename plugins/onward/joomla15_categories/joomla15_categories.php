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
class plgOnwardJoomla15_Categories extends OnwardImporterAdapter
{
	/**
	 * The context is the name of the entity that the plugin imports. This should
	 * unique and will generally follow the table and might include version.
	 * For example, it might be com_content_1_5
	 *
	 * @var		string		The plugin identifier.
	 */
	protected $context = 'jos_categories';

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
		$sql->from('#__categories AS a');
		$sql->where('0 + a.section > 0');

		return $sql;
	}

	protected function getDependencies()
	{
		return array('jos_sections');
	}

	/**
	 * Method to import an item.
	 *
	 * @param	object		The item to import.
	 * @return	boolean		True on success.
	 * @throws	Exception on database error.
	 */
	protected function import($oldCategory)
	{
		$catObject = JTable::getInstance('category');

		$parent = OnwardImporter::getMappedId('jos_sections', $oldCategory->section);

		$parentCategory = JTable::getInstance('category');
		$parentCategory->load($parent);
		//print_r($parentCategory);
		$catObject->id = 0;
		$catObject->parent_id = $parent;
		$catObject->setLocation($parent, 'last-child');
		$catObject->level = 2;
		$catObject->path = $parentCategory->path.'/'.$oldCategory->alias;
		$catObject->extension = 'com_content';
		$catObject->title = $oldCategory->title;
		$catObject->alias = $oldCategory->alias;
		$catObject->description = $oldCategory->description;
		$catObject->published = $oldCategory->published;
		$catObject->checked_out = 0;
		$catObject->checked_out_time = '0000-00-00 00:00:00';
		$catObject->access = $oldCategory->access + 1;  // TODO figure out mapping
		$catObject->params = $oldCategory->params;
		$catObject->metadesc = '';
		$catObject->metakey = '';
		$catObject->metadata = '';
		$catObject->created_user_id = 0;
		$catObject->created_time = '0000-00-00 00:00:00';
		$catObject->modified_user_id = 0;
		$catObject->modified_time = '0000-00-00 00:00:00';
		$catObject->hits = $oldCategory->count; 	// TODO check what count does
		$catObject->language = '*';
		// TODO - deal with ordering

		$result = $catObject->store();

		if ($result) {
			OnwardImporter::map($this->context, $oldCategory->id, $catObject->id);
			return true;
		} else {
			return false;
		}
	}
}
