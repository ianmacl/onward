<?php
/**
 * @version		$Id $
 * @package		Onward
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Prototype adapter class for the Onward Importer package.
 *
 * @package	Onward
 * @subpackage	joomla15_content_frontpage
 */
class plgOnwardJoomla15_Content_Rating extends OnwardImporterAdapter
{
	/**
	 * The context is the name of the entity that the plugin imports. This should
	 * unique and will generally follow the table and might include version.
	 * For example, it might be com_content_1_5
	 *
	 * @var		string		The plugin identifier.
	 */
	protected $context = 'jos_content_rating';

	protected function getDependencies()
	{
		return array('jos_content');
	}

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
		$sql->from('#__content_rating AS a');

		return $sql;
	}

	/**
	 * Method to import an item.
	 *
	 * @param	object		The item to import.
	 * @return	boolean		True on success.
	 * @throws	Exception on database error.
	 */
	protected function import($oldContentRating)
	{
		$db = JFactory::getDBO();

		$content_id = (int)OnwardImporter::getMappedId('jos_content', $oldContentRating->content_id);
		$rating_sum = (int)$oldContentRating->rating_sum;
		$rating_count = (int)$oldContentRating->rating_count;
		$lastip = $oldContentRating->lastip;

		if ($content_id == 0)
		{
			return false;
		}

		$db->setQuery(
			'INSERT INTO #__content_rating (`content_id`, `rating_sum`, `rating_count`, `lastip`)' .
			' VALUES ('.$content_id.', '.$rating_sum.', '.$rating_count.', '.$db->quote($lastip).')'
		);

		if (!$db->query()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		return false;

	}
}
