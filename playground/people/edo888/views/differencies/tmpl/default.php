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
    <table class="adminlist">
    <thead>
        <tr>
            <th width="5">
                <?php echo JText::_('NUM'); ?>
            </th>
            <th width="20">
                <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
            </th>
            <th>
                <?php echo JText::_('Path'); ?>
            </th>
            <th>
                <?php echo JText::_('Modified Date'); ?>
            </th>
        </tr>
    </thead>
    <?php
    $k = 0;
    for($i = 0, $n = count($this->items); $i < $n; $i++) {
        $row =& $this->items[$i];
        $checked = JHTML::_('grid.checkedout', $row, $i);
        ?>
        <tr class="<?php echo "row$k"; ?>">
            <td>
                <?php echo $i + 1; ?>
            </td>
            <td>
                <?php echo $checked; ?>
            </td>
            <td>
                <?php echo $row[0]; ?>
            </td>
            <td>
                <?php echo $row[1]; ?>
            </td>
        </tr>
        <?php
        $k = 1 - $k;
    }
    ?>
    </table>

<input type="hidden" name="option" value="com_wcp" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHTML::_('form.token'); ?>

</form>