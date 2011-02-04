<?php
/**
 * @version		$Id $
 * @package		Onward
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

// Register dependent classes.
JLoader::register('OnwardImporter', JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'importer'.DS.'importer.php');
JLoader::register('OnwardHelperSite', JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'site.php');
JLoader::register('OnwardImporterAdapter', JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers/importer/adapter.php');

/**
 * Indexer controller class for Finder.
 *
 * @package		JXtended.Finder
 * @subpackage	com_finder
 */
class OnwardControllerSite extends JControllerForm
{
	
	protected function allowEdit($data = array(), $key = 'id')
	{
		return true;
	}	
	
	/**
	 * Method to start the importer.
	 *
	 * @return	void
	 */
	public function scan()
	{
		// Check for a valid token. If invalid, die.
		JRequest::checkToken('request') or die('Invalid Token');

		$data = JRequest::getVar('jform', array(), 'post', 'array');

		$model = $this->getModel('site');

		$model->scan($data);
		
		$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.'&layout=edit&id='.$data['id'], false));
	}
}
