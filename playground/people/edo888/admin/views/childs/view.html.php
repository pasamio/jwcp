<?php
/**
 * @version   $Id$
 * @copyright Copyright (C) 2009 Edvard Ananyan. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

class WCPViewChilds extends JView {

	function display($tpl = null) {
	    global $mainframe, $option;

        JToolBarHelper::title(JText::_('WCP Manager'), 'generic.png');
        JToolBarHelper::addNew();
        JToolBarHelper::editList();
        JToolBarHelper::deleteList();
        JToolBarHelper::help('screen.wcp');

        $filter_order     = $mainframe->getUserStateFromRequest($option.'.filter_order',     'filter_order',     'w.id', 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($option.'.filter_order_Dir', 'filter_order_Dir', '',     'word');

	    // Get data from the model
        $items =& $this->get('Data');

        // table ordering
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order'] = $filter_order;

        $this->assignRef('lists', $lists);
        $this->assignRef('items', $items);
        parent::display($tpl);
	}

}