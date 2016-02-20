<?php
/**
* @copyright Copyright 2015-2020 Limonade & Co (Paris)
* @author Franck Dupont <kyfr59@gmail.com>
* @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
* @package NakalaImport
* @subpackage Controllers
*/

/**
 * Collections controller
 *
 * @subpackage Controllers
 */
class NakalaImport_CollectionsController extends Omeka_Controller_AbstractActionController
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
        
        // Disable the view rendering
        //$this->_helper->viewRenderer->setNoRender();

        $collections = $this->sparql->retrieveCollections();

        $this->view->collections    = $collections;

/*
        $updated = $created = 0;

        foreach ($imports as $key => $import) {

            $handle = getHandleFormNakalaUrl((string)$import->dataUrl);
            $elementTexts['Dublin Core']['Identifier'][]['text'] = $handle;
            if (itemExists($elementTexts)) {
                $imports[$key]->importType = 'mise à jour';
                $updated++;
            }
            else {
                $imports[$key]->importType = 'création';
                $created++;
            }
        }

        if ($this->view->options['ignore-updates']) {
            $onlyNewImports = array();
            foreach ($imports as $key => $import) {
                if ($import->importType == 'création')
                    $onlyNewImports[] = $import;
            }
            $imports = $onlyNewImports;
        }

        $this->view->lastImport = $lastImportDate;
        $this->view->updated    = $updated;
        $this->view->created    = $created;
        $this->view->imports    = $imports;
*/
    }


}
