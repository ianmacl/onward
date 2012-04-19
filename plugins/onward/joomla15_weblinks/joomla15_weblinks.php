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
 * @subpackage	joomla15weblinks
 */
class plgOnwardJoomla15_Weblinks extends OnwardImporterAdapter
{
	/**
	 * The context is the name of the entity that the plugin imports. This should
	 * unique and will generally follow the table and might include version.
	 * For example, it mightables be com_content_1_5
	 *
	 * @var		string		The plugin identifier.
	 */
	protected $context = 'jos_weblinks';

	protected function getDependencies()
	{
		return array('jos_categories', 'jos_users');
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
		$sql = is_a($sql, 'JDatabaseQuery') ? $sql : JFactory::getDbo()->getQuery(true);
		$sql->select('a.*');
		$sql->from('#__weblinks AS a');

		return $sql;
	}

	protected function getUncategorisedId()
	{
		$db = JFactory::getDBO();
		$db->setQuery('SELECT id FROM #__categories WHERE path = \'uncategorised\' AND extension = \'com_weblinks\'');
		$result = $db->loadResult();
	}


	/**
	 * Method to import an item.
	 *
	 * @param	object		The item to import.
	 * @return	boolean		True on success.
	 * @throws	Exception on database error.
	 */
	protected function import($oldWeblinks)
	{
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_weblinks/tables');
		$weblinksObject= JTable::getInstance('weblink', 'WeblinksTable');

		$category = OnwardImporter::getMappedId('jos_categories', $oldWeblinks->catid);
//var_dump($weblinksObject);die;
		$weblinksObject->id = 0;
		$weblinksObject->title = $oldWeblinks->title;
		$weblinksObject->alias = $oldWeblinks->alias;
		$weblinksObject->description = $oldWeblinks->description;
		//$weblinksObject->state = $oldWeblinks->published;
		$weblinksObject->url = $oldWeblinks->url;
		$weblinksObject->sid = 0;
		$weblinksObject->catid = $category;
		$weblinksObject->created = '0000-00-00 00:00:00';
		$weblinksObject->created_by = '';
		$weblinksObject->created_by_alias = '';
		$weblinksObject->modified = '0000-00-00 00:00:00';
		$weblinksObject->modified_by = '';
		$weblinksObject->checked_out_time = '0000-00-00 00:00:00';
		$weblinksObject->checked_out = 0;
		$weblinksObject->publish_up = '0000-00-00 00:00:00';
		$weblinksObject->publish_down = '0000-00-00 00:00:00';
		$weblinksObject->params = $oldWeblinks->params;
		$weblinksObject->version = $oldWeblinks->version;
		$weblinksObject->ordering = '';
		$weblinksObject->metakey = null;
		$weblinksObject->metadesc = null;
		$weblinksObject->access = $oldWeblinks->access + 1;  //  TODO Map access
		$weblinksObject->hits = $oldWeblinks->hits;
		$weblinksObject->metadata = null;
		$weblinksObject->archived = $oldWeblinks->archived;
		$weblinksObject->checked_out_time = '0000-00-00 00:00:00';
		$weblinksObject->checked_out = 0;
		$weblinksObject->publish_up = '0000-00-00 00:00:00';
		$weblinksObject->publish_down = '0000-00-00 00:00:00';
		$weblinksObject->params = $oldWeblinks->params;
		$weblinksObject->version = $oldWeblinks->version;
		$weblinksObject->ordering = '';
		$weblinksObject->access = $oldWeblinks->access + 1;  //  TODO Map access
		$weblinksObject->hits = $oldWeblinks->hits;
		$weblinksObject->archived = $oldWeblinks->archived;
		$weblinksObject->approved = $oldWeblinks->approved;
		$weblinksObject->featured = 0;
		$weblinksObject->language = '*';
		$weblinksObject->xreference = '';


		$result = $weblinksObject->store();

		if ($result) {
			OnwardImporter::map($this->context, $oldWeblinks->id, $weblinksObject->id);
			return true;
		} else {
			return false;
		}

	}
}
