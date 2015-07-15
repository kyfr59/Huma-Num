<?php
/**
 * Shibboleth Login
 * 
 * @copyright Copyright 2015-2020 Limonade & Co (Paris)
 * @author Franck Dupont <kyfr59@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

// Define the username prefix in database (sb_username)
define('SHIBBOLETH_USERS_PREFIX', 'sb_');

// Include the plugin auth adapter
require_once dirname(__FILE__) . '/models/ShibbolethLoginUserTable.php';


/**
 * The Shibboleth Login plugin.
 * 
 * @package Omeka\Plugins\ShibbolethLogin
 */
class ShibbolethLoginPlugin extends Omeka_Plugin_AbstractPlugin
{
    // Hooks
    protected $_hooks = array(
        'define_acl',
        'public_head',
        'config_form',
        'config',
        'install'
    );

    protected $_filters = array(
        'public_navigation_admin_bar'
    );

    public function filterPublicNavigationAdminBar($links) 
    {
        $user = current_user();
        if($user && self::isShibbolethSessionActive() && $user->role != 'Super')
        {
            unset($links);
            echo '<ul class="navigation shibboleth-username"><li>'.$user->name.'</li></ul>';
            $links = array(
                array(
                    'label' => __('Log Out'),
                    'uri' => url('/users/logout')
                )
            );
        }
        return $links;
        
    }

    public function hookInstall() 
    {
        $defaults = array(
            'admin-email' => 'shibboleth-admin@your-company.com'
        );
        set_option('shibboleth_login_settings', serialize($defaults));
    }

    public function hookConfigForm() 
    {
        $settings = unserialize(get_option('shibboleth_login_settings'));
                
        $options['admin-email'] = (string) $settings['admin-email'];
        
        include 'forms/config-form.php';
    }

    public function hookConfig()
    {
        $settings = unserialize(get_option('shibboleth_login_settings'));
        
        $settings['admin-email'] = (string) $_POST['admin-email'];
        
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
        $acl->deny(array('contributor', 'admin'), 'Users', 'edit');
    }


    /**
     * Hooks into public_head :
     *  - Add a css file for the plugin (to customize the SB login form)
     *  - Redirect to Shibboleth's login form if a SB session is active
     *  - Login the user if a SB session is active & an Omeka account exists
     *
     * @param array $args
     */
    public function hookPublicHead($view)
    {
        queue_css_file('shibboleth-login');

        // If a SB session is active & the user isn't connected
        if (self::isShibbolethSessionActive() && ltrim($_SERVER['REQUEST_URI'],'/') != 'shibboleth-login' && !current_user() ) 
        {
            // If we've all informations about the user
            if ( $userInfos = self::checkShibbolethUserInfos()) {

                // If the user already has an OMEKA account
                if (self::shibbolethUserHasOmekaAccount($userInfos['mail'])) {
                    
                    // Login the user (without password)
                    $user = new User();
                    $authAdapter = new ShibbolethLogin_Auth_Adapter_UserTable(get_db());
                    $authAdapter->setIdentity(SHIBBOLETH_USERS_PREFIX . $_SERVER['givenName'])->setCredential("no-password");
                    $auth = Zend_Registry::get('bootstrap')->getResource('Auth');
                    $authResult = $auth->authenticate($authAdapter);
                    header("Refresh:0"); // Avoid cache problems
                    return;


                } else { // The user hasn't an OMEKA account
                    
                    header("location: /shibboleth-login");
                    exit;
                    
                }

            } else { // We don't have all informations about the user

                header("location: /shibboleth-login/error/error=not_enouth_params");
                exit;
                
            }
        }
        
    }

    /**
     * Returns TRUE if a Shibboleth session is active for the app,
     *
     * @return bool 'true' if a SB session is active otherwhise 'false'.
     */
    private static function isShibbolethSessionActive() 
    {
        $session_headers = array('Shib-Session-ID', 'Shib_Session_ID', 'HTTP_SHIB_IDENTITY_PROVIDER');
        foreach ($session_headers as $header) {
                if ( array_key_exists($header, $_SERVER) && !empty($_SERVER[$header]) ) {
                        return true;
                        break;
                }
        }
        return false;
    }


    /**
     * Returns TRUE if the current Shibboleth user has an Omeka account (based on the e-mail address)
     *
     * @return User|false Returns the user object if the user has an account, otherwhise 'false'.
     */
    private static function shibbolethUserHasOmekaAccount($email) 
    {
        return get_db()->getTable('User')->findByEmail($email);
    }

    /**
     * Returns the information about the user (provided by the Shibboleth session)
     * If an info is missing, returns false
     *
     * @return array|false Returns an array containing the user info, otherwhise 'false'.
     */
    private static function checkShibbolethUserInfos()
    {
        $infos = array('mail', 'displayName', 'givenName');
        $userInfos = array();

        foreach($infos as $info) 
        {
            if(isset($_SERVER[$info])) {
                $userInfos[$info] = $_SERVER[$info];
            } else {
                return false;
            }
        }
        return $userInfos;
    }

    





/*
Vérifier si le nom d'utilisateur existe déjà dans la base
Si l'utilisateur n'est pas déjà connecté !
 

*/



    
    
}
