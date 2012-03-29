<?php
/**
 * @version		$Id $
 * @package		Onward
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit a site.
 *
 * @package		Onward
 * @subpackage	com_onward
 */
class OnwardViewSite extends JView
{
	protected $item;
	protected $state;
	protected $importState;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		// Initialiase variables.
		$this->item		= $this->get('Item');
		$this->state	= $this->get('State');
		$this->importState = $this->get('importState');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{
		JRequest::setVar('hidemainmenu', true);


		$state	= $this->get('State');
		$canDo	= OnwardHelper::getActions();
		$user	= JFactory::getUser();

		JToolBarHelper::title(JText::_('COM_ONWARD_MANAGER_SITE_VIEW'), 'onward.png');
		if ($canDo->get('core.admin')) {
			JToolBarHelper::addNew('site.batch', 'JTOOLBAR_BATCH');
			JToolBarHelper::addNew('site.scan', 'JTOOLBAR_SCAN');
			JToolBarHelper::cancel('site.cancel','JTOOLBAR_CANCEL');
		}

		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_COMPONENTS_ONWARD_SITE_VIEW');
	}
}

