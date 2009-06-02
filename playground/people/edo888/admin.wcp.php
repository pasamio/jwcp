<?php
/**
 * @version   $Id$
 * @copyright Copyright (C) 2009 Edvard Ananyan. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Make sure the user is authorized to view this page
$acl =& JFactory::getACL();
$acl->addACL('com_wcp', 'manage', 'users', 'super administrator');
$user =& JFactory::getUser();
if(!$user->authorize('com_wcp', 'manage'))
    $mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));

// Require the base controller
require_once(JPATH_COMPONENT.DS.'controller.php');

// Create the controller
$controller = new WCPController();

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));

// Redirect if set by the controller
$controller->redirect();