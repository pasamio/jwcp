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
        JToolBarHelper::title(JText::_('WCP Manager') . ': <small><small>[ ' . JText::_('Differencies') . ' ]</small></small>', 'generic.png');
        JToolBarHelper::custom('createPatch', 'new.png', 'new.png', 'Create Patch');
        JToolBarHelper::custom('refreshDiff', 'refresh.png', 'refresh.png', 'Refresh', '', false);
        JToolBarHelper::help('screen.wcp.differencies');

        $cache =& JFactory::getCache('com_wcp', 'callback', 'file');
        $cache->setCaching(true);

	    // Get data from the cache
	    $items = $cache->call(array('WCPHelper', 'getDifferencies'));

        $this->assignRef('items', $items);
        parent::display($tpl);
	}

}