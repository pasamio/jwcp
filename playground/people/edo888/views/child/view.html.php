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
 * Working Copy Child View class
 *
 */
class WCPViewChild extends JView {

    /**
     * Display the child
     *
     * @access public
     */
	function display($tpl = null) {
        JToolBarHelper::title(JText::_('WCP Manager') . ': <small><small>[ ' . (JRequest::getVar('task', 'edit') == 'edit' ? JText::_('Edit Child') : JText::_('New Child')) . ' ]</small></small>', 'generic.png');
        JToolBarHelper::save();
        JToolBarHelper::apply();
        JRequest::getVar('task', 'edit') == 'edit' ? JToolBarHelper::cancel('cancel', 'Close') : JToolBarHelper::cancel();
        JToolBarHelper::help('screen.wcp.new', true);

	    // Get data from the model
        $child =& $this->get('Data');

        // Generate Random Site ID
	    if(JRequest::getVar('task', 'edit') == 'add') {
            jimport('joomla.user.helper');
            $secret = JUserHelper::genRandomPassword(16);
        } else {
            $secret = $child->sid;
        }

        global $mainframe;

        // Get child params
        if(JRequest::getVar('task', 'edit') == 'add') {
            // TODO: define child defaults
            $child->path = './child';

            $exclude_files = array();
            $exclude_files[] = './cache';
            $exclude_files[] = './includes';
            $exclude_files[] = './installation';
            $exclude_files[] = './libraries';
            $exclude_files[] = './logs';
            $exclude_files[] = './tmp';
            $exclude_files[] = './xmlrpc';
            $exclude_files[] = './configuration.php';
            $exclude_files[] = './administrator/backups';
            $exclude_files[] = './administrator/cache';
            $exclude_files[] = './administrator/help';
            $exclude_files[] = './administrator/images';
            $exclude_files[] = './administrator/includes';
            $exclude_files[] = './administrator/templates';
            $exclude_files[] = './plugins/editors/tinymce';
            $exclude_files[] = './plugins/system/legacy';
            $exclude_files[] = './templates/beez';
            $exclude_files[] = './templates/ja_purity';
            $exclude_files[] = './templates/rhuk_milkyway';
            $exclude_files[] = './templates/system';

            $exclude_tables = array();
            $exclude_tables[] = '#__core_acl_aro';
            $exclude_tables[] = '#__core_acl_aro_groups';
            $exclude_tables[] = '#__core_acl_aro_map';
            $exclude_tables[] = '#__core_acl_aro_sections';
            $exclude_tables[] = '#__core_acl_groups_aro_map';
            $exclude_tables[] = '#__core_log_items';
            $exclude_tables[] = '#__core_log_searches';
            $exclude_tables[] = '#__groups';
            $exclude_tables[] = '#__migration_backlinks';
            $exclude_tables[] = '#__session';
            $exclude_tables[] = '#__stats_agents';

            $database = new JObject;
            $database->set('host', $mainframe->getCfg('host'));
            $database->set('user', $mainframe->getCfg('user'));
            $database->set('password', $mainframe->getCfg('password'));
            $database->set('database', $mainframe->getCfg('db'));
            $database->set('prefix', 'wcp_');

            $ftp = new JObject;
            $ftp->set('enable', $mainframe->getCfg('ftp_enable'));
            $ftp->set('host', $mainframe->getCfg('ftp_host'));
            $ftp->set('port', $mainframe->getCfg('ftp_port'));
            $ftp->set('user', $mainframe->getCfg('ftp_user'));
            $ftp->set('pass', $mainframe->getCfg('ftp_pass'));
            $ftp->set('root', $mainframe->getCfg('ftp_root'));
        } else {
            $params = new JParameter($child->params);
            $exclude_files = json_decode($params->get('exclude_files'));
            $exclude_tables = json_decode($params->get('exclude_tables'));
            $database = json_decode($params->get('database'));
            $ftp = json_decode($params->get('ftp'));
        }

        $this->assignRef('exclude_files', $exclude_files);
        $this->assignRef('exclude_tables', $exclude_tables);
        $this->assignRef('database', $database);
        $this->assignRef('ftp', $ftp);
        $this->assignRef('secret', $secret);
        $this->assignRef('item', $child);
        parent::display($tpl);
	}

}