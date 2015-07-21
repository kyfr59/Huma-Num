<?php
/**
 * @package NakalaExport
 * @subpackage Helpers
 * @copyright Copyright (c) 2009-2011 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Nakala console mangement class
 *
 * @package NakalaExport
 * @subpackage Helpers
 */
class NakalaConsole_Helper
{
    const ZIP_CREATED = "zip-created";

    public function __construct()
    {
        
    }

    /**
     * Generate the ZIP archive for an item
     * 
     * @param String $xml The XML content for the item
     * @param Item $item The Item object
     * @return void
     */
    public function generateZip($xml, $item)
    {
        if (get_class($firstFile = $item->getFiles()[0]) == 'File' && $xml) {

            // Getting first files of the item
            $file = FILES_DIR . '/' . $firstFile->getStoragePath('original');

            // Checking existance of the file on server
            if (!file_exists($file))
                throw new Exception('Erreur lors de la génératio de l\'archive ZIP : le fichier n\'existe pas sur le serveur.');
            
            // Creating archive on server
            header('Content-Type: text/html; charset=iso-8859-1');
            $zip = new ZipArchive();  
            echo $archive_path = BATCH_INPUT_PATH . (int)$item->id . '.zip';
            
            if (file_exists($archive_path))
                unlink($archive_path);

            $zip->open($archive_path, ZipArchive::CREATE);  

            $zip->addFromString((int)$item->id.'.xml', $xml);

            // Removing accents of orignal filename
            $accents = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                        'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                        'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                        'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
            $original_filename = strtr($firstFile->original_filename, $accents);
            
            $zip->addFile($file, $original_filename);  
              
            $zip->close();
            
        }
    }

}