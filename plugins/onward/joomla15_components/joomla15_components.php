<?php
/**
 * @version		$Id $
 * @package		Onward
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla 1.5 Sections Importer Plugin
 *
 * @package		Onward
 * @subpackage	jos_sections
 */
class plgOnwardJoomla15_Components extends OnwardImporterAdapter
{
	/**
	 * The context is the name of the entity that the plugin imports. This should
	 * unique and will generally follow the table and might include version.
	 * For example, it might be com_content_1_5
	 *
	 * @var		string		The plugin identifier.
	 */
	protected $context = 'jos_components';

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
		$sql->from('#__components AS a');
		$sql->where('parent = 0');

		return $sql;
	}

	protected function componentExists($oldComponent)
	{
		if (in_array($oldComponent->option,
				array('com_admin', 'com_banners', 'com_cache', 'com_categories', 'com_checkin', 'com_config',
					'com_contact', 'com_content', 'com_cpanel', 'com_frontpage', 'com_installer', 'com_languages',
					'com_login', 'com_massmail', 'com_media', 'com_menus', 'com_messages', 'com_modules',
					'com_newsfeeds', 'com_plugins', 'com_search', 'com_sections', 'com_templates', 'com_trash',
					'com_users', 'com_weblinks', 'com_mailto', 'com_user', 'com_wrapper')
		)) {
			return true;
		}

		$db = JFactory::getDBO();

		$sql = $db->getQuery(true);
		$sql->select('a.extension_id');
		$sql->from('#__extensions AS a');
		$sql->where('name = '.$db->quote($oldComponent->option));
		$db->setQuery($sql);

		$result = $db->loadResult();

		if ($result)
		{
			return true;
		}
	}


	/**
	 * Method to import an item.
	 *
	 * @param	object		The item to import.
	 * @return	boolean		True on success.
	 * @throws	Exception on database error.
	 */
	protected function import($oldComponent)
	{

		if ($this->componentExists($oldComponent))
		{
			return false;
		}

		$extObject = JTable::getInstance('extension');

		$extObject->extension_id = 0;
		$extObject->name = $oldComponent->option;
		$extObject->type = 'component';
		$extObject->element = $oldComponent->option;
		$extObject->folder = '';
		$extObject->client_id = 0;
		$extObject->enabled = $oldComponent->enabled;
		$extObject->access = 0;
		$extObject->protected = $oldComponent->iscore;
		$extObject->manifest_cache = '';
		$extObject->params = $oldComponent->params;
		$extObject->custom_data = '';
		$extObject->system_data = '';
		$extObject->checked_out = 0;
		$extObject->checked_out_time = '0000-00-00 00:00:00';
		$extObject->ordering = 0;
		$extObject->state = $oldComponent->enabled;

		$result = $extObject->store();

		if ($result) {
			OnwardImporter::map($this->context, (int)$oldComponent->id, $extObject->extension_id);
			$this->copyComponent($oldComponent, $extObject);
			return true;
		} else {
			echo $extObject->getError();
			return false;
		}
	}

	protected function copyComponent($old, $new)
	{
		$siteData = JFactory::getApplication()->getUserState('com_onward.import.site_data');
		$location = $siteData['location'];
		jimport('joomla.filesystem.folder');

		$component = $new->name;
		//echo $component; die();
		if (JFolder::exists(JPATH_ROOT.'/administrator/components/'.$component)) {
			return false;
		}
		if (JFolder::exists(JPATH_ROOT.'/components/'.$component)) {
			return false;
		}
		//echo $new->name;
		JFolder::copy($location.'/administrator/components/'.$component, JPATH_ROOT.'/administrator/components/'.$component);
		JFolder::copy($location.'/components/'.$component, JPATH_ROOT.'/components/'.$component);
	}
}
