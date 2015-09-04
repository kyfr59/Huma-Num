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


class NakalaExport_ExportController extends Omeka_Controller_AbstractActionController
{    

    private $_consoleHelper;

    public function init()
    {
        $this->_helper->viewRenderer->setNoRender();

        $this->_consoleHelper = new NakalaConsole_Helper;

        if (!$this->getParam('items') && !$this->getParam('collections'))
            $this->_helper->redirector->gotoUrl('/nakala-export');
    }
    
    /** 
     * Exports the items
     */
    public function indexAction()
    {
        $facile = $this->getParam('facile') ? array_unique($this->getParam('facile')) : array();
        $items = array_unique($this->getParam('items'));

        // Récupération et vérification des items à exporter (ceux n'ayant pas de pièce jointe sont exclus)
        foreach($items as $key => $item_id)
        {
            $item  = get_record_by_id('Item', $item_id);
            if ($item->getProperty('has_files'))
                $exports[$item->id] = in_array($item_id, $facile);
        }

        if ($exports)
        {
            // Création de l'enregistrement dans la base de données (table nakala_export_exports)
            $export = new NakalaExport_Export;
            $export_id = $export->create();

            // Génération des archives ZIP sur le serveur
            foreach($exports as $key => $value)
            {
                $item = get_record_by_id('Item', $key);
                
                // Récupération d'informations supplémentaires en vue de l'export (infos sur la collection par exemple)
                if ($collection = $item->getCollection()) {
                    $identifier = metadata($collection, array("Dublin Core", "Identifier"));
                    if ($handle = getHandleFormCollectionUrl($identifier))
                        $this->view->nakala_collection = $handle;
                }

                $elements = all_element_texts($item, array("return_type" => "array", "show_empty_elements" => true));
                $this->view->elements = $elements['Dublin Core'];
                echo $xml = $this->view->render('export/items.php');

                // Création de l'enregistrement dans la base de données (table nakala_export_records)
                $record = new NakalaExport_Record;
                $record->create($item, $export_id);

                // Création effective de l'archive ZIP sur le serveur
                try {
                    $this->_consoleHelper->generateZip($xml, $item);
                } catch (Exception $e) {
                    $error = $e->getMessage();
                    $record->stopRecord($error);
                }
                $recordsObjects[] = $record;
            }

            // Envoi des archives vers Nakala
            if (!isset($error)) {

                $results = $this->_consoleHelper->sendToNakala();

                // Mise à jour de la base de données suite a la réponse de Nakala
                foreach($recordsObjects as $record)
                {
                    $item_id = $record->item_id;
                    
                    if (isset($results[$item_id])) { // Nakala a renvoyé une réponse
                        
                        $status = $results[$item_id]['status'];
                        $message = $status == NakalaConsole_Helper::RESPONSE_ERROR ? $results[$key]['message'] : '';
                        $record->update($status, $message);
                        $record->deleteZip();

                    } else { // Nakala n'a renvoyé aucune réponse

                        $record->update(NakalaConsole_Helper::RESPONSE_ERROR, 'Pas de réponse de Nakala suite à l\'export');
                        $record->deleteZip();
                    }

                }   
            }    

            // Fin de l'export
            $export->close();
        }

        $this->_helper->redirector->gotoUrl('/nakala-export');
    }


    /** 
     * Exports the collections
     */
    public function collectionsAction()
    {
        
        $collections = array_unique($this->getParam('collections'));

        if (count($collections))
        {
            // Création de l'enregistrement dans la base de données (table nakala_export_exports)
            $export = new NakalaExport_Export;
            $export_id = $export->create('collection');

            // Génération des archives ZIP sur le serveur
            foreach($collections as $collection_id)
            {
                $collection = get_record_by_id('Collection', $collection_id);   
                
                $elements = all_element_texts($collection, array("return_type" => "array", "show_empty_elements" => true));
                echo $this->getParam('nakala_collection_'.$collection_id);
                echo $this->view->nakala_collection = getHandleFormCollectionUrl($this->getParam('nakala_collection_'.$collection_id));
                $this->view->elements = $elements['Dublin Core'];
                $xml = $this->view->render('export/collections.php');
                
                // Création de l'enregistrement dans la base de données (table nakala_export_records)
                $collection_record = new NakalaExport_Collection;
                $collection_record->create($collection, $export_id);

                // Création effective de l'archive ZIP sur le serveur
                try {
                    $this->_consoleHelper->generateZipCollection($xml, $collection);
                } catch (Exception $e) {
                    $error = $e->getMessage();
                    $collection_record->stopRecord($error);
                }
                $recordsObjects[] = $collection_record;
                
                
            }

            // Envoi des archives vers Nakala
            if (!isset($error)) {

                $results = $this->_consoleHelper->sendToNakala('collection');

                // Mise à jour de la base de données suite a la réponse de Nakala
                foreach($recordsObjects as $record)
                {
                   
                    $collection_id = $record->collection_id;

                    if (isset($results[$collection_id])) { // Nakala a renvoyé une réponse
                        
                        // Mise à jour de la collection dans la base de données (ajout de l'identifier)
                        $status = $results[$collection_id]['status'];
                        $message = $status == NakalaConsole_Helper::RESPONSE_ERROR ? $results[$key]['message'] : '';
                        $record->update($status, $message);
                        $logFile = BATCH_OUTPUT_PATH . $collection_id . '.xml';
                        if (file_exists($logFile)) {
                            $content = new SimpleXMLElement(file_get_contents($logFile));
                            $identifier = NAKALA_COLLECTION_PREFIX . (string)$content->identifier;
                            
                            $elementTexts['Dublin Core']['Identifier'][] = array('text' => (string) $identifier, 'html' => false);

                            // Update the collection
                            update_collection(
                                $collection_id, 
                                array('overwriteElementTexts' => true), 
                                $elementTexts 
                            );
                        }

                        $record->deleteZip();

                    } else { // Nakala n'a renvoyé aucune réponse

                        $record->update(NakalaConsole_Helper::RESPONSE_ERROR, 'Pas de réponse de Nakala suite à l\'export');
                        $record->deleteZip();
                    }

                }   
                
            }    

            // Fin de l'export
            $export->close();
           
        }
        
        $this->_helper->redirector->gotoUrl('/nakala-export/collections');
    }


   
    
}

