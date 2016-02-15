<?php
/**
* @copyright Copyright 2015-2020 Limonade & Co (Paris)
* @author Franck Dupont <kyfr59@gmail.com>
* @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
* @package NakalaImport
* @subpackage Controllers
*/

//require_once dirname(__FILE__) . '/../forms/Harvest.php';

/**
 * Index controller
 *
 * @package OaipmhHarvester
 * @subpackage Controllers
 */
class NakalaImport_IndexController extends Omeka_Controller_AbstractActionController
{
    /**
     * Initialization controller
     *  - Load the plugin configuration
     *  - Load the Sparql library
     *
     * @return void
     */
    public function init() 
    {
        // Load configuration options
        $options = unserialize(get_option('nakala_import_settings'));
        $this->view->options = $options;

        // Load Sparql library
        $this->sparql = new NakalaImportSparql;

    }
    
    /**
     * Prepare the index view.
     * 
     * @return void
     */
    public function indexAction()
    {
        
    }


    /**
     * Prepare the index view.
     * 
     * @return void
     */
    public function homeAction()
    {
        $lastImportDate = $this->_helper->db->getTable('NakalaImport')->getLastImportDateXsd();
        
        $imports = $this->sparql->retrieveUpdates($lastImportDate);

        foreach ($imports as $key => $import) {

            $handle = getHandleFormNakalaUrl((string)$import->dataUrl);
            $elementTexts['Dublin Core']['Identifier'][]['text'] = $handle;
            if (itemExists($elementTexts))
                $imports[$key]->importType = 'mise à jour';
            else 
                $imports[$key]->importType = 'création';
        }

        if ($this->view->options['ignore-updates']) {
            $onlyNewImports = array();
            foreach ($imports as $key => $import) {
                if ($import->importType == 'création')
                    $onlyNewImports[] = $import;
            }
            $imports = $onlyNewImports;
        }

        $this->view->imports = $imports;
    }


    /**
     * Test the Sparql server connectivity (call via ajax)
     *
     * @return void
     */
    public function testAction()
    {
        // Disable the view rendering
        $this->_helper->viewRenderer->setNoRender();

        // Retrieve the account handle
        $handle = $this->getParam('handle'); // Received from request (config-form.php)

        // Check the account connectivity
        $serverOk = $this->sparql->testServer($handle);

        // Returns the result as JSON - like : {"server_ok":true}
        $response = array("server_ok" => $serverOk);
        $this->getResponse()->setHeader('Content-Type', 'application/json');
        echo json_encode($response);
    }


    /**
     * Test the Sparql server connectivity (call via ajax)
     *
     * @return JSON (Ajax)
     */
    public function importAction()
    {
        $ignoreUpdates  = $this->getParam('ignore_updates');
        $dataUrls       = $this->getParam('dataUrl');


        // Removing updates from $dataUrls array
        if ($ignoreUpdates) {
            foreach($dataUrls as $key => $dataUrl) {
                $handle = getHandleFormNakalaUrl($dataUrl);
                $elementTexts['Dublin Core']['Identifier'][]['text'] = $handle;
                if (itemExists($elementTexts))
                    unset($dataUrls[$key]);
            }
            $dataUrls = array_values($dataUrls); // Reset keys
        }

        // Adding new import in database (table omeka_imports)
        $importItem = new NakalaImportItem;
        $importId = $importItem->startImport();

        // Version Single Page PHP
        /*
        foreach ($dataUrls as $dataUrl) {
            $infos = $this->sparql->getInformations($dataUrl);
            $importItem->import($infos, $importId);
        }
        $importItem->closeImport($importId);
        */

        // Version AJAX
        $this->view->dataUrls = json_encode($dataUrls);        
        $this->view->importId = json_encode($importId);        

    }


    /**
     * Retrieve informations about an Item
     *
     * @return JSON (Ajax)
     */
    public function importViaAjaxAction()
    {
        // Disable the view rendering
        $this->_helper->viewRenderer->setNoRender();

        // Retrieve params provided by view script (import.php)
        $last         = $this->getParam('last'); 
        $dataUrl      = $this->getParam('dataUrl'); 
        $importId     = $this->getParam('importId'); 
        $i            = $this->getParam('i'); 

        // Check if the item exists
        $handle = getHandleFormNakalaUrl($dataUrl);
        $elementTexts['Dublin Core']['Identifier'][]['text'] = $handle;
        $insertType = itemExists($elementTexts) ? "mise à jour" : "importée";

        // Retrieve infos about item (Sparql)
        $infos = $this->sparql->getInformations($dataUrl);

        // Add the item in database
        $importItem = new NakalaImportItem;
        $importItem->import($infos, $importId);

        // Informations return to display import avancement
        $title = cut_string(implode(' // ', $infos['dc_title']),80);

        // Prepare response params
        $response = array(  "last"          => $last, 
                            "i"             => $i,
                            "dataUrl"       => $dataUrl,
                            "count"         => count($infos),                
                            "title"         => $title,        
                            "insertType"    => $insertType,        
        );

        // Closing import in database (table omeka_imports)
        if ($last == "true") {
            $importItem = new NakalaImportItem;
            $importItem->closeImport($importId);
        }

        // Returns the result as JSON, like : {"last":false}
        $this->getResponse()->setHeader('Content-Type', 'application/json');
        echo json_encode($response);
    }

}
