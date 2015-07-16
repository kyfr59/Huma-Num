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


class ExportToNakala_ExportController extends Omeka_Controller_AbstractActionController
{    
    //private $_options;

    private $_auth;

    public function init()
    {
          
    }
    

    public function indexAction()
    {
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
            $cmd = "java -jar ".$path."nakala-console.jar -email kyfr59@gmail.com -inputFolder ".$path."input -outputFolder ".$path."output -errorFolder ".$path."error -passwordFile ".$path."password_file.sha";
            exec($cmd." 2>&1", $output, $return_var);
            Zend_Debug::dump($output);
            Zend_Debug::dump($return_var);

        }
       

    }

    
}

