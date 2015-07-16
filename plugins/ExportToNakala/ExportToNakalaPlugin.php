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


/**
 * The Shibboleth Login plugin.
 * 
 * @package Omeka\Plugins\ShibbolethLogin
 */
class ExportToNakalaPlugin extends Omeka_Plugin_AbstractPlugin
{
    // Hooks
    protected $_hooks = array(
        'define_acl',
    );

    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array(
        'admin_navigation_main', 
    );

    /**
     * Define the ACL.
     * 
     * @param array $args
     */
    public function hookDefineAcl($args)
    {
        $acl = $args['acl']; // get the Zend_Acl
        $acl->addResource('ExportToNakala_Index');
        $acl->allow(null, 'ExportToNakala_Index', array('index', 'export'));
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
            'uri'       => url('export-to-nakala'),
            'resource'  => 'ExportToNakala_Index',
            'privilege' => 'index',
            'class'     => 'nav-oai-pmh-harvester'
        );       
        return $nav;
    }

}
