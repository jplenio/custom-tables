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
if (!defined('_JEXEC') and !defined('WPINC')) {
    die('Restricted access');
}

// import Joomla controllerform library
jimport('joomla.application.component.controllerform');

class CustomTablesControllerDatabaseCheck extends JControllerForm
{
    protected $task;

    public function __construct($config = array())
    {
        $this->view_list = 'databasecheck'; // safeguard for setting the return view listing to the main view.
        parent::__construct($config);
    }
}
