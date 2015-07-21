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


class NakalaExport_ExportsController extends Omeka_Controller_AbstractActionController
{    

    public function init()
    {
        // $this->_helper->viewRenderer->setNoRender();
    }
   

   
    public function indexAction()
    {
        
        $exports = $this->_helper->_db
                   ->getTable('NakalaExport_Export')
                   ->findAll();
        
        $this->view->exports = $exports;
    }


}

