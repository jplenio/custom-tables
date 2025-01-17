<?php
/**
 * CustomTables Joomla! 3.x/4.x Native Component
 * @package Custom Tables
 * @author Ivan Komlev <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright (C) 2018-2022 Ivan Komlev
 * @license GNU/GPL Version 2 or later - https://www.gnu.org/licenses/gpl-2.0.html
 **/

// no direct access
if (!defined('_JEXEC') and !defined('WPINC')) {
    die('Restricted access');
}

use CustomTables\CT;
use CustomTables\CTUser;
use Joomla\CMS\Factory;

jimport('joomla.html.pane');
jimport('joomla.application.component.view'); //Important to get menu parameters

class CustomTablesViewEditItem extends JViewLegacy
{
    var CT $ct;

    function display($tpl = null): bool
    {
        $this->ct = new CT(null, false);

        $Model = $this->getModel();
        $Model->load($this->ct, true);

        if (!CTUser::CheckAuthorization($this->ct)) {
            //not authorized
            Factory::getApplication()->enqueueMessage(JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_NOT_AUTHORIZED'), 'error');
            return false;
        }

        if (!isset($this->ct->Table->fields) or !is_array($this->ct->Table->fields))
            return false;

        $formLink = $this->ct->Env->WebsiteRoot . 'index.php?option=com_customtables&amp;view=edititem' . ($this->ct->Params->ItemId != 0 ? '&amp;Itemid=' . $this->ct->Params->ItemId : '');
        if (!is_null($this->ct->Params->ModuleId))
            $formLink .= '&amp;ModuleId=' . $this->ct->Params->ModuleId;

        if ($this->ct->Env->frmt == 'json')
            require_once('tmpl' . DIRECTORY_SEPARATOR . 'json.php');
        else {

            CTViewEdit($this->ct, $Model->row, $Model->pageLayout, $formLink, 'eseditForm');

            echo '
            <!-- Modal content -->
<div id="ctModal" class="ctModal">
    <div id="ctModal_box" class="ctModal_content">
        <span id="ctModal_close" class="ctModal_close">&times;</span>
        <div id="ctModal_content"></div>
    </div>
</div><!-- end of the modal -->';

        }
        return true;
    }
}
