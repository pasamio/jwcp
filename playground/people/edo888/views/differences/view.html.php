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
 * Working Copy Differences View class
 *
 */
class WCPViewDifferences extends JView {

    /**
     * Display differences
     *
     * @access public
     */
	function display($tpl = null) {
        JToolBarHelper::title(JText::_('WCP Manager') . ': <small><small>[ ' . JText::_('Differences') . ' ]</small></small>', 'generic.png');
        JToolBarHelper::custom('createPatch', 'new.png', 'new.png', 'Create Patch');
        JToolBarHelper::custom('syncChild', '', '', 'Synchronize', '', false);
        JToolBarHelper::custom('revertChild', 'restore.png', 'restore.png', 'Revert');
        JToolBarHelper::custom('refreshDiff', 'refresh.png', 'refresh.png', 'Refresh', '', false);
        JToolBarHelper::custom('cancel', 'back.png', 'back.png', 'Back', '', false);
        JToolBarHelper::help('screen.wcp.differences', true);

        $cache =& JFactory::getCache('com_wcp', 'callback', 'file');
        $cache->setCaching(true);

	    // Get data from the cache
	    $items = $cache->call(array('WCPHelper', 'getDifferences'));
	    $table_items = $cache->call(array('WCPHelper', 'getTableDifferences'));
	    $db_items = $cache->call(array('WCPHelper', 'getDatabaseDifferences'));

        $this->assignRef('items', $items);
        $this->assignRef('table_items', $table_items);
        $this->assignRef('db_items', $db_items);
        parent::display($tpl);
	}

}