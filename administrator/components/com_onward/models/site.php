<?php
/**
 * @version		$Id $
 * @package		Onward
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Site model.
 *
 * @package		Onward
 * @subpackage	com_onward
 */
class OnwardModelSite extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 */
	protected $text_prefix = 'COM_ONWARD_SITE';

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 */
	public function getTable($type = 'Site', $prefix = 'OnwardTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_onward.site', 'site', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	public function getImportState()
	{
		$id		= (int) $this->getState($this->getName().'.id');
		$query = 'SELECT * FROM #__onward_site_state WHERE site_id = '.$id;
		$this->_db->setQuery($query);
		$states = $this->_db->loadObjectList();
		return $states;
	}

	public function scan($data)
	{
		$id = (int)$data['id'];
		OnwardImporter::$site_id = $id;
		$source_db = OnwardImporter::getSourceDatabase($id);
		JPluginHelper::importPlugin('onward');

		$this->resetState($id);

		JDispatcher::getInstance()->trigger('onStartImport');
	}

	/**
	 * Method to reset the importer state.
	 *
	 * @return	void
	 */
	protected function resetState($id)
	{
		$db = JFactory::getDBO();
		$query = 'DELETE FROM #__onward_site_state WHERE site_id = '.(int)$id;
		$db->setQuery($query);
		$db->query();
	}


}
