<?php
/**
 * Shibboleth Login
 * 
 * @copyright Copyright 2015-2020 Limonade & Co (Paris)
 * @author Franck Dupont <kyfr59@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

// Define the username prefix in database (sb_username)
//define('SHIBBOLETH_USERS_PREFIX', 'sb_');

// Include the plugin auth adapter
//require_once dirname(__FILE__) . '/models/ShibbolethLoginUserTable.php';

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
        'install'
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
        $db = $this->_db;

        $sql = "
        CREATE TABLE IF NOT EXISTS `{$db->prefix}nakala_exports` (
          `id` int unsigned NOT NULL auto_increment,
          `status` enum('queued','in progress','completed','error','deleted','killed') NOT NULL default 'queued',
          `status_messages` text,
          `initiated` datetime default NULL,
          `completed` datetime default NULL,
          `start_from` datetime default NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $db->query($sql);

        $sql = "
        CREATE TABLE IF NOT EXISTS `{$db->prefix}nakala_exports_records` (
          `id` int unsigned NOT NULL auto_increment,
          `export_id` int unsigned NOT NULL,
          `item_id` int unsigned default NULL,
          `datestamp` tinytext NOT NULL,
          PRIMARY KEY  (`id`),
          UNIQUE KEY `item_id_idx` (item_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
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
        $acl->allow(null, 'NakalaExport_Index', array('index', 'export'));
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
            'label'     => __('Nakala Export'),
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
        // $request = Zend_Controller_Front::getInstance()->getRequest();
        
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
        //ob_end_clean();
        //header ("Content-Type:text/xml");
        //$url = "http://trippi.fr/oai-pmh-repository/request?verb=GetRecord&metadataPrefix=mods&identifier=oai:default.must.change:103";
        //$curl = curl_init();
        //curl_setopt($curl, CURLOPT_URL, $url);
        //curl_exec($curl);
    }

}
