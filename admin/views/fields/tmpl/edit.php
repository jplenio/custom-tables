<?php
/**
 * CustomTables Joomla! 3.x/4.x Native Component
 * @package Custom Tables
 * @subpackage views/fields/tmpl/edit.php
 * @author Ivan komlev <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright Copyright (C) 2018-2021. All Rights Reserved
 * @license GNU/GPL Version 2 or later - https://www.gnu.org/licenses/gpl-2.0.html
 **/

// No direct access to this file
if (!defined('_JEXEC') and !defined('WPINC')) {
    die('Restricted access');
}

use CustomTables\Fields;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$document = Factory::getDocument();

$document->addCustomTag('<link href="' . JURI::root(true) . '/components/com_customtables/libraries/customtables/media/css/style.css" rel="stylesheet">');
$document->addCustomTag('<link href="' . JURI::root(true) . '/components/com_customtables/libraries/customtables/media/css/fieldtypes.css" rel="stylesheet">');
$document->addCustomTag('<link href="' . JURI::root(true) . '/components/com_customtables/libraries/customtables/media/css/modal.css" rel="stylesheet">');
$document->addCustomTag('<script src="' . JURI::root(true) . '/components/com_customtables/libraries/customtables/media/js/ajax.js"></script>');
$document->addCustomTag('<script src="' . JURI::root(true) . '/components/com_customtables/libraries/customtables/media/js/typeparams_common.js"></script>');
$document->addCustomTag('<script src="' . JURI::root(true) . '/components/com_customtables/libraries/customtables/media/js/typeparams.js"></script>');

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

require_once(JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_customtables' . DIRECTORY_SEPARATOR . 'libraries'
    . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR . 'extratasks' . DIRECTORY_SEPARATOR . 'extratasks.php');

$input = Factory::getApplication()->input;

if (in_array($input->getCmd('extratask', ''), $this->extrataskOptions)) {
    extraTasks::prepareJS();
}

foreach ($this->allTables as $table) {
    $fields = Fields::getFields($table[0], true);
    $list = array();
    foreach ($fields as $field)
        $list[] = [$field->id, $field->fieldname];

    echo '<div id="fieldsData' . $table[0] . '" style="display:none;">' . json_encode($list) . '</div>
';
} ?>
<script>

    var outerDiv = jQuery('body');
    jQuery('<div id="loading"></div>')
        .css("background", "rgba(255, 255, 255, .8) url('<?php echo JURI::root(true); ?>/components/com_customtables/libraries/images/controlpanel/images/import.gif') 50% 15% no-repeat")
        .css("top", outerDiv.position().top - jQuery(window).scrollTop())
        .css("left", outerDiv.position().left - jQuery(window).scrollLeft())
        .css("width", outerDiv.width())
        .css("height", outerDiv.height())
        .css("position", "fixed")
        .css("opacity", "0.80")
        .css("-ms-filter", "progid:DXImageTransform.Microsoft.Alpha(Opacity = 80)")
        .css("filter", "alpha(opacity = 80)")
        .css("display", "none")
        .appendTo(outerDiv);
    jQuery('#loading').show();
    // when page is ready remove and show
    jQuery(window).load(function () {
        jQuery('#customtables_loader').fadeIn('fast');
        jQuery('#loading').hide();
    });

    <?php

    if ($this->ct->Env->advancedtagprocessor) {
        echo '
		proversion=true;
';
    }
    echo 'all_tables=' . json_encode($this->allTables) . ';';
    ?>
</script>
<div id="customtables_loader" style="display: none;">

    <form action="<?php echo JRoute::_('index.php?option=com_customtables&layout=edit&id=' . (int)($this->item->id) . $this->referral); ?>"
          method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

        <div class="form-horizontal">

            <?php
            echo JHtml::_('bootstrap.startTabSet', 'fieldsTab', array('active' => 'general')); ?>

            <?php echo JHtml::_('bootstrap.addTab', 'fieldsTab', 'general', Text::_('COM_CUSTOMTABLES_FIELDS_GENERAL', true)); ?>
            <div class="row-fluid form-horizontal-desktop">
                <div class="span12">

                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('tableid'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('tableid', null, $this->tableid); ?></div>
                    </div>

                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('fieldname'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('fieldname'); ?></div>
                    </div>

                    <?php if ($this->table_row->customtablename != ''): ?>
                        <hr/>
                        <p><?php echo Text::_('COM_CUSTOMTABLES_FIELDS_THIS_IS_THIRDPARTY_FIELD', true) . ': "' . $this->table_row->customtablename . '"'; ?></p>
                        <div class="control-group">
                            <div class="control-label"><?php echo $this->form->getLabel('customfieldname'); ?></div>
                            <div class="controls"><?php echo $this->form->getInput('customfieldname'); ?></div>
                        </div>

                    <?php endif; ?>

                    <hr/>

                    <?php

                    $moreThanOneLanguage = false;
                    foreach ($this->ct->Languages->LanguageList as $lang) {
                        $id = 'fieldtitle';
                        if ($moreThanOneLanguage) {
                            $id .= '_' . $lang->sef;

                            $cssclass = 'text_area';
                            $att = '';
                        } else {
                            $cssclass = 'text_area required';
                            $att = ' required aria-required="true"';
                        }

                        $item_array = (array)$this->item;
                        $vlu = '';

                        if (isset($item_array[$id]))
                            $vlu = $item_array[$id];

                        if ($moreThanOneLanguage)
                            $field_label = Text::_('COM_CUSTOMTABLES_FIELDS_FIELDTITLE', true);
                        else
                            $field_label = $this->form->getLabel('fieldtitle');

                        echo '
					<div class="control-group">
						<div class="control-label">' . $field_label . '</div>
						<div class="controls">
							<input type="text" name="jform[' . $id . ']" id="jform_' . $id . '"  value="' . $vlu . '" class="' . $cssclass . '"     placeholder="Field Title"   maxlength="255" ' . $att . '  />
							<b>' . $lang->title . '</b>
						</div>

					</div>
					';

                        $moreThanOneLanguage = true; //More than one language installed
                    }
                    ?>

                    <hr/>
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('type'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('type'); ?></div>
                    </div>

                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('typeparams'); ?></div>
                        <div class="controls">
                            <div class="typeparams_box" id="typeparams_box"></div>
                        </div>
                    </div>

                    <div class="control-group">
                        <div class="control-label"></div>
                        <div class="controls"><?php echo $this->form->getInput('typeparams'); ?></div>
                    </div>

                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('parent'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('parent'); ?></div>
                    </div>
                </div>
            </div>
            <?php echo JHtml::_('bootstrap.endTab'); ?>

            <?php echo JHtml::_('bootstrap.addTab', 'fieldsTab', 'optional', Text::_('COM_CUSTOMTABLES_FIELDS_OPTIONAL', true)); ?>
            <div class="row-fluid form-horizontal-desktop">
                <div class="span12">
                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('isrequired'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('isrequired'); ?></div>
                    </div>

                    <div class="control-group<?php echo(!$this->ct->Env->advancedtagprocessor ? ' ct_pro' : ''); ?>">
                        <div class="control-label"><?php echo $this->form->getLabel('defaultvalue'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('defaultvalue'); ?></div>
                    </div>

                    <div class="control-group">
                        <div class="control-label"><?php echo $this->form->getLabel('allowordering'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('allowordering'); ?></div>
                    </div>

                    <div class="control-group<?php echo(!$this->ct->Env->advancedtagprocessor ? ' ct_pro' : ''); ?>">
                        <div class="control-label"><?php echo $this->form->getLabel('valuerule'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('valuerule'); ?></div>
                    </div>

                    <div class="control-group<?php echo(!$this->ct->Env->advancedtagprocessor ? ' ct_pro' : ''); ?>">
                        <div class="control-label"><?php echo $this->form->getLabel('valuerulecaption'); ?></div>
                        <div class="controls"><?php echo $this->form->getInput('valuerulecaption'); ?></div>
                    </div>
                </div>
            </div>
            <?php echo JHtml::_('bootstrap.endTab'); ?>

            <?php

            $moreThanOneLanguage = false;
            foreach ($this->ct->Languages->LanguageList as $lang) {
                $id = 'description';
                if ($moreThanOneLanguage)
                    $id .= '_' . $lang->sef;

                JHtml::_('bootstrap.addTab', 'fieldsTab', $id, Text::_('COM_CUSTOMTABLES_FIELDS_DESCRIPTION', true) . ' <b>' . $lang->title . '</b>');
                echo '
			<div id="' . $id . '" class="tab-pane">
				<div class="row-fluid form-horizontal-desktop">
					<div class="span12">';

                $editor = Factory::getEditor();

                $item_array = (array)$this->item;
                $vlu = '';

                if (isset($item_array[$id]))
                    $vlu = $item_array[$id];

                echo '<textarea rows="10" cols="20" name="jform[' . $id . ']" id="jform_' . $id . '" style="width:100%;height:100%;"
				class="text_area" placeholder="Field Description" >' . $vlu . '</textarea>';

                echo '
					</div>
				</div>
			</div>';
                $moreThanOneLanguage = true; //More than one language installed
            }

            echo JHtml::_('bootstrap.endTabSet'); ?>

            <div>
                <input type="hidden" name="task" value="fields.edit"/>
                <input type="hidden" name="tableid" value="<?php echo $this->tableid; ?>"/>
                <?php echo JHtml::_('form.token'); ?>
            </div>

            <script>
                updateTypeParams("jform_type", "jform_typeparams", "typeparams_box");
                <?php if(!$this->ct->Env->advancedtagprocessor): ?>
                disableProField("jform_defaultvalue");
                disableProField("jform_valuerule");
                disableProField("jform_valuerulecaption");
                <?php endif; ?>
            </script>

        </div>

        <div id="ct_fieldtypeeditor_box" style="display: none;"><?php
            $attributes = array('name' => 'ct_fieldtypeeditor', 'id' => 'ct_fieldtypeeditor', 'directory' => 'images', 'recursive' => true, 'label' => 'Select Folder', 'readonly' => false);
            echo CTTypes::getField('folderlist', $attributes, null)->input;
            ?></div>

    </form>
</div>

<!-- Modal content -->
<div id="ctModal" class="ctModal">
    <div id="ctModal_box" class="ctModal_content">
        <span id="ctModal_close" class="ctModal_close">&times;</span>
        <div id="ctModal_content"></div>
    </div>
</div>
<!-- end of the modal -->