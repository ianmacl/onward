<?php
/**
 * @version		$Id $
 * @package		Onward
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2; see LICENSE.txt
 */

defined('_JEXEC') or die;

class OnwardHelperSite
{
	public static function getDbo($site_id = null)
	{
		static $db;
		
		if (isset($db)) {
			return $db;
		}
		
		$siteData = JFactory::getApplication()->getUserState('com_onward.import.site_data.'.(int)$site_id, null);

		if (!is_null($siteData) && $siteData['host']) {
			$db = JDatabase::getInstance($siteData);
			return $db;
		}
		$table = JTable::getInstance('Site', 'OnwardTable');

		$table->load($site_id);

		$location = $table->location;

		$configFile = file_get_contents($location.'/configuration.php');
		
		$matches = array();
		if (!preg_match('#var\s+\$user\s*=\s*[\']([^\']+)[\']\s*;#', $configFile, $matches))
		{
			// throw error	
		}
		$user = $matches[1];

		$matches = array();
		if (!preg_match('#var\s+\$dbtype\s*=\s*[\']([^\']+)[\']\s*;#', $configFile, $matches))
		{
			// throw error	
		}
		$dbtype = $matches[1];

		$matches = array();
		if (!preg_match('#var\s+\$host\s*=\s*[\']([^\']+)[\']\s*;#', $configFile, $matches))
		{
			// throw error
		}
		$host = $matches[1];

		$matches = array();
		if (!preg_match('#var\s+\$password\s*=\s*[\']([^\']+)[\']\s*;#', $configFile, $matches))
		{
			// throw error
		}
		$password = $matches[1];

		$matches = array();
		if (!preg_match('#var\s+\$db\s*=\s*[\']([^\']+)[\']\s*;#', $configFile, $matches))
		{
			// throw error
		}
		$database = $matches[1];

		$matches = array();
		if (!preg_match('#var\s+\$dbprefix\s*=\s*[\']([^\']+)[\']\s*;#', $configFile, $matches))
		{
			// throw error
		}
		$dbprefix = $matches[1];

		$option = array(); //prevent problems
 
		$siteData = array();
		$siteData['site_id'] 	= $site_id;
		$siteData['user'] 		= $user;
		$siteData['host'] 		= $host;
		$siteData['password'] 	= $password;
		$siteData['database'] 	= $database;
		$siteData['prefix'] 	= $dbprefix;
		$siteData['driver']		= $dbtype;

		JFactory::getApplication()->setUserState('com_onward.import.site_data', $siteData);

		$db = JDatabase::getInstance($siteData);
		return $db;
	}
}
