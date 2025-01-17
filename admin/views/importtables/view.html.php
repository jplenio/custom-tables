<?php
/**
 * CustomTables Joomla! 3.x/4.x Native Component
 * @package Custom Tables
 * @subpackage view.html.php
 * @author Ivan komlev <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright Copyright (C) 2018-2020. All Rights Reserved
 * @license GNU/GPL Version 2 or later - https://www.gnu.org/licenses/gpl-2.0.html
 **/

// no direct access
if (!defined('_JEXEC') and !defined('WPINC')) {
    die('Restricted access');
}

// Import Joomla! libraries
jimport('joomla.application.component.view');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;

class CustomTablesViewImportTables extends JViewLegacy
{
    var $catalogview;
    var $version;

    function display($tpl = null)
    {
        $version = new Version;
        $this->version = (int)$version->getShortVersion();

        JToolBarHelper::title(Text::_('Custom Tables - Import Tables', 'generic.png'));//

        parent::display($tpl);
    }

    function generateRandomString($length = 32)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++)
            $randomString .= $characters[rand(0, $charactersLength - 1)];

        return $randomString;
    }
}
