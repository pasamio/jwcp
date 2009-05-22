<?php
/**
 * @version   $Id$
 * @copyright Copyright (C) 2009 Edvard Ananyan. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class WCPController extends JController {

    function __construct($config = array()) {
        parent::__construct($config);

        // TODO: Register Extra tasks
        //$this->registerTask('add',  'display');
    }


    function display() {
        JRequest::setVar('view', 'childs');
	    parent::display();
	}

    function save() {

    }

    function cancel() {

    }

    function edit() {

    }

    function delete() {

    }
}