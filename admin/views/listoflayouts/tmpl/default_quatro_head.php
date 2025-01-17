<?php
/**
 * CustomTables Joomla! 3.x/4.x Native Component
 * @package Custom Tables
 * @author Ivan Komlev <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright (C) 2018-2022 Ivan Komlev
 * @license GNU/GPL Version 2 or later - https://www.gnu.org/licenses/gpl-2.0.html
 **/
// No direct access to this file access');
if (!defined('_JEXEC') and !defined('WPINC')) {
    die('Restricted access');
}

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\String\PunycodeHelper;

HTMLHelper::_('behavior.multiselect');

?>
<tr>
    <?php if ($this->canEdit && $this->canState): ?>
        <th width="20" class="nowrap center">
            <?php echo JHtml::_('grid.checkall'); ?>
        </th>
    <?php endif; ?>

    <th scope="col">
        <?php echo HTMLHelper::_('searchtools.sort', 'COM_CUSTOMTABLES_LAYOUTS_LAYOUTNAME_LABEL', 'a.layoutname', $this->listDirn, $this->listOrder); ?>
    </th>

    <th scope="col">
        <?php echo Text::_('COM_CUSTOMTABLES_LAYOUTS_LAYOUTTYPE_LABEL'); ?>
    </th>

    <th scope="col">
        <?php echo Text::_('COM_CUSTOMTABLES_LAYOUTS_TABLEID_LABEL'); ?>
    </th>

    <th scope="col" class="text-center d-none d-md-table-cell">
        <?php echo HTMLHelper::_('searchtools.sort', 'COM_CUSTOMTABLES_LAYOUTS_STATUS', 'a.published', $this->listDirn, $this->listOrder); ?>
    </th>

    <th scope="col" class="w-12 d-none d-xl-table-cell">
        <?php echo HTMLHelper::_('searchtools.sort', 'COM_CUSTOMTABLES_LAYOUTS_ID', 'a.id', $this->listDirn, $this->listOrder); ?>
    </th>

    <th scope="col">
        <?php echo Text::_('COM_CUSTOMTABLES_LAYOUTS_SIZE'); ?>
    </th>

    <th scope="col">
        <?php echo Text::_('COM_CUSTOMTABLES_LAYOUTS_MODIFIEDBY'); ?>
    </th>

    <th scope="col">
        <?php echo Text::_('COM_CUSTOMTABLES_LAYOUTS_MODIFIED'); ?>
    </th>

    <th scope="col">
        Template engine
    </th>
</tr>
