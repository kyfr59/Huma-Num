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
            $export = new NakalaExport_Export;
            $export_id = $export->create();

            foreach($exports as $key => $export)
            {
                $item  = get_record_by_id('Item', $key);

                foreach($dcElementNames as $elementName)
                {   
                    $dcElements = $item->getElementTexts('Dublin Core', Inflector::camelize($elementName));
                    foreach($dcElements as $elementText)
                        $elements[$elementName][] = $elementText->text;
                }

                $this->view->elements = $elements;

                // Génération du XML
                $xml = $this->view->render('export/index.php');

                $record = new NakalaExport_Record;
                $record->create($item, $export_id);

                // Création de l'archive ZIP
                try {
                    $this->_consoleHelper->generateZip($xml, $item);
                } catch (Exception $e) {
                    $error = $e->getMessage();
                    $record->stopExport($error);
                }

                echo $error;
                




                /*

                // Si l'item a un fichier joint
                if (get_class($file = $item->getFiles()[0]) == 'File') {

                    $media = FILES_DIR . '/' . $file->getStoragePath('original');

                    if (file_exists($media)) {

                        // Création de l'archive ZIP
                        $zip = new ZipArchive();  
                        echo $archive_path = BATCH_PATH . 'input/1.zip';
                        if (file_exists($archive_path))
                            unlink($archive_path);

                        $zip->open($archive_path, ZipArchive::CREATE);  
                        $zip->addFromString("1.xml", $xml);
                          
                        // Add the media file to archive
                        $zip->addFile($media, $file->original_filename);  
                          
                        $zip->close();
                    }

                    chdir(BATCH_PATH);
                    $cmd = "java -jar ".BATCH_PATH."nakala-console.jar -email kyfr59@gmail.com -inputFolder ".BATCH_PATH."input -outputFolder ".BATCH_PATH."output -errorFolder ".BATCH_PATH."error -passwordFile ".BATCH_PATH."password_file.sha";
                    // exec($cmd." 2>&1", $output, $return_var);
                    //Zend_Debug::dump($output);
                    //Zend_Debug::dump($return_var);

                }

                // $item  = get_record_by_id('Item', $item_id);
                // echo $key .' : '.$export .'<br>';
                */
            }

        }

        
        /*
        
        $itemId = $_POST['items'][0];
        $this->_helper->viewRenderer->setNoRender();

        $item = get_record_by_id('Item', $itemId);
        
        $title = metadata($item, array("Dublin Core","Title"));

        $dcElementNames = array( 'title', 'creator', 'type', 'date',
                                 'identifier', 'subject', 'coverage', 
                                 'rights', 'source' );

        foreach($dcElementNames as $elementName)
        {   
            $upperName = Inflector::camelize($elementName);
            $dcElements = $item->getElementTexts('Dublin Core',$upperName );

            foreach($dcElements as $elementText)
            {
                $elements[$elementName][] = $elementText->text;
            }
        }

        $this->view->elements = $elements;

        // Génération du XML
        $xml = $this->view->render('export/index.php');

        if (get_class($file = $item->getFiles()[0]) == 'File') {

            $media = FILES_DIR . '/' . $file->getStoragePath('original');

            $path = "/home/franck/sites/omeka-humanum/nakala-console/";

            if (file_exists($media)) {

                // Création de l'archive ZIP
                $zip = new ZipArchive();  
                echo $archive_path = $path . 'input/1.zip';
                if (file_exists($archive_path))
                    unlink($archive_path);

                $zip->open($archive_path, ZipArchive::CREATE);  
                $zip->addFromString("1.xml", $xml);
                  
                // Add the media file to archive
                $zip->addFile($media, $file->original_filename);  
                  
                $zip->close();
            }

            chdir($path);
            echo $cmd = "java -jar ".$path."nakala-console.jar -email kyfr59@gmail.com -inputFolder ".$path."input -outputFolder ".$path."output -errorFolder ".$path."error -passwordFile ".$path."password_file.sha";
            // exec($cmd." 2>&1", $output, $return_var);
            Zend_Debug::dump($output);
            Zend_Debug::dump($return_var);

        }
        */
       

    }

    

    
    
}

