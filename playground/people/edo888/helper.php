<?php
/**
 * @version   $Id$
 * @copyright Copyright (C) 2009 Edvard Ananyan. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Working Copy helper class
 *
 */
class WCPHelper {

    /**
     * Determines if the site is master
     *
     * @access public
     * @return boolean
     */
    function isMaster() {
        global $mainframe;
        $db =& JFactory::getDBO();

        $db->setQuery('select id from #__wcp where sid = "' . $mainframe->getCfg('secret') . '"');
        $db->query();
        return !(bool) $db->getNumRows();
    }

    /**
     * Returns the internal time, against which changes
     * will be determined
     *
     * @access public
     * @return int
     */
    function getInternalTime() {
        // TODO: Write get internal time function
        return filemtime(JPATH_ROOT.DS.'configuration.php');
    }

    /**
     * Determines the primary key field of the table
     *
     * @access public
     * @param object DBO
     * @param string Table name
     * @return string
     */
    function getPrimaryKeyField($db, $table) {
        $db->setQuery('show columns from ' . $table);
        $fields = $db->loadObjectList();
        foreach($fields as $field) {
            if($field->Key == 'PRI')
                return $field->Field;
        }

        return '';
    }

    /**
     * Returns connection link to master database
     *
     * @access public
     * @return object JDatabaseMySQL
     */
    function &getMasterDBO() {
        global $mainframe;
        $db =& JFactory::getDBO();
        $db->setQuery("select params from #__wcp where sid = '" . $mainframe->getCfg('secret') . "'");
        $params = $db->loadResult();
        $params = new JParameter($params);
        $master_db = json_decode($params->get('master_db'));

        return new JDatabaseMySQL(array('host' => $master_db->host, 'user' => $master_db->user, 'password' => $master_db->password, 'database' => $master_db->database, 'prefix' => $master_db->prefix));
    }

    /**
     * Get the exclude files list of the child
     *
     * @access public
     * @param string The path, relative to which it will generate the exclude files
     * @return array
     */
    function getExcludeFiles($path = JPATH_ROOT) {
        global $mainframe;

        $db =& JFactory::getDBO();
        $db->setQuery("select path, params from #__wcp where sid = '" . $mainframe->getCfg('secret') . "'");
        $child = $db->loadObject();

        $params = new JParameter($child->params);
        $exclude_files = json_decode($params->get('exclude_files'));
        $exclude_files[] = $child->path;
        foreach($exclude_files as $i => $exclude_file) {
            $exclude_files[$i] = str_replace('./', $path . DS, $exclude_file);
            $exclude_files[$i] = str_replace('/', DS, $exclude_files[$i]);
        }

        return $exclude_files;
    }

    /**
     * Get the exclude tables list of the child
     *
     * @access public
     * @return array
     */
    function getExcludeTables() {
        global $mainframe;
        $db =& JFactory::getDBO();

        $db->setQuery("select params from #__wcp where sid = '" . $mainframe->getCfg('secret') . "'");
        $params = $db->loadResult();
        $params = new JParameter($params);

        return json_decode($params->get('exclude_tables'));
    }

    /**
     * Creates a child from master
     *
     * @access public
     * @return boolean True on success, False on failure
     */
    function createChild() {
        // TODO: Add friendly error reporting

        // Try to set the script execution time to unlimited, if php is in safe mode there is no workaround
        set_time_limit(0);

        global $mainframe;

        $master_db =& JFactory::getDBO();
        $child_db = new JDatabaseMySQL(array('host' => JRequest::getVar('host'), 'user' => JRequest::getVar('user'), 'password' => JRequest::getVar('password'), 'database' => JRequest::getVar('database'), 'prefix' => JRequest::getVar('prefix')));
        // Debug: $child_db->debug(1);

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

        $master_database = new JObject;
        $master_database->set('host', JRequest::getVar('master_host'));
        $master_database->set('user', JRequest::getVar('master_user'));
        $master_database->set('password', JRequest::getVar('master_password'));
        $master_database->set('database', JRequest::getVar('master_database'));
        $master_database->set('prefix', JRequest::getVar('master_prefix'));
        $params->set('master_db', json_encode($master_database));

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

        // Create #__wcp_log_queries table
        $child_db->setQuery("create table #__log_queries (
                `id` int(11) unsigned not null auto_increment,
                `action` enum('insert', 'update', 'delete') not null,
                `table_name` varchar(20) not null,
                `table_key` varchar(20) not null,
                `value` varchar(20) not null,
                `date` timestamp not null default current_timestamp,
                primary key (`id`),
                unique key `id` (`id`),
                unique key `repeat` (`table_name`, `value`)
            ) engine=MyISAM default charset=utf8");
        $child_db->query();

        // Get all joomla tables from master
        $master_db->setQuery("show tables like '".$master_db->_table_prefix."%'");
        $master_tables = $master_db->loadResultArray();
        // Debug: echo '<pre>', print_r($master_tables, true), '</pre>';

        // Copy all tables w/ data to the child
        foreach($master_tables as $master_table) {
            $master_table_ddl = array_pop($master_db->getTableCreate($master_table));
            $child_table = str_replace($master_db->_table_prefix, '#__', $master_table);
            $child_table_ddl = preg_replace('/'.$master_table.'/', $child_table, $master_table_ddl, 1);
            // Debug: echo '<pre>', $child_table_ddl, '</pre>';

            $child_db->setQuery($child_table_ddl);
            $child_db->query();

            if(!in_array($child_table, array('#__core_log_items', '#__core_log_searches', '#__session', '#__stats_agents'))) {
                $master_db->setQuery('select * from '.$master_table);
                $master_rows = $master_db->loadObjectList();
                foreach($master_rows as $master_row)
                    $child_db->insertObject($child_table, $master_row);

                // Create triggers for child table
                $child_table = str_replace('#__', $child_db->_table_prefix, $child_table);
                $key = WCPHelper::getPrimaryKeyField($child_db, $child_table);
                if($key != '') {
                    $child_db->setQuery("create trigger on_insert_$child_table after insert on $child_table for each row " .
                        "replace into #__log_queries (action, table_name, table_key, value) values('insert', '$child_table', '$key', new.$key)");
                    $child_db->query();

                    $child_db->setQuery("create trigger on_update_$child_table after update on $child_table for each row " .
                        "replace into #__log_queries (action, table_name, table_key, value) values('update', '$child_table', '$key', old.$key)");
                    $child_db->query();

                    $child_db->setQuery("create trigger on_delete_$child_table after delete on $child_table for each row " .
                        "replace into #__log_queries (action, table_name, table_key, value) values('delete', '$child_table', '$key', old.$key)");
                    $child_db->query();
                }

                // Increase child table auto_increment values
                $child_db->setQuery("select auto_increment from information_schema.tables where table_schema = database() and table_name = '$child_table'");
                $table_auto_increment = $child_db->loadResult();
                if($table_auto_increment != '') {
                    $table_auto_increment *= 10; // TODO: Select different multiplier depending on $table_auto_increment value
                    $child_db->setQuery("alter table $child_table auto_increment = $table_auto_increment");
                    $child_db->query();
                }
            }
        }

        // Copy all files and folders to the child
        $master_folders = JFolder::folders(JPATH_ROOT, '.', true, true);
        // Debug: echo '<pre>', print_r($master_folders, true), '</pre>';

        $master_files = JFolder::files(JPATH_ROOT, '.', true, true);
        // Debug: echo '<pre>', print_r($master_files, true), '</pre>';

        jimport('joomla.filesystem.file');
        foreach($master_folders as $master_folder)
            JFolder::create(str_replace(JPATH_ROOT, JPATH_ROOT.DS.JRequest::getVar('path'), $master_folder));
        foreach($master_files as $master_file)
            JFile::copy($master_file, str_replace(JPATH_ROOT, JPATH_ROOT.DS.JRequest::getVar('path'), $master_file), '', false);


        // Configure the child
        $config = new JRegistry('config');
        $config_array = array();

        // SITE SETTINGS
        $config_array['offline'] = $mainframe->getCfg('offline');
        $config_array['editor'] = $mainframe->getCfg('editor');
        $config_array['list_limit'] = $mainframe->getCfg('list_limit');
        $config_array['helpurl'] = $mainframe->getCfg('helpurl');

        // DEBUG
        $config_array['debug'] = $mainframe->getCfg('debug');
        $config_array['debug_lang'] = $mainframe->getCfg('debug_lang');

        // SEO SETTINGS
        $config_array['sef'] = $mainframe->getCfg('sef');
        $config_array['sef_rewrite'] = $mainframe->getCfg('sef_rewrite');
        $config_array['sef_suffix'] = $mainframe->getCfg('sef_suffix');

        // FEED SETTINGS
        $config_array['feed_limit'] = $mainframe->getCfg('feed_limit');
        $config_array['feed_email'] = $mainframe->getCfg('feed_email');

        // SERVER SETTINGS
        $config_array['secret'] = JRequest::getVar('sid', 0, 'post', 'string');
        $config_array['gzip'] = $mainframe->getCfg('gzip');
        $config_array['error_reporting'] = $mainframe->getCfg('error_reporting');
        $config_array['xmlrpc_server'] = $mainframe->getCfg('xmlrpc_server');
        $config_array['log_path'] = $mainframe->getCfg('log_path'); // TODO: change it for child
        $config_array['tmp_path'] = $mainframe->getCfg('tmp_path'); //  TODO: change it for child
        $config_array['live_site'] = $mainframe->getCfg('live_site'); // TODO: change it for child
        $config_array['force_ssl'] = $mainframe->getCfg('force_ssl');

        // LOCALE SETTINGS
        $config_array['offset'] = $mainframe->getCfg('offset');

        // CACHE SETTINGS
        $config_array['caching'] = $mainframe->getCfg('caching');
        $config_array['cachetime'] = $mainframe->getCfg('cachetime');
        $config_array['cache_handler'] = $mainframe->getCfg('cache_handler');
        $config_array['memcache_settings'] = $mainframe->getCfg('memcache_settings');

        // FTP SETTINGS
        $config_array['ftp_enable'] = $mainframe->getCfg('ftp_enable');
        $config_array['ftp_host'] = $mainframe->getCfg('ftp_host');
        $config_array['ftp_port'] = $mainframe->getCfg('ftp_port');
        $config_array['ftp_user'] = $mainframe->getCfg('ftp_user');
        $config_array['ftp_pass'] = $mainframe->getCfg('ftp_pass');
        $config_array['ftp_root'] = $mainframe->getCfg('ftp_root');

        // DATABASE SETTINGS
        $config_array['dbtype'] = $mainframe->getCfg('dbtype');
        $config_array['host'] = JRequest::getVar('host', 'localhost', 'post', 'string');
        $config_array['user'] = JRequest::getVar('user', '', 'post', 'string');
        $config_array['password'] = JRequest::getVar('password', '', 'post', 'string');
        $config_array['db'] = JRequest::getVar('database', '', 'post', 'string');
        $config_array['dbprefix'] = JRequest::getVar('prefix', 'wcp_', 'post', 'string');

        // MAIL SETTINGS
        $config_array['mailer'] = $mainframe->getCfg('mailer');
        $config_array['mailfrom'] = $mainframe->getCfg('mailfrom');
        $config_array['fromname'] = $mainframe->getCfg('fromname');
        $config_array['sendmail'] = $mainframe->getCfg('sendmail');
        $config_array['smtpauth'] = $mainframe->getCfg('smtpauth');
        $config_array['smtpuser'] = $mainframe->getCfg('smtpuser');
        $config_array['smtppass'] = $mainframe->getCfg('smtppass');
        $config_array['smtphost'] = $mainframe->getCfg('smtphost');

        // META SETTINGS
        $config_array['MetaAuthor'] = $mainframe->getCfg('MetaAuthor');
        $config_array['MetaTitle'] = $mainframe->getCfg('MetaTitle');
        $config_array['sitename'] = $mainframe->getCfg('sitename');
        $config_array['offline_message'] = $mainframe->getCfg('offline_message');

        // SESSION SETTINGS
        $config_array['lifetime'] = $mainframe->getCfg('lifetime');
        $config_array['session_handler'] = $mainframe->getCfg('session_handler');

        // Load config array
        $config->loadArray($config_array);

        // Get the path of the child configuration file
        $fname = JPATH_CONFIGURATION.DS.JRequest::getVar('path').DS.'configuration.php';

        // Get the config registry in PHP class format and write it to configuation.php
        jimport('joomla.filesystem.file');
        JFile::write($fname, $config->toString('PHP', 'config', array('class' => 'JConfig')));

        return true;
    }

    /**
     * Applies made changes to child
     *
     * @access public
     * @return boolean
     */
    function applyChild() {
        list($cid) = JRequest::getVar('cid');
        $master_db =& JFactory::getDBO();
        $child_db = new JDatabaseMySQL(array('host' => JRequest::getVar('host'), 'user' => JRequest::getVar('user'), 'password' => JRequest::getVar('password'), 'database' => JRequest::getVar('database'), 'prefix' => JRequest::getVar('prefix')));

        if(!$child_db->connected())
            return false;

        // Update child settings in jos_wcp and #__wcp
        $wcp_table = new TableWCP($master_db);
        $wcp_table->load((int) $cid);
        $wcp_table->set('sid', JRequest::getVar('sid'));
        $wcp_table->set('name', JRequest::getVar('name'));
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

        $master_database = new JObject;
        $master_database->set('host', JRequest::getVar('master_host'));
        $master_database->set('user', JRequest::getVar('master_user'));
        $master_database->set('password', JRequest::getVar('master_password'));
        $master_database->set('database', JRequest::getVar('master_database'));
        $master_database->set('prefix', JRequest::getVar('master_prefix'));
        $params->set('master_db', json_encode($master_database));

        $ftp = new JObject;
        $ftp->set('enable', JRequest::getVar('ftp_enable'));
        $ftp->set('host', JRequest::getVar('ftp_host'));
        $ftp->set('port', JRequest::getVar('ftp_port'));
        $ftp->set('user', JRequest::getVar('ftp_user'));
        $ftp->set('pass', JRequest::getVar('ftp_pass'));
        $ftp->set('root', JRequest::getVar('ftp_root'));
        $params->set('ftp', json_encode($ftp));

        $wcp_table->set('params', $params->toString());

        // Save changes to master
        $wcp_table->store();

        // Save changes to child
        $wcp_table->_db = $child_db;
        $wcp_table->store();
        // Debug: echo '<pre>', print_r($wcp_table, true), '</pre>';

        // Re-configure child
        $config = new JRegistry('config');

        // Get the path of the child configuration file
        $fname = JPATH_CONFIGURATION.DS.JRequest::getVar('path').DS.'configuration.php';

        $config->loadObject(new JConfig);
        $config_array = $config->toArray();
        // Debug: echo '<pre>', print_r($config_array, true), '</pre>';

        $config_array['secret'] = JRequest::getVar('sid');

        // DATABASE SETTINGS
        $config_array['host'] = JRequest::getVar('host', 'localhost', 'post', 'string');
        $config_array['user'] = JRequest::getVar('user', '', 'post', 'string');
        $config_array['password'] = JRequest::getVar('password', '', 'post', 'string');
        $config_array['db'] = JRequest::getVar('database', '', 'post', 'string');
        $config_array['dbprefix'] = JRequest::getVar('prefix', 'wcp_', 'post', 'string');

        // Load config array
        $config->loadArray($config_array);

        // Get the config registry in PHP class format and write it to configuation.php
        jimport('joomla.filesystem.file');
        JFile::write($fname, $config->toString('PHP', 'config', array('class' => 'JConfig')));
    }

    /**
     * Removes child
     *
     * @access public
     * @return bool
     */
    function removeChild() {
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

            $child_db->setQuery("show tables like '" . $child_db->_table_prefix . "%'");
            $child_tables = $child_db->loadResultArray();
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

    }

    /**
     * Get file system differences between master and child
     *
     * @access public
     * @param string The path, in which it will try to find modified files
     * @return array
     */
    function getDifferences($path = JPATH_ROOT) {
        $diffs = array();

        // Get internal timer
        $internal_timer = WCPHelper::getInternalTime();

        // Get exclude files list
        $exclude_files = WCPHelper::getExcludeFiles($path);

        $child_files = JFolderWCP::files($path, array_merge($exclude_files, array('.svn', 'CVS')));
        foreach($child_files as $child_file) {
            // Make file path relative
            $child_file = str_replace($path, '.', $child_file);

            // Make file path unix format
            $child_file = str_replace(DS, '/', $child_file);

            $m_time = filemtime($path . DS . $child_file);
            if($m_time > $internal_timer)
                $diffs[] = array($child_file, date('r', $m_time));
        }

        // Debug: echo '<pre>', print_r($diffs, true), '</pre>';
        return $diffs;
    }

    /**
     * Get table differences between master and child
     *
     * @access public
     * @return array
     */
    function getTableDifferences() {
        $diffs = array();

        // Get internal timer
        $internal_timer = date('Y-m-d H:i:s', WCPHelper::getInternalTime());

        $db =& JFactory::getDBO();
        $db->setQuery("select id, action, table_name, table_key, value, unix_timestamp(date) as mdate from #__log_queries where date > '$internal_timer' order by date asc");
        $diffs = $db->loadObjectList();

        return $diffs;
    }

    /**
     * Get database differences between master and child
     *
     * @access public
     * @return array
     */
    function getDatabaseDifferences() {
        global $mainframe;
        $diffs = array();

        // TODO: It's possible to get created tables from information_schema.tables

        // Get connection to master db
        $master_db =& WCPHelper::getMasterDBO();
        $master_db->setQuery("show tables like '" . $master_db->_table_prefix . "%'");
        $master_tables = $master_db->loadResultArray();
        foreach($master_tables as $i => $table)
            $master_tables[$i] = str_replace($master_db->_table_prefix, '#__', $table);

        $child_db =& JFactory::getDBO();
        $child_db->setQuery("show tables like '" . $child_db->_table_prefix . "%'");
        $child_tables = $child_db->loadResultArray();
        foreach($child_tables as $i => $table)
            $child_tables[$i] = str_replace($child_db->_table_prefix, '#__', $table);

        $exclude_tables = WCPHelper::getExcludeTables();

        // Get all added/deleted tables
        $tables_added = array_diff($child_tables, $master_tables, $exclude_tables);
        // Debug: echo '<pre>', print_r($tables_added, true), '</pre>';

        $tables_deleted = array_diff($master_tables, $child_tables, $exclude_tables);
        // Debug: echo '<pre>', print_r($tables_deleted, true), '</pre>';

        foreach($tables_added as $table) {
            $diff = new JObject;
            $diff->set('id', '');
            $diff->set('action', 'add table');
            $diff->set('table_name', str_replace('#__', $child_db->_table_prefix, $table));
            $diffs[] = $diff;
        }

        foreach($tables_deleted as $table) {
            $diff = new JObject;
            $diff->set('id', '');
            $diff->set('action', 'delete table');
            $diff->set('table_name', str_replace('#__', $child_db->_table_prefix, $table));
            $diffs[] = $diff;
        }

        return $diffs;
    }


    /**
     * Create patch from the child
     *
     * @access public
     * @return boolean
     */
    function createPatch() {
        $changes = JRequest::getVar('cid');

        $files = $tables = $rows = array();
        foreach($changes as $i => $change)
            if(file_exists(JPATH_ROOT.DS.$change))
                $files[] = JPATH_ROOT.DS.$change;
            elseif(intval($change) == $change)
                $rows[] = $change;
            elseif(false)
                $tables[] = $change;

        // Debug: echo '<pre>', print_r($files, true), '</pre>';
        // Debug: echo '<pre>', print_r($tables, true), '</pre>';
        // Debug: echo '<pre>', print_r($rows, true), '</pre>';

        // TODO: Write database patch

        // Tables patch
        $sql = array();
        $db =& JFactory::getDBO();
        $db->setQuery('select action, table_name, table_key, value from #__log_queries where id in (' . implode(',', $rows) . ')');
        $rows = $db->loadObjectList();
        foreach($rows as $row) {
            $db->setQuery("select * from $row->table_name where $row->table_key = '$row->value'");
            $data = $db->loadAssoc();
            $row->table_name = str_replace($db->_table_prefix, '#__', $row->table_name);
            switch($row->action) {
                case 'insert':
                case 'update':
                    foreach($data as $key => $val)
                        $data[$key] = $db->isQuoted($key) ? $db->Quote($val) : (int) $val; // TODO: make sure NULL values will not cause issues

                    $data = implode(',', $data);
                    $sql[] = "replace into $row->table_name values ($data)";
                    break;
                case 'delete':
                    $sql[] = "delete from $row->table_name where $row->table_key = '$row->value'";
                    break;
            }
        }

        $sql = implode(";\n", $sql) . ';';
        $patch_id = uniqid('patch_');
        $patch_file_sql = JPATH_ROOT.DS.$patch_id.'.sql';
        jimport('joomla.filesystem.file');
        JFile::write($patch_file_sql, $sql);
        $files[] = $patch_file_sql;

        // Creating the patch package
        jimport('joomla.filesystem.archive');
        $patch_file = $patch_id.'.tar.gz';
        JArchive::create(JPATH_ROOT.DS.'tmp'.DS.$patch_file, $files, 'gz', '', JPATH_ROOT);

        // Delete sql file
        JFile::delete($patch_file_sql);

        // Loading download form
        $document =& JFactory::getDocument();
        $document->addStyleDeclaration('.icon-48-download {background-image:url(./templates/khepri/images/header/icon-48-install.png);}');
        JToolBarHelper::title(JText::_('WCP Manager') . ': <small><small>[ ' . JText::_('Download Patch') . ' ]</small></small>', 'download.png');
        JToolBarHelper::custom('cancel', 'back.png', 'back.png', 'Back', '', false);
        JToolBarHelper::help('screen.wcp.createPatch', true);

        echo '<form action="index.php" method="post" name="adminForm">';
        echo JText::_('Download will start automatically') . ' <a href="' . JURI::root() . 'tmp/' . $patch_file . '"> ' . JText::_('Start download manually') . '</a>';
        echo '<iframe src="' . JURI::root() . 'tmp/' . $patch_file . '" style="display:none;"></iframe>';
        echo '<input type="hidden" name="task" value="" />';
        echo '</form>';

        // Return to Create Patch interface
        $document->setMetaData('REFRESH', '5; url='.JURI::base().'index.php?option=com_wcp&view=differences', true);

        return true;
    }

    /**
     * Apply the patch to the master
     *
     * @access public
     * @return boolean
     */
    function applyPatch() {
        // TODO: Add friendly error reporting

        // Get the uploaded file information
        $userfile = JRequest::getVar('patch_file', null, 'files', 'array');

        // If there is no uploaded file, we have a problem...
        if(empty($userfile['name'])) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::_('No file selected'));
            return false;
        }

        // Check if there was a problem uploading the file.
        if($userfile['error'] or $userfile['size'] < 1) {
            JError::raiseWarning('SOME_ERROR_CODE', JText::_('Cannot upload the file'));
            return false;
        }

        // Build the appropriate paths
        $tmp_dest = JPATH_ROOT.DS.'tmp'.DS.$userfile['name'];
        $tmp_src  = $userfile['tmp_name'];

        // Move uploaded file
        jimport('joomla.filesystem.file');
        JFile::upload($tmp_src, $tmp_dest);

        // Unpack the patch file
        $patch_src = $tmp_dest;
        $patch_dest = JPATH_ROOT.DS.'tmp'.DS.uniqid('patch_');
        jimport('joomla.filesystem.archive');
        JArchive::extract($patch_src, $patch_dest);

        // Run queries from sql file
        $db =& JFactory::getDBO();
        $sql_file = $patch_dest.DS.str_replace('.tar.gz', '.sql', $userfile['name']);
        $sql = file($sql_file);
        foreach($sql as $query) {
            $db->setQuery($query);
            $db->query();
        }

        // Remove sql file
        JFile::delete($sql_file);

        // Replace files
        $files = JFolder::files($patch_dest, '.', true, true);
        // Debug: echo '<pre>', print_r($files, true), '</pre>';
        foreach($files as $file) {
            // Debug: echo '<pre>', $file, ' -> ', str_replace($patch_dest, JPATH_ROOT, $file), '</pre>';
            JFile::delete(str_replace($patch_dest, JPATH_ROOT, $file));
            JFile::move($file, str_replace($patch_dest, JPATH_ROOT, $file));
        }

        // Remove tmp files
        JFile::delete($patch_src);
        JFolder::delete($patch_dest);

        return true;
    }

    /**
     * Revert the child
     *
     * @access public
     * @return boolean
     */
    function revertChild() {
        global $mainframe;
        $changes = JRequest::getVar('cid');
        $db =& JFactory::getDBO();

        $files = $tables = $rows = array();
        foreach($changes as $i => $change)
            if(file_exists(JPATH_ROOT.DS.$change))
                $files[] = $change;
            elseif(intval($change) == $change)
                $rows[] = $change;
            elseif(false)
                $tables[] = $change;

        // Debug: echo '<pre>', print_r($files, true), '</pre>';
        // Debug: echo '<pre>', print_r($tables, true), '</pre>';
        // Debug: echo '<pre>', print_r($rows, true), '</pre>';

        // Revert files
        jimport('joomla.filesystem.file');
        $db->setQuery('select path from #__wcp where sid = "' . $mainframe->getCfg('secret') . '"');
        $path = $db->loadResult();
        $master_root = JPath::clean(str_replace(str_replace(array('./', '/'), DS, $path), '', JPATH_ROOT));
        foreach($files as $i => $file)
            JFile::copy($master_root.DS.$file, JPATH_ROOT.DS.$file);

        // TODO: Write database revert part

        // Revert rows
        // Get connection to master db
        $master_db = WCPHelper::getMasterDBO();
        foreach($rows as $row) {
            $db->setQuery('select action, table_name, table_key, value from #__log_queries where id = ' . $row);
            $change = $db->loadObject();
            if(empty($change->action))
                continue;

            switch($change->action) {
                case 'insert':
                    $db->setQuery("delete from $change->table_name where $change->table_key = '$change->value'");
                    $db->query();

                    // Remove from query log - remember: id is changed after delete
                    $db->setQuery("delete from #__log_queries where table_name = '$change->table_name' and table_key = '$change->table_key' and value = '$change->value'");
                    $db->query();
                    break;
                case 'update':
                case 'delete':
                    $master_db->setQuery("select * from " . str_replace($db->_table_prefix, '#__', $change->table_name) . " where $change->table_key = '$change->value'");
                    $original = $master_db->loadAssoc();

                    if(count($original) == 0) {
                        // The original row doesn't exist in master table, deleting row from child_db
                        $db->setQuery("delete from $change->table_name where $change->table_key = '$change->value'");
                        $db->query();

                        // Remove from query log - remember: id is changed after delete
                        $db->setQuery("delete from #__log_queries where table_name = '$change->table_name' and table_key = '$change->table_key' and value = '$change->value'");
                        $db->query();

                        break;
                    }

                    foreach($original as $key => $val)
                        $original[$key] = $master_db->isQuoted($key) ? $master_db->Quote($val) : (int) $val; // TODO: make sure NULL values will not cause issues

                    $original = implode(',', $original);
                    $db->setQuery("replace into $change->table_name values ($original)");
                    $db->query();

                    // Remove from query log - remember: id is changed after store
                    $db->setQuery("delete from #__log_queries where table_name = '$change->table_name' and table_key = '$change->table_key' and value = '$change->value'");
                    $db->query();
                    break;
            }
        }

        return true;
    }

    /**
     * Synchronize the child
     *
     * @access public
     * @return boolean
     */
    function syncChild() {
        global $mainframe;

        $db =& JFactory::getDBO();
        $db->setQuery('select path from #__wcp where sid = "' . $mainframe->getCfg('secret') . '"');
        $path = $db->loadResult();

        // Get all files on master and child, which are newer than the internal timer
        // then update to child the newer ones, but keep those which are already modified
        // on child
        $master_root = JPath::clean(str_replace(str_replace(array('./', '/'), DS, $path), '', JPATH_ROOT));
        $diffs_master = WCPHelper::getDifferences($master_root);
        $diffs_child = WCPHelper::getDifferences();

        foreach($diffs_master as $i => $diff_master)
            $diffs_master[$i] = $diff_master[0];

        foreach($diffs_child as $i => $diff_child)
            $diffs_child[$i] = $diff_child[0];

        $diffs = array_diff($diffs_master, $diffs_child);
        // Debug: echo '<pre>', print_r($diffs, ture), '</pre>';

        jimport('joomla.filesystem.file');
        foreach($diffs as $file)
            JFile::copy($master_root . DS . $file, JPATH_ROOT . DS . $file);

        // TODO: Treat configuration.php and other special files cases separately

        // TODO: Write tables sync part

    }

    /**
     * Function for test purposes
     * TODO: Remove this function
     *
     * @access public
     * @return
     */
    function test() {
        echo '<pre>', print_r(WCPHelper::getExcludeFiles('C:\xampp\htdocs\joomla_dev\Joomla 1.5 Source'), true), '</pre>';
        //echo '<pre>', print_r(JFolderWCP::files(JPATH_ROOT, array_merge(WCPHelper::getExcludeFiles(), array('.svn', 'CVS'))), true), '</pre>';
    }
}

/**
 * JFolder extension class
 *
 */
class JFolderWCP {
    /**
     * Utility function to read the files in a folder.
     *
     * @param string The full path of the folder to read
     * @param array The exclude list
     * @return array Files and folders in the given folder
     * @access public
     */
    function files($path, $exclude = array('.svn', 'CVS')) {
        // Initialize variables
        $arr = array();

        // read the source directory
        $handle = opendir($path);
        while(($file = readdir($handle)) !== false) {
            if($file != '.' and $file != '..' and !in_array($file, $exclude)) {
                $dir = $path . DS . $file;
                if(!in_array($dir, $exclude)) {
                    if(is_dir($dir))
                        $arr = array_merge($arr, JFolderWCP::files($dir, $exclude));
                    else
                        $arr[] = $dir;
                }
            }
        }
        closedir($handle);

        return $arr;
    }
}