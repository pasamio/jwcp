<?php
/**
 * @version   $Id$
 * @copyright Copyright (C) 2009 Edvard Ananyan. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class WCPController extends JController {

    function __construct($config = array()) {
        parent::__construct($config);

        require_once(JPATH_COMPONENT.DS.'helpers'.DS.'wcp.php');
        require_once(JPATH_COMPONENT.DS.'tables'.DS.'wcp.php');

        // TODO: Register Extra tasks
        $this->registerTask('add', 'edit');
        $this->registerTask('edit', 'edit');
        $this->registerTask('save', 'save');
        $this->registerTask('apply', 'save');
        $this->registerTask('cancel', 'cancel');
        $this->registerTask('remove', 'remove');
    }


    function display() {
        JRequest::setVar('view', 'childs');
	    parent::display();
	}

    function edit() {
        JRequest::setVar('view', 'child');
        parent::display();
    }

    function save() {
        // TODO: write the save function
        WCPHelper::createChild();
        $msg = JText::_('Child created successfully');
        $link = 'index.php?option=com_wcp';
        $this->setRedirect($link, $msg);
    }

    function cancel() {
        // Check for request forgeries
        JRequest::checkToken() or jexit('Invalid Token');
        $this->setRedirect('index.php?option=com_wcp');
    }

    function remove() {
        // TODO: write the remove function
        $db =& JFactory::getDBO();
        $wcp_table = new TableWCP($db);

        $cid = JRequest::getVar('cid');
        foreach($cid as $id) {
            $wcp_table->load($id);
            // Debug: echo '<pre>', print_r($wcp_table, true), '</pre>';

            if($wcp_table->path != '')
                JFolder::delete(JPATH_ROOT.DS.$wcp_table->path);

            $wcp_table->delete($id);
        }

        $msg = JText::_('Child(s) deleted successfully');
        $link = 'index.php?option=com_wcp';
        $this->setRedirect($link, $msg);
    }
}