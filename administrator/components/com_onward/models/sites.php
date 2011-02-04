<?php
/**
 * @version		$Id $
 * @package		Onward
 * @subpackage	com_onward
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of import sites.
 *
 * @package		Onward
 * @subpackage	com_onward
 */
class OnwardModelSites extends JModelList
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = JRequest::getVar('layout')) {
			$this->context .= '.'.$layout;
		}

		// List state information.
		parent::populateState('a.name', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 *
	 * @return	string		A store id.
	 */
	protected function getStoreId($id = '')
	{
		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('a.*');
		$query->from('#__onward_sites AS a');

		// Add the list ordering clause.
		$orderCol	= 'a.name';
		$orderDirn	= 'asc';

		$query->order($db->getEscaped($orderCol.' '.$orderDirn));

		return $query;
	}

	/**
	 * Discover sites eligible for importing
	 *
	 * @return	true on success
	 */
	public function discover()
	{
		$searchPath = dirname(JPATH_ROOT);

		set_time_limit(0);
		
		jimport('joomla.filesystem.folder');
		$configFiles = JFolder::files($searchPath, 'configuration\\.php', 3, true);
		
		foreach ($configFiles AS $configFile)
		{
			if (substr($configFile, -3) != 'php')
			{
				continue;
			}
			//echo $configFile;
			$location = dirname($configFile);
			$version = 'joomla15';

			$configContent = file_get_contents($configFile);
			$matches = array();
			if (!$siteMatches = preg_match('#var\s+\$sitename\s*=\s*[\']([^\']+)[\']\s*;#', $configContent, $matches))
			{		
				continue;
			}

			$name = $matches[1];
			// Create a new query object.
			$db = $this->getDbo();
			$query = $db->getQuery(true);

			// Check to see if we already have a record of the site
			$query->select('count(a.id)');
			$query->from('#__onward_sites AS a');
			$query->where('a.location = '.$db->Quote($location));

			$db->setQuery((string)$query);
			if ($db->loadResult())
			{
				continue;
			}

			$query = $db->getQuery(true);

			// Select the required fields from the table.
			$query->insert('#__onward_sites');
			$query->set('location='.$db->Quote($location));
			$query->set('version='.$db->Quote($version));
			$query->set('name='.$db->Quote($name));

			$db->setQuery((string)$query);

			if (!$db->query()) {

			}
		}
		return true;
	}


}
