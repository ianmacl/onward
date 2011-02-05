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
 * @subpackage	joomla15_content_frontpage
 */
class plgOnwardJoomla15_Content_Frontpage extends OnwardImporterAdapter
{
	/**
	 * The context is the name of the entity that the plugin imports. This should
	 * unique and will generally follow the table and might include version.
	 * For example, it might be com_content_1_5
	 *
	 * @var		string		The plugin identifier.
	 */
	protected $context = 'jos_content_frontpage';

	protected function getDependencies()
	{
		return array('jos_content');
	}

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
		$sql->from('#__content_frontpage AS a');

		return $sql;
	}

	/**
	 * Method to import an item.
	 *
	 * @param	object		The item to import.
	 * @return	boolean		True on success.
	 * @throws	Exception on database error.
	 */
	protected function import($oldContentFrontpage)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_content/tables');
		$contentFrontpageObject = JTable::getInstance('Featured', 'ContentTable');
		
		$contentFrontpageObject->content_id = OnwardImporter::getMappedId('jos_content_frontpage', $oldContentFrontpage->content_id);
		$contentFrontpageObject->ordering = $oldContentFrontpage->ordering;

		$result = $contentFrontpageObject->store();

		$contentObject = JTable::getInstance('Content');

		$contentObject->load($contentFrontpageObject->content_id);
		$contentObject->featured = 1;
		$contentObject->store();

		if ($result) {
			OnwardImporter::map($this->context, $oldContentFrontpage->id, $contentFrontpageObject->id);
			return true;
		} else {
			return false;
		}
		
	}
}
