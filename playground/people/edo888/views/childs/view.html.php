<?php
/**
 * @version   $Id$
 * @copyright Copyright (C) 2009 Edvard Ananyan. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * Working Copy Childs View class
 *
 */
class WCPViewChilds extends JView {

    /**
     * Display childs
     *
     * @access public
     */
	function display($tpl = null) {
	    global $mainframe, $option;

	    require_once(JPATH_COMPONENT.DS.'helper.php');

        JToolBarHelper::title(JText::_('WCP Manager') . ': <small><small>[ ' . (WCPHelper::isMaster() ? JText::_('Master') : JText::_('Child')) . ' ]</small></small>', 'generic.png');
        JToolBarHelper::addNew();
        JToolBarHelper::editList();
        JToolBarHelper::deleteList();
        if(!WCPHelper::isMaster())
            JToolBarHelper::custom('diff', 'diff.png', 'diff.png', 'Differences', '', false);
        if(WCPHelper::isMaster())
            JToolBarHelper::custom('applyPatch', 'apply.png', 'apply.png', 'Apply Patch', '', false);
        JToolBarHelper::help('screen.wcp', true);

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