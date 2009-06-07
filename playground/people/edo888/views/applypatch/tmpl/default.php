<?php
/**
 * @version   $Id$
 * @copyright Copyright (C) 2009 Edvard Ananyan. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm">
    <table class="adminform">
    <tr>
        <th colspan="2"><?php echo JText::_('Upload Patch File'); ?></th>
    </tr>
    <tr>
        <td width="120">
            <label for="patch_file"><?php echo JText::_('Patch File'); ?>:</label>
        </td>
        <td>
            <input class="input_box" id="patch_file" name="patch_file" type="file" size="57" />
            <input class="button" type="button" value="<?php echo JText::_('Upload File and Patch'); ?>" onclick="submitbutton()" />
        </td>
    </tr>
    </table>

    <input type="hidden" name="type" value="" />
    <input type="hidden" name="task" value="creatPatch" />
    <input type="hidden" name="option" value="com_wcp" />
    <?php echo JHTML::_( 'form.token' ); ?>
</form>