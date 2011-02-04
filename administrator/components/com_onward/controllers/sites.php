<?php
/**
 * @version		$Id $
 * @package		Onward
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Sites list controller class.
 *
 * @package		Onward
 */
class OnwardControllerSites extends JControllerAdmin
{
	/**
	 * Proxy for getModel.
	 */
	public function getModel($name = 'Sites', $prefix = 'OnwardModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
	
	/**
	 * Discover sites
	 */
	public function discover()
	{
		$model = $this->getModel();
		
		$model->discover();
		
		$this->setRedirect('index.php?option=com_onward&view=sites');
	}
	
}
