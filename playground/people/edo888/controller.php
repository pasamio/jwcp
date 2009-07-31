<?php
/**
 * @version   $Id$
 * @copyright Copyright (C) 2009 Edvard Ananyan. All rights reserved.
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

jimport('joomla.application.component.controller');

/**
 * Working Copy Controller class
 *
 */
class WCPController extends JController {

    function __construct($config = array()) {
        parent::__construct($config);

        require_once(JPATH_COMPONENT.DS.'helper.php');
        require_once(JPATH_COMPONENT.DS.'tables'.DS.'wcp.php');
        jimport('joomla.filesystem.file');

        // Register tasks
        $this->registerTask('add', 'edit');
        $this->registerTask('edit', 'edit');
        $this->registerTask('save', 'save');
        $this->registerTask('apply', 'save');
        $this->registerTask('cancel', 'cancel');
        $this->registerTask('remove', 'remove');
        $this->registerTask('diff', 'differences');
        $this->registerTask('refreshDiff', 'refreshDiff');
        $this->registerTask('createPatch', 'createPatch');
        $this->registerTask('applyPatch', 'applyPatch');
        $this->registerTask('commit', 'commit');
        $this->registerTask('merge', 'merge');
        $this->registerTask('revertChild', 'revertChild');
        $this->registerTask('syncChild', 'syncChild');
        // TODO: Remove this task
        $this->registerTask('test', 'test');
    }


    function display() {
        JRequest::setVar('view', 'childs');
	    parent::display();
	}

    function edit() {
        JRequest::setVar('view', 'child');
        parent::display();
    }

    function save() {
        list($cid) = JRequest::getVar('cid', array(''));
        if($cid == '') {
            WCPHelper::createChild();
            $this->setRedirect('index.php?option=com_wcp', JText::_('Child created successfully'));
        } else {
            WCPHelper::applyChild();
            if(JRequest::getVar('task') == 'save')
                $this->setRedirect('index.php?option=com_wcp', JText::_('Child info saved successfully'));
            else
                $this->setRedirect('index.php?option=com_wcp&task=edit&cid[]='.$cid, JText::_('Child info saved successfully'));
        }
    }

    function cancel() {
        // Check for request forgeries
        JRequest::checkToken() or jexit('Invalid Token');
        $this->setRedirect('index.php?option=com_wcp');
    }

    function remove() {
        WCPHelper::removeChild();
        $this->setRedirect('index.php?option=com_wcp', JText::_('Child(s) deleted successfully'));
    }

    function differences() {
        JRequest::setVar('view', 'differences');
        parent::display();
    }

    function refreshDiff() {
        $cache =& JFactory::getCache('com_wcp', 'callback', 'file');
        $cache->clean('com_wcp', 'group');

        WCPHelper::setInternalTime();

        $this->setRedirect('index.php?option=com_wcp&task=differences', JText::_('List Refreshed'));
    }

    function createPatch() {
        WCPHelper::createPatch();

        //$this->setRedirect('index.php?option=com_wcp&task=differences', JText::_('Patch Created'));
    }

    function applyPatch() {
        if(!JRequest::getVar('submitted', false)) {
            JRequest::setVar('view', 'applyPatch');
            parent::display();
        } else {
            // Check for request forgeries
            JRequest::checkToken() or jexit('Invalid Token');

            if(WCPHelper::applyPatch())
                $this->setRedirect('index.php?option=com_wcp', JText::_('Patch Applied Successfully'));
            else
                $this->setRedirect('index.php?option=com_wcp&task=applyPatch');
        }
    }

    function commit() {
        WCPHelper::commit();

        $cache =& JFactory::getCache('com_wcp', 'callback', 'file');
        $cache->clean('com_wcp', 'group');

        $this->setRedirect('index.php?option=com_wcp&task=differences', JText::_('Commit completed'));
    }

    function merge() {
        WCPHelper::merge();
        $this->setRedirect('index.php?option=com_wcp', JText::_('Childs merged successfully'));
    }

    function revertChild() {
        WCPHelper::revertChild();

        $cache =& JFactory::getCache('com_wcp', 'callback', 'file');
        $cache->clean('com_wcp', 'group');

        $this->setRedirect('index.php?option=com_wcp&task=differences', JText::_('Revert completed'));
    }

    function syncChild() {
        WCPHelper::syncChild();

        $cache =& JFactory::getCache('com_wcp', 'callback', 'file');
        $cache->clean('com_wcp', 'group');

        $this->setRedirect('index.php?option=com_wcp&task=differences', JText::_('Synchronization proccess completed'));
    }

    // TODO: Remove this function
    function test() {
        WCPHelper::test();
    }

}
