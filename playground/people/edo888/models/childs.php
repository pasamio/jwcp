<?php
/**
 * @version   $Id$
 * @copyright Copyright (C) 2009 Edvard Ananyan. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class WCPModelChilds extends JModel {

    var $_data = null;

    function __construct() {
        parent::__construct();
    }

    function _buildQuery() {
        $where   = $this->_buildWhere();
        $orderby = $this->_buildOrderBy();

        $query = 'SELECT w.* FROM #__wcp AS w'
            . $where
            . $orderby;
        return $query;
    }

    function _buildWhere() {
        $config = new JConfig();
        $where = ' WHERE parent_sid = "' . $config->secret .'"';
        return $where;
    }

    function _buildOrderBy() {
        global $mainframe, $option;

        $filter_order     = $mainframe->getUserStateFromRequest($option.'.filter_order',     'filter_order',     'w.id', 'cmd');
        $filter_order_Dir = $mainframe->getUserStateFromRequest($option.'.filter_order_Dir', 'filter_order_Dir', '',     'word');

        $orderby = ' ORDER BY '. $filter_order .' '. $filter_order_Dir;
        return $orderby;
    }

    function getData() {
        // Lets load the data if it doesn't already exist
        if (empty($this->_data)) {
            $query = $this->_buildQuery();
            $this->_data = $this->_getList($query);
        }

        return $this->_data;
    }
}