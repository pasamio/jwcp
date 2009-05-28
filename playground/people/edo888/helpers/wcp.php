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
    }

    function getDifferencies() {
        // TODO: write getDifferencies
    }
}