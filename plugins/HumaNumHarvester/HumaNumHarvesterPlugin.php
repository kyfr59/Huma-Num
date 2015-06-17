<?php
/**
* HumaNumHarvesterPlugin class - represents the OAI-PMH Harvester plugin for Huma-Num (Nakala)
*
* @copyright Copyright 2015-2020 Limonade & Co (Paris)
* @author Franck Dupont <kyfr59@gmail.com>
* @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
* @package HumaNumHarvester
*/


/** Path to plugin directory */

defined('OAIPMH_HARVESTER_PLUGIN_DIRECTORY') 
    or define('OAIPMH_HARVESTER_PLUGIN_DIRECTORY', dirname(__FILE__));

/** Path to plugin maps directory */
defined('OAIPMH_HARVESTER_MAPS_DIRECTORY') 
    or define('OAIPMH_HARVESTER_MAPS_DIRECTORY', OAIPMH_HARVESTER_PLUGIN_DIRECTORY 
                                        . '/models/OaipmhHarvester/Harvest');

/** Huma-num constants */

/** Path to temp directory of the plugin */
defined('OAIPMH_HARVESTER_PLUGIN_DIRECTORY_TEMP') 
    or define('OAIPMH_HARVESTER_PLUGIN_DIRECTORY_TEMP', OAIPMH_HARVESTER_PLUGIN_DIRECTORY 
                                        . '/temp');
/** Nakala prefix for data */
defined('NAKALA_DATA_PREFIX') 
    or define('NAKALA_DATA_PREFIX', "http://nakala.fr/data/");

/** Nakala prefix for resources */
defined('NAKALA_RESOURCE_PREFIX') 
    or define('NAKALA_RESOURCE_URL', "http://www.nakala.fr/resource/");
                
/** URL of Nakala SPARQL endpoint */
defined('NAKALA_SPARQL_ENDPOINT') 
    or define('NAKALA_SPARQL_ENDPOINT', "http://www.nakala.fr/sparql");                
                
/** Path of the EasyRDF library */
require(OAIPMH_HARVESTER_PLUGIN_DIRECTORY . '/easyrdf/lib/EasyRdf.php');


require_once dirname(__FILE__) . '/functions.php';

/**
 * HumaNumHarvesterPlugin plugin.
 */
class HumaNumHarvesterPlugin extends Omeka_Plugin_AbstractPlugin
{
    
    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array('install', 
                              'uninstall',
                              'upgrade',
                              'public_head',
                              'define_acl', 
                              'before_delete_item',
                              'admin_items_show_sidebar',
                              'items_browse_sql');

    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array('admin_navigation_main', 
                                'file_markup',
                                'browse_plugins');

    /**
     * @var array Options and their default values.
     */
    protected $_options = array();

    /**
     * Install the plugin.
     */
    public function hookInstall()
    {

        //echo "oo".plugin_is_active('OaipmhHarvester');  
        //$this->_helper->flashMessenger($message, 'success')
        //Zend_Debug::dump(get_view());

        $db = $this->_db;

        /* Harvests/collections:
          id: primary key
          collection_id: the corresponding collection id in `collections`
          base_url: the OAI-PMH base URL
          metadata_prefix: the OAI-PMH metadata prefix used for this harvest
          set_spec: the OAI-PMH set spec (unique identifier)
          set_name: the OAI-PMH set name
          set_description: the Dublin Core description of the set, if any
          status: the current harvest status for this set: starting, in progress,
          completed, error, deleted
          status_messages: any messages sent from the harvester, usually during
          an error status
          initiated: the datetime the harvest initiated
          completed: the datetime the harvest completed
        */
        $sql = "
        CREATE TABLE IF NOT EXISTS `{$db->prefix}oaipmh_harvester_harvests` (
          `id` int unsigned NOT NULL auto_increment,
          `collection_id` int unsigned default NULL,
          `base_url` text NOT NULL,
          `metadata_prefix` tinytext NOT NULL,
          `set_spec` text,
          `set_name` text,
          `set_description` text,
          `status` enum('queued','in progress','completed','error','deleted','killed') NOT NULL default 'queued',
          `status_messages` text,
          `resumption_token` text,
          `initiated` datetime default NULL,
          `completed` datetime default NULL,
          `start_from` datetime default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $db->query($sql);

        /* Harvested records/items.
          id: primary key
          harvest_id: the corresponding set id in `oaipmh_harvester_harvests`
          item_id: the corresponding item id in `items`
          identifier: the OAI-PMH record identifier (unique identifier)
          datestamp: the OAI-PMH record datestamp
        */
        $sql = "
        CREATE TABLE IF NOT EXISTS `{$db->prefix}oaipmh_harvester_records` (
          `id` int unsigned NOT NULL auto_increment,
          `harvest_id` int unsigned NOT NULL,
          `item_id` int unsigned default NULL,
          `identifier` text NOT NULL,
          `datestamp` tinytext NOT NULL,
          PRIMARY KEY  (`id`),
          KEY `identifier_idx` (identifier(255)),
          UNIQUE KEY `item_id_idx` (item_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $db->query($sql);

    }
    
    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
        $db = $this->_db;
        
        // drop the tables        
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}oaipmh_harvester_harvests`;";
        $db->query($sql);
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}oaipmh_harvester_records`;";
        $db->query($sql);
        
        $this->_uninstallOptions();
    }

    public function hookUpgrade($args)
    {
        $db = $this->_db;
        $oldVersion = $args['old_version'];

        if (version_compare($oldVersion, '2.0-dev', '<')) {
            $sql = <<<SQL
ALTER TABLE `{$db->prefix}oaipmh_harvester_harvests`
    DROP COLUMN `metadata_class`,
    DROP COLUMN `pid`,
    ADD COLUMN `resumption_token` TEXT COLLATE utf8_unicode_ci AFTER `status_messages`
SQL;
            $db->query($sql);

            $sql = <<<SQL
ALTER TABLE `{$db->prefix}oaipmh_harvester_records`
    ADD INDEX `identifier_idx` (identifier(255)),
    ADD UNIQUE INDEX `item_id_idx` (item_id)
SQL;
            $db->query($sql);
        }
    }
    /**
     * Define the ACL.
     * 
     * @param array $args
     */
    public function hookDefineAcl($args)
    {
        $acl = $args['acl']; // get the Zend_Acl
        $acl->addResource('OaipmhHarvester_Index');
    }

    /**
     * Specify plugin uninstall message
     *
     * @param array $args
     */
    public function hookAdminAppendToPluginUninstallMessage($args)
    {
        echo '<p>While you will not lose the items and collections created by your
        harvests, you will lose all harvest-specific metadata and the ability to
        re-harvest.</p>';
    }
    
    /**
    * Appended to admin item show pages.
    *
    * @param array $args
    */
    public function hookAdminItemsShowSidebar($args)
    {
        $item = $args['item'];
        echo $this->_expose_duplicates($item);
    }
    
    /**
     * Returns a view of any duplicate harvested records for an item
     *
     * @param Item $item The item.
     */
    protected function _expose_duplicates($item)
    {
        if (!$item->exists()) {
            return;
        }
        $items = get_db()->getTable('Item')->findBy(
            array(
                'oaipmh_harvester_duplicate_items' => $item->id,
            )
        );
        if (!count($items)) {
            return '';
        }
        return get_view()->partial('index/_duplicates.php', array('items' => $items));
    }
    
    /**
     * Deletes harvester record associated with a deleted item.
     *
     * @param array $args
     */
    public function hookBeforeDeleteItem($args)
    {
        $db = $this->_db;
        $item = $args['record'];
        $id = $item->id;
        $recordTable = $db->getTable('OaipmhHarvester_Record');
        $record = $recordTable->findByItemId($id);
        if ($record) {
            $record->delete();
            release_object($record);
        }
    }
    
    /**
     * Hooks into item_browse_sql to return items in a particular oaipmh record.
     *
     * @param array $args
     */
    public function hookItemsBrowseSql($args)
    {
        $db = $this->_db;
        $select = $args['select'];
        $params = $args['params'];
        
        // Filter based on duplicates of a given oaipmh record.
        $dupKey = 'oaipmh_harvester_duplicate_items';
        if (array_key_exists($dupKey, $params)) {
            $itemId = $params[$dupKey];
            $select->where(
                "items.id IN (
                    SELECT oaipmhharvestor_records.item_id FROM $db->OaipmhHarvester_Record oaipmhharvestor_records
                    WHERE oaipmhharvestor_records.identifier IN (
                        SELECT oaipmhharvestor_records2.identifier FROM $db->OaipmhHarvester_Record oaipmhharvestor_records2
                        WHERE oaipmhharvestor_records2.item_id = ?
                    ) AND oaipmhharvestor_records.item_id != ?
                )",
                $itemId
            );
        }
    }
    
    /**
     * Add the OAI-PMH Harvester link to the admin main navigation.
     * 
     * @param array Navigation array.
     * @return array Filtered navigation array.
     */
    public function filterAdminNavigationMain($nav)
    {        
        $nav[] = array(
            'label' => __('Huma-Num Harvester'),
            'uri' => url('huma-num-harvester'),
            'resource' => 'OaipmhHarvester_Index',
            'privilege' => 'index',
            'class' => 'nav-oai-pmh-harvester'
        );       
        return $nav;
    }


    /**
     * Hide the OAI-PMH Harvester plugin in the list of plugins
     * (This plugin replace the OAI-PHM Harvester plugin for Huma-Num, the 2
     * plugins can't work in conjonction).
     * 
     * @param array List of plugins array.
     * @return array Filtered list of plugin array.
     */
    public function filterBrowsePlugins($allPlugins)
    {        
        if ($allPlugins['OaipmhHarvester'])
          unset($allPlugins['OaipmhHarvester']);
        return $allPlugins;
    }


    
    /**
     * Replace the src of medias by a Nakala url
     * Correct display bug on Html5Media plugin
     * Autostart media players
     *                                          
     * @param html The HTML code of filter
     * @param options An array containing options (in particular the $file element)
     * @return array Filtered HTML code.
     */
    public function filterFileMarkup($html, $options) {

      $item = $options['file']->getItem();
        
        if ($item->exists()) {

            if ($nakalaUri = getNakalaUriFromItem($item))
            {
                // Correcting a bug on mediaelementplayer() call (remove this call from the original HTML)
                $html = @ereg_replace("mediaelementplayer", '', $html);
                
                // Autostart audio & videos
                $html = @ereg_replace("<video ", '<video autoplay="true" ', $html);
                $html = @ereg_replace("<audio ", '<audio autoplay="true" ', $html);

                $html = @ereg_replace('src="([^"]*)"', 'src="'.$nakalaUri.'"', $html);
                $html = @ereg_replace('href="([^"]*)"', 'href="'.$nakalaUri.'"', $html);
            }
        }
        return $html;
    }


    /**
     * Hooks into public_head to add a css file for the plugin
     *
     * @param array $args
     */
    public function hookPublicHead($args)
    {
      queue_css_file('huma-num');
    }



}
