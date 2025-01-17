<?php
/**
 * CustomTables Joomla! 3.x/4.x Native Component
 * @package Custom Tables
 * @author Ivan Komlev <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright (C) 2018-2022 Ivan Komlev
 * @license GNU/GPL Version 2 or later - https://www.gnu.org/licenses/gpl-2.0.html
 **/

// No direct access to this file
defined('JPATH_PLATFORM') or die;

/**
 * Utility class to render a list view batch selection options
 *
 * @since  3.0
 */
abstract class JHtmlBatch_
{
    /**
     * ListSelection
     *
     * @var    array
     * @since  3.0
     */
    protected static $ListSelection = array();

    /**
     * Render the batch selection options.
     *
     * @return  string  The necessary HTML to display the batch selection options
     *
     * @since   3.0
     */
    public static function render()
    {
        // Collect display data
        $data = new stdClass;
        $data->ListSelection = static::getListSelection();

        // Create a layout object and ask it to render the batch selection options
        $layout = new JLayoutFile('batchselection');
        $batchHtml = $layout->render($data);

        return $batchHtml;
    }

    /**
     * Returns an array of all ListSelection
     *
     * @return  array
     *
     * @since   3.0
     */
    public static function getListSelection()
    {
        return static::$ListSelection;
    }

    /**
     * Method to add a list selection to the batch modal
     *
     * @param string $label Label for the menu item.
     * @param string $name Name for the filter. Also used as id.
     * @param string $options Options for the select field.
     * @param bool $noDefault Don't the label as the empty option
     *
     * @return  void
     *
     * @since   3.0
     */
    public static function addListSelection($label, $name, $options, $noDefault = false)
    {
        array_push(static::$ListSelection, array('label' => $label, 'name' => $name, 'options' => $options, 'noDefault' => $noDefault));
    }
}
