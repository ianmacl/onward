<?php
/**
 * @version		$Id: adapter.php 984 2010-06-22 00:55:25Z robs $
 * @package		JXtended.Finder
 * @subpackage	com_finder
 * @copyright	Copyright (C) 2007 - 2010 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @link		http://jxtended.com
 */

defined('_JEXEC') or die;

// Register dependent classes.
//JLoader::register('FinderIndexer', dirname(__FILE__).DS.'indexer.php');
//JLoader::register('FinderIndexerHelper', dirname(__FILE__).DS.'helper.php');
//JLoader::register('FinderIndexerQueue', dirname(__FILE__).DS.'queue.php');
//JLoader::register('FinderIndexerResult', dirname(__FILE__).DS.'result.php');
//JLoader::register('FinderIndexerTaxonomy', dirname(__FILE__).DS.'taxonomy.php');

/**
 * Prototype adapter class for the Onward Importer package.
 *
 * @package		JXtended.Finder
 * @subpackage	com_finder
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
	protected $_context;

	/**
	 * @var		object		The database object.
	 */
	protected $_db;

	/**
	 * Method to instantiate the indexer adapter.
	 *
	 * @param	object		The object to observe.
	 * @param	array		An array that holds the plugin configuration.
	 * @return	void
	 */
	public function __construct(&$subject, $config)
	{
		// the database needs to be configurable so that we can import from
		// other databases than the one we're currently working with.
		if (isset($config['dbo']))
		{
			$this->_db = $config['dbo'];
		}
		else
		{
			// Get the database object.
			$this->_db = JFactory::getDBO();
		}
		
		// Call the parent constructor.
		parent::__construct($subject, $config);
	}

	/**
	 * Method to get the adapter state and push it into the importer.
	 *
	 * @return	boolean		True on success.
	 * @throws	Exception on error.
	 */
	public function onStartIndex()
	{
		// Get the indexer state.
		$iState	= OnwardImporter::getState();

		// Get the number of data items.
		$total	= (int)$this->_getContentCount();

		// Add the content count to the total number of items.
		$iState->totalItems += $total;

		// Populate the indexer state information for the adapter.
		$iState->pluginState[$this->_context]['total']	= $total;
		$iState->pluginState[$this->_context]['offset']	= 0;

		// Set the indexer state.
		OnwardImporter::setState($iState);
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
		// Get the importer and adapter state.
		$iState	= OnwardImporter::getState();
		$aState	= $iState->pluginState[$this->_context];

		$dependencies = $this->getDependencies();
		
		// Check to see if we are ready to process these items
		foreach ($dependencies AS $dependency)
		{
			if (!$iState[$dependency]['complete'])
			{
				return true;
			}
		}
		
		// Check the progress of the indexer and the adapter.
		if ($iState->batchOffset == $iState->batchSize || $aState['offset'] == $aState['total']) {
			return true;
		}

		// Get the batch offset and size.
		$offset	= (int)$aState['offset'];
		$limit	= (int)($iState->batchSize - $iState->batchOffset);

		// Get the content items to import.
		$items = $this->_getItems($offset, $limit);

		// Iterate through the items and index them.
		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			// Index the item.
			$this->_import($items[$i]);

			// Adjust the offsets.
			$offset++;
			$iState->batchOffset++;
			$iState->totalItems--;
		}

		// Update the indexer state.
		$aState['offset'] = $offset;
		$iState->pluginState[$this->_context] = $aState;
		OnwardImporter::setState($iState);

		return true;
	}

	/**
	 * Method to index an item.
	 *
	 * @param	object		The item to index as an FinderIndexerResult object.
	 * @return	boolean		True on success.
	 * @throws	Exception on database error.
	 */
	abstract protected function _index(FinderIndexerResult $item);

	/**
	 * Method to setup the adapter before indexing.
	 *
	 * @return	boolean		True on success, false on failure.
	 * @throws	Exception on database error.
	 */
	abstract protected function _setup();

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
		$this->_db->setQuery($sql);
		$return = (int)$this->_db->loadResult();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			// Throw database error exception.
			throw new Exception($this->_db->getErrorMsg(), 500);
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
	protected function _getItems($offset, $limit, $sql = null)
	{
		$items = array();

		// Get the content items to index.
		$this->_db->setQuery($this->_getListQuery($sql), $offset, $limit);
		$rows = $this->_db->loadAssocList();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			// Throw database error exception.
			throw new Exception($this->_db->getErrorMsg(), 500);
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

		return $sql;
	}

}
