<?php
/**
 * @package OaipmhHarvester
 * @subpackage Models
 * @copyright Copyright (c) 2009-2011 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Metadata format map for the required oai_dc Dublin Core format
 *
 * @package OaipmhHarvester
 * @subpackage Models
 */
class OaipmhHarvester_Harvest_OaiDc extends OaipmhHarvester_Harvest_Abstract
{
    /*  XML schema and OAI prefix for the format represented by this class.
        These constants are required for all maps. */
    const METADATA_SCHEMA = 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd';
    const METADATA_PREFIX = 'oai_dc';

    const OAI_DC_NAMESPACE = 'http://www.openarchives.org/OAI/2.0/oai_dc/';
    const DUBLIN_CORE_NAMESPACE = 'http://purl.org/dc/elements/1.1/';

    const AUDIO_SAMPLE_FILE = 'sample.mp3';
    const VIDEO_SAMPLE_FILE = 'sample.mp4';

    /**
     * Collection to insert items into.
     * @var Collection
     */
    protected $_collection;
    
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
     * Actions to be carried out before the harvest of any items begins.
     */
     protected function _beforeHarvest()
    {
        $harvest = $this->_getHarvest();
   
        $collectionMetadata = array(
            'metadata' => array(
                'public' => $this->getOption('public'),
                'featured' => $this->getOption('featured'),
            ),);
        $collectionMetadata['elementTexts']['Dublin Core']['Title'][]
            = array('text' => (string) $harvest->set_name, 'html' => false); 
        $collectionMetadata['elementTexts']['Dublin Core']['Description'][]
            = array('text' => (string) $harvest->set_Description, 'html' => false); 
        
        //$this->_collection = $this->_insertCollection($collectionMetadata);
    }
    
    /**
     * Harvest one record.
     *
     * @param SimpleXMLIterator $record XML metadata record
     * @return array Array of item-level, element texts and file metadata.
     */
    protected function _harvestRecord($record)
    {
        $itemMetadata = array(
            'collection_id' => $this->_collection->id, 
            'public'        => $this->getOption('public'), 
            'featured'      => $this->getOption('featured'),
        );
        
        $dcMetadata = $record
                    ->metadata
                    ->children(self::OAI_DC_NAMESPACE)
                    ->children(self::DUBLIN_CORE_NAMESPACE);
        
        $elementTexts = array();
        $elements = array('contributor', 'coverage', 'creator', 
                          'date', 'description', 'format', 
                          'identifier', 'language', 'publisher', 
                          'relation', 'rights', 'source', 
                          'subject', 'title', 'type');
        foreach ($elements as $element) {
            if (isset($dcMetadata->$element)) {
                foreach ($dcMetadata->$element as $rawText) {
                    $text = trim($rawText);
                    $elementTexts['Dublin Core'][ucwords($element)][] 
                        = array('text' => (string) $text, 'html' => false);

                    // Huma-Num : Retrieve correct Nakala URI for this item (like "http://nakala.fr/data/11280/74c63500")
                    if ($element == 'identifier' && is_integer(strpos($text, NAKALA_DATA_PREFIX))) {
                        $handleUrl = $text;
                    }          
                }
            }
        }


        /* Huma-Num */

        // Retrieve handle from URL (for example 11280/74c63500)
        $handle = getHandleFormNakalaUrl($handleUrl);

        // Retrieve the original filename in the triple store
        $sparql = new EasyRdf_Sparql_Client(NAKALA_SPARQL_ENDPOINT);
        
        $query  = "PREFIX dcterms: <http://purl.org/dc/terms/>";
        $query .= "PREFIX ore: <http://www.openarchives.org/ore/terms/>";
        $query .= "SELECT * WHERE {";
        $query .= "<" . NAKALA_RESOURCE_URL . $handle."> skos:altLabel ?label . ";
        $query .= "OPTIONAL {<" . NAKALA_RESOURCE_URL . $handle."> dcterms:format ?format } . ";
        $query .= "OPTIONAL {<" . NAKALA_RESOURCE_URL . $handle."> dcterms:extent ?extent } . ";
        $query .= "OPTIONAL {<" . NAKALA_RESOURCE_URL . $handle."> ore:isAggregatedBy ?collections } . ";
        $query .= "}";
        

        $result = $sparql->query($query);
            
        if ($filename = $result[0]->label) {

            // If the file is downloadable (not movie or sound)
            $pathinfo = pathinfo($filename);
            $extension = $pathinfo['extension'];

            // Update the file metadata depending on the file extension
            if (in_array($extension,self::$_audioExtensionNotDownloaded)) {

                $fileMetadata['files'] = OAIPMH_HARVESTER_PLUGIN_DIRECTORY_TEMP . '/'. self::AUDIO_SAMPLE_FILE;
                $fileMetadata['delete_file_after_insert'] = false;               
                $fileMetadata['format']     = (string)$result[0]->format;
                $fileMetadata['extent']     = (string)$result[0]->extent;

            } elseif (in_array($extension,self::$_videoExtensionNotDownloaded)) {

                $fileMetadata['files'] = OAIPMH_HARVESTER_PLUGIN_DIRECTORY_TEMP . '/'. self::VIDEO_SAMPLE_FILE;
                $fileMetadata['delete_file_after_insert'] = false;               
                $fileMetadata['format']     = (string)$result[0]->format;       
                $fileMetadata['extent']     = (string)$result[0]->extent;
                
            } else {

                // The file isn't an audio or video file, we download it (to generate the thumbmails)
                $cmd = "wget -O " . OAIPMH_HARVESTER_PLUGIN_DIRECTORY_TEMP . '/'. urlencode($filename) ." ". $handleUrl;            
                shell_exec($cmd);
                $fileMetadata['files'] = OAIPMH_HARVESTER_PLUGIN_DIRECTORY_TEMP . '/'. urlencode($filename);
                $fileMetadata['delete_file_after_insert'] = true;               
            }

            // Define 'Filesystem' as default transfert type
            $fileMetadata['file_transfer_type'] = 'Filesystem';               
            $fileMetadata['file_from_nakala_harvest'] = true;               
            $fileMetadata['label'] = (string)$result[0]->label;


        } else {
            $this->_addStatusMessage("Pas de nom de fichier pour le handle ".$handle, 'error');         
        }

        
        return array('itemMetadata' => $itemMetadata,
                     'elementTexts' => $elementTexts,
                     'fileMetadata' => $fileMetadata); // Huma-num
    }
}
