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


class NakalaExport_IndexController extends Omeka_Controller_AbstractActionController
{    
    //private $_options;

    private $_auth;

    public function init()
    {
        // If the user is already connected, redirect to homepage (do not access to SB login page)
        //if (current_user()) $this->_helper->redirector->gotoUrl('/');

        // Set the model class so this controller can perform some functions, 
        //$this->_helper->db->setDefaultModelName('User');

        //$this->_auth = $this->getInvokeArg('bootstrap')->getResource('Auth');
   
    }

   
    public function indexAction()
    {

        //$pid = system('ls -lR / > /dev/null 2>&1 & echo $!');
        //echo "exists : ".posix_getpgid($pid);

        $this->_helper->db->setDefaultModelName('Item');

        // Respect only GET parameters when browsing.
        $this->getRequest()->setParamSources(array('_GET'));
        
        // Inflect the record type from the model name.
        $pluralName = $this->view->pluralize($this->_helper->db->getDefaultModelName());

        // Apply controller-provided default sort parameters
        if (!$this->_getParam('sort_field')) {
            $defaultSort = apply_filters("{$pluralName}_browse_default_sort",
                $this->_getBrowseDefaultSort(),
                array('params' => $this->getAllParams())
            );
            if (is_array($defaultSort) && isset($defaultSort[0])) {
                $this->setParam('sort_field', $defaultSort[0]);

                if (isset($defaultSort[1])) {
                    $this->setParam('sort_dir', $defaultSort[1]);
                }
            }
        }
        
        $params = $this->getAllParams();
        $recordsPerPage = $this->_getBrowseRecordsPerPage($pluralName);
        $currentPage = $this->getParam('page', 1);
        
        $export = new NakalaExport_Record;
        $records = $this->_helper->db
                        ->getTable('NakalaExport_Record')
                        ->getItemsToExport();
        $params['range'] = implode($records, ',');
        
        // Get the records filtered to Omeka_Db_Table::applySearchFilters().
        $records = $this->_helper->db->findBy($params, $recordsPerPage, $currentPage);
        //echo $this->_helper->db->getSelectForFindBy();


        $totalRecords = $this->_helper->db->count($params);
        
        // Add pagination data to the registry. Used by pagination_links().
        if ($recordsPerPage) {
            Zend_Registry::set('pagination', array(
                'page' => $currentPage, 
                'per_page' => $recordsPerPage, 
                'total_results' => $totalRecords, 
            ));
        }
        
        $this->view->assign(array($pluralName => $records, 'total_results' => $totalRecords));
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

