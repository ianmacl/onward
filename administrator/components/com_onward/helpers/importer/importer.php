<?php
/**
 * @version		$Id $
 * @package		Onward
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Main importer class for Onward.
 *
 * @package		Onward
 */
class OnwardImporter
{
	/**
	 * @var		object		The importer state object.
	 */
	public static $state;

	/**
	 * @var		integer		The site id
	 */
	public static $site_id;

	/**
	 * @var		object		The importer profiler object.
	 */
	public static $profiler;

	/**
	 * @var		object		The state JTable object
	 */
	protected static $stateTable;

	/**
	 * @var		object		The map JTable object
	 */
	protected static $mapTable;

	/**
	 * @var		object		The source database object
	 */
	protected static $sourceDatabase;

	/**
	 * @var		object		The target database object
	 */
	protected static $targetDatabase;

	/**
	 * Method to get the importer state.
	 *
	 * @return	object		The importer state object.
	 */
	public static function getState()
	{
		// First, try to load from the internal state.
		if (!empty(self::$state)) {
			return self::$state;
		}

		// If we couldn't load from the internal state, get it from the DB
		$query = 'SELECT * FROM #__onward_site_state WHERE site_id = '.(int)self::$site_id;
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$data = $db->loadObjectList('asset');

		// Setup the profiler if debugging is enabled.
		if (JFactory::getApplication()->getCfg('debug')) {
			jimport('joomla.error.profiler');
			self::$profiler = JProfiler::getInstance('OnwardImporter');
		}

		// Set the state.
		self::$state = $data;

		return self::$state;
	}

	/**
	 * Method to set the importer state.
	 *
	 * @param	array		A new importer state object.
	 * @return	boolean		True on success, false on failure.
	 */
	public static function setState($data)
	{
		// Check the state object.
		if (empty($data) || !is_a($data, 'JObject')) {
			return false;
		}

		if (!isset(self::$stateTable)) {
			self::$stateTable = JTable::getInstance('SiteState', 'OnwardTable');
		}

		// Set the new internal state.
		self::$state[$data->asset] = $data;

		self::$stateTable->load($data->id);

		self::$stateTable->bind($data);

		self::$stateTable->store();

		return true;
	}

	public static function getTargetDbo()
	{
		return self::$targetDatabase;
	}
	
	public static function getSourceDbo()
	{
		print_r(self::$sourceDatabase);
		self::$sourceDatabase->test();
		return self::$sourceDatabase;
	}
	
	public static function setTargetDbo($db)
	{
		self::$targetDatabase = $db;
	}
	
	public static function setSourceDbo($db)
	{

		self::$sourceDatabase = $db;
		print_r(self::$sourceDatabase);
		self::$sourceDatabase->test();


	}

	/**
	 * Method to reset the importer state.
	 *
	 * @return	void
	 */
	public static function resetState()
	{
		// Reset the internal state to null.
		self::$state = null;

		$db = JFactory::getDBO();
		
		$query = 'DELETE FROM #__onward_site_state WHERE site_id = '.(int)self::$site_id;
		$db->setQuery($query);
		$db->query();
	}

	public static function map($data)
	{
		if (!isset(self::$mapTable)) {
			self::$mapTable = JTable::getInstance('DataMap', 'OnwardTable');
		}
		
		$mapper = self::$mapTable;
		$mapper->site_id = self::$site_id;
		$mapper->asset = $data->asset;
		$mapper->original_id = $data->original_id;
		$mapper->new_id = $data->new_id;
		$mapper->store();
	}
	
	public static function getMappedId($asset, $original_id)
	{
		$db = JFactory::getDbo();
		$query = 'SELECT new_id FROM #__onward_data_map WHERE original_id = '.(int)$original_id.' AND site_id = '.(int)self::$site_id.' AND asset = \''.$asset.'\'';
		$db->setQuery($query);
		$id = $db->loadResult();
		return $id;
	}

	/**
	 * Method to import a data item.
	 *
	 * @param	object		The data item to import.
	 * @return	boolean		True on success.
	 * @throws	Exception on database error.
	 */
	public static function import($item)
	{
		// Mark beforeIndexing in the profiler.
		self::$profiler ? self::$profiler->mark('beforeImporting') : null;

		$db	= JFactory::getDBO();
		$nd = $db->getNullDate();


		// Mark afterUnmapping in the profiler.
		self::$profiler ? self::$profiler->mark('afterUnmapping') : null;

		return true;
	}

}
