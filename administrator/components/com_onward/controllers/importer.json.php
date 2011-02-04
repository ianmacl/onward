<?php
/**
 * @version		$Id $
 * @package		Onward
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Register dependent classes.
//JLoader::register('FinderIndexer', JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'indexer'.DS.'indexer.php');

/**
 * Indexer controller class for Finder.
 *
 * @package		JXtended.Finder
 * @subpackage	com_finder
 */
class OnwardControllerImporter extends OnwardController
{
	/**
	 * Method to start the indexer.
	 *
	 * @return	void
	 */
	public function start()
	{
		// We don't want this form to be cached.
		header('Pragma: no-cache');
		header('Cache-Control: no-cache');
		header('Expires: -1');

		// Reset the importer state.
		OnwardImporter::resetState();

		// Import the finder plugins.
		JPluginHelper::importPlugin('onward');

		// Start the indexer.
		try {
			// Trigger the onStartIndex event.
			JDispatcher::getInstance()->trigger('onStartImport');

			// Get the indexer state.
			$state = OnwardImporter::getState();
			$state->start = 1;

			// Send the response.
			$this->sendResponse($state);
		}
		// Catch an exception and return the response.
		catch (Exception $e) {
			$this->sendResponse($e);
		}
	}

	/**
	 * Method to run the next batch of content through the indexer.
	 *
	 * @return	void
	 */
	public function batch()
	{
		// We don't want this form to be cached.
		header('Pragma: no-cache');
		header('Cache-Control: no-cache');
		header('Expires: -1');

		// Check for a valid token. If invalid, send a 403 with the error message.
		JRequest::checkToken('request') or $this->sendResponse(new JException(JText::_('JX_INVALID_TOKEN'), 403));

		// Put in a buffer to silence noise.
		ob_start();

		// Remove the script time limit.
		@set_time_limit(0);

		// Get the indexer state.
		$state = OnwardImporter::getState();

		// Reset the batch offset.
		$state->batchOffset = 0;

		// Update the indexer state.
		OnwardImporter::setState($state);

		// Import the importer plugins.
		JPluginHelper::importPlugin('onward');

		/*
		 * We are going to swap out the raw document object with an HTML document
		 * in order to work around some plugins that don't do proper environment
		 * checks before trying to use HTML document functions.
		 */
		$raw	= clone(JFactory::getDocument());
		$lang	= JFactory::getLanguage();

		// Get the document properties.
		$attributes = array (
			'charset'	=> 'utf-8',
			'lineend'	=> 'unix',
			'tab'		=> '  ',
			'language'	=> $lang->getTag(),
			'direction'	=> $lang->isRTL() ? 'rtl' : 'ltr'
		);

		// Get the HTML document.
		$html	= JDocument::getInstance('html', $attributes);
		$doc	= &JFactory::getDocument();

		// Swap the documents.
		$doc = $html;

		// Get the admin application.
		$admin = clone(JFactory::getApplication());

		// Get the site app.
		require_once JPATH_SITE.DS.'includes'.DS.'application.php';
		$site = JApplication::getInstance('site');

		// Swap the app.
		$app = &JFactory::getApplication();
		$app = $site;

		// Start the indexer.
		try {
			// Trigger the onBeforeIndex event.
			JDispatcher::getInstance()->trigger('onBeforeIndex');

			// Trigger the onBuildIndex event.
			JDispatcher::getInstance()->trigger('onBuildIndex');

			// Get the indexer state.
			$state = FinderIndexer::getState();
			$state->start = 0;
			$state->complete = 0;

			// Swap the documents back.
			$doc = $raw;

			// Swap the applications back.
			$app = $admin;

			// Send the response.
			$this->sendResponse($state);
		}
		// Catch an exception and return the response.
		catch (Exception $e) {
			// Swap the documents back.
			$doc = $raw;

			// Send the response.
			$this->sendResponse($e);
		}
	}

	/**
	 * Method to handle a send a JSON response. The body parameter
	 * can be a Exception object for when an error has occurred or
	 * a JObject for a good response.
	 *
	 * @param	object		JObject on success, JException/Exception on error.
	 * @return	void
	 */
	public function sendResponse($data = null)
	{
		$backtrace = null;

		// Send the assigned error code if we are catching an exception.
		if (JError::isError($data) || $data instanceof Exception) {
			JResponse::setHeader('status', $data->getCode());
			JResponse::sendHeaders();
		}

		// Create the response object.
		$response = new OnwardImporterResponse($data);

		// Add the buffer.
		$response->buffer = JDEBUG ? ob_get_contents() : ob_end_clean();

		// Send the JSON response.
		echo json_encode($response);

		// Close the application.
		JFactory::getApplication()->close();
	}
}

/**
 * Onward Importer JSON Response Class
 *
 * @package		Onward
 */
class OnwardImporterResponse
{
	public function __construct($state)
	{
		// The old token is invalid so send a new one.
		$this->token = JUtility::getToken();

		// Check if we are dealing with an error.
		if (JError::isError($state) || $state instanceof Exception)
		{
			// Prepare the error response.
			$this->error		= true;
			$this->header		= JText::_('ONWARD_IMPORTER_HEADER_ERROR');
			$this->message		= $state->getMessage();
		}
		else
		{
			// Prepare the response data.
			$this->batchSize	= (int)$state->batchSize;
			$this->batchOffset	= (int)$state->batchOffset;
			$this->totalItems	= (int)$state->totalItems;

			$this->startTime	= $state->startTime;
			$this->endTime		= JFactory::getDate()->toMySQL();

			$this->start		= !empty($state->start) ? (int)$state->start : 0;
			$this->complete		= !empty($state->complete) ? (int)$state->complete : 0;

			// Set the appropriate messages.
			if ($this->totalItems <= 0 && $this->complete) {
				$this->header	= JText::_('ONWARD_IMPORTER_HEADER_COMPLETE');
				$this->message	= JText::_('ONWARD_IMPORTER_MESSAGE_COMPLETE');
			}
			elseif ($this->totalItems <= 0) {
				$this->header	= JText::_('ONWARD_IMPORTER_HEADER_OPTIMIZE');
				$this->message	= JText::_('ONWARD_IMPORTER_MESSAGE_OPTIMIZE');
			}
			else {
				$this->header	= JText::_('ONWARD_IMPORTER_HEADER_RUNNING');
				$this->message	= JText::_('ONWARD_IMPORTER_MESSAGE_RUNNING');
			}
		}
	}
}

// Register the error handler.
JError::setErrorHandling(E_ALL, 'callback', array('OnwardControllerImporter', 'sendResponse'));
