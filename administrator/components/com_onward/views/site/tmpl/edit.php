<?php
/**
 * @version		$Id $
 * @package		Onward
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('script', 'onward/importer.js', false, true);
JHtml::_('script', 'system/progressbar-uncompressed.js', false, true);
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'site.batch') {
			onwardImporter.doBatch();
		} else {
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
	};

	var progressBars = [];

	var onwardImporter = new Importer({
		site_id: <?php echo $this->item->id; ?>,
		url: 'index.php?<?php echo JSession::getFormToken(); ?>=1'
	});

	window.addEvent('domready', function(){
		$$('.progress_bar').each(function(item) {
			progressBars[item.get('id')] = new Fx.ProgressBar(item, {
				text: new Element('span', {'class': 'progress-text'}).inject(item, 'bottom'),
				url: '<?php echo JUri::root(); ?>media/media/images/progress.gif'
			});
		});
	});
</script>

<form action="<?php echo JRoute::_('index.php?option=com_onward&layout=edit&id='.(int) $this->item->id); ?>" method="post" id="adminForm" class="form-validate">
<div class="width-100 fltlft">
	<fieldset class="adminform">
		<legend><?php echo JText::sprintf('COM_ONWARD_SITE_DETAILS', $this->item->id); ?></legend>
		<ul class="adminformlist">
			<li><?php echo JTEXT::_('COM_ONWARD_SITE_NAME'); ?>
			<?php echo $this->item->name; ?></li>

			<li><?php echo JTEXT::_('COM_ONWARD_LOCATION'); ?>
			<?php echo $this->item->location; ?></li>

			<li><?php echo JTEXT::_('COM_ONWARD_VERSION'); ?>
			<?php echo $this->item->version; ?></li>

			<li><?php echo JTEXT::_('COM_ONWARD_DESCRIPTION'); ?>
			<?php echo $this->item->description; ?></li>

		</ul>
		<div class="clr"> </div>
		<table class="adminlist">
			<thead>
			<tr>
				<th width="1%">

				</th>
				<th>
					<?php echo JText::_('COM_ONWARD_ASSET'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_ONWARD_SCAN_DATE'); ?>
				</th>
				<th width="20%">
					<?php echo JText::_('COM_ONWARD_PROGRESS'); ?>
				</th>
			</tr>
			</thead>
		<tbody>
		<?php
		$n = count($this->importState);
		foreach ($this->importState as $i => $item) :
			//$item->import_link = JRoute::_('index.php?option=com_onward&task=site.edit&id='. $item->id);
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php  ?>
				</td>
				<td>
					<?php echo $item->asset; ?>
				</td>
				<td>
					<?php echo $item->import_date; ?>
				</td>
				<td>
					<?php echo JHTML::_('image','media/bar.gif', JText::_('COM_MEDIA_OVERALL_PROGRESS'), array('class' => 'progress progress_bar', 'id' => 'progress_'.$item->asset), true); ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

		<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>

	</fieldset>
</div>

<div class="clr"></div>
</form>
