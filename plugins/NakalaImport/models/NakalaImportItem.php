<?php
/**
* @copyright Copyright 2015-2020 Limonade & Co (Paris)
* @author Franck Dupont <kyfr59@gmail.com>
* @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
* @package NakalaImport
* @subpackage Models
*/

/**
 * Record that keeps track of contributions; links items to contributors.
 */

class NakalaImportItem extends Omeka_Record_AbstractRecord
{

    const AUDIO_SAMPLE_FILE = 'sample.mp3';
    const VIDEO_SAMPLE_FILE = 'sample.mp4';

    /**
     * Mapping array between OMEKA (keys) and NAKALA (values)
     */
    static private $_mapping = array(
        "alternative"              => "Alternative Title",            
        "abstract"                 => "Abstract",                     
        "accessRights"             => "Access Rights",                
        "accrualMethod"            => "Accrual Method",               
        "accrualPeriodicity"       => "Accrual Periodicity",          
        "accrualPolicy"            => "Accrual Policy",               
        "audience"                 => "Audience",                     
        "educationLevel"           => "Audience Education Level",     
        "bibliographicCitation"    => "Bibliographic Citation",       
        "conformsTo"               => "Conforms To",                  
        "contributor"              => "Contributor",                  
        "coverage"                 => "Coverage",                     
        "creator"                  => "Creator",                      
        "date"                     => "Date",                         
        "dateAccepted"             => "Date Accepted",                
        "available"                => "Date Available",               
        "dateCopyrighted"          => "Date Copyrighted",             
        "created"                  => "Date Created",                 
        "issued"                   => "Date Issued",                  
        "modified"                 => "Date Modified",                
        "dateSubmitted"            => "Date Submitted",               
        "valid"                    => "Date Valid",                   
        "description"              => "Description",                  
        "extent"                   => "Extent",                       
        "format"                   => "Format",                       
        "hasFormat"                => "Has Format",                   
        "hasPart"                  => "Has Part",                     
        "hasVersion"               => "Has Version",                  
        "identifier"               => "Identifier",                   
        "instructionalMethod"      => "Instructional Method",         
        "isFormatOf"               => "Is Format Of",                 
        "isPartOf"                 => "Is Part Of",                   
        "isReferencedBy"           => "Is Referenced By",             
        "isReplacedBy"             => "Is Replaced By",               
        "isRequiredBy"             => "Is Required By",               
        "isVersionOf"              => "Is Version Of",                
        "language"                 => "Language",                     
        "license"                  => "License",                      
        "mediator"                 => "Mediator",                     
        "medium"                   => "Medium",                       
        "provenance"               => "Provenance",                   
        "publisher"                => "Publisher",                    
        "references"               => "References",                   
        "relation"                 => "Relation",                     
        "replaces"                 => "Replaces",                     
        "requires"                 => "Requires",                     
        "rights"                   => "Rights",                       
        "rightsHolder"             => "Rights Holder",                
        "source"                   => "Source",                       
        "spatial"                  => "Spatial Coverage",             
        "subject"                  => "Subject",                      
        "tableOfContents"          => "Table Of Contents",            
        "temporal"                 => "Temporal Coverage",            
        "title"                    => "Title",                        
        "type"                     => "Type");                         


    /**
     * Mapping array between Values (keys) and RDF graphes (values)
     */
    static private $_values = array(
        "collectionUrl"            => "http://www.openarchives.org/ore/terms/isAggregatedBy",
        "filename"                 => "http://www.w3.org/2004/02/skos/core#altLabel",
    );
    
    /**
     * Array of audio file extension to exclude from media download
     */
    static private $_audioExtensionNotDownloaded = array(
        'ogx', 
        'aac',
        'aif',
        'aiff',
        'aifc',
        'mid',
        'midi',
        'kar',
        'rmi',
        'mpga',
        'mp2',
        'mp2a',
        'mp3',
        'm2a',
        'm3a',
        'mp4a',
        'oga',
        'ogg',
        'spx',
        'wav',
        'wma'
    );

    /**
     * Array of videos file extension to exclude from media download
     */
    static private $_videoExtensionNotDownloaded = array(
        'mp4',
        'mp4v',
        'mpg4',
        'mpeg',
        'mpg',
        'mpe',
        'm1v',
        'm2v',
        'ogv',
        'qt',
        'mov',
        'avi'
    );

    
    /**
     * Import an item in OMEKA
     * 
     * @see insert_item()
     * @see insert_files_for_item()
     * @param array $infos An array containing all informations about the item
     * @param integer $importId The ID of the target import (in nakala_imports)
     * @return true
     */
    public function import($infos, $importId, $setPublic = false) {

        $filename       = $infos['filename'];
        $extent         = $infos['dc_extent'];
        $format         = isset($infos['dc_format'])?$infos['dc_format']:null;
        $dataUrl        = $infos['dataUrl'];
        $collectionUrl  = $infos['collectionUrl'];

        // Managing collection
        if ($collection = collectionExists($collectionUrl)) {
            $collection_id = $collection->id;
        } else {
            $insertedCollection = $this->_insertCollection($collectionUrl);
            $collection_id = $insertedCollection->id;
        }


        $itemMetadata = array(
            'collection_id' => $collection_id, 
            'public'        => $setPublic, 
            'featured'      => 0,
        );

        // Creating element texts
        foreach ($infos as $nakalaKey => $values) {

            if (substr($nakalaKey, 0, 3) == 'dc_') {

                $nakalaKey = str_replace('dc_', '', $nakalaKey);
                $omekaKey = self::$_mapping[$nakalaKey];
                foreach ($values as $value) {
                    $elementTexts['Dublin Core'][$omekaKey][]         = array('text' => $value, 'html' => false);
                }
            }
        }

        // Updating or insering the item
        if ($item = itemExists($elementTexts)) {

            // Update the item
            $this->_updateItem($item, $itemMetadata, $elementTexts);

        } else {

            // Extracting extension from filename
            if ($filename) {
                $pathinfo = pathinfo($filename);
                if (isset($pathinfo['extension'])) 
                    $extension = strtolower($pathinfo['extension']);
            }

            // Managing file corresponding to the item
            if (isset($extension) && in_array($extension,self::$_audioExtensionNotDownloaded)) { // If it's an audio extension

                $fileMetadata['file'] = PLUGIN_DIRECTORY_TEMP . '/'. self::AUDIO_SAMPLE_FILE;
                $fileMetadata['delete_file_after_insert'] = false;               
                $fileMetadata['format']     = $format;
                $fileMetadata['extent']     = $extent;

            } elseif (isset($extension) && in_array($extension,self::$_videoExtensionNotDownloaded)) {  // If it's a video extension

                $fileMetadata['file'] = PLUGIN_DIRECTORY_TEMP . '/'. self::VIDEO_SAMPLE_FILE;
                $fileMetadata['delete_file_after_insert'] = false;               
                $fileMetadata['format']     = $format;
                $fileMetadata['extent']     = $extent;

            } else { // The file isn't an audio or video file, we download it (to generate the thumbmails)
                
                $cmd = "wget -O " . PLUGIN_DIRECTORY_TEMP . '/'. urlencode($filename) ." ". $dataUrl;            
                shell_exec($cmd);
                $fileMetadata['file'] = PLUGIN_DIRECTORY_TEMP . '/'. urlencode($filename);
                $fileMetadata['delete_file_after_insert'] = true;               
            }
            $fileMetadata['label'] = $filename;

            // Insert the item in database and create the thumbmail if necessary
            $this->_insertItem($itemMetadata, $elementTexts, $fileMetadata, $importId, $dataUrl);
        }
    }


    /**
     * Convenience method for inserting a collection
     * 
     * @see insert_collection()
     * @param string $collectionaUrl The NAKALA collections's URL
     * @return Collection The Collection object inserted
     */
    final protected function _insertCollection($collectionUrl) {

        $collectionMetadata = array(
            'collection_id' => null, 
            'public'        => 1, 
            'featured'      => 0,
        );

        // Get collection's informations from Sparql Server
        $sparql = new NakalaImportSparql;
        $collectionInformations = $sparql->getCollectionInformations($collectionUrl);
        $collectionElementTexts['Dublin Core']['Title'][] = array('text' => $collectionInformations['collectionName'], 'html' => false);
        $collectionElementTexts['Dublin Core']['Identifier'][] = array('text' => getHandleFormNakalaCollectionUrl($collectionUrl), 'html' => false);
        $collection = insert_collection($collectionMetadata, $collectionElementTexts);
        return $collection;
    }


     /**
     * Convenience method for inserting an item and its files.
     * 
     * 
     * @see insert_item()
     * @see insert_files_for_item()
     * @param mixed $metadata Item metadata
     * @param mixed $elementTexts The item's element texts
     * @param mixed $fileMetadata The item's file metadata
     * @param integer $importId The ID of the target import (in nakala_imports)
     * @param string $dataUrl The NAKALA data URL
     * @return true
     */
    final protected function _insertItem(
        $metadata = array(), 
        $elementTexts = array(), 
        $fileMetadata = array(),
        $importId,
        $dataUrl
    ) {
        
        // Insert the item in database
        $item = insert_item($metadata, $elementTexts);
            
        // If there are files, insert one file at a time so the file objects can 
        // be released individually.
        if (isset($fileMetadata['file'])) {
            
            // The default file transfer type is URL.
            $fileTransferType = 'Filesystem';               
            
            // The default option is ignore invalid files.
            $fileOptions = array('ignore_invalid_files' => true);
            
            // Prepare the files value for one-file-at-a-time iteration.
            $files = array($fileMetadata['file']);
            
            foreach ($files as $file) {
                
                $file = array("source"=>$file, "name"=>$fileMetadata['label']); 

                $fileOb = insert_files_for_item(
                    $item, 
                    $fileTransferType, 
                    $file, 
                    $fileOptions);

                // If the file is marked for deletatation (image, text or pdf file downloaded on the server)
                if ($fileMetadata['delete_file_after_insert'] && file_exists($fileMetadata['file'])) {

                    $cmd = "rm " . $fileMetadata['file'];
                    shell_exec($cmd);

                } else { // Video or audio file (not downloaded)

                    // Adding metadata from Saprql
                    if (isset($fileOb[0]) && get_class($fileOb[0]) == 'File') {
                        $met['format'] = $fileMetadata['format'];
                        $met['extent'] = $fileMetadata['extent'];
                        $met['original_filename'] = $fileMetadata['label'];
                        $fileOb[0]->metadata = json_encode($met);
                        $fileOb[0]->save();
                    }
                }
                                  
                // Release the File object from memory. 
                release_object($fileObject);
            }
        }
        
        // Release the Item object from memory.
        release_object($item);
        
        return true;
    }

     /**
     * Convenience method for updating an item
     * 
     * The files update is not managed, because NAKALA can't modify his files
     * 
     * @see update_item()
     * @param Item $item The item object
     * @return void
     */
    final protected function _updateItem($item, $metadata, $elementTexts) {

        // Delete all element texts for this item
        $item->deleteElementTexts();

        // Update the item in database
        update_item($item, $metadata, $elementTexts);
    }


    /**
     * Insert an import into the database (table omeka_imports)
     * 
     * @return The ID of the import created
     */
    public function startImport()
    {
        $import = new NakalaImport;
       
        $import->initiated      = date('Y:m:d H:i:s');
        $import->completed      = null;
        $import->logs           = "logs";
        $import->save();

        return $import->id;
    }

    /**
     * Close an import into the database (table omeka_imports) : update "modified" field
     * 
     * @param Item $importId The ID item of target item
     * @return void
     */
    public function closeImport($importId)
    {
        $db = $this->_db;
        $sql = "UPDATE `{$db->prefix}nakala_imports` SET `completed` = '".date('Y:m:d H:i:s')."' WHERE `id` = ".$importId;
        $db->query($sql);
    }

    /**
     * Insert an import into the database (table omeka_imports)
     * 
     * @param integer $importId The ID of the target import (in nakala_imports)
     * @param Item $item The Item object created
     * @param string $dataUrl The NAKALA data URL
     * @return The ID of the import created
     */
    private function _insertImportRecord($importId, $item, $dataUrl)
    {
        
        $record = new NakalaImportsRecord;

        $record->import_id  = $importId;
        $record->handle     = getHandleFormNakalaUrl($dataUrl);
        $record->item_id    = $item->id;
        $record->date       = date('Y:m:d H:i:s');
        $record->logs       = 'logs';
        $record->save();
      
    }

}
