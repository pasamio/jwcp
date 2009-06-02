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
        $master_db =& JFactory::getDBO();
        $child_db  = new JDatabaseMySQL(array('host', 'user', 'password', 'database', 'wcp_'));

        // Copy all tables w/ data to the child
        $master_tables = $master_db->getTableList();
        foreach($master_tables as $master_table) {
            $master_table_ddl = $master_db->getTableCreate($master_table);
            $child_table = '#__'.$master_table;
            $child_table_ddl = str_replace($master_table, $child_table, $table_ddl);
            $child_db->query($child_table_ddl);
            $master_db->query('select * from '.$master_table);
            $master_rows = $master_db->loadObjectList();
            foreach($master_rows as $master_row)
                $child_db->insertObject($child_table, $master_row);
        }

        // Copy all files and folders to the child
        $master_folders = JFolder::folders('master path', '.', true);
        $master_files = JFolder::files('master path', '.', true);

        $child_fs = new JFTP();
        $child_fs->connect('host', 'port');
        $child_fs->login('user', 'pass');
        $child_fs->mkdir('child path');
        $child_fs->chdir('child path');
        foreach($master_folders as $child_folder)
            $child_fs->mkdir($child_folder); // TODO: make recursive
        foreach($master_files as $master_file)
            $child_fs->store($master_file, $master_file);

        // JFolder::copy('master path', 'child path', '', true);

        return true;
    }

    function getDifferencies() {
        // TODO: write getDifferencies
    }
}