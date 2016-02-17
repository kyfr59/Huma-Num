<?php
/**
* NakalaImport plugin class - imports data from NAKALA platforms (Huma-Num)
*
* @copyright Copyright 2015-2020 Limonade & Co (Paris)
* @author Franck Dupont <kyfr59@gmail.com>
* @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
* @package NakalaImport
*/

/** Path to plugin directory */

defined('NAKALA_IMPORT_PLUGIN_DIRECTORY') 
    or define('NAKALA_IMPORT_PLUGIN_DIRECTORY', dirname(__FILE__));


/** Path to plugin maps directory */
/*
defined('OAIPMH_HARVESTER_MAPS_DIRECTORY') 
    or define('OAIPMH_HARVESTER_MAPS_DIRECTORY', NAKALA_IMPORT_PLUGIN_DIRECTORY 
                                        . '/models/OaipmhHarvester/Harvest');
*/

/** Huma-num constants */

/** Path to temp directory of the plugin */
defined('PLUGIN_DIRECTORY_TEMP') 
    or define('PLUGIN_DIRECTORY_TEMP', NAKALA_IMPORT_PLUGIN_DIRECTORY 
                                        . '/temp');
/** Nakala prefix for data */
defined('NAKALA_DATA_PREFIX') 
    or define('NAKALA_DATA_PREFIX', "http://nakala.fr/data/");

/** Nakala prefix for resources */
defined('NAKALA_RESOURCE_PREFIX') 
    or define('NAKALA_RESOURCE_URL', "http://www.nakala.fr/resource/");
                
/** Nakala prefix for collections */
defined('NAKALA_COLLECTION_PREFIX') 
    or define('NAKALA_COLLECTION_PREFIX', "http://www.nakala.fr/collection/");
                
/** Nakala prefix for accounts */
defined('NAKALA_ACCOUNT_PREFIX') 
    or define('NAKALA_ACCOUNT_PREFIX', "http://www.nakala.fr/account/");

/** URL of Nakala SPARQL endpoint */
defined('NAKALA_SPARQL_ENDPOINT') 
    or define('NAKALA_SPARQL_ENDPOINT', "http://www.nakala.fr/sparql");                
                
/** Path of the EasyRDF library */
require(NAKALA_IMPORT_PLUGIN_DIRECTORY . '/easyrdf/lib/EasyRdf.php');

/** Path of the Sparql library */
require(NAKALA_IMPORT_PLUGIN_DIRECTORY . '/libraries/NakalaImportSparql.php');


/** Path of the database models */
require(NAKALA_IMPORT_PLUGIN_DIRECTORY . '/models/NakalaImport.php');

/** Path of the plugin globals functions */
require_once dirname(__FILE__) . '/functions.php';

/**
 * NakalaImportPlugin plugin.
 */
class NakalaImportPlugin extends Omeka_Plugin_AbstractPlugin
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
                              'items_browse_sql',
                              'config_form',
                              'config');

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

        $defaults = array(
            'nakala-handle' => '11280/f1401838',
            'ignore-updates' => ''
        );
        set_option('nakala_import_settings', serialize($defaults));

        $db = $this->_db;

        $sql = "
        CREATE TABLE IF NOT EXISTS `{$db->prefix}nakala_imports` (
          `id` int unsigned NOT NULL auto_increment,
          `initiated` datetime default NULL,
          `completed` datetime default NULL,
          `logs` text NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $db->query($sql);

    }

    public function hookConfigForm() 
    {

        $settings = unserialize(get_option('nakala_import_settings'));
                
        $options['nakala-handle']    = (string) $settings['nakala-handle'];
        $options['ignore-updates']    = (string) $settings['ignore-updates'];
        
        include 'forms/config-form.php';

        // Checking for max_execution_time value of PHP
        echo "<div style=\"clear:both; margin-left:12px; margin-top:30px;\"><strong>Vérification du réglage de la valeur max_execution_time de PHP</strong><br />";

        $max = (int)ini_get('max_execution_time');
        if ($max > 1000) {
            echo "<font color=\"#75940A\"><strong>La valeur de la variable max_execution_time de PHP est de ".$max." secondes.</strong></font>";
        } else {
            echo "<font color=\"red\"><strong>La valeur de la variable max_execution_time de PHP est de ".$max." secondes, la valeur conseilllée est de 1000 secondes. Cela peut être un problème avec les dépôts volumineux.</strong></font>";
        }
        echo '</div>';

        // Checking for max_input_vars value of PHP
        echo "<div style=\"clear:both; margin-left:12px; margin-top:30px;\"><strong>Vérification du réglage de la valeur max_input_vars de PHP</strong><br />";

        $max = (int)ini_get('max_input_vars');
        if ($max > 50000) {
            echo "<font color=\"#75940A\"><strong>La valeur de la variable max_input_vars de PHP est de ".$max." secondes.</strong></font>";
        } else {
            echo "<font color=\"red\"><strong>La valeur de la variable max_input_vars de PHP est de ".$max.", la valeur conseilllée est de 50000. Cela peut être un problème avec les dépôts volumineux.</strong></font>";
        }
        echo '</div>';

        // Checking file perms on /temp directory
        echo "<div style=\"clear:both; margin-left:12px; margin-top:30px;\"><strong>Vérification des permissions sur le dossier /temp du plugin</strong><br />";
        

        if (is_writable(PLUGIN_DIRECTORY_TEMP)) {
            echo "<font color=\"#75940A\"><strong>Les droits sont corrects, le dossier est accessible en écriture.</strong></font>";
        } else {
            echo "<font color=\"red\"><strong>Les droits sont incorrects, veuillez vérifier les droits sur le dossier suivant :<br />".PLUGIN_DIRECTORY_TEMP."</strong></font>";
        }
        echo '</div>';

        echo '<div class="inputs seven columns omega"><br />'.$view->formCheckbox("ignore-updates", 1, array('checked' => $options['ignore-updates'])).'&nbsp;&nbsp;Ignorer les mises à jour lors de l\'import ?</div>';
    }

    public function hookConfig()
    {
        $settings = unserialize(get_option('nakala_import_settings'));
        
        $settings['nakala-handle']      = (string) $_POST['nakala-handle'];
        $settings['ignore-updates']     = (string) $_POST['ignore-updates'];
        
        set_option('nakala_import_settings', serialize($settings));
    }

    
    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
        $db = $this->_db;
        
        // drop the tables        
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}nakala_imports`;";
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
        $acl->addResource('NakalaImport_Index');
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
            'label' => __('NAKALA Import'),
            'uri' => url('nakala-import'),
            'resource' => 'NakalaImport_Index',
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

            if ($identifier = getNakalaUriFromItem($item))
            {
                $nakalaUri = NAKALA_DATA_PREFIX . $identifier;

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
