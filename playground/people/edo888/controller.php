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

            // Delete tables
            $params = new JParameter($wcp_table->params);
            $database = json_decode($params->get('database'));
            // Debug: echo '<pre>', print_r($database, true), '</pre>';
            $child_db = new JDatabaseMySQL(array('host' => $database->host, 'user' => $database->user, 'password' => $database->password, 'database' => $database->database, 'prefix' => $database->prefix));
            // Debug: $child_db->debug(1);

            $child_tables = $child_db->getTableList();
            // Debug: echo '<pre>', print_r($child_tables, true), '</pre>';
            foreach($child_tables as $child_table) {
                if(substr($child_table, 0, 4) == $database->prefix) {
                    $child_db->setQuery('drop table ' . $child_table);
                    $child_db->query();
                }
            }

            // Delete files
            if($wcp_table->path != '')
                JFolder::delete(JPATH_ROOT.DS.$wcp_table->path);

            // Delete database entry
            $wcp_table->delete($id);
        }

        $msg = JText::_('Child(s) deleted successfully');
        $link = 'index.php?option=com_wcp';
        $this->setRedirect($link, $msg);
    }
}