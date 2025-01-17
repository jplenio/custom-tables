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
use CustomTables\Details;
use CustomTables\TwigProcessor;
use Joomla\CMS\Factory;

class CustomTablesViewLog extends JViewLegacy
{
    var CT $ct;
    var Details $details;

    var int $limit;
    var int $limitstart;
    var int $record_count;
    var int $userid;
    var string $action;

    var int $tableid;
    var bool $isUserAdministrator;
    var ?array $records;
    var string $actionSelector;
    var string $userSelector;
    var string $tableSelector;

    function display($tpl = null)
    {
        $this->ct = new CT;

        $user = Factory::getUser();
        $this->userid = $user->id;

        $this->action = Factory::getApplication()->input->getString('action', '');
        if ($this->action == '')
            $this->action = -1;

        $this->userid = Factory::getApplication()->input->getInt('user', 0);

        $this->tableid = Factory::getApplication()->input->getInt('table', 0);

        //Is user super Admin?
        $this->isUserAdministrator = $this->ct->Env->isUserAdministrator;

        $this->records = $this->getRecords($this->action, $this->userid, $this->tableid);

        $this->actionSelector = $this->ActionFilter($this->action);

        $this->userSelector = $this->getUsers($this->userid);

        $this->tableSelector = $this->gettables($this->tableid);

        parent::display($tpl);
    }

    function getRecords($action, $userid, $tableid)
    {
        $mainframe = Factory::getApplication('site');
        $this->limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        if ($this->limit == 0)
            $this->limit = 20;

        $this->limitstart = Factory::getApplication()->input->get('start', 0, 'INT');
        // In case limit has been changed, adjust it
        $this->limitstart = ($this->limit != 0 ? (floor($this->limitstart / $this->limit) * $this->limit) : 0);

        $db = Factory::getDBO();

        $selects = array();
        $selects[] = '*';
        $selects[] = '(SELECT name FROM #__users WHERE id=userid) AS UserName';
        $selects[] = '(SELECT tabletitle FROM #__customtables_tables WHERE id=tableid) AS TableName';
        $selects[] = '(SELECT fieldname FROM #__customtables_fields WHERE #__customtables_fields.published=1 AND #__customtables_fields.tableid=#__customtables_log.tableid '
            . 'ORDER BY ordering LIMIT 1) AS FieldName';

        $where = array();
        if ($action != -1)
            $where[] = 'action=' . $action;

        if ($userid != 0)
            $where[] = 'userid=' . $userid;

        if ($tableid != 0)
            $where[] = 'tableid=' . $tableid;

        $query = 'SELECT ' . implode(',', $selects) . ' FROM #__customtables_log ' . (count($where) > 0 ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY datetime DESC';

        $this->record_count = 1000;

        $the_limit = $this->limit;
        if ($the_limit > 500)
            $the_limit = 500;

        if ($the_limit == 0)
            $the_limit = 500;

        if ($this->record_count < $this->limitstart or $this->record_count < $the_limit)
            $this->limitstart = 0;

        $db->setQuery($query, $this->limitstart, $the_limit);

        return $db->loadAssocList();
    }

    function ActionFilter($action)
    {
        $actions = ['New', 'Edit', 'Publish', 'Unpublish', 'Delete', 'Image Uploaded', 'Image Deleted', 'File Uploaded', 'File Deleted', 'Refreshed'];
        $result = '<select onchange="ActionFilterChanged(this)">';
        $result .= '<option value="-1" ' . ($action == -1 ? 'selected="SELECTED"' : '') . '>- ' . JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_SELECT') . '</option>';

        $v = 1;
        foreach ($actions as $a) {
            $result .= '<option value="' . $v . '" ' . ($action == $v ? 'selected="SELECTED"' : '') . '>' . $a . '</option>';
            $v++;
        }

        $result .= '</select>';
        return $result;
    }

    function getUsers($userid)
    {
        $db = Factory::getDBO();

        $query = 'SELECT #__users.id AS id, #__users.name AS name FROM #__customtables_log INNER JOIN #__users ON #__users.id=#__customtables_log.userid GROUP BY #__users.id ORDER BY name';

        $db->setQuery($query);

        $rows = $db->loadAssocList();

        $result = '<select onchange="UserFilterChanged(this)">';
        $result .= '<option value="0" ' . ($userid == 0 ? 'selected="SELECTED"' : '') . '>- ' . JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_SELECT') . '</option>';

        foreach ($rows as $row) {
            $result .= '<option value="' . $row['id'] . '" ' . ($userid == $row['id'] ? 'selected="SELECTED"' : '') . '>' . $row['name'] . '</option>';
        }

        $result .= '</select>';

        return $result;
    }

    function getTables($tableid): string
    {
        $db = Factory::getDBO();

        $query = 'SELECT id,tablename FROM #__customtables_tables ORDER BY tablename';

        $db->setQuery($query);

        $rows = $db->loadAssocList();

        $result = '<select onchange="TableFilterChanged(this)">';
        $result .= '<option value="0" ' . ($tableid == 0 ? 'selected="SELECTED"' : '') . '>- ' . JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_SELECT') . '</option>';

        foreach ($rows as $row) {
            $result .= '<option value="' . $row['id'] . '" ' . ($tableid == $row['id'] ? 'selected="SELECTED"' : '') . '>' . $row['tablename'] . '</option>';
        }

        $result .= '</select>';

        return $result;
    }

    function renderLogLine($rec): string
    {
        $actions = ['New', 'Edit', 'Publish', 'Unpublish', 'Delete', 'Image Uploaded', 'Image Deleted', 'File Uploaded', 'File Deleted', 'Refreshed'];
        $action_images = ['new.png', 'edit.png', 'publish.png', 'unpublish.png', 'delete.png', 'photomanager.png', 'photomanager.png', 'filemanager.png', 'filemanager.png', 'refresh.png'];
        $action_image_path = '/components/com_customtables/libraries/customtables/media/images/icons/';

        $a = (int)$rec['action'] - 1;
        $alt = $actions[$a];

        $result = '<tr>'
            . '<td>';

        if ($a == 1 or $a == 2) {
            $link = '/index.php?option=com_customtables&view=edititem&listing_id=' . $rec['listingid'] . '&Itemid=' . $rec['Itemid'];
            $result .= '<a href="' . $link . '" target="_blank"><img src="' . $action_image_path . $action_images[$a] . '" alt=' . $alt . ' title=' . $alt . ' width="16" height="16" /></a>';
        } else
            $result .= '<img src="' . $action_image_path . $action_images[$a] . '" alt=' . $alt . ' title=' . $alt . ' width="16" height="16" />';

        $result .= '</td>'
            . '<td>' . $rec['UserName'] . '</td>';

        $link = '/index.php?option=com_customtables&view=details&listing_id=' . $rec['listingid'] . '&Itemid=' . $rec['Itemid'];

        $result .= '<td><a href="' . $link . '" target="_blank">' . $rec['datetime'] . '</a></td>'

            . '<td>' . $rec['TableName'] . '</td>'
            . '<td>' . $this->getRecordValue($rec['listingid'], $rec['Itemid'], $rec['FieldName']) . '<br/>(id: ' . $rec['listingid'] . ')</td>'
            . '<td>' . $alt . '</td>'
            . '</tr>';

        return $result;
    }

    function getRecordValue($listing_id, $Itemid, $FieldName): string
    {
        if (!isset($FieldName) or $FieldName == '')
            return "Table/Field not found.";

        $app = Factory::getApplication();
        $jinput = Factory::getApplication()->input;

        $jinput->set("listing_id", $listing_id);
        $jinput->set('Itemid', $Itemid);

        $menu = $app->getMenu();
        $menuParams = $menu->getParams($Itemid);

        $ct = new CT($menuParams, false);

        $this->details = new Details($ct);

        if ($ct->Table->tablename === null)
            return "Table " . $ct->Table->tablename . "not found.";

        $layoutContent = '{{ ' . $FieldName . ' }}';
        $twig = new TwigProcessor($ct, $layoutContent);

        $row = $ct->Table->loadRecord($listing_id);

        return $twig->process($row);
    }
}