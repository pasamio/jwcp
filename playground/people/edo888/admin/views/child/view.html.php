<?php
/**
 * @version   $Id$
 * @copyright Copyright (C) 2009 Edvard Ananyan. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

class WCPViewChild extends JView {

	function display($tpl = null) {
        JToolBarHelper::title(JText::_('WCP Manager') . ': <small><small>[ ' . (JRequest::getVar('edit', true) ? JText::_('New Child') : JText::_('Edit Child')) . ' ]</small></small>', 'generic.png');
        JToolBarHelper::save();
        JToolBarHelper::apply();
        JRequest::getVar('edit', true) ? JToolBarHelper::cancel('cancel', 'Close') : JToolBarHelper::cancel();
        JToolBarHelper::help('screen.wcp.new');

	    // Get data from the model
        $item =& $this->get('Data');

        $this->assignRef('item', $item);
        parent::display($tpl);
	}

}