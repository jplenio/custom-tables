<?php
/**
 * CustomTables Joomla! 3.x Native Component
 * @package Custom Tables
 * @author Ivan Komlev <support@joomlaboat.com>
 * @link http://www.joomlaboat.com
 * @copyright (C) 2018-2022 Ivan Komlev
 * @license GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 **/

namespace CustomTables;

// no direct access
defined('_JEXEC') or die('Restricted access');

use \Joomla\CMS\Factory;

use \ESTables;
use CustomTables\Fields;

class Table
{
    use Logs;

    var $Languages;
    var $Env;
    var $tableid;
    var $tablerow;
    var $tablename;
    var $published_field_found;
    var $customtablename;
    var $realtablename;
    var $realidfieldname;
    var $tabletitle;
    var $alias_fieldname;
    var $useridfieldname;
    var $useridrealfieldname;
    var $fields;
    var $record;
    var $recordcount;
    var $recordlist;
    var $db;

    function __construct(&$Languages, &$Env, $tablename_or_id_not_sanitized, $useridfieldname = null)
    {
        $this->db = Factory::getDBO();

        $this->Languages = $Languages;
        $this->Env = $Env;

        if ($tablename_or_id_not_sanitized == null or $tablename_or_id_not_sanitized == '')
            return;
        elseif (is_numeric($tablename_or_id_not_sanitized)) {
            $this->tablerow = ESTables::getTableRowByIDAssoc((int)$tablename_or_id_not_sanitized);// int sanitizes the input
        } else {
            $tablename_or_id = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $tablename_or_id_not_sanitized)));
            $this->tablerow = ESTables::getTableRowByNameAssoc($tablename_or_id);
        }

        if (!isset($this->tablerow['id']))
            return;

        $this->setTable($this->tablerow, $useridfieldname, $load_fields = true);
    }

    public function getRecordFieldValue($listingid, $resultfield)
    {
        $db = Factory::getDBO();
        $query = ' SELECT ' . $resultfield . ' FROM ' . $this->realtablename . ' WHERE ' . $this->realidfieldname . '=' . $db->quote($listingid) . ' LIMIT 1';

        $db->setQuery($query);
        $recs = $db->loadAssocList();

        if (count($recs) > 0)
            return $recs[0][$resultfield];

        return "";
    }

    function setTable(&$tablerow, $useridfieldname = null, $load_fields = true): void
    {
        $this->tablerow = $tablerow;
        $this->tablename = $this->tablerow['tablename'];
        $this->tableid = $this->tablerow['id'];
        $this->published_field_found = $this->tablerow['published_field_found'];
        $this->customtablename = $this->tablerow['customtablename'];
        $this->realtablename = $this->tablerow['realtablename'];
        $this->realidfieldname = $this->tablerow['realidfieldname'];

        if (isset($this->tablerow['tabletitle' . $this->Languages->Postfix]) and $this->tablerow['tabletitle' . $this->Languages->Postfix] != "")
            $this->tabletitle = $this->tablerow['tabletitle'];

        $this->alias_fieldname = '';
        $this->imagegalleries = array();
        $this->fileboxes = array();
        $this->useridfieldname = '';

        //Fields
        $this->fields = Fields::getFields($this->tableid);

        foreach ($this->fields as $fld) {
            switch ($fld['type']) {
                case 'alias':
                    $this->alias_fieldname = $fld['fieldname'];
                    break;
                case 'imagegallery':
                    $this->imagegalleries[] = array($fld['fieldname'], $fld['fieldtitle' . $this->Languages->Postfix]);
                    break;
                case 'filebox':
                    $this->fileboxes[] = array($fld['fieldname'], $fld['fieldtitle' . $this->Languages->Postfix]);
                    break;

                case 'user':
                case 'userid':

                    if ($useridfieldname == null or $useridfieldname == $fld['fieldname']) {
                        $this->useridfieldname = $fld['fieldname'];
                        $this->useridrealfieldname = $fld['realfieldname'];;
                    }
                    break;
            }
        }
    }

    function loadRecord($listing_id)
    {
        $query = 'SELECT ' . $this->tablerow['query_selects'] . ' FROM ' . $this->realtablename . ' WHERE ' . $this->realidfieldname . '=' . $this->db->quote($listing_id) . ' LIMIT 1';
        $this->db->setQuery($query);

        $recs = $this->db->loadAssocList();
        if (!$recs) return $this->record = null;
        if (count($recs) < 1) return $this->record = null;

        return $recs[0];
    }

    function isRecordEmpty($row): bool
    {
        if(!is_array($row))
            return true;

        if(is_null($row[$this->realidfieldname]))
            return true;

        if($row[$this->realidfieldname] == '')
            return true;

        if(is_numeric($row[$this->realidfieldname]) and (int)$row[$this->realidfieldname] == 0)
            return true;

        return false;
    }
}
