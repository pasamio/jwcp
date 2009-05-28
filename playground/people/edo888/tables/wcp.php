<?php
/**
 * @version   $Id$
 * @copyright Copyright (C) 2009 Edvard Ananyan. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class TableWCP extends JTable {
	/** @var int */
	var $id				= null;
	/** @var string */
	var $name			= '';
	/** @var string */
	var $sid            = '';
	/** @var string */
	var $parent_sid     = '';
	/** @var string */
	var $path           = '';
	/** @var string */
	var $params			= '';

	function __construct(&$_db) {
		parent::__construct('#__wcp', 'id', $_db);

		$config = new JConfig;
		$this->set('parent_sid', $config->secret);
	}

	/**
	 * Overloaded check function
	 *
	 * @access public
	 * @return boolean
	 * @see JTable::check
	 * @since 1.5
	 */
	function check() {
		return true;
	}
}