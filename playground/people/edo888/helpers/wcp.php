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
     * @var bool
     */
    var $isMaster = null;

    /**
     * Determines if the site is master
     *
     * @access public
     * @return boolean
     */
    function isMaster() {
        if($this->isMaster == null) {
            global $mainframe;
            $db =& JFactory::getDBO();

            $db->setQuery('select id from #__wcp where sid = "' . $mainframe->getCfg('secret') . '"');
            $db->query();
            $this->isMaster = !(bool) $db->getNumRows();
        }

        return $this->isMaster;
    }

    /**
     * Creates a child from master
     *
     * @access public
     * @return boolean True on success, False on failure
     */
    function createChild() {
        // TODO: write createChild function
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
        // Debug: echo '<pre>', print_r($master_tables, true), '</pre>';
        foreach($master_tables as $master_table) {
            // TODO: alter master table for adding create date and last modified date fields

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
            }

            // TODO: alter child table for changing auto increment value
        }

        // Copy all files and folders to the child
        $master_folders = JFolder::folders(JPATH_ROOT, '.', true, true);
        $master_files = JFolder::files(JPATH_ROOT, '.', true, true);

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
            jimport('joomla.filesystem.file');
            foreach($master_folders as $master_folder)
                JFolder::create(str_replace(JPATH_ROOT, JPATH_ROOT.DS.JRequest::getVar('path'), $master_folder));
            foreach($master_files as $master_file)
                JFile::copy($master_file, str_replace(JPATH_ROOT, JPATH_ROOT.DS.JRequest::getVar('path'), $master_file), '', false);
        }

        // TODO: Configure child
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

        // TODO: add friendly error reporting

        return true;
    }

    /**
     * Get differences between master and child
     *
     * @access public
     * @return array
     */
    function getDifferencies() {
        // TODO: write getDifferencies
        $diffs = array();

        // TODO: get internal timer
        $internal_timer = strtotime('2009-06-04 03:28:10');

        $child_files = JFolder::files(JPATH_ROOT, '.', true, true);
        foreach($child_files as $child_file) {
            $m_time = filemtime($child_file);
            if($m_time > $internal_timer)
                $diffs[] = array(str_replace(JPATH_ROOT, '.', $child_file), date('r', $m_time));
        }

        // Debug: echo '<pre>', print_r($diffs, true), '</pre>';
        return $diffs;
    }
}