<?php
/**
 * Shibboleth Login
 *
 * @copyright Copyright 2008-2012 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Shibboleth Login error controller class.
 *
 * @package Shibboleth Login
 */


class ShibbolethLogin_ErrorController extends Omeka_Controller_AbstractActionController
{   
    // An array containing all error messages 
    private $_errors;

    public function init()    
    {
        $this->_errors['not_enouth_params'] = __("Not enouth params to complete the request");
        $this->_errors['other_error']       = __("An unrecognized error has occurred");
    }


    public function indexAction()
    {
        if ($this->_errors[$this->getParam('error')]) {
            $this->view->errorMessage = $this->_errors[$errorCode];
        } else {
            $this->view->errorMessage = $this->_errors['other_error'];
        }

    }

}


