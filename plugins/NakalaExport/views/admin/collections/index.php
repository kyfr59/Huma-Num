<?php
queue_js_file('items-browse');
$pageTitle = __('Browse Collections') . ' ' . __('(%s total)', $total_results);
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
<ul id="section-nav" class="navigation">
    <li class="<?php if ($uri == '/omeka-humanum/admin/nakala-export') {echo 'current';} ?>">
        <a href="<?php echo html_escape(url('nakala-export')); ?>"><?php echo __('Notices à exporter'); ?></a>
    </li>
    <li class="<?php if ($uri == '/omeka-humanum/admin/nakala-export/collections') {echo 'current';} ?>">
        <a href="<?php echo html_escape(url('nakala-export/collections')); ?>"><?php echo __('Collections à exporter'); ?></a>
    </li>
</ul>
<?php echo flash(); ?>

<?php if (total_records('Collection') > 0): ?>
    <?php echo pagination_links(); ?>

    <form action="<?php echo html_escape(url('nakala-export/export/collections')); ?>" method="post" accept-charset="utf-8">
    <?php if (has_loop_records('collections')): ?>

        <div class="table-actions batch-edit-option">
            <?php if (is_allowed('Items', 'add')): ?>
            <input type="submit" value="<?php echo __('Envoyer vers Nakala'); ?>" />
            <?php endif; ?>
        </div>

        <table id="items" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                <?php if (is_allowed('Collections', 'edit')): ?>
                <th class="batch-edit-heading"><?php echo __('Select'); ?></th>
                <?php endif; ?>
                <?php
                $sortLinks = array(
                    __('Titre') => 'Dublin Core,Title',
                    __('Collection parente dans Nakala') => null,
                );
                ?>
                <?php echo browse_sort_links($sortLinks, array('link_tag' => 'th scope="col"', 'list_tag' => '')); ?>
                </tr>
            </thead>
            <tbody>
                <?php $key = 0; ?>
                <?php foreach (loop('Collection') as $collection): ?>
                <tr class="collection<?php if(++$key%2==1) echo ' odd'; else echo ' even'; ?>">
                    <?php $id = metadata($collection, 'id'); ?>

                    <?php if (is_allowed($collection, 'edit') || is_allowed($collection, 'tag')): ?>
                    <td class="batch-edit-check" scope="row"><input type="checkbox" name="collections[]" value="<?php echo $id; ?>" /></td>
                    <?php endif; ?>

                    <td class="title<?php if ($collection->featured) { echo ' featured';} ?>">
                        <?php if ($collectionImage = record_image('collection', 'square_thumbnail')): ?>
                            <?php echo link_to_collection($collectionImage, array('class' => 'image')); ?>
                        <?php endif; ?>
                        <?php echo link_to_collection(); ?>
                        <?php if (!$collection->public) echo __('(Private)'); ?>
                        <?php if (is_allowed($collection, 'edit')): ?>
                        <ul class="action-links">
                            <li><?php echo link_to_collection(__('Edit'), array('class'=>'edit'), 'edit'); ?></li>
                        </ul>
                        <?php endif; ?>
                        <?php fire_plugin_hook('admin_collections_browse_each', array('collection' => $collection, 'view' => $this)); ?>
                    </td>
                    <td>
                    <?php
                        if(count($this->nakala_collections)) {
                            echo "<select name='nakala_collection_".$id."'>";
                            echo "<option value=''>Faites votre choix</option>";
                            foreach($this->nakala_collections as $nakala_collection)
                            {
                              echo "<option value='".(string)$nakala_collection->collection."'>".(string)$nakala_collection->nomCollection."</option>";
                            }
                            echo "</select>";
                        }
                    ?>    
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (is_allowed('Collections', 'add')): ?>
            <a href="<?php echo html_escape(url('collections/add')); ?>" class="small green button"><?php echo __('Add a Collection'); ?></a>
        <?php endif; ?>
    <?php else: ?>
        <p><?php echo __('There are no collections on this page.'); ?> <?php echo link_to('collections', null, __('View All Collections')); ?></p>
    <?php endif; ?> 
    </form>

    <script type="text/javascript">
    Omeka.addReadyCallback(Omeka.ItemsBrowse.setupDetails, [
        <?php echo js_escape(__('Details')); ?>,
        <?php echo js_escape(__('Show Details')); ?>,
        <?php echo js_escape(__('Hide Details')); ?>
    ]);
    Omeka.addReadyCallback(Omeka.ItemsBrowse.setupBatchEdit);
    </script>

<?php else: ?>
    <h2><?php echo __('You have no collections.'); ?></h2>
    <?php if(is_allowed('Collections', 'add')): ?>
        <p><?php echo __('Get started by adding your first collection.'); ?></p>
        <a href="<?php echo html_escape(url('collections/add')); ?>" class="add big green button"><?php echo __('Add a Collection'); ?></a>
    <?php endif; ?>
<?php endif; ?>

<?php fire_plugin_hook('admin_collections_browse', array('collections' => $collections, 'view' => $this)); ?>

<?php echo foot(); ?>
