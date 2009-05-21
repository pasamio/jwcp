<?php
/**
 * @version   $Id$
 * @copyright Copyright (C) 2009 Edvard Ananyan. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// TODO: Make sure the user is authorized to view this page

/*
// Set the table directory
JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_banners'.DS.'tables');

$controllerName = JRequest::getCmd( 'c', 'banner' );

if($controllerName == 'client') {
	JSubMenuHelper::addEntry(JText::_('Banners'), 'index.php?option=com_banners');
	JSubMenuHelper::addEntry(JText::_('Clients'), 'index.php?option=com_banners&c=client', true );
	JSubMenuHelper::addEntry(JText::_('Categories'), 'index.php?option=com_categories&section=com_banner');
} else {
	JSubMenuHelper::addEntry(JText::_('Banners'), 'index.php?option=com_banners', true );
	JSubMenuHelper::addEntry(JText::_('Clients'), 'index.php?option=com_banners&c=client');
	JSubMenuHelper::addEntry(JText::_('Categories'), 'index.php?option=com_categories&section=com_banner');
}

switch ($controllerName)
{
	default:
		$controllerName = 'banner';
		// allow fall through

	case 'banner' :
	case 'client':
		// Temporary interceptor
		$task = JRequest::getCmd('task');
		if ($task == 'listclients') {
			$controllerName = 'client';
		}

		require_once( JPATH_COMPONENT.DS.'controllers'.DS.$controllerName.'.php' );
		$controllerName = 'BannerController'.$controllerName;

		// Create the controller
		$controller = new $controllerName();

		// Perform the Request task
		$controller->execute( JRequest::getCmd('task') );

		// Redirect if set by the controller
		$controller->redirect();
		break;
}
*/