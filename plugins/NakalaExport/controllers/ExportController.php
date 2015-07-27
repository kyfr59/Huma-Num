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
    }
    

    public function indexAction()
    {
        $facile = $this->getParam('facile') ? array_unique($this->getParam('facile')) : array();
        $items = array_unique($this->getParam('items'));

        // Récupération des éléments de l'item
        $dcElementNames = array( 'title', 'creator', 'type', 'date',
                 'identifier', 'subject', 'coverage', 
                 'rights', 'source' );

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

                // Génération du XML
                foreach($dcElementNames as $elementName)
                {   
                    $dcElements = $item->getElementTexts('Dublin Core', Inflector::camelize($elementName));
                    foreach($dcElements as $elementText)
                        $elements[$elementName][] = $elementText->text;
                }

                // Récupération d'informations supplémentaires en vue de l'export (infos sur la collection par exemple)
                $collection = $item->getCollection();
                $identifier = metadata($collection, array("Dublin Core", "Identifier"));
                if ($handle = getHandleFormCollectionUrl($identifier))
                    $this->view->nakala_collection = $handle;

                $this->view->elements = $elements;
                echo $xml = $this->view->render('export/index.php');

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

                    } else { // Nakala n'a renvoyé aucune réponse

                        $record->update(NakalaConsole_Helper::RESPONSE_ERROR, 'Pas de réponse de Nakala suite à l\'export');
                    }
                }   
            }    

            // Fin de l'export
            $export->close();
        }

        $this->_helper->redirector->gotoUrl('/nakala-export');
    }

   
    
}

