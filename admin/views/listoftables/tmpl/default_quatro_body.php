<?php
/**
 * CustomTables Joomla! 3.x/4.x Native Component
 * @package Custom Tables
 * @author Ivan komlev <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright Copyright (C) 2018-2020. All Rights Reserved
 * @license GNU/GPL Version 2 or later - https://www.gnu.org/licenses/gpl-2.0.html
 **/
// No direct access to this file access');
if (!defined('_JEXEC') and !defined('WPINC')) {
    die('Restricted access');
}

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

use CustomTables\Fields;

$edit = "index.php?option=com_customtables&view=listoftables&task=tables.edit";

$conf = Factory::getConfig();
$dbPrefix = $conf->get('dbprefix');

?>
<?php foreach ($this->items as $i => $item): ?>
    <?php
    $canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $item->checked_out == $this->user->id || $item->checked_out == 0;
    $userChkOut = Factory::getUser($item->checked_out);
    $table_exists = ESTables::checkIfTableExists($item->realtablename);
    //$canDo = CustomtablesHelper::getActions('categories',$item,'listofcategories');
    ?>
    <tr class="row<?php echo $i % 2; ?>">

        <?php if ($this->canState or $this->canDelete): ?>
            <td class="text-center">
                <?php if ($item->checked_out) : ?>
                    <?php if ($canCheckin) : ?>
                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                    <?php else: ?>
                        &#9633;
                    <?php endif; ?>
                <?php else: ?>
                    <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                <?php endif; ?>
            </td>
        <?php endif; ?>

        <td>
            <div class="name">
                <?php if ($this->canEdit): ?>
                    <a href="<?php echo $edit; ?>&id=<?php echo $item->id; ?>"><?php echo $this->escape($item->tablename); ?></a>
                    <?php if ($item->checked_out): ?>
                        <?php echo JHtml::_('jgrid.checkedout', $i, $userChkOut->name, $item->checked_out_time, 'listoftables.', $canCheckin); ?>
                    <?php endif; ?>
                <?php else: ?>
                    <?php echo $this->escape($item->tablename); ?>
                <?php endif; ?>

                <?php
                if ($this->ct->Env->advancedtagprocessor) {
                    $hashRealTableName = str_replace($dbPrefix, '#__', $item->realtablename);
                    echo '<br/><span style="color:grey;">' . $hashRealTableName . '</span>';
                }
                ?>
            </div>
        </td>

        <td>
            <div class="name">
                <ul style="list-style: none !important;margin-left:0;padding-left:0;">
                    <?php

                    $item_array = (array)$item;

                    $moreThanOneLang = false;

                    foreach ($this->languages as $lang) {
                        $tableTitle = 'tabletitle';
                        $tableDescription = 'description';
                        if ($moreThanOneLang) {
                            $tableTitle .= '_' . $lang->sef;
                            $tableDescription .= '_' . $lang->sef;

                            if (!array_key_exists($tableTitle, $item_array)) {
                                Fields::addLanguageField('#__customtables_tables', 'tabletitle', $tableTitle);
                                $item_array[$tableTitle] = '';
                            }

                            if (!array_key_exists($tableTitle, $item_array)) {
                                Fields::addLanguageField('#__customtables_tables', 'description', $tableDescription);
                                $item_array[$tableDescription] = '';
                            }
                        }

                        echo '<li>' . (count($this->languages) > 1 ? $lang->title . ': ' : '') . '<b>' . $this->escape($item_array[$tableTitle]) . '</b></li>';

                        $moreThanOneLang = true; //More than one language installed
                    }

                    ?>
                </ul>
            </div>

        </td>

        <td class="text-center btns d-none d-md-table-cell itemnumber">


            <?php echo '<a class="btn btn-success" aria-describedby="tip-tablefields' . $item->id . '" href="' . JURI::root(true) . '/administrator/index.php?option=com_customtables&view=listoffields&tableid=' . $item->id . '">'
                . $item->fieldcount . '</a>'; ?>
            <div role="tooltip"
                 id="tip-tablefields<?php echo $item->id; ?>"><?php echo Text::_('COM_CUSTOMTABLES_TABLES_FIELDS_LABEL'); ?></div>


        </td>

        <td class="text-center btns d-none d-md-table-cell itemnumber">
            <?php
            if (!$table_exists)
                echo Text::_('COM_CUSTOMTABLES_TABLES_TABLE_NOT_CREATED');
            elseif (($item->customtablename !== null and $item->customtablename != '') and ($item->customidfield === null or $item->customidfield == ''))
                echo Text::_('COM_CUSTOMTABLES_TABLES_ID_FIELD_NOT_SET');
            else {
                echo '<a class="btn btn-secondary" aria-describedby="tip-tablerecords' . $item->id . '" href="' . JURI::root(true) . '/administrator/index.php?option=com_customtables&view=listofrecords&tableid=' . $item->id . '">'
                    . $this->getNumberOfRecords($item->realtablename, $item->realidfieldname) . '</a>'
                    . '<div role="tooltip" id="tip-tablerecords' . $item->id . '">' . Text::_('COM_CUSTOMTABLES_TABLES_RECORDS_LABEL') . '</div>';
            }
            ?>
        </td>

        <td>
            <div class="name">
                <?php if ($this->canEdit): ?>
                    <a href="<?php echo $edit; ?>&id=<?php echo $item->id; ?>"><?php echo $this->escape($item->categoryname); ?></a>
                <?php else: ?>
                    <?php echo $this->escape($item->categoryname); ?>
                <?php endif; ?>
            </div>
        </td>

        <td class="text-center btns d-none d-md-table-cell">
            <?php if ($this->canState) : ?>
                <?php if ($item->checked_out) : ?>
                    <?php if ($canCheckin) : ?>
                        <?php echo JHtml::_('jgrid.published', $item->published, $i, 'listoftables.', true, 'cb'); ?>
                    <?php else: ?>
                        <?php echo JHtml::_('jgrid.published', $item->published, $i, 'listoftables.', false, 'cb'); ?>
                    <?php endif; ?>
                <?php else: ?>
                    <?php echo JHtml::_('jgrid.published', $item->published, $i, 'listoftables.', true, 'cb'); ?>
                <?php endif; ?>
            <?php else: ?>
                <?php echo JHtml::_('jgrid.published', $item->published, $i, 'listoftables.', false, 'cb'); ?>
            <?php endif; ?>
        </td>
        <td class="d-none d-md-table-cell">
            <?php echo $item->id; ?>
        </td>
    </tr>
<?php endforeach; ?>
