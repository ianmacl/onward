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
class plgOnwardJoomla15_Users extends OnwardImporterAdapter
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

		$query = 'SELECT COUNT(*) FROM #__users';
		$source_db->setQuery($query);
		$total = $source_db->loadResult();
		$table = JTable::getInstance('SiteState', 'OnwardTable');
		$table->site_id = $site_id;
		$table->asset = 'jos_users';
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
	public function onImport($site_id, $source_db, &$states)
	{

		$dependencies = $this->getDependencies();
		
		// Check to see if we are ready to process these items
		foreach ($dependencies AS $dependency)
		{
			if ($states[$dependency]['limit'] != $states[$dependency]['offset']) {
				return true;
			}
		}
		
		// Get the batch offset and size.
		$offset = (int)$states['jos_users']->offset;
		$total = (int)$states['jos_users']->total;

		// Get the content items to import.
		$items = $this->_getItems($source_db, $offset, 20);

		// Iterate through the items and index them.
		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			// Index the item.
			$this->_import($items[$i]);
			// Adjust the offsets.
			$offset++;
		}

		// Update the indexer state.
		$states['jos_users']->offset = $offset;

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
	protected function _getItem($source_db, $offset, $limit)
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
	protected function _getItems($source_db, $offset, $limit)
	{
		$items = array();

		// Get the content items to index.
		$source_db->setQuery($this->_getListQuery(), $offset, $limit);
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
	protected function _getListQuery($sql = null)
	{
		// Check if we can use the supplied SQL query.
		$sql = is_a($sql, 'JDatabaseQuery') ? $sql : new JDatabaseQuery();
		$sql->select('a.*');
		$sql->from('#__users AS a');
		
		return $sql;
	}

	/**
	 * Method to index an item.
	 *
	 * @param	object		The item to import.
	 * @return	boolean		True on success.
	 * @throws	Exception on database error.
	 */
	protected function _import($oldUser)
	{
		$db = JFactory::getDBO();
		
		//$userObject = JUser::getInstance(0);
		$userObject = JTable::getInstance('user');

		$userObject->id = 0;
		$userObject->name = $oldUser->name;
		$userObject->username = $oldUser->username;
		$userObject->email = $oldUser->email;
		$userObject->password = $oldUser->password;
		$userObject->password_clear = '';
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
		//$processedUsers[] = $oldUser->id;

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
