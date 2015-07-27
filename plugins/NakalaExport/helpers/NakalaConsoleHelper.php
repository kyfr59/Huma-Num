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
    // Constants about responses of Nakala
    const PACKET_CREATED    = ':: packet created:';
    const PACKET_WITH_ERROR = ':: packet [';

    // Global constants
    const RESPONSE_OK    = 'ok';
    const RESPONSE_ERROR = 'error';    

    private $_archive_path; // The path on the archive on the server (when created :)
    private $_archive_id; // The ID of the item containing in the archive

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
                throw new Exception('Erreur lors de la génération de l\'archive ZIP : le fichier média n\'existe pas sur le serveur.');
            
            // Creating archive on server
            header('Content-Type: text/html; charset=iso-8859-1');
            $zip = new ZipArchive();  
            $archive_path = BATCH_INPUT_PATH . (int)$item->id . '.zip';
            
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

            // Checking existance of the file on server
            if (!file_exists($archive_path))
                throw new Exception('Erreur lors de la génération de l\'archive ZIP : la création de l\archive sur le serveur à échouée.');
            
            $this->_archive_path = $archive_path;
            $this->_archive_id = $item->id;
            
        }
    }

    /**
     * Delete a ZIP archive from the server
     * 
     * @param integer $item_id The Item ID
     * @return void
     */
    public static function deleteZip($item_id)
    {
         $archive_path = BATCH_INPUT_PATH . $item_id . '.zip';
         if (is_file($archive_path)) {
            $cmd = "rm -f ".$archive_path;
            exec("$cmd");
        }
    }


    /**
     * Send an archive to Nakala
     * 
     * @param TODO
     * @param TODO
     * @return TODO
     */
    public function sendToNakala()
    {
        // Retrieving all the ZIP files of the input directory
        $cmd = 'ls '.BATCH_INPUT_PATH .'*.zip';
        exec($cmd." 2>&1", $output);

        chdir(BATCH_PATH);
        $cmd = "java -jar ".BATCH_PATH."nakala-console.jar -email kyfr59@gmail.com -inputFolder ".BATCH_INPUT_PATH." -outputFolder ".BATCH_OUTPUT_PATH." -errorFolder ".BATCH_ERRORS_PATH." -passwordFile ".BATCH_PATH."password_file.sha";
        exec($cmd." 2>&1", $output);

        return $this->_getResults($output);
    }    

    private function _getResults($response)
    {
        $results = array();

        foreach ($response as $key => $value)
        {
            if (0 === strpos($value, self::PACKET_CREATED)) {
                $id = trim(ltrim($value, self::PACKET_CREATED));
                $results[$id]['status'] = self::RESPONSE_OK;
            }
            elseif (0 === strpos($value, self::PACKET_WITH_ERROR)) {
                $packetWithError = trim(ltrim($value, self::PACKET_WITH_ERROR));
                $pos = strpos($packetWithError, ']');
                $id = trim(substr($packetWithError, 0, $pos));
                $results[$id]['status'] = self::RESPONSE_ERROR;
                $results[$id]['message'] = $value;
            }
        }

        return $results;
        
    }


    /**
     * Returns the content of an output file for a given item ID
     * 
     * @param integer $item_id The ID of the item (the file has the format /185.xml)
     * @return string|bool The content of the file output, otherwise false
     */    
    public static function readOutputFile($item_id) 
    {
        if (is_file($outputFile = BATCH_OUTPUT_PATH . $item_id. '.xml')) {

            return file_get_contents($outputFile);
        }
        return false;
    }

}