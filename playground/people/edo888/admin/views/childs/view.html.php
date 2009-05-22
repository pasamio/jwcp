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
	    echo 'in viewChilds display';
        JToolBarHelper::title(JText::_('WCP Manager'), 'generic.png');
        JToolBarHelper::addNew();
        JToolBarHelper::editList();
        JToolBarHelper::deleteList();
        JToolBarHelper::help('screen.wcp');

	    // Get data from the model
        $items =& $this->get('Data');

        $this->assignRef('items', $items);
        parent::display($tpl);
	}

}