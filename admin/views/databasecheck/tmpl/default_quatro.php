<?php
/**
 * CustomTables Joomla! 3.x/4.x Native Component
 * @package Custom Tables
 * @author Ivan komlev <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright Copyright (C) 2018-2022. All Rights Reserved
 * @license GNU/GPL Version 2 or later - https://www.gnu.org/licenses/gpl-2.0.html
 **/

// No direct access to this file
if (!defined('_JEXEC') and !defined('WPINC')) {
    die('Restricted access');
}

use CustomTables\IntegrityChecks;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$tables = $this->prepareTables();

$document = Factory::getDocument();
$document->addCustomTag('<script src="' . JURI::root(true) . '/media/vendor/jquery/js/jquery.min.js"></script>');
$document->addCustomTag('<script src="' . JURI::root(true) . '/components/com_customtables/libraries/customtables/media/js/raphael.min.js"></script>');
$document->addCustomTag('<script src="' . JURI::root(true) . '/components/com_customtables/libraries/customtables/media/js/diagram.js"></script>');
?>

<form action="<?php echo JRoute::_('index.php?option=com_customtables&view=databasecheck'); ?>" method="post"
      name="adminForm" id="adminForm">
    <style>
        #canvas_container {
            width: 100%;
            min-height: <?php echo (count($tables)>50 ? '4000' : '2000'); ?>px;
            border: 1px solid #aaa;
        }
    </style>

    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">


                <?php
                //$this->filterForm = $this->get('FilterForm');
                //echo $this->filterForm->renderField('tablecategory'); ?>

                <?php echo HTMLHelper::_('uitab.startTabSet', 'schemaTab', ['active' => 'diagram', 'recall' => true, 'breakpoint' => 768]); ?>

                <?php echo HTMLHelper::_('uitab.addTab', 'schemaTab', 'diagram', Text::_('COM_CUSTOMTABLES_TABLES_DIAGRAM')); ?>

                <?php
                // Search tools bar

                /*
                echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this,
                    'options' => array('filtersHidden' => 0), 'filtersHidden' => 0
                ));
*/
                ?>

                <div id="canvas_container"></div>

                <?php echo HTMLHelper::_('uitab.endTab'); ?>

                <?php echo HTMLHelper::_('uitab.addTab', 'schemaTab', 'checks', Text::_('COM_CUSTOMTABLES_TABLES_CHECKS')); ?>

                <?php
                $result = IntegrityChecks::check($this->ct);

                if (count($result) > 0)
                    echo '<ol><li>' . implode('</li><li>', $result) . '</li></ol>';
                else
                    echo '<p>Database table structure is up-to-date.</p>';

                ?>

                <?php echo HTMLHelper::_('uitab.endTab'); ?>
                <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

                <script>

                    TableCategoryID = <?php echo (int)$this->state->get('list.tablecategory'); ?>;
                    AllTables = <?php echo json_encode($tables); ?>;

                </script>

                <input type="hidden" name="task" value=""/>
                <?php echo JHtml::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>