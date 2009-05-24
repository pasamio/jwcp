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
                <?php echo JHTML::_('grid.sort',  JText::_('Name'), 'w.name', $this->lists['order_Dir'], $this->lists['order']); ?>
            </th>
            <th>
                <?php echo JHTML::_('grid.sort',  JText::_('Path'), 'w.path', $this->lists['order_Dir'], $this->lists['order']); ?>
            </th>
            <th width="5">
                <?php echo JHTML::_('grid.sort',  JText::_('ID'), 'w.id', $this->lists['order_Dir'], $this->lists['order']); ?>
            </th>
        </tr>
    </thead>
    <?php
    $k = 0;
    for($i=0, $n=count( $this->items ); $i < $n; $i++) {
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
                <?php echo $row->name; ?>
            </td>
            <td>
                <?php echo $row->path; ?>
            </td>
            <td>
                <?php echo $row->id; ?>
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
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
<?php echo JHTML::_('form.token'); ?>

</form>