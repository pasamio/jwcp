<?php
/**
 * @version   $Id$
 * @copyright Copyright (C) 2009 Edvard Ananyan. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class WCPModelChild extends JModel {

    var $_data = null;

    function __construct() {
        parent::__construct();
    }

    function _buildQuery() {
        $where   = $this->_buildWhere();

        $query = 'SELECT w.* FROM #__wcp AS w'
            . $where
            . $orderby;
        return $query;
    }

    function _buildWhere() {
        $where = ' WHERE id = ' . JRequest::getInt('id', 0);
        return $where;
    }

    function getData() {
        // Lets load the data if it doesn't already exist
        if (empty($this->_data)) {
            $query = $this->_buildQuery();
            list($this->_data) = $this->_getList($query);
        }

        return $this->_data;
    }
}