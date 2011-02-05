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
 * @package		Onward
 * @subpackage	com_onward
 */
abstract class OnwardImporterAdapter extends JPlugin
{
	/**
	 * The context is the name of the entity that the plugin imports. This should
	 * unique and will generally follow the table and might include version.
	 * For example, it might be com_content_1_5
	 *
	 * @var		string		The plugin identifier.
	 */
	protected $context;

	protected $site_id;

	/**
	 * Method to get the adapter state and push it into the importer.
	 *
	 * @return	boolean		True on success.
	 * @throws	Exception on error.
	 */
	public function onStartImport()
	{
		// Get the number of data items.
		$this->site_id = OnwardImporter::$site_id;

		$total = (int)$this->getContentCount();
		$table = JTable::getInstance('SiteState', 'OnwardTable');
		if ($table->load(array('site_id' => $this->site_id, 'asset' => $this->context))) {
			$table->total = $total;
			$table->offset = 0;
		} else {
			$table->site_id = $this->site_id;
			$table->asset = $this->context;
			$table->total = $total;
			$table->offset = 0;
		}
		
		if (!$table->store()) {
			// error
		}
		
		OnwardImporter::$state[$this->context]->total = $total;
		OnwardImporter::$state[$this->context]->offset = 0;
	}

	/**
	 * Method to get the other items that this item depends on. 
	 * Default is an empty array, but if we depend on other items (i.e.
	 * articles might depend on categories), we need to specify that so
	 * we know we're ready.
	 *
	 * @return	array		Items that this item depends on.
	 * @throws	Exception on error.
	 */
	protected function getDependencies()
	{
		return array();
	}

	/**
	 * Method to check if the dependencies for this item have been resolved
	 */
	protected function checkDependencies()
	{
		$dependencies_met = true;
		
		$dependencies = $this->getDependencies();
		
		// Check to see if we are ready to process these items
		foreach ($dependencies AS $dependency)
		{
			if (OnwardImporter::$state[$dependency]->total != OnwardImporter::$state[$dependency]->offset) {
				$dependencies_met = false;
			}
		}
		return $dependencies_met;
	}

	/**
	 * Method to index a batch of content items. This method can be called by
	 * the indexer many times throughout the indexing process depending on how
	 * much content is available for indexing. It is important to track the
	 * progress correctly so we can display it to the user.
	 *
	 * @return	boolean		True on success.
	 * @throws	Exception on error.
	 */
	public function onImport()
	{
		// we don't start importing until all of our dependencies have been met
		if (!$this->checkDependencies()) {
			return true;
		}
		
		$states = OnwardImporter::$state;
		
		// Get the batch offset and size.
		$offset = (int)$states[$this->context]->offset;
		$total = (int)$states[$this->context]->total;

		if ($offset == $total) {
			return true;
		}

		// Get the content items to import.
		$items = $this->getItems($offset, 20);

		// Iterate through the items and import them.
		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			// Index the item.
			$this->import($items[$i]);
			// Adjust the offsets.
			$offset++;
		}

		// Update the indexer state.
		$states[$this->context]->offset = $offset;

		return true;
	}

	/**
	 * Method to index an item.
	 *
	 * @param	object		The item to index as an FinderIndexerResult object.
	 * @return	boolean		True on success.
	 * @throws	Exception on database error.
	 */
	abstract protected function import($item);

	/**
	 * Method to get the number of content items available to index.
	 *
	 * @return	integer		The number of content items available to index.
	 * @throws	Exception on database error.
	 */
	protected function getContentCount()
	{
		$source_db = OnwardImporter::getSourceDatabase();
		
		$return = 0;

		// Get the list query.
		$sql = $this->getListQuery();

		// Check if the query is valid.
		if (empty($sql)) {
			return $return;
		}

		// Tweak the SQL query to make the total lookup faster.
		if ($sql instanceof JDatabaseQuery) {
			$sql = clone($sql);
			$sql->clear('select');
			$sql->select('COUNT(*)');
			$sql->clear('order');
		}

		// Get the total number of content items to import.
		$source_db->setQuery($sql);
		$return = (int)$source_db->loadResult();

		// Check for a database error.
		if ($source_db->getErrorNum()) {
			// Throw database error exception.
			throw new Exception($source_db->getErrorMsg(), 500);
		}

		return $return;
	}

	/**
	 * Method to get a content item to import.
	 *
	 * @param	integer		The id of the content item.
	 * @return	object		A OnwardImporterResult object.
	 * @throws	Exception on database error.
	 */
	protected function getItem($id)
	{
		// Get the list query and add the extra WHERE clause.
		$sql = $this->getListQuery();
		$sql->where('a.id = '.(int)$id);

		// Get the item to index.
		$this->_db->setQuery($sql);
		$row = $this->_db->loadAssoc();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			// Throw database error exception.
			throw new Exception($this->_db->getErrorMsg(), 500);
		}

		// Convert the item to a result object.
		$item = JArrayHelper::toObject($row, 'OnwardImporterResult');

		return $item;
	}

	/**
	 * Method to get a list of content items to import.
	 *
	 * @param	integer		The list offset.
	 * @param	integer		The list limit.
	 * @return	array		An array of OnwardImporterResult objects.
	 * @throws	Exception on database error.
	 */
	protected function getItems($offset, $limit)
	{
		$items = array();
		$source_db = OnwardImporter::getSourceDatabase();

		// Get the content items to index.
		$source_db->setQuery($this->getListQuery(), $offset, $limit);
		$rows = $source_db->loadObjectList();

		// Check for a database error.
		if ($source_db->getErrorNum()) {
			// Throw database error exception.
			throw new Exception($source_db->getErrorMsg(), 500);
		}

		return $rows;
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

		return $sql;
	}

}
