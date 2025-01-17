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
use CustomTables\Layouts;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldCTLayoutType extends JFormFieldList
{
    /**
     * Element name
     *
     * @access    public
     * @var        string
     *
     */
    public $type = 'ctlayouttype';

    public function getOptions()
    {
        $ct = new CT;

        // Get a db connection.
        $db = Factory::getDbo();

        // Create a new query object.
        $query = $db->getQuery(true);

        // Select the text.
        $query->select($db->quoteName('layouttype'));
        $query->from($db->quoteName('#__customtables_layouts'));
        $query->order($db->quoteName('layouttype'));

        // Reset the query using our newly populated query object.
        $db->setQuery($query);

        $results = $db->loadColumn();

        $options = array();
        if ($results) {
            $Layouts = new Layouts($ct);
            $translations = $Layouts->layoutTypeTranslation();

            $results = array_unique($results);
            $options[] = JHtml::_('select.option', '', Text::_('COM_CUSTOMTABLES_LAYOUTS_LAYOUTTYPE_SELECT'));

            foreach ($results as $layouttype) {
                // Translate the layouttype selection
                if ((int)$layouttype != 0) {
                    $text = $translations[$layouttype];
                    // Now add the layouttype and its text to the options array
                    $options[] = JHtml::_('select.option', $layouttype, Text::_($text));
                }
            }
        }
        return $options;
    }
}
