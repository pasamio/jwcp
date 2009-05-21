<?php
/**
 * @version	  $Id$
 * @copyright Copyright (C) 2009 Edvard Ananyan. All rights reserved.
 * @license	  http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once(JApplicationHelper::getPath('toolbar_html'));

switch($task) {
	default:
		TOOLBAR_config::_DEFAULT();
		break;
}