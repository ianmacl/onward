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
 * @subpackage	joomla15_content
 */
class plgOnwardJoomla15_Content extends OnwardImporterAdapter
{
	/**
	 * The context is the name of the entity that the plugin imports. This should
	 * unique and will generally follow the table and might include version.
	 * For example, it might be com_content_1_5
	 *
	 * @var		string		The plugin identifier.
	 */
	protected $context = 'jos_content';

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
		$sql->from('#__content AS a');

		return $sql;
	}

	protected function getUncategorisedId()
	{
		$db = JFactory::getDBO();
		$db->setQuery('SELECT id FROM #__categories WHERE path = \'uncategorised\' AND extension = \'com_content\'');
		$result = $db->loadResult();
	}


	/**
	 * Method to import an item.
	 *
	 * @param	object		The item to import.
	 * @return	boolean		True on success.
	 * @throws	Exception on database error.
	 */
	protected function import($oldContent)
	{
		$contentObject = JTable::getInstance('content');

		if ($oldContent->sectionid == 0 && $oldContent->catid == 0)
		{
			$category = $this->getUncategorisedId();
		}
		else
		{
			$category = OnwardImporter::getMappedId('jos_categories', $oldContent->catid);
		}

		$contentObject->id = 0;
		$contentObject->title = $oldContent->title;
		$contentObject->alias = $oldContent->alias;
		$contentObject->title_alias = $oldContent->title_alias;
		$contentObject->introtext = $oldContent->introtext;
		$contentObject->fulltext = $oldContent->fulltext;
		$contentObject->state = $oldContent->state;
		$contentObject->sectionid = 0;
		$contentObject->mask = 0;
		$contentObject->catid = $category;
		$contentObject->created = $oldContent->created;
		$contentObject->created_by = OnwardImporter::getMappedId('jos_users', $oldContent->created_by);
		$contentObject->created_by_alias = $oldContent->created_by_alias;
		$contentObject->modified = $oldContent->modified;
		$contentObject->modified_by = OnwardImporter::getMappedId('jos_users', $oldContent->modified_by);
		$contentObject->checked_out_time = '0000-00-00 00:00:00';
		$contentObject->checked_out = 0;
		$contentObject->publish_up = $oldContent->publish_up;
		$contentObject->publish_down = $oldContent->publish_down;
		$contentObject->images = ''; //$oldContent->images;
		$contentObject->urls = ''; //$oldContent->urls;
		$contentObject->attribs = $oldContent->attribs;
		$contentObject->version = $oldContent->version;
		$contentObject->parentid = 0;
		$contentObject->ordering = $oldContent->ordering;
		$contentObject->metakey = $oldContent->metakey;
		$contentObject->metadesc = $oldContent->metadesc;
		$contentObject->access = $oldContent->access + 1;  //  TODO Map access
		$contentObject->hits = $oldContent->hits;
		$contentObject->metadata = $oldContent->metadata;
		$contentObject->featured = 0;			// we will have to fix this later when we get the featured table
		$contentObject->language = '*';
		$contentObject->xreference = '';

		$result = $contentObject->store();

		if ($result) {
			OnwardImporter::map($this->context, $oldContent->id, $contentObject->id);
			return true;
		} else {
			return false;
		}

	}
}
