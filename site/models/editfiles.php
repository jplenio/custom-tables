<?php
/**
 * CustomTables Joomla! 3.x/4.x Native Component
 * @package Custom Tables
 * @author Ivan komlev <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright Copyright (C) 2018-2022. All Rights Reserved
 * @license GNU/GPL Version 2 or later - https://www.gnu.org/licenses/gpl-2.0.html
 **/

// no direct access
if (!defined('_JEXEC') and !defined('WPINC')) {
    die('Restricted access');
}

use CustomTables\CT;
use CustomTables\Field;
use CustomTables\Fields;

use Joomla\CMS\Factory;

jimport('joomla.application.component.model');

JTable::addIncludePath(JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_customtables' . DIRECTORY_SEPARATOR . 'tables');

class CustomTablesModelEditFiles extends JModelLegacy
{
    var CT $ct;
    var ?array $row;
    var $filemethods;
    var $fileboxname;
    var $FileBoxTitle;
    var $fileboxfolder;
    var $fileboxfolderweb;
    var int $maxfilesize;
    var $fileboxtablename;
    var string $allowedExtensions;
    var Field $field;

    function __construct()
    {
        $this->ct = new CT(null, false);
        parent::__construct();

        $this->allowedExtensions = 'doc docx pdf rtf txt xls xlsx psd ppt pptx webp png mp3 jpg jpeg csv accdb pages';

        $this->maxfilesize = JoomlaBasicMisc::file_upload_max_size();
        $this->filemethods = new CustomTablesFileMethods;

        $this->ct->getTable($this->ct->Params->tableName, null);

        if ($this->ct->Table->tablename === null) {
            Factory::getApplication()->enqueueMessage('Table not selected (63).', 'error');
            return false;
        }

        if (!$this->ct->Env->jinput->getCmd('fileboxname'))
            return false;

        $this->fileboxname = $this->ct->Env->jinput->getCmd('fileboxname');

        $this->row = $this->ct->Table->loadRecord($this->ct->Params->listing_id);

        if (!$this->getFileBox())
            return false;

        $this->fileboxtablename = '#__customtables_filebox_' . $this->ct->Table->tablename . '_' . $this->fileboxname;

        parent::__construct();
        return true;
    }

    function getFileBox(): bool
    {
        $fieldrow = Fields::FieldRowByName($this->fileboxname, $this->ct->Table->fields);
        $this->field = new Field($this->ct, $fieldrow, $this->row);

        $this->fileboxfolderweb = 'images/' . $this->field->params[1];

        $this->fileboxfolder = JPATH_SITE . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $this->fileboxfolderweb);
        //Create folder if not exists
        if (!file_exists($this->fileboxfolder))
            mkdir($this->fileboxfolder, 0755, true);

        $this->FileBoxTitle = $this->field->title;

        return true;
    }

    function getFileList()
    {
        // get database handle
        $db = Factory::getDBO();
        $query = 'SELECT fileid, file_ext FROM ' . $this->fileboxtablename . ' WHERE listingid=' . $db->quote($this->ct->Params->listing_id) . ' ORDER BY fileid';
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    function delete(): bool
    {
        $db = Factory::getDBO();

        $fileIds = $this->ct->Env->jinput->getString('fileids', '');
        $file_arr = explode('*', $fileIds);

        foreach ($file_arr as $fileid) {
            if ($fileid != '') {
                $file_ext = CustomTablesFileMethods::getFileExtByID($this->ct->Table->tablename, $this->fileboxname, $fileid);

                CustomTablesFileMethods::DeleteExistingFileBoxFile($this->fileboxfolder, $this->ct->Table->tableid, $this->fileboxname, $fileid, $file_ext);

                $query = 'DELETE FROM ' . $this->fileboxtablename . ' WHERE listingid=' . $this->ct->Params->listing_id . ' AND fileid=' . $fileid;
                $db->setQuery($query);
                $db->execute();
            }
        }

        $this->ct->Table->saveLog($this->ct->Params->listing_id, 9);

        return true;
    }

    function add(): bool
    {
        $file = $this->ct->Env->jinput->files->get('uploadedfile'); //not zip -  regular Joomla input method will be used

        $uploadedFile = "tmp/" . basename($file['name']);

        if (!move_uploaded_file($file['tmp_name'], $uploadedFile))
            return false;


        if ($this->ct->Env->jinput->getCmd('base64ecnoded', '') == "true") {
            $src = $uploadedFile;
            $dst = "tmp/decoded_" . basename($file['name']);
            CustomTablesFileMethods::base64file_decode($src, $dst);
            $uploadedFile = $dst;
        }

        //Save to DB
        $file_ext = CustomTablesFileMethods::FileExtension($uploadedFile, $this->allowedExtensions);
        if ($file_ext == '') {
            //unknown file extension (type)
            unlink($uploadedFile);

            return false;
        }

        $filenameParts = explode('/', $uploadedFile);
        $filename = end($filenameParts);
        $title = str_replace('.' . $file_ext, '', $filename);

        $fileid = $this->addFileRecord($file_ext, $title);

        //es Thumb
        $newfilename = $this->fileboxfolder . DIRECTORY_SEPARATOR . $this->ct->Table->tableid . '_' . $this->fileboxname . '_' . $fileid . "." . $file_ext;

        if (!copy($uploadedFile, $newfilename)) {
            unlink($uploadedFile);
            return false;
        }

        unlink($uploadedFile);

        $this->ct->Table->saveLog($this->ct->Params->listing_id, 8);
        return true;
    }


    function addFileRecord(string $file_ext, string $title): int
    {
        $db = Factory::getDBO();

        $query = 'INSERT ' . $this->fileboxtablename . ' SET '
            . 'file_ext=' . $db->quote($file_ext) . ', '
            . 'ordering=0, '
            . 'listingid=' . $db->quote($this->ct->Params->listing_id) . ', '
            . 'title=' . $db->quote($title);

        $db->setQuery($query);

        try {
            $db->execute();
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
            die;
        }

        $query = ' SELECT fileid FROM ' . $this->fileboxtablename . ' WHERE listingid=' . $db->quote($this->ct->Params->listing_id) . ' ORDER BY fileid DESC LIMIT 1';
        $db->setQuery($query);

        $rows = $db->loadObjectList();
        if (count($rows) == 1) {
            return $rows[0]->fileid;
        }

        return -1;
    }
}
