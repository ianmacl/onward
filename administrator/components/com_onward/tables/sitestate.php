<?php
/**
 * @version		$Id $
 * @package		Onward
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2; see LICENSE.txt
 */
 
// No direct access.
defined('_JEXEC') or die;

/**
 * Site table
 *
 * @package		Onward
 * @subpackage	com_onward
 * @since		1.5
 */
class OnwardTableSiteState extends JTable
{
	/**
	 * Constructor
	 *
	 * @since	1.5
	 */
	function __construct(&$_db)
	{
		parent::__construct('#__onward_site_state', 'id', $_db);
	}
}
