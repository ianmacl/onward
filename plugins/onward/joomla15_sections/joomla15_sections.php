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
 * @package		JXtended.Finder
 * @subpackage	com_finder
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
	protected $_context;

	/**
	 * @var		object		The database object.
	 */
	protected $sourceDatabase;
	
	/**
	 * @var		object		The database object.
	 */
	protected $targetDatabase;
	

	/**
	 * Method to get the adapter state and push it into the importer.
	 *
	 * @return	boolean		True on success.
	 * @throws	Exception on error.
	 */
	public function onStartImport($site_id, $source_db)
	{
		// Get the number of data items.
		//$total	= (int)$this->_getContentCount();

		$query = 'SELECT COUNT(*) FROM #__sections';
		$source_db->setQuery($query);	
		$total = $source_db->loadResult();
		$tables = $source_db->getTableList();

		//echo $source_db->name;
		echo $total;
		$table = JTable::getInstance('SiteState', 'OnwardTable');
		$table->site_id = $site_id;
		$table->asset = 'jos_sections';
		$table->total = $total;
		$table->limit = 20;
		$table->offset = 0;
		$table->store();
	}

	/**
	 * Method to prepare for the importer to be run. This method will often
	 * be used to include dependencies and things of that nature.
	 *
	 * @return	boolean		True on success.
	 * @throws	Exception on error.
	 */
	public function onBeforeIndex()
	{
		// Get the indexer and adapter state.
		$iState	= OnwardImporter::getState();
		$aState	= $iState->pluginState[$this->_context];

		// Check the progress of the indexer and the adapter.
		if ($iState->batchOffset == $iState->batchSize || $aState['offset'] == $aState['total']) {
			return true;
		}

		// Run the setup method.
		return $this->_setup();
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

		return true;
	}

	/**
	 * Method to get the number of content items available to index.
	 *
	 * @return	integer		The number of content items available to index.
	 * @throws	Exception on database error.
	 */
	protected function _getContentCount()
	{
		$return = 0;

		// Get the list query.
		$sql = $this->_getListQuery();

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

		// Get the total number of content items to index.
		$this->sourceDatabase->setQuery($sql);
		$return = (int)$this->sourceDatabase->loadResult();

		// Check for a database error.
		if ($this->sourceDatabase->getErrorNum()) {
			// Throw database error exception.
			throw new Exception($this->sourceDatabase->getErrorMsg(), 500);
		}

		return $return;
	}

	/**
	 * Method to get a content item to index.
	 *
	 * @param	integer		The id of the content item.
	 * @return	object		A FinderIndexerResult object.
	 * @throws	Exception on database error.
	 */
	protected function _getItem($id)
	{
		// Get the list query and add the extra WHERE clause.
		$sql = $this->_getListQuery();
		$sql->where('a.id = '.(int)$id);

		// Get the item to index.
		$this->sourceDatabase->setQuery($sql);
		$row = $this->sourceDatabase->loadAssoc();

		// Check for a database error.
		if ($this->sourceDatabase->getErrorNum()) {
			// Throw database error exception.
			throw new Exception($this->sourceDatabase->getErrorMsg(), 500);
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
	protected function _getItems($offset, $limit, $sql = null)
	{
		$items = array();

		// Get the content items to index.
		$this->sourceDatabase->setQuery($this->_getListQuery($sql), $offset, $limit);
		$rows = $this->sourceDatabase->loadAssocList();

		// Check for a database error.
		if ($this->sourceDatabase->getErrorNum()) {
			// Throw database error exception.
			throw new Exception($this->sourceDatabase->getErrorMsg(), 500);
		}

		// Convert the items to result objects.
		foreach ($rows as $row)
		{
			// Convert the item to a result object.
			$item = JArrayHelper::toObject($row, 'OnwardImporterResult');

			// Set the item type.
			$item->type_id	= $this->_type_id;

			// Set the mime type.
			$item->mime		= $this->_mime;

			// Set the item layout.
			$item->layout	= $this->_layout;

			// Add the item to the stack.
			$items[] = $item;
		}

		return $items;
	}

	/**
	 * Method to get the SQL query used to retrieve the list of content items.
	 *
	 * @param	mixed		A JDatabaseQuery object or null.
	 * @return	object		A JDatabaseQuery object.
	 */
	protected function _getListQuery($sql = null)
	{
		// Check if we can use the supplied SQL query.
		$sql = is_a($sql, 'JDatabaseQuery') ? $sql : new JDatabaseQuery();
		$sql->select('a.*');
		$sql->from('#__users AS a');
		//$sql->from('#__users AS a');

		return $sql;
	}

	/**
	 * Method to index an item.
	 *
	 * @param	object		The item to index as an FinderIndexerResult object.
	 * @return	boolean		True on success.
	 * @throws	Exception on database error.
	 */
	protected function _import($item)
	{
			return true;
	}

	/**
	 * Method to setup the adapter before indexing.
	 *
	 * @return	boolean		True on success, false on failure.
	 * @throws	Exception on database error.
	 */
	protected function _setup()
	{
		return true;
	}


}
