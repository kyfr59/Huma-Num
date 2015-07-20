<?php
queue_js_file('items-browse');
$pageTitle = __('Browse Items') . ' ' . __('(%s total)', $total_results);
echo head(
    array(
        'title' => $pageTitle,
        'bodyclass' => 'items browse'
    )
);
echo flash();
echo item_search_filters();
?>

<?php echo flash(); ?>

<?php if ($total_results): ?>
    <?php echo pagination_links(); ?>

    <form action="<?php echo html_escape(url('export-to-nakala/export')); ?>" method="post" accept-charset="utf-8">
        <div class="table-actions batch-edit-option">
            <?php if (is_allowed('Items', 'add')): ?>
            <input type="submit" value="<?php echo __('Send to Nakala'); ?>" />
            <?php endif; ?>
        </div>

        <table id="items" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <?php if (is_allowed('Items', 'edit')): ?>
                <th class="batch-edit-heading"><?php echo __('Select'); ?></th>
                <?php endif; ?>
                <th class="batch-edit-heading"><?php echo __('FACILE'); ?></th>
                <?php
                $browseHeadings[__('Title')] = 'Dublin Core,Title';
                $browseHeadings[__('Creator')] = 'Dublin Core,Creator';
                $browseHeadings[__('Type')] = null;
                $browseHeadings[__('Date Added')] = 'added';
                echo browse_sort_links($browseHeadings, array('link_tag' => 'th scope="col"', 'list_tag' => '')); 
                ?>
            </tr>
        </thead>
        <tbody>
            <?php $key = 0; ?>
            <?php foreach (loop('Item') as $item): ?>
            <tr class="item <?php if(++$key%2==1) echo 'odd'; else echo 'even'; ?>">
                <?php $id = metadata('item', 'id'); ?>

                <?php if (is_allowed($item, 'edit') || is_allowed($item, 'tag')): ?>
                <td class="batch-edit-check" scope="row"><input type="checkbox" name="items[]" value="<?php echo $id; ?>" /></td>
                <?php endif; ?>


                <?php if (is_allowed($item, 'edit') || is_allowed($item, 'tag')): ?>
                <td class="batch-edit-check" scope="row"><input type="checkbox" name="items[]" value="<?php echo $id; ?>" /></td>
                <?php endif; ?>

                <?php if ($item->featured): ?>
                <td class="item-info featured">
                <?php else: ?>
                <td class="item-info">
                <?php endif; ?>

                    <?php if (metadata('item', 'has files')): ?>
                    <?php echo link_to_item(item_image('square_thumbnail', array(), 0, $item), array('class' => 'item-thumbnail'), 'show', $item); ?>
                    <?php endif; ?>

                    <span class="title">
                    <?php echo link_to_item(); ?>

                    <?php if(!$item->public): ?>
                    <?php echo __('(Private)'); ?>
                    <?php endif; ?>
                    </span>
                    <ul class="action-links group">
                        <?php if (is_allowed($item, 'edit')): ?>
                        <li><?php echo link_to_item(__('Edit'), array(), 'edit'); ?></li>
                        <?php endif; ?>

                        <?php if (is_allowed($item, 'delete')): ?>
                        <li><?php echo link_to_item(__('Delete'), array('class' => 'delete-confirm'), 'delete-confirm'); ?></li>
                        <?php endif; ?>
                    </ul>

                    <?php fire_plugin_hook('admin_items_browse_simple_each', array('item' => $item, 'view' => $this)); ?>

                    
                </td>
                <td><?php echo strip_formatting(metadata('item', array('Dublin Core', 'Creator'))); ?></td>
                <td>
                    <?php
                    echo ($typeName = metadata('item', 'Item Type Name'))
                        ? $typeName
                        : metadata('item', array('Dublin Core', 'Type'), array('snippet' => 35));
                    ?>
                </td>
                <td><?php echo format_date(metadata('item', 'added')); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
    </form>
    <?php else: ?>
        <p><?php echo __('The query searched %s items and returned no results.', total_records('Item')); ?> <?php echo __('Would you like to %s?', link_to_item_search(__('refine your search'))); ?></p>
    <?php endif; ?>

        
<?php echo foot(); ?>
