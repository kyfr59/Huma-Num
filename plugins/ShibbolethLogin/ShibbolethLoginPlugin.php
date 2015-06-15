<?php
/**
 * Shibboleth Login
 * 
 * @copyright Copyright 2015-2020 Limonade & Co (Paris)
 * @author Franck Dupont <kyfr59@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

define('SHIBBOLETH_USERS_PREFIX', 'sb_');

/**
 * The Shibboleth Login plugin.
 * 
 * @package Omeka\Plugins\ShibbolethLogin
 */
class ShibbolethLoginPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'define_acl',
        'install',
        'config',
        'config_form',
        'public_head',
        'initialize'
    );

    protected $_filters = array(
        'login_form',
        'login_adapter',
        
    );

    public function hookInitialize()
    {
        //header('location:http://free.fr');
    }

    public function filterLoginAdapter($authAdapter, $options) 
    {
        
        $settings = unserialize(get_option('shibboleth_login_settings'));

        $form = $options['login_form'];

        $username = $form->getValue('username');

        $user = new User();

        if (substr($username, 0, strlen(SHIBBOLETH_USERS_PREFIX)) == SHIBBOLETH_USERS_PREFIX) {
            header("location:".$settings['idp-url']);
        } else {
            return $authAdapter
                ->setIdentity($form->getValue('username'))
                ->setCredential($form->getValue('password'));
        }
    }

    public function filterLoginForm($html)
    {
        return $html;
    }

    public function hookInstall() 
    {
        $defaults = array(
            'idp-url' => 'https://idp.testshib.org/idp/Authn/UserPassword'
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
     * Hooks into public_head :
     *  - Add a css file for the plugin
     *  - Redirect to Shibboleth's login form is a SB session is active
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
                    
                    // Login the user
                    $user = new User();
                    $authAdapter = new Omeka_Auth_Adapter_UserTable(get_db());
                    $options = unserialize(get_option('shibboleth_login_settings'));
                    require_once dirname(__FILE__) . '/controllers/IndexController.php';
                    $authAdapter->setIdentity($options['username-prefix'] . $_SERVER['givenName'])->setCredential(ShibbolethLogin_IndexController::SHIBBOLETH_USERS_PASSWORD);
                    $auth = Zend_Registry::get('bootstrap')->getResource('Auth');
                    $authResult = $auth->authenticate($authAdapter);
                    return;

                } else { // The user hasn't an OMEKA account
                    
                    header("location: /shibboleth-login");
                    exit;
                    
                }

            } else { // We don't have all informations about the user

                Zend_Debug::dump("// Error : pas assez d'informations sur l'utilisateur");
                // Afficher un message d'erreur
                
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
