<?php
/**
 * CustomTables Joomla! 3.x Native Component
 * @package Custom Tables
 * @author Ivan Komlev <support@joomlaboat.com>
 * @link http://www.joomlaboat.com
 * @copyright (C) 2018-2022 Ivan Komlev
 * @license GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 **/

// no direct access

defined('_JEXEC') or die('Restricted access');

use CustomTables\TwigProcessor;
use Joomla\CMS\HTML\HTMLHelper;

function CTViewEdit(&$ct, $row, &$pagelayout, $formLink,$formName): void
{
	if($ct->Env->legacysupport)
	{
		$path = JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_customtables'.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR;
		require_once($path . 'tagprocessor' . DIRECTORY_SEPARATOR . 'edittags.php');
		require_once($path . 'layout.php');
	}
	
	HTMLHelper::_('jquery.framework');
	jimport('joomla.html.html.bootstrap');

	$ct->loadJSAndCSS();

	if (!$ct->Params->blockExternalVars and $ct->Params->showPageHeading )
	{
		echo '<div class="page-header'.strip_tags($ct->Params->pageClassSFX).'"><h2 itemprop="headline">'
			.JoomlaBasicMisc::JTextExtended($ct->Params->pageTitle ).'</h2></div>';
	}

	if(isset($row[$ct->Table->realidfieldname]))
		$listing_id=(int)$row[$ct->Table->realidfieldname];
	else
		$listing_id=0;

	echo '<form action="'.$formLink.'" method="post" name="'.$formName.'" id="'.$formName.'" class="form-validate form-horizontal well" '
		.'data-tableid="'.$ct->Table->tableid.'" data-recordid="'.$listing_id.'" '
		.'data-version='.$ct->Env->version.'>';

	echo ($ct->Env->version < 4 ? '<fieldset>' : '<fieldset class="options-form">');

	//Calendars of the child should be built again, because when Dom was ready they didn't exist yet.

	$ct->isEditForm = true; //This changes inputbox prefix

	if($ct->Env->legacysupport)
	{
		$LayoutProc=new LayoutProcessor($ct,$pagelayout);

		//Better to run tag processor before rendering form edit elements because of IF statments that can exclude the part of the layout that contains form fields.
		$pagelayout = $LayoutProc->fillLayout($row,null,'||',false,true);
		
		tagProcessor_Edit::process($ct,$pagelayout,$row);
	}

	$twig = new TwigProcessor($ct, $pagelayout);
	$pagelayout = $twig->process($row);
	
	if($ct->Params->allowContentPlugins)
		JoomlaBasicMisc::applyContentPlugins($pagelayout);

	echo $pagelayout;

	$returnto='';

	if($ct->Env->jinput->get('returnto','','BASE64'))
		$returnto=base64_decode($ct->Env->jinput->get('returnto','','BASE64'));
	elseif($ct->Params->returnTo)
		$returnto=$ct->Params->returnTo;

	$encoded_returnto=base64_encode ($returnto);

	if($listing_id==0)
	{
		$publishstatus=$ct->Env->menu_params->get( 'publishstatus' );
		echo '<input type="hidden" name="published" value="'.(int)$publishstatus.'" />';
	}

	echo '
	<input type="hidden" name="task" id="task" value="save" />
	<input type="hidden" name="returnto" id="returnto" value="'.$encoded_returnto.'" />
	<input type="hidden" name="listing_id" id="listing_id" value="'.$listing_id.'" />'
	.($ct->Env->jinput->getCmd('tmpl','') != '' ? '<input type="hidden" name="tmpl" value="'.$ct->Env->jinput->getCmd('tmpl','').'" />' : '')
	.JHtml::_('form.token')
	.'</fieldset>
</form>';

	if($ct->Env->isModal)
		die;
}
