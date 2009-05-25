<?php
/**
 * @version   $Id$
 * @copyright Copyright (C) 2009 Edvard Ananyan. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<script type="text/javascript">
function addExcludeFile(anchor) {
	var n = document.getElementsByName('exclude_files[]').length + 1;
	var a = document.getElementById('exclude_files_add');

    var newEntry = document.createElement('input');
    newEntry.setProperty('class', 'inputbox');
    newEntry.setProperty('type', 'text');
    newEntry.setProperty('name', 'exclude_files[]');
    newEntry.setProperty('id', 'exclude_files_' + n);
    newEntry.setProperty('size', '60');
    newEntry.setProperty('style', 'margin-bottom:2px;');

    var lineBreak = document.createElement('br');
    var spacer = document.createElement('span');
    spacer.innerHTML = '&nbsp;';

    lineBreak.inject(a, 'before');
    newEntry.inject(a, 'before');
    spacer.inject(a, 'before');
}

function addExcludeTable() {
    var n = document.getElementsByName('exclude_tables[]').length + 1;
    var a = document.getElementById('exclude_tables_add');

    var newEntry = document.createElement('input');
    newEntry.setProperty('class', 'inputbox');
    newEntry.setProperty('type', 'text');
    newEntry.setProperty('name', 'exclude_tables[]');
    newEntry.setProperty('id', 'exclude_tables_' + n);
    newEntry.setProperty('size', '60');
    newEntry.setProperty('style', 'margin-bottom:2px;');

    var lineBreak = document.createElement('br');
    var spacer = document.createElement('span');
    spacer.innerHTML = '&nbsp;';

    lineBreak.inject(a, 'before');
    newEntry.inject(a, 'before');
    spacer.inject(a, 'before');
}
</script>

<form action="index.php" method="post" name="adminForm">

    <fieldset class="adminform">
        <legend><?php echo JText::_('General'); ?></legend>

        <table class="admintable">
        <tr>
            <td width="200" class="key">
                <label for="name">
                    <?php echo JText::_('Name'); ?>:
                </label>
            </td>
            <td>
                <input class="inputbox" type="text" name="name" id="name" size="60" value="<?php echo $this->item->name; ?>" />
            </td>
        </tr>
        <tr>
            <td width="200" class="key">
                <label for="path">
                    <?php echo JText::_('Path'); ?>:
                </label>
            </td>
            <td>
                <input class="inputbox" type="text" name="path" id="path" size="60" value="<?php echo $this->item->path; ?>" />
            </td>
        </tr>
        <tr>
            <td width="200" class="key">
                <?php echo JText::_('Site ID'); ?>:
            </td>
            <td>
                <b><?php echo $this->secret; ?></b>
            </td>
        </tr>
        </table>
    </fieldset>

    <fieldset class="adminform">
        <legend><?php echo JText::_('Exclude Files'); ?></legend>

        <table class="admintable">
        <tr>
            <td width="200" class="key" valign="top">
                <label for="exclude_files_1">
                    <?php echo JText::_('Path'); ?>:
                </label>
            </td>
            <td>
                <?php for($i = 0, $n = count($this->exclude_files); $i < $n; $i++): ?>
                <input class="inputbox" type="text" name="exclude_files[]" id="exclude_files_<?php echo $i + 1; ?>" size="60" style="margin-bottom:2px;" value="<?php echo $this->exclude_files[$i]; ?>" />
                <?php if($i + 1 < $n): ?><br />
                <?php else: ?><a href="javascript:addExcludeFile();" id="exclude_files_add"><?php echo JText::_('Add row'); ?></a>
                <?php endif; ?>
                <?php endfor; ?>
            </td>
        </tr>
        </table>
    </fieldset>

    <fieldset class="adminform">
        <legend><?php echo JText::_('Exclude Tables'); ?></legend>

        <table class="admintable">
        <tr>
            <td width="200" class="key" valign="top">
                <label for="exclude_tables_1">
                    <?php echo JText::_('Table Name'); ?>:
                </label>
            </td>
            <td>
                <?php for($i = 0, $n = count($this->exclude_tables); $i < $n; $i++): ?>
                <input class="inputbox" type="text" name="exclude_tables[]" id="exclude_tables_<?php echo $i + 1; ?>" size="60" style="margin-bottom:2px;" value="<?php echo $this->exclude_tables[$i]; ?>" />
                <?php if($i + 1 < $n): ?><br />
                <?php else: ?><a href="javascript:addExcludeTable();" id="exclude_tables_add"><?php echo JText::_('Add row'); ?></a>
                <?php endif; ?>
                <?php endfor; ?>
            </td>
        </tr>
        </table>
    </fieldset>

    <div class="clr"></div>

    <input type="hidden" name="option" value="com_wcp" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>" />
    <input type="hidden" name="secret" value="<?php echo $this->secret; ?>" />
    <?php echo JHTML::_('form.token'); ?>

</form>