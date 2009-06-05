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
 * Working Copy Differencies View class
 *
 */
class WCPViewDifferencies extends JView {

    /**
     * Display differencies
     *
     * @access public
     */
	function display($tpl = null) {
	    global $mainframe, $option;

	    require_once(JPATH_COMPONENT.DS.'helpers'.DS.'wcp.php');

        JToolBarHelper::title(JText::_('WCP Manager') . ': <small><small>[ ' . JText::_('Differencies') . ' ]</small></small>', 'generic.png');
        JToolBarHelper::custom('patch', 'new.png', 'new.png', 'Create Patch');
        JToolBarHelper::custom('refreshDiff', 'refresh.png', 'refresh.png', 'Refresh', '', false);
        JToolBarHelper::help('screen.wcp.differencies');

        $cache =& JFactory::getCache();
        $cache->setCaching(true);

	    // Get data from the model
        $items = $cache->call(array('WCPHelper', 'getDifferencies'));

        $this->assignRef('items', $items);
        parent::display($tpl);
	}

}