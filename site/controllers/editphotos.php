<?php
/**
 * CustomTables Joomla! 3.x Native Component
 * @package Custom Tables
 * @author Ivan komlev <support@joomlaboat.com>
 * @link https://www.joomlaboat.com
 * @copyright Copyright (C) 2018-2022. All Rights Reserved
 * @license GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 **/

// no direct access
use CustomTables\CT;
use CustomTables\CTUser;
use Joomla\CMS\Factory;

defined('_JEXEC') or die('Restricted access');

	$jinput = Factory::getApplication()->input;

    $ct = new CT;
	$model = $this->getModel('edititem');
	//$model->params=Factory::getApplication()->getParams();;
	//$model->listing_id = $jinput->getCmd("listing_id");
	
	if(!CTUser::CheckAuthorization($ct))
	{
		//not authorized
		$link =JRoute::_('index.php?option=com_users&view=login&return='.base64_encode(JoomlaBasicMisc::curPageURL()));
		$this->setRedirect($link,JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_YOU_MUST_LOGIN_FIRST'));
	}
	else
	{
		switch(Factory::getApplication()->input->getCmd( 'task' ))
		{
			case 'add' :
				$model = $this->getModel('editphotos');
				$model->load($ct);

				if ($model->add())
				{
					$msg = JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_IMAGE_ADDED' );
				}
				else
				{
					$msg = JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_IMAGE_NOT_ADDED');
				}
				
				$establename=Factory::getApplication()->input->getCmd( 'establename');
				$galleryname=Factory::getApplication()->input->get('galleryname','','CMD');
				$listing_id=Factory::getApplication()->input->get("listing_id",0,'INT');
				$returnto=Factory::getApplication()->input->get('returnto','','BASE64');
				$Itemid=Factory::getApplication()->input->get('Itemid',0,'INT');

				$link 	= 'index.php?option=com_customtables&view=editphotos'
					.'&establename='.$establename
					.'&galleryname='.$galleryname
					.'&listing_id='.$listing_id
					.'&returnto='.$returnto
					.'&Itemid='.$Itemid;
				
				$this->setRedirect($link, $msg);
			break;

			case 'delete' :
				$model = $this->getModel('editphotos');
				$model->load($ct);

				if ($model->delete())
				{
					$msg = JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_IMAGE_DELETED' );
				}
				else
				{
					$msg = JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_IMAGE_NOT_DELETED');
				}
			
				$establename=Factory::getApplication()->input->getCmd( 'establename');
				$galleryname=Factory::getApplication()->input->get('galleryname','','CMD');
				$listing_id=Factory::getApplication()->input->get("listing_id",0,'INT');
				$returnto=Factory::getApplication()->input->get('returnto','','BASE64');
				$Itemid=Factory::getApplication()->input->get('Itemid',0,'INT');

				$link 	= 'index.php?option=com_customtables&view=editphotos'
											.'&establename='.$establename
											.'&galleryname='.$galleryname
											.'&listing_id='.$listing_id
											.'&returnto='.$returnto
											.'&Itemid='.$Itemid;

				$this->setRedirect($link, $msg);
				break;

			case 'saveorder' :
				$model = $this->getModel('editphotos');
				$model->load($ct);

				if ($model->reorder())
				{
					$msg = JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_IMAGE_ORDER_SAVED' );
				}
				else
				{
					$msg = JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_IMAGE_ORDER_NOT_SAVED');
				}

				$returnto=Factory::getApplication()->input->get('returnto','','BASE64');

				$link 	= $returnto=base64_decode (Factory::getApplication()->input->get('returnto','','BASE64'));
				$this->setRedirect($link, $msg);
				break;

			case 'cancel' :
				$msg = JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_EDIT_CANCELED' );
				$link 	= $returnto=base64_decode (Factory::getApplication()->input->get('returnto','','BASE64'));
				$this->setRedirect($link, $msg);
				break;
			default:
				parent::display();
		}
	}
