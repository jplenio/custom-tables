<?php
/**
 * CustomTables Joomla! 3.x Native Component
 * @author Ivan komlev <support@joomlaboat.com>
 * @link http://www.joomlaboat.com
 * @license GNU/GPL
 **/

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Pagination Class.  Provides a common interface for content pagination for the
 * Joomla! Framework.
 *
 * @package     Joomla.Platform
 * @subpackage  HTML
 * @since       11.1
 */
class JESPagination extends JObject
{
	/**
	 * The record number to start dislpaying from.
	 *
	 * @var    integer
	 * @since  11.1
	 */
	public $limitstart = null;

	/**
	 * Number of rows to display per page.
	 *
	 * @var integer
	 * @since  11.1
	 */
	public $limit = null;

	/**
	 * Total number of rows.
	 *
	 * @var integer
	 * @since  11.1
	 */
	public $total = null;

	/**
	 * Prefix used for request variables.
	 *
	 * @var    integer
	 * @since  11.1
	 */
	public $prefix = null;

	/**
	 * View all flag
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	protected $_viewall = false;

	/**
	 * Additional URL parameters to be added to the pagination URLs generated by the class.  These
	 * may be useful for filters and extra values when dealing with lists and GET requests.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $_additionalUrlParams = array();

	/**
	 * Constructor.
	 *
	 * @param   integer  $total       The total number of items.
	 * @param   integer  $limitstart  The offset of the item to start at.
	 * @param   integer  $limit       The number of items to display per page.
	 * @param   string   $prefix      The prefix used for request variables.
	 */
	function __construct($total, $limitstart, $limit, $prefix = '')
	{
		// Value/type checking.
		$this->total		= (int) $total;
		$this->limitstart	= (int) max($limitstart, 0);
		$this->limit		= (int) max($limit, 0);
		$this->prefix		= $prefix;

		if ($this->limit > $this->total) {
			$this->limitstart = 0;
		}

		if (!$this->limit)
		{
			$this->limit = $total;
			$this->limitstart = 0;
		}

		/*
		 * If limitstart is greater than total (i.e. we are asked to display records that don't exist)
		 * then set limitstart to display the last natural page of results
		 */
		if ($this->limitstart > $this->total - $this->limit) {
			$this->limitstart = max(0, (int)(ceil($this->total / $this->limit) - 1) * $this->limit);
		}

		// Set the total pages and current page values.
		if ($this->limit > 0)
		{
			$this->set('pages.total', ceil($this->total / $this->limit));
			$this->set('pages.current', ceil(($this->limitstart + 1) / $this->limit));
		}

		// Set the pagination iteration loop values.
		$displayedPages	= 10;
		$this->set('pages.start', $this->get('pages.current') - ($displayedPages / 2));
		if ($this->get('pages.start') < 1) {
			$this->set('pages.start', 1);
		}
		if (($this->get('pages.start') + $displayedPages) > $this->get('pages.total')) {
			$this->set('pages.stop', $this->get('pages.total'));
			if ($this->get('pages.total') < $displayedPages) {
				$this->set('pages.start', 1);
			} else {
				$this->set('pages.start', $this->get('pages.total') - $displayedPages + 1);
			}
		} else {
			$this->set('pages.stop', ($this->get('pages.start') + $displayedPages - 1));
		}

		// If we are viewing all records set the view all flag to true.
		if ($limit == 0) {
			$this->_viewall = true;
		}
	}

	/**
	 * Method to set an additional URL parameter to be added to all pagination class generated
	 * links.
	 *
	 * @param   string   $key    The name of the URL parameter for which to set a value.
	 * @param   mixed    $value  The value to set for the URL parameter.
	 *
	 * @return  mixed    The old value for the parameter.
	 *
	 * @since   11.1
	 */
	public function setAdditionalUrlParam($key, $value)
	{
		// Get the old value to return and set the new one for the URL parameter.
		$result = isset($this->_additionalUrlParams[$key]) ? $this->_additionalUrlParams[$key] : null;

		// If the passed parameter value is null unset the parameter, otherwise set it to the given value.
		if ($value === null) {
			unset($this->_additionalUrlParams[$key]);
		}
		else {
			$this->_additionalUrlParams[$key] = $value;
		}

		return $result;
	}

	/**
	 * Method to get an additional URL parameter (if it exists) to be added to
	 * all pagination class generated links.
	 *
	 * @param   string   $key	The name of the URL parameter for which to get the value.
	 *
	 * @return  mixed    The value if it exists or null if it does not.
	 *
	 * @since   11.1
	 */
	public function getAdditionalUrlParam($key)
	{
		$result = isset($this->_additionalUrlParams[$key]) ? $this->_additionalUrlParams[$key] : null;

		return $result;
	}

	/**
	 * Return the rationalised offset for a row with a given index.
	 *
	 * @param   integer  $index   The row index
	 *
	 * @return  integer  Rationalised offset for a row with a given index.
	 * @since   11.1
	 */
	public function getRowOffset($index)
	{
		return $index +1 + $this->limitstart;
	}

	/**
	 * Return the pagination data object, only creating it if it doesn't already exist.
	 *
	 * @return  object   Pagination data object.
	 * @since   11.1
	 */
	public function getData()
	{
		static $data;
		if (!is_object($data)) {
			$data = $this->_buildDataObject();
		}
		return $data;
	}

	/**
	 * Create and return the pagination pages counter string, ie. Page 2 of 4.
	 *
	 * @return  string   Pagination pages counter string.
	 * @since   11.1
	 */
	public function getPagesCounter()
	{
		// Initialise variables.
		$html = null;
		if ($this->get('pages.total') > 1) {
			$html .= JText::sprintf('JLIB_HTML_PAGE_CURRENT_OF_TOTAL', $this->get('pages.current'), $this->get('pages.total'));
		}
		return $html;
	}

	/**
	 * Create and return the pagination result set counter string, e.g. Results 1-10 of 42
	 *
	 * @return  string   Pagination result set counter string.
	 * @since   11.1
	 */
	public function getResultsCounter()
	{
		// Initialise variables.
		$html = null;
		$fromResult = $this->limitstart + 1;

		// If the limit is reached before the end of the list.
		if ($this->limitstart + $this->limit < $this->total) {
			$toResult = $this->limitstart + $this->limit;
		}
		else {
			$toResult = $this->total;
		}

		// If there are results found.
		if ($this->total > 0) {
			$msg = JText::sprintf('JLIB_HTML_RESULTS_OF', $fromResult, $toResult, $this->total);
			$html .= "\n".$msg;
		}
		else {
			$html .= "\n".JoomlaBasicMisc::JTextExtended('JLIB_HTML_NO_RECORDS_FOUND');
		}

		return $html;
	}

	/**
	 * Create and return the pagination page list string, ie. Previous, Next, 1.6.1 ... x.
	 *
	 * @return  string   Pagination page list string.
	 * @since   11.1
	 */
	public function getPagesLinks($columns)
	{
	    $the_step=(int)$columns*2;

		$app = JFactory::getApplication();

		// Build the page navigation list.
		$data = $this->_buildDataObject();

		$list = array();
		$list['prefix']			= $this->prefix;

		$itemOverride = false;
		$listOverride = false;

		/*
		$chromePath = JPATH_THEMES . '/' . $app->getTemplate() . '/html/pagination.php';
		if (file_exists($chromePath))
		{
			require_once $chromePath;
			if (function_exists('pagination_item_active') && function_exists('pagination_item_inactive')) {
				$itemOverride = true;
			}
			if (function_exists('pagination_list_render')) {
				$listOverride = true;
			}
		}
		*/

		// Build the select list
		if ($data->all->base !== null) {
			$list['all']['active'] = true;
			$list['all']['data'] = ($itemOverride) ? pagination_item_active($data->all) : $this->_item_active($data->all);
		} else {
			$list['all']['active'] = false;
			$list['all']['data'] = ($itemOverride) ? pagination_item_inactive($data->all) : $this->_item_inactive($data->all);
		}

		if ($data->start->base !== null) {
			$list['start']['active'] = true;
			$list['start']['data'] = ($itemOverride) ? pagination_item_active($data->start) : $this->_item_active($data->start);
		} else {
			$list['start']['active'] = false;
			$list['start']['data'] = ($itemOverride) ? pagination_item_inactive($data->start) : $this->_item_inactive($data->start);
		}
		if ($data->previous->base !== null) {
			$list['previous']['active'] = true;
			$list['previous']['data'] = ($itemOverride) ? pagination_item_active($data->previous) : $this->_item_active($data->previous);
		} else {
			$list['previous']['active'] = false;
			$list['previous']['data'] = ($itemOverride) ? pagination_item_inactive($data->previous) : $this->_item_inactive($data->previous);
		}

		$list['pages'] = array(); //make sure it exists
		foreach ($data->pages as $i => $page)
		{
			if ($page->base !== null) {
				$list['pages'][$i]['active'] = true;
				$list['pages'][$i]['data'] = ($itemOverride) ? pagination_item_active($page) : $this->_item_active($page);
			} else {
				$list['pages'][$i]['active'] = false;
				$list['pages'][$i]['data'] = ($itemOverride) ? pagination_item_inactive($page) : $this->_item_inactive($page,$current_page = true);
			}
		}

		if ($data->next->base !== null) {
			$list['next']['active'] = true;
			$list['next']['data'] = ($itemOverride) ? pagination_item_active($data->next) : $this->_item_active($data->next);
		}
		else {
			$list['next']['active'] = false;
			$list['next']['data'] = ($itemOverride) ? pagination_item_inactive($data->next) : $this->_item_inactive($data->next);
		}

		if ($data->end->base !== null) {
			$list['end']['active'] = true;
			$list['end']['data'] = ($itemOverride) ? pagination_item_active($data->end) : $this->_item_active($data->end);
		}

		else {
			$list['end']['active'] = false;
			$list['end']['data'] = ($itemOverride) ? pagination_item_inactive($data->end) : $this->_item_inactive($data->end);
		}

		if ($this->total > $this->limit){
			return ($listOverride) ? pagination_list_render($list) : $this->_list_render($list);
		}
		else {
			return '';
		}
	}

	/**
	 * Return the pagination footer.
	 *
	 * @return  string   Pagination footer.
	 * @since   11.1
	 */
	public function getListFooter()
	{
		$app = JFactory::getApplication();

		$list = array();
		$list['prefix']			= $this->prefix;
		$list['limit']			= $this->limit;
		$list['limitstart']		= $this->limitstart;
		$list['total']			= $this->total;
		$list['limitfield']		= $this->getLimitBox();
		$list['pagescounter']	= $this->getPagesCounter();
		$list['pageslinks']		= $this->getPagesLinks();

		/*
		$chromePath	= JPATH_THEMES . '/' . $app->getTemplate() . '/html/pagination.php';
		if (file_exists($chromePath))
		{
			require_once $chromePath;
			if (function_exists('pagination_list_footer')) {
				return pagination_list_footer($list);
			}
		}
		*/
		return $this->_list_footer($list);
	}

	/**
	 * Creates a dropdown box for selecting how many records to show per page.
	 *
	 * @return  string   The HTML for the limit # input box.
	 * @since   11.1
	 */
	public function getLimitBox($the_step = 5)
	{
		$the_step = (int)$the_step;
		
		if($the_step < 1)
			$the_step = 1;
		
		if($the_step > 1000)
			$the_step = 1000;
		
		$app = JFactory::getApplication();

		// Initialise variables.
		$limits = array ();

		// Make the option list.
        for ($i = $the_step; $i <= $the_step*6; $i += $the_step) {
		    $limits[] = JHTML::_('select.option', "$i");
		}

		$limits[] = JHTML::_('select.option', $the_step*10);
		$limits[] = JHTML::_('select.option', $the_step*20);
		//$limits[] = JHTML::_('select.option', '0', JoomlaBasicMisc::JTextExtended('JALL')); don't show all ever

		$selected = $this->_viewall ? 0 : $this->limit;

		// Build the select list.
		$html = JHtml::_('select.genericlist',  $limits, $this->prefix . 'limit', 'class="inputbox" size="1" onchange="ctLimitChanged(this);"', 'value', 'text', $selected);
		return $html;
	}

	/**
	 * Return the icon to move an item UP.
	 *
	 * @param   integer  $i          The row index.
	 * @param   boolean  $condition  True to show the icon.
	 * @param   string   $task       The task to fire.
	 * @param   string   $alt        The image alternative text string.
	 * @param   boolean  $enabled    An optional setting for access control on the action.
	 * @param   string   $checkbox   An optional prefix for checkboxes.
	 *
	 * @return  string   Either the icon to move an item up or a space.
	 * @since   11.1
	 */
	public function orderUpIcon($i, $condition = true, $task = 'orderup', $alt = 'JLIB_HTML_MOVE_UP', $enabled = true, $checkbox='cb')
	{
		if (($i > 0 || ($i + $this->limitstart > 0)) && $condition) {
			return JHtml::_('jgrid.orderUp', $i, $task, '', $alt, $enabled, $checkbox);
		}
		else {
			return '&#160;';
		}
	}

	/**
	 * Return the icon to move an item DOWN.
	 *
	 * @param   integer  $i          The row index.
	 * @param   integer  $n          The number of items in the list.
	 * @param   boolean  $condition  True to show the icon.
	 * @param   string   $task       The task to fire.
	 * @param   string   $alt        The image alternative text string.
	 * @param   boolean  $enabled    An optional setting for access control on the action.
	 * @param   string   $checkbox   An optional prefix for checkboxes.
	 *
	 * @return  string   Either the icon to move an item down or a space.
	 * @since   11.1
	 */
	public function orderDownIcon($i, $n, $condition = true, $task = 'orderdown', $alt = 'JLIB_HTML_MOVE_DOWN', $enabled = true, $checkbox='cb')
	{
		if (($i < $n -1 || $i + $this->limitstart < $this->total - 1) && $condition) {
			return JHtml::_('jgrid.orderDown', $i, $task, '', $alt, $enabled, $checkbox);
		}
		else {
			return '&#160;';
		}
	}

	/*
	 * Create the HTML for a list footer
	 *
	 * @param    array  $list
	 *
	 * @return   string  HTML for a list footer
	 * @since    11.1
	 */
	protected function _list_footer($list)
	{
		$html = "<div class=\"list-footer\">\n";

		$html .= "\n<div class=\"limit\">".JoomlaBasicMisc::JTextExtended('JGLOBAL_DISPLAY_NUM').$list['limitfield']."</div>";
		$html .= $list['pageslinks'];
		$html .= "\n<div class=\"counter\">".$list['pagescounter']."</div>";

		$html .= "\n<input type=\"hidden\" name=\"" . $list['prefix'] . "start\" value=\"".$list['limitstart']."\" />";
		$html .= "\n</div>";

		return $html;
	}

	/*
	 * Create the html for a list footer
	 *
	 * @param    array  $list
	 *
	 * @return   string  HTML for a list start, previous, next,end
	 * @since    11.1
	 */
	protected function _list_render($list)
	{
		// Reverse output rendering for right-to-left display.
		$html = '<ul>';
		$html .= '<li class="pagination-start">'.$list['start']['data'].'</li>';
		$html .= '<li class="pagination-prev">'.$list['previous']['data'].'</li>';
		foreach($list['pages'] as $page) {
			$html .= '<li>'.$page['data'].'</li>';
		}
		$html .= '<li class="pagination-next">'. $list['next']['data'].'</li>';
		$html .= '<li class="pagination-end">'. $list['end']['data'].'</li>';
		$html .= '</ul>';

		return $html;
	}

	/*
	 *
	 *
	 * @param    object  $item
	 *
	 * @return   string  HTML link
	 * @since    11.1
	 */
	protected function _item_active(&$item)
	{
		$app = JFactory::getApplication();
		if($app->isClient('administrator'))
		{
			if ($item->base > 0) {
				return "<a title=\"".$item->text."\" onclick=\"document.adminForm." . $this->prefix . "start.value=".$item->base."; Joomla.submitform();return false;\">".$item->text."</a>";
			}
			else {
				return "<a title=\"".$item->text."\" onclick=\"document.adminForm." . $this->prefix . "start.value=0; Joomla.submitform();return false;\">".$item->text."</a>";
			}
		}
		else {
			return "<a title=\"".$item->text."\" href=\"".$item->link."\" class=\"pagenav\">".$item->text."</a>";
		}
	}

	/*
	 *
	 *
	 * @param    object  $item
	 *
	 * @return   string
	 * @since    11.1
	 */
	protected function _item_inactive(&$item, $current_page = false)
	{
		$app = JFactory::getApplication();
		if($app->isClient('administrator'))
			return "<span>".$item->text."</span>";
		else
			return "<span class=\"pagenav".($current_page ? ' active' : '')."\">".$item->text."</span>";
	}

	/**
	 * Create and return the pagination data object.
	 *
	 * @return  object  Pagination data object.
	 * @since   11.1
	 */
	protected function _buildDataObject()
	{
		// Initialise variables.
		$data = new stdClass;

		// Build the additional URL parameters string.
		$query_params = '';
		if (!empty($this->_additionalUrlParams))
		{
			foreach($this->_additionalUrlParams as $key => $value)
			{
				$query_params .= '&'.$key.'='.$value;
			}
		}
		
		$query_paramsPlusPrefix = JoomlaBasicMisc::curPageURL();
		$query_paramsPlusPrefix = JoomlaBasicMisc::deleteURLQueryOption($query_paramsPlusPrefix, 'start');
			
		if($query_params!='')
			$query_paramsPlusPrefix .= (strpos($query_paramsPlusPrefix,'?')===false ? '?' : '&') . $this->prefix;
			
		if($this->prefix!='')
			$query_paramsPlusPrefix .= (strpos($query_paramsPlusPrefix,'?')===false ? '?' : '&') . $this->prefix;
		
		$data->all = new JESPaginationObject(JoomlaBasicMisc::JTextExtended('JLIB_HTML_VIEW_ALL'), $this->prefix);
		if (!$this->_viewall) {
			$data->all->base	= '0';
			$data->all->link	= JRoute::_($query_paramsPlusPrefix);
		}

		// Set the start and previous data objects.
		$data->start	= new JESPaginationObject(JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_START'), $this->prefix);
		$data->previous	= new JESPaginationObject(JoomlaBasicMisc::JTextExtended('JPREV'), $this->prefix);

		if ($this->get('pages.current') > 1)
		{
			$page = ($this->get('pages.current') -2) * $this->limit;

			// Set the empty for removal from route
			$data->start->base	= '0';
			$data->start->link	= JRoute::_($query_paramsPlusPrefix);
			$data->previous->base	= $page;
			
			if($page == 0)
				$data->previous->link	= JRoute::_($query_paramsPlusPrefix);
			else
				$data->previous->link	= JRoute::_($query_paramsPlusPrefix.(strpos($query_paramsPlusPrefix,'?')===false ? '?' : '&') .'start='.$page);
		}

		// Set the next and end data objects.
		$data->next	= new JESPaginationObject(JoomlaBasicMisc::JTextExtended('JNEXT'), $this->prefix);
		$data->end	= new JESPaginationObject(JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_END'), $this->prefix);

		if ($this->get('pages.current') < $this->get('pages.total'))
		{
			$next = $this->get('pages.current') * $this->limit;
			$end  = ($this->get('pages.total') -1) * $this->limit;

			$data->next->base	= $next;
			$data->next->link	= JRoute::_($query_paramsPlusPrefix.(strpos($query_paramsPlusPrefix,'?')===false ? '?' : '&') .'start='.$next);
			$data->end->base	= $end;
			$data->end->link	= JRoute::_($query_paramsPlusPrefix.(strpos($query_paramsPlusPrefix,'?')===false ? '?' : '&') .'start='.$end);
		}

		$data->pages = array();
		$stop = $this->get('pages.stop');
		for ($i = $this->get('pages.start'); $i <= $stop; $i ++)
		{
			$offset = ($i -1) * $this->limit;
			// Set the empty for removal from route

			$data->pages[$i] = new JESPaginationObject($i, $this->prefix);
			if ($i != $this->get('pages.current') || $this->_viewall)
			{
				$data->pages[$i]->base	= $offset;
				if($offset == 0)
					$data->pages[$i]->link	= JRoute::_($query_paramsPlusPrefix);
				else
					$data->pages[$i]->link	= JRoute::_($query_paramsPlusPrefix.(strpos($query_paramsPlusPrefix,'?')===false ? '?' : '&') .'start='.$offset);
			}
		}
		return $data;
	}
}

/**
 * Pagination object representing a particular item in the pagination lists.
 *
 * @package     Joomla.Platform
 * @subpackage  HTML
 * @since       11.1
 */
class JESPaginationObject extends JObject
{
	/**
	 *
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $text;

	/**
	 *
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $base;

	/**
	 *
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $link;

	/**
	 *
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $prefix;

	/*
	 *
	 *
	 * @param   string   $text
	 * @param   string   $prefix
	 * @param   string   $base
	 * @param   string   $link
	 *
	 * @return
	 * @since    11.1
	 */
	public function __construct($text, $prefix = '', $base = null, $link = null)
	{
		$this->text = $text;
		$this->prefix = $prefix;
		$this->base = $base;
		$this->link = $link;
	}
}
