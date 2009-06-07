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
 * Working Copy Apply Patch View class
 *
 */
class WCPViewApplyPatch extends JView {

    /**
     * Display Apply Patch form
     *
     * @access public
     */
    function display($tpl = null) {
        JToolBarHelper::title(JText::_('WCP Manager') . ': <small><small>[ ' . JText::_('Apply Patch') . ' ]</small></small>', 'generic.png');
        JToolBarHelper::help('screen.wcp.applypatch');

        parent::display($tpl);
    }

}