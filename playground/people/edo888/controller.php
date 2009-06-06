<?php
/**
 * @version   $Id$
 * @copyright Copyright (C) 2009 Edvard Ananyan. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * Working Copy Controller class
 *
 */
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
        $this->registerTask('diff', 'differencies');
        $this->registerTask('refreshDiff', 'refreshDiff');
        $this->registerTask('createPatch', 'createPatch');
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
        WCPHelper::createChild();
        $this->setRedirect('index.php?option=com_wcp', JText::_('Child created successfully'));
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

        $this->setRedirect('index.php?option=com_wcp', JText::_('Child(s) deleted successfully'));
    }

    function differencies() {
        JRequest::setVar('view', 'differencies');
        parent::display();
    }

    function refreshDiff() {
        $cache =& JFactory::getCache('com_wcp', 'callback', 'file');
        $cache->clean('com_wcp', 'group');

        $this->setRedirect('index.php?option=com_wcp&task=differencies', JText::_('List Refreshed'));
    }

    function createPatch() {
        WCPHelper::createPatch();

        // TODO: Start the download

        $this->setRedirect('index.php?option=com_wcp&task=differencies', JText::_('Patch Created'));
    }
}
