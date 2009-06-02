<?php
/**
 * @version   $Id$
 * @copyright Copyright (C) 2009 Edvard Ananyan. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class WCPHelper {
    function isMaster() {
        $config = new JConfig();
        $db =& JFactory::getDBO();

        $db->query('select id from #__wcp where sid = "' . $config->secret . '"');
        return (bool) $db->getNumRows();
    }

    function createChild() {
        // TODO: write createChild function
        global $mainframe;

        $master_db =& JFactory::getDBO();
        $child_db  = new JDatabaseMySQL(array(JRequest::getVar('host'), JRequest::getVar('user'), JRequest::getVar('password'), JRequest::getVar('database'), JRequest::getVar('wcp_')));

        if(!$child_db->connected())
            return false;

        // Insert new child to #__wcp
        $wcp_table = new TableWCP($master_db);
        $wcp_table->set('sid', JRequest::getVar('sid'));
        $wcp_table->set('name', JRequest::getVar('name'));
        $wcp_table->set('parent_sid', $mainframe->getCfg('secret'));
        $wcp_table->set('path', JRequest::getVar('path'));

        $params = new JParameter('');
        $params->set('exclude_files', json_encode(array_values(array_filter(JRequest::getVar('exclude_files'), 'strlen'))));
        $params->set('exclude_tables', json_encode(array_values(array_filter(JRequest::getVar('exclude_tables'), 'strlen'))));

        $database = new JObject;
        $database->set('host', JRequest::getVar('host'));
        $database->set('user', JRequest::getVar('user'));
        $database->set('password', JRequest::getVar('password'));
        $database->set('database', JRequest::getVar('database'));
        $database->set('prefix', JRequest::getVar('prefix'));
        $params->set('database', json_encode($database));

        $ftp = new JObject;
        $ftp->set('enable', JRequest::getVar('ftp_enable'));
        $ftp->set('host', JRequest::getVar('ftp_host'));
        $ftp->set('port', JRequest::getVar('ftp_port'));
        $ftp->set('user', JRequest::getVar('ftp_user'));
        $ftp->set('pass', JRequest::getVar('ftp_pass'));
        $ftp->set('root', JRequest::getVar('ftp_root'));
        $params->set('ftp', json_encode($ftp));

        $wcp_table->set('params', $params->toString());

        $wcp_table->store();

        // Debug: echo '<pre>', print_r($wcp_table, true), '</pre>';

        // Copy all tables w/ data to the child
        $master_tables = $master_db->getTableList();
        foreach($master_tables as $master_table) {
            // TODO: alter master table for adding create date and last modified date fields
            $master_table_ddl = array_pop($master_db->getTableCreate($master_table));
            $child_table = str_replace($master_db->_table_prefix, '#__', $master_table);
            $child_table_ddl = preg_replace('/'.$master_table.'/', $child_table, $master_table_ddl, 1);
            // Debug: echo '<pre>', $child_table_ddl, '</pre>';
            $child_db->query($child_table_ddl);
            $master_db->query('select * from '.$master_table);
            $master_rows = $master_db->loadObjectList();
            foreach($master_rows as $master_row)
                $child_db->insertObject($child_table, $master_row);
            // TODO: alter child table for changing auto increment value
        }

        // Copy all files and folders to the child
        $master_folders = JFolder::folders(JPATH_ROOT, '.', false, true);
        $master_files = JFolder::files(JPATH_ROOT, '.', false, true);

        // Debug: echo '<pre>', print_r($master_folders, true), '</pre>';
        // Debug: echo '<pre>', print_r($master_files, true), '</pre>';

        if(JRequest::getBool('ftp_enable')) {
            // TODO: Write recursive copy method for FTP layer
            $child_fs = new JFTP();
            $child_fs->connect(JRequest::getVar('ftp_host'), JRequest::getVar('ftp_port'));
            $child_fs->login(JRequest::getVar('ftp_user'), JRequest::getVar('ftp_pass'));
            $child_fs->mkdir('child path');
            $child_fs->chdir('child path');
            foreach($master_folders as $child_folder)
                $child_fs->mkdir($child_folder); // TODO: make recursive
            foreach($master_files as $master_file)
                $child_fs->store($master_file, $master_file);
        } else {
            foreach($master_folders as $master_folder)
                JFolder::copy($master_folder, str_replace(JPATH_ROOT, JPATH_ROOT.DS.JRequest::getVar('path'), $master_folder), '', false);
            foreach($master_files as $master_file)
                JFile::copy($master_file, str_replace(JPATH_ROOT, JPATH_ROOT.DS.JRequest::getVar('path'), $master_file), '', false);
        }

        // TODO: Configure child

        return true;
    }

    function getDifferencies() {
        // TODO: write getDifferencies
    }
}