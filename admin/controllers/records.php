<?php
/**
 * CustomTables Joomla! 3.x/4.x Native Component
 * @package Custom Tables
 * @author Ivan komlev <support@joomlaboat.com>
 * @link https://joomlaboat.com
 * @copyright Copyright (C) 2018-2020. All Rights Reserved
 * @license GNU/GPL Version 2 or later - https://www.gnu.org/licenses/gpl-2.0.html
 **/

// No direct access to this file
if (!defined('_JEXEC') and !defined('WPINC')) {
    die('Restricted access');
}

use CustomTables\CT;
use CustomTables\Layouts;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

// import Joomla controllerform library
jimport('joomla.application.component.controllerform');

/**
 * Records Controller
 */
class CustomtablesControllerRecords extends JControllerForm
{
    /**
     * Current or most recently performed task.
     *
     * @var    string
     * @since  12.2
     * @note   Replaces _task.
     */
    protected $task;

    public function __construct($config = array())
    {
        $this->view_list = 'Listofrecords'; // safeguard for setting the return view listing to the main view.
        parent::__construct($config);
    }

    /**
     * Method to cancel an edit.
     *
     * @param string $key The name of the primary key of the URL variable.
     *
     * @return  boolean  True if access level checks pass, false otherwise.
     *
     * @since   12.2
     */
    public function cancel($key = null): bool
    {
        // get the referral details
        $tableid = $this->input->get('tableid', 0, 'int');

        $cancel = parent::cancel($key);

        // Redirect to the items screen.
        $this->setRedirect(
            JRoute::_(
                'index.php?option=' . $this->option . '&view=listofrecords&tableid=' . (int)$tableid, false
            //'index.php?option=' . $this->option . '&view=listofrecords&layout=edit&tableid='.(int)$tableid, false
            )
        );

        return $cancel;
    }

    public function save($key = null, $urlVar = null): bool
    {
        $tablename = null;

        $tableid = $this->input->get('tableid', 0, 'int');
        if ($tableid != 0) {
            $table = ESTables::getTableRowByID($tableid);
            if (!is_object($table) and $table == 0) {
                Factory::getApplication()->enqueueMessage('Table not found', 'error');
                return false;
            } else {
                $tablename = $table->tablename;
            }
        }

        $listing_id = $this->input->getCmd('id', 0);

        $paramsArray = array();
        $paramsArray['tableid'] = $tableid;
        $paramsArray['establename'] = $tablename;
        $paramsArray['publishstatus'] = 1;
        $paramsArray['listingid'] = $listing_id;


        $params = new JRegistry;
        $params->loadArray($paramsArray);

        $ct = new CT;
        $ct->setParams($params, true);

        require_once(JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_customtables' . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'edititem.php');
        $editModel = JModelLegacy::getInstance('EditItem', 'CustomTablesModel', $params);
        $editModel->load($ct);

        $Layouts = new Layouts($ct);

        $editModel->pageLayout = $Layouts->createDefaultLayout_Edit($ct->Table->fields, false);

        $msg_ = '';

        if ($this->task == 'save2copy')
            $saved = $editModel->copy($msg_, $link);
        elseif ($this->task == 'save' or $this->task == 'apply' or $this->task == 'save2new')
            $saved = $editModel->store($msg_, $link);

        $redirect = 'index.php?option=' . $this->option;

        if ($this->task == 'apply') {
            Factory::getApplication()->enqueueMessage(JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_RECORD_SAVED'), 'success');
            $redirect .= '&view=records&layout=edit&id=' . $listing_id . '&tableid=' . (int)$tableid;
        } elseif ($this->task == 'save2copy') {
            Factory::getApplication()->enqueueMessage(JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_RECORDS_COPIED'), 'success');
            $redirect .= '&view=records&task=records.edit&tableid=' . (int)$tableid . '&id=' . $ct->Params->listing_id;
        } elseif ($this->task == 'save2new') {
            Factory::getApplication()->enqueueMessage(JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_RECORD_SAVED'), 'success');
            $redirect .= '&view=records&task=records.edit&tableid=' . (int)$tableid;
        } else {
            Factory::getApplication()->enqueueMessage(JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_RECORD_SAVED'), 'success');
            $redirect .= '&view=listofrecords&tableid=' . (int)$tableid;
        }

        if ($saved) {
            // Redirect to the item screen.
            $this->setRedirect(
                JRoute::_(
                    $redirect, false
                )
            );
        }

        return $saved;
    }

    /**
     * Method override to check if you can add a new record.
     *
     * @param array $data An array of input data.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    protected function allowAdd($data = array())
    {
        // In the absence of better information, revert to the component permissions.
        return parent::allowAdd($data);
    }

    protected function allowEdit($data = array(), $key = 'id')
    {
        //To support char type record id
        $recordId = (int)isset($data[$key]) ? $data[$key] : 0;

        if ($recordId) {
            $user = Factory::getUser();

            // The record has been set. Check the record permissions.
            $permission = $user->authorise('core.edit', 'com_customtables.records.' . $recordId);

            if (!$permission) {
                if ($user->authorise('core.edit.own', 'com_customtables.records.' . $recordId)) {
                    // Now test the owner is the user.
                    $ownerId = (int)isset($data['created_by']) ? $data['created_by'] : 0;
                    if (empty($ownerId)) {
                        // Need to do a lookup from the model.
                        $record = $this->getModel()->getItem($recordId);

                        if (empty($record)) {
                            return false;
                        }
                        $ownerId = $record->created_by;
                    }

                    // If the owner matches 'me' then allow.
                    if ($ownerId == $user->id) {
                        if ($user->authorise('core.edit.own', 'com_customtables')) {
                            return true;
                        }
                    }
                }
                return false;
            }
        }
        // Since there is no permission, revert to the component permissions.
        return true;
    }

    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param integer $listing_id The primary key id for the item.
     * @param string $urlVar The name of the URL variable for the id.
     *
     * @return  string  The arguments to append to the redirect URL.
     *
     * @since   12.2
     */
    protected function getRedirectToItemAppend($listing_id = null, $urlVar = 'id'): string
    {
        $tmpl = $this->input->get('tmpl');
        $layout = $this->input->get('layout', 'edit', 'string');

        $ref = $this->input->get('ref', 0, 'string');
        $refid = $this->input->getCmd('refid', 0);

        //To support char type record id
        $listing_id = $this->input->getCmd('id', null);
        if ($listing_id === null) {
            $cid = Factory::getApplication()->input->post->get('cid', array(), 'array');
            $cid = ArrayHelper::toInteger($cid);
            $listing_id = $cid[0];
        }

        //throw new Exception('stop here');

        $tableid = $this->input->getInt('tableid', 0);
        // Setup redirect info.

        $append = '';

        if ($refid) {
            $append .= '&ref=' . $ref . '&refid=' . $refid;
        } elseif ($ref) {
            $append .= '&ref=' . $ref;
        }

        if ($tmpl) {
            $append .= '&tmpl=' . $tmpl;
        }

        if ($layout) {
            $append .= '&layout=' . $layout;
        }

        $append .= '&tableid=' . $tableid;

        if ($listing_id) {
            $append .= '&' . $urlVar . '=' . $listing_id;
        }

        //This is to overwrite Joomla current record ID state value. Joomla converts ID to integer, but we want to support both int and cmd (A-Za-z\d_-)
        $values = (array)Factory::getApplication()->getUserState('com_customtables.edit.records.id');
        $values[] = $listing_id;
        Factory::getApplication()->setUserState('com_customtables.edit.records.id', $values);

        return $append;
    }
}
