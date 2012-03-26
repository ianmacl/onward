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
 * View class for a list of sites.
 *
 * @package		Onward
 * @subpackage	com_onward
 * @since		1.6
 */
class OnwardViewSites extends JView
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 *
	 * @return	void
	 */
	public function display($tpl = null)
	{
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

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
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		$state	= $this->get('State');
		$canDo	= OnwardHelper::getActions();
		$user	= JFactory::getUser();

		JToolBarHelper::title(JText::_('COM_ONWARD_MANAGER_SITES'), 'onward.png');
		if ($canDo->get('core.admin')) {
			JToolBarHelper::addNew('sites.discover', 'JTOOLBAR_DISCOVER');
		}
		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_onward');
			JToolBarHelper::divider();
		}
		JToolBarHelper::help('JHELP_COMPONENTS_ONWARD_SITES');
	}
}
