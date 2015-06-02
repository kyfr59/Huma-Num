<?php
/**
 * Shibboleth Login
 * 
 * @copyright Copyright 2015-2020 Limonade & Co (Paris)
 * @author Franck Dupont <kyfr59@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

//define('USERS_STATS_PLUGIN_DIR', PLUGIN_DIR . '/ShibbolethLogin');

//require_once USERS_STATS_PLUGIN_DIR . '/helpers/UsersStatsFunctions.php';


/**
 * The Shibboleth Login plugin.
 * 
 * @package Omeka\Plugins\ShibbolethLogin
 */
class ShibbolethLoginPlugin extends Omeka_Plugin_AbstractPlugin
{


 
// show plugin configuration page
function configure_this_config_form() {
    
}
 
// save plugin configurations in the database
function configure_this_config() {
    set_option('configure_this_configuration', trim($_POST['configure_this_configuration']));
}

    protected $_hooks = array(
        'define_acl',
        'install',
        'config',
        'config_form',
        'public_head'
    );

    protected $_filters = array(
        'login_form'
    );


    public function filterLoginForm($html)
    {
        return $html;
    }

    public function hookInstall() 
    {
        $defaults = array(
            'idp-url' => 'https://idp.testshib.org/idp/Authn/UserPassword',
            'username-prefix' => 'sb_'
        );
        set_option('shibboleth_login_settings', serialize($defaults));
    }

    public function hookConfigForm() 
    {
        $settings = unserialize(get_option('shibboleth_login_settings'));

        /*
        $audio = $settings['audio'];
        $audio['types'] = implode(',', $audio['types']);
        $audio['extensions'] = implode(',', $audio['extensions']);
        */
        
        $options['idp-url'] = (string) $settings['idp-url'];
        $options['username-prefix'] = (string) $settings['username-prefix'];
        /*
        $text = $settings['text'];
        $text['types'] = implode(',', $text['types']);
        $text['extensions'] = implode(',', $text['extensions']);
        */
        include 'forms/config-form.php';
    }


    public function hookConfig()
    {
        $settings = unserialize(get_option('shibboleth_login_settings'));
        

        $settings['idp-url'] = (string) $_POST['idp-url'];
        $settings['username-prefix'] = (string) $_POST['username-prefix'];
        
        set_option('shibboleth_login_settings', serialize($settings));
    }


    /**
     * Define the ACL.
     * 
     * @param Omeka_Acl
     */
    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];
        
        $indexResource = new Zend_Acl_Resource('ShibbolethLogin_Index');
        $acl->add($indexResource);

        $acl->allow(null, 'ShibbolethLogin_Index', 'index');
        $acl->allow(null, 'Users', 'change-role');
    }


    /**
     * Hooks into public_head to add a css file for the plugin
     *
     * @param array $args
     */
    public function hookPublicHead($args)
    {
      queue_css_file('shibboleth-login');
    }


    
    
}
