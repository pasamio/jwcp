<?php
/**
 * @version   $Id$
 * @copyright Copyright (C) 2009 Edvard Ananyan. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<form action="index.php" method="post" name="adminForm">

    <fieldset class="adminform">
        <legend><?php echo JText::_('Details'); ?></legend>

        <table class="admintable">
        <tr>
            <td width="200" class="key">
                <label for="name">
                    <?php echo JText::_('name'); ?>:
                </label>
            </td>
            <td>
                <input class="inputbox" type="text" name="name" id="name" size="60" value="<?php echo @$this->item->title; ?>" />
            </td>
        </tr>
        </table>
    </fieldset>

    <div class="clr"></div>

    <input type="hidden" name="option" value="com_wcp" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="id" value="<?php echo @$this->item->id; ?>" />

</form>