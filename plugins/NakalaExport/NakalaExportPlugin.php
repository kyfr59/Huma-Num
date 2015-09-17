<?php
/**
 * Shibboleth Login
 * 
 * @copyright Copyright 2015-2020 Limonade & Co (Paris)
 * @author Franck Dupont <kyfr59@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

define('NAKALA_EXPORT_DIR', dirname(__FILE__));
define('NAKALA_EXPORT_HELPERS_DIR', NAKALA_EXPORT_DIR . DIRECTORY_SEPARATOR . 'helpers');

require_once NAKALA_EXPORT_HELPERS_DIR . DIRECTORY_SEPARATOR . 'NakalaConsoleHelper.php';

// Nakala Console Paths
define('BATCH_PATH', BASE_DIR . DIRECTORY_SEPARATOR . '/nakala-console/');
define('BATCH_INPUT_PATH', NAKALA_EXPORT_DIR . DIRECTORY_SEPARATOR . 'zips/input/');
define('BATCH_INPUT_COLLECTIONS_PATH', NAKALA_EXPORT_DIR . DIRECTORY_SEPARATOR . 'zips/input-collections/');
define('BATCH_OUTPUT_PATH', NAKALA_EXPORT_DIR . DIRECTORY_SEPARATOR . 'zips/output/');
define('BATCH_ERRORS_PATH', NAKALA_EXPORT_DIR . DIRECTORY_SEPARATOR . 'zips/errors/');

/** Nakala prefix for data */
defined('NAKALA_COLLECTION_PREFIX') 
    or define('NAKALA_COLLECTION_PREFIX', "http://www.nakala.fr/collection/");


require_once dirname(__FILE__) . '/functions.php';

/**
 * The Shibboleth Login plugin.
 * 
 * @package Omeka\Plugins\ShibbolethLogin
 */
class NakalaExportPlugin extends Omeka_Plugin_AbstractPlugin
{
    // Hooks
    protected $_hooks = array(
        'define_acl',
        'install',
        'uninstall',
        'config_form',
        'config'
    );

    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array(
        'admin_navigation_main', 
    );

        /**
     * Install the plugin.
     */
    public function hookInstall()
    {
        $defaults = array(
            'nakala-user-handle' => 'test',
            'nakala-user' => 'test',
            'nakala-user-password' => 'test'
        );
        set_option('nakala_export_settings', serialize($defaults));

        $db = $this->_db;

        $sql = "
        CREATE TABLE IF NOT EXISTS `{$db->prefix}nakala_export_exports` (
          `id` int unsigned NOT NULL auto_increment,
          `status` enum('queued','in progress','completed','error','deleted','killed') NOT NULL default 'queued',
          `message` text default NULL,
          `start_from` datetime NOT NULL,          
          `completed_at` datetime default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $db->query($sql);

        $sql = "
        CREATE TABLE IF NOT EXISTS `{$db->prefix}nakala_export_records` (
          `id` int unsigned NOT NULL auto_increment,
          `export_id` int unsigned NOT NULL,
          `item_id` int unsigned NOT NULL,
          `handle` int unsigned default NULL,
          `status` enum('in progress','error','ok') NOT NULL default 'in progress',
          `message` text default NULL,
          `start_from` datetime NOT NULL,          
          `completed_at` datetime default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $db->query($sql);

        $sql = "
        CREATE TABLE IF NOT EXISTS `{$db->prefix}nakala_export_collections` (
          `id` int unsigned NOT NULL auto_increment,
          `export_id` int unsigned NOT NULL,
          `collection_id` int unsigned NOT NULL,
          `handle` int unsigned default NULL,
          `status` enum('in progress','error','ok') NOT NULL default 'in progress',
          `message` text default NULL,
          `start_from` datetime NOT NULL,          
          `completed_at` datetime default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $db->query($sql);        
    }

    public function hookConfigForm() 
    {
        $settings = unserialize(get_option('nakala_export_settings'));
                
        $options['nakala-user-handle']    = (string) $settings['nakala-user-handle'];
        $options['nakala-user']           = (string) $settings['nakala-user'];
        $options['nakala-user-password']  = (string) $settings['nakala-user-password'];

        include 'forms/config-form.php';
    }

    public function hookConfig()
    {
        $settings = unserialize(get_option('nakala_export_settings'));
        
        $settings['nakala-user-handle']   = (string) $_POST['nakala-user-handle'];
        $settings['nakala-user']          = (string) $_POST['nakala-user'];
        $settings['nakala-user-password'] = (string) $_POST['nakala-user-password'];
        
        set_option('nakala_export_settings', serialize($settings));

        $cmd = "echo -n ".(string) $settings['nakala-user-password']." | sha1sum | awk '{printf $1}' > ".BASE_DIR."/nakala-console/password_file.sha";
        exec($cmd." 2>&1", $output);
                
    }


    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {        
        // Drop the table.
        $db = $this->_db;
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}nakala_export_exports`";
        $db->query($sql);

        $sql = "DROP TABLE IF EXISTS `{$db->prefix}nakala_export_records`";
        $db->query($sql);
    }

    

    /**
     * Define the ACL.
     * 
     * @param array $args
     */
    public function hookDefineAcl($args)
    {
        $acl = $args['acl']; // get the Zend_Acl
        $acl->addResource('NakalaExport_Index');
        //$acl->allow(null, 'NakalaExport_Index', array('index', 'export'));
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
            'label'     => __('NAKALA Export'),
            'uri'       => url('nakala-export'),
            'resource'  => 'NakalaExport_Index',
            'privilege' => 'index',
            'class'     => 'nav-oai-pmh-harvester'
        );       
        return $nav;
    }

    public function filterResponseContexts($contexts)
    {

        $contexts['rssm'] = array(
            'suffix'  => 'rssm',
            'headers' => array('Content-Type' => 'text/xml'),
        );

        return $contexts;
    }

    public function filterActionContexts($contexts, $controller)
    {
        if ($controller['controller'] instanceof ItemsController) {
            $contexts['show'][] = 'rssm';
            $contexts['browse'][] = 'rssm';
        }
        return $contexts;
    }

    public function hookAdminItemsShow($args) 
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        Zend_Debug::dump($request);
    }


}
