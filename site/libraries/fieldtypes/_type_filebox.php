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

use Joomla\CMS\Factory;

class CT_FieldTypeTag_filebox
{
    public static function process($FileBoxRows, &$field, $listing_id, array $option_list)
    {
        $filesrclistarray = array();

        foreach ($FileBoxRows as $filerow) {
            $filename = $field->ct->Table->tableid . '_' . $field->fieldname . '_' . $filerow->fileid . '.' . $filerow->file_ext;
            $filesrclistarray[] = CT_FieldTypeTag_file::process($filename, $field, $option_list, $listing_id);
        }

        $listformat = '';
        if (isset($option_list[4]))
            $listformat = $option_list[4];

        switch ($listformat) {
            case 'ul':

                $filetaglistarray = array();

                foreach ($filesrclistarray as $filename)
                    $filetaglistarray[] = '<li>' . $filename . '</li>';

                return '<ul>' . implode('', $filetaglistarray) . '</ul>';

            case ',':
                return implode(',', $filesrclistarray);

            case ';':
                return implode(';', $filesrclistarray);

            default:
                //INCLUDING OL
                $filetaglistarray = array();

                foreach ($filesrclistarray as $filename)
                    $filetaglistarray[] = '<li>' . $filename . '</li>';

                return '<ol>' . implode('', $filetaglistarray) . '</ol>';
        }
    }

    public static function getFileBoxRows($tablename, $fieldname, $listing_id)
    {
        $db = Factory::getDBO();
        $fileboxtablename = '#__customtables_filebox_' . $tablename . '_' . $fieldname;

        $query = 'SELECT fileid, file_ext FROM ' . $fileboxtablename . ' WHERE listingid=' . (int)$listing_id . ' ORDER BY fileid';
        $db->setQuery($query);

        return $db->loadObjectList();
    }
}
