<?php
/**
 * Shibboleth Login
 *
 * @copyright Copyright 2008-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Shibboleth Login index controller class.
 *
 * @package Shibboleth Login
 */


class ShibbolethLogin_IndexController extends Omeka_Controller_AbstractActionController
{    
    //private $_options;

    private $_auth;

    public function init()
    {
        // If the user is already connected, redirect to homepage (do not access to SB login page)
        if (current_user()) $this->_helper->redirector->gotoUrl('/');

        // Set the model class so this controller can perform some functions, 
        $this->_helper->db->setDefaultModelName('User');

        $this->_auth = $this->getInvokeArg('bootstrap')->getResource('Auth');
   
    }
    
    public function indexAction()
    {

        $user = new User();
        
        $form = $this->_getShibbolethUserForm($user);
        $this->view->form = $form;
        $this->view->user = $user;
        
        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($_POST)) {
            $this->_helper->flashMessenger(__('There was an invalid entry on the form. Please try again.'), 'error');
            return;
        }
        
        $user->setPostData($_POST);

        // Generate random password
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
        $password = substr( str_shuffle( $chars ), 0, 10);
        $user->setPassword($password);

        if ($user->save(false)) {
            
            // Login the user & redirect to homepage
            $authAdapter = new Omeka_Auth_Adapter_UserTable($this->_helper->db->getDb());
            $authAdapter->setIdentity($user->username)->setCredential($password);
            $authResult = $this->_auth->authenticate($authAdapter);
            if (!$authResult->isValid()) {
                if ($log = $this->_getLog()) {
                    $ip = $this->getRequest()->getClientIp();
                    $log->info(__("Failed login attempt from %s", $ip));
                }
                $this->_helper->flashMessenger($this->getLoginErrorMessages($authResult), 'error');
                return;
            }
            $this->_helper->redirector->gotoUrl('/');
            return;

        } else {
            $this->_helper->flashMessenger($user->getErrors());
        }
        
    }

    protected function _getShibbolethUserForm(User $user, $ua = null)
    {

        $form = new Omeka_Form_User(array(
            'hasRoleElement'    => true,
            'hasActiveElement'  => false,
            'user'              => $user,
            'usersActivations'  => $ua
        ));

        $form->removeElement('user_csrf');

        $displayName = $_SERVER['displayName'];
        $givenName = $_SERVER['givenName'];
        $mail = $_SERVER['mail'];

        if (!$displayName || !$givenName || !$mail) {
            $this->_helper->redirector->gotoUrl('/shibboleth-login/error?error=not_enouth_params');
        }

        // Retrive values of Shibboleth session
        $form->name->setValue($displayName);
        $form->username->setValue(SHIBBOLETH_USERS_PREFIX . $givenName);
        $form->email->setValue($mail);
/*
        $form->name->setValue("test");
        $form->username->setValue("test");
        $form->email->setValue("test@red.de");
*/
        
        // Disable field modification
        $form->name->setAttrib('readonly', 'readonly');
        $form->username->setAttrib('readonly', 'readonly');
        $form->email->setAttrib('readonly', 'readonly');

        // Change fields descritions
        $form->name->setDescription(__('The username from your Shibboleth session (cannot be changed)'));
        $form->username->setDescription(__('The display name from your Shibboleth session (cannot be changed)'));
        $form->email->setDescription(__('The email from your Shibboleth session (cannot be changed)'));

        // Add 'role' element
        $form->addElement('hidden', 'role', array(
            'size' => '30',
            'required' => true,
            'value' => 'contributor',
            'validators' => array(
                array('validator' => 'NotEmpty', 'breakChainOnFailure' => true, 'options' => array(
                    'messages' => array(
                        Zend_Validate_NotEmpty::IS_EMPTY => __('Real Name is required.')
                    )
                ))
            )
        ));

        // Add 'active' element
        $form->addElement('hidden', 'active', array(
            'value' => true,
        ));
       

        $form->removeDecorator('Form');
        fire_plugin_hook('shibboleth_login_users_form', array('form' => $form, 'user' => $user));
        return $form;
    }


}

