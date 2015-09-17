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

<?php $uri = $_SERVER['REQUEST_URI']; ?>
<img src="<?php echo WEB_ROOT ?>/plugins/NakalaImport/images/logo-huma-num.png"/ style="margin-bottom:30px;"><br /><br />
<ul id="section-nav" class="navigation">
    <li class="<?php if ($uri == '/omeka-humanum/admin/nakala-export') {echo 'current';} ?>">
        <a href="<?php echo html_escape(url('nakala-export')); ?>"><?php echo __('Notices à exporter'); ?></a>
    </li>
    <li class="<?php if ($uri == '/omeka-humanum/admin/nakala-export/collections') {echo 'current';} ?>">
        <a href="<?php echo html_escape(url('nakala-export/collections')); ?>"><?php echo __('Collections à exporter'); ?></a>
    </li>
</ul>
<?php echo flash(); ?>

<?php if ($total_results): ?>
    <?php echo pagination_links(); ?>

    <form action="<?php echo html_escape(url('nakala-export/export')); ?>" method="post" accept-charset="utf-8">
        <div class="table-actions batch-edit-option">
            <?php if (is_allowed('Items', 'add')): ?>
            <input type="submit" value="<?php echo __('Envoyer vers NAKALA'); ?>" />
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
                $browseHeadings[__('Date Added')] = 'added';
                $browseHeadings[__('Status')] = 'status';
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
                <td class="batch-edit-check" scope="row"><input type="checkbox" name="facile[]" value="<?php echo $id; ?>" /></td>
                <?php endif; ?>

                <td><?php if (metadata('item', 'has files')): ?>
                    <?php echo link_to_item(item_image('square_thumbnail', array(), 0, $item), array('class' => 'item-thumbnail'), 'show', $item); ?>
                    <?php endif; ?>
                    <?php echo link_to_item(); ?>
                </td>

                <td>
                    <?php
                    echo ($typeName = metadata('item', 'Item Type Name'))
                        ? $typeName
                        : metadata('item', array('Dublin Core', 'Type'), array('snippet' => 35));
                    ?>
                </td>
                <td><?php echo format_date(metadata('item', 'added')); ?></td>

                <td><?php echo $item['status'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
    </form>

    <?php echo pagination_links(); ?>

    <script type="text/javascript">
    Omeka.addReadyCallback(Omeka.ItemsBrowse.setupDetails, [
        <?php echo js_escape(__('Details')); ?>,
        <?php echo js_escape(__('Show Details')); ?>,
        <?php echo js_escape(__('Hide Details')); ?>
    ]);
    Omeka.addReadyCallback(Omeka.ItemsBrowse.setupBatchEdit);
    </script>

    <script>
        jQuery(document).ready(function() {
            jQuery('input[name="items[]"]').click(function() {
                item = jQuery(this);
                value = item.attr("value");
                elem = jQuery('input[name="facile[]"][value='+value+']');
                checked = item.is(':checked');
                elem[0].checked = checked;
            });
        });
    </script>


    <?php else: ?>
        <p>Aucune notice à exporter.</p>
    <?php endif; ?>

        
<?php echo foot(); ?>
