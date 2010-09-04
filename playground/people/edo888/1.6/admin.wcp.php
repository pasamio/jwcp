<?php
/**
 * @version   $Id$
 * @copyright Copyright (C) 2009 - 2010 Edvard Ananyan. All rights reserved.
 * @author    Edvard Ananyan <edo888@gmail.com>
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Access check.
if(!JFactory::getUser()->authorise('core.manage', 'com_wcp'))
    return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));

// Require the base controller
require_once(JPATH_COMPONENT.DS.'controller.php');

// Create the controller
$controller = new WCPController();

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));

// Redirect if set by the controller
$controller->redirect();