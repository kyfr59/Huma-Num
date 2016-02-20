<?php
/**
* @copyright Copyright 2015-2020 Limonade & Co (Paris)
* @author Franck Dupont <kyfr59@gmail.com>
* @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
* @package NakalaImport
* @subpackage Libraries
*/

class NakalaImportSparql 
{
    const DCTERMS_PREFIX    = 'http://purl.org/dc/terms';

    /**
     * Array of mapping between term retrieved from server and OMEKA application
     */
    static private $_termsMapping = array(
        "http://www.openarchives.org/ore/terms/isAggregatedBy"  => "collectionUrl",
        "http://www.w3.org/2004/02/skos/core#prefLabel"         => "collectionName",
        "http://www.w3.org/2004/02/skos/core#altLabel"          => "filename",
    );

    public function __construct() {
             
        $this->sparql = new EasyRdf_Sparql_Client(NAKALA_SPARQL_ENDPOINT);

        // Load configuration options and store handle
        $options = unserialize(get_option('nakala_import_settings'));
        $this->handle = $options['nakala-handle'];

        unset($_SESSION['sparql_query']);
    }


    /**
     * Gives the prefixes for Sparql requests
     *
     * @return String
     */
    private function _getSparlPrefixes() {

        $prefixes  = "PREFIX foaf: <http://xmlns.com/foaf/0.1/> \r\n";
        $prefixes .= "PREFIX dcterms: <http://purl.org/dc/terms/> \r\n";
        $prefixes .= "PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> \r\n";
        $prefixes .= "PREFIX skos: <http://www.w3.org/2004/02/skos/core#> \r\n";
        $prefixes .= "PREFIX ore: <http://www.openarchives.org/ore/terms/> \r\n";
        return $prefixes;
    }

    /**
     * Query Sparql server for a given account to check if there's a response
     * If connection to server and account is correct, the Sparql query must return one result, otherwhise 0
     *
     * @param String $handle the handle identifier of the account to test
     * @return Boolean
     */
    public function testServer($handle)
    {
        $query   = "PREFIX foaf: <http://xmlns.com/foaf/0.1/>";
        $query  .= "SELECT <" . NAKALA_ACCOUNT_PREFIX . $handle.">";
        $query  .= "WHERE {";
        $query  .= "<" . NAKALA_ACCOUNT_PREFIX . $handle."> a foaf:Agent .";
        $query  .= "}";

        $result = $this->sparql->query($query);

        $serverOk = true;
        if (!$result || count($result) == 0)
            $serverOk = false;

        return $serverOk;
    }


    /**
     * Query Sparql server for retrieve updates
     *
     * @param String $lastImportDate The date (xsd:dateTime) of the last import
     * @return Record_Set
     */
    public function retrieveUpdates($lastImportDate, $collectionUrl = null)
    {
        $query = $this->_getSparlPrefixes();
        $query  .= "SELECT ?resourceUrl ?dataUrl ?fileLabel ?modified (GROUP_CONCAT(?title ; SEPARATOR = \"  // \") AS ?title) \r\n";
        $query  .= "WHERE { \r\n";
        $query  .= "?resourceUrl dcterms:publisher <" . NAKALA_ACCOUNT_PREFIX . $this->handle."> . \r\n";
        $query  .= "?resourceUrl skos:altLabel ?fileLabel . \r\n";
        $query  .= "?resourceUrl foaf:primaryTopic ?dataUrl . \r\n";
        $query  .= "?dataUrl dcterms:title ?title . \r\n";
        if (null != $collectionUrl)
            $query  .= "?resourceUrl ore:isAggregatedBy <" . $collectionUrl ."> . \r\n";
        $query  .= "?resourceUrl dcterms:modified ?modified  \r\n";
        $query  .= "FILTER ( ?modified >= xsd:dateTime('$lastImportDate') )  \r\n";
        $query  .= "}  \r\n";
        $query  .= "ORDER BY DESC(?modified) \r\n";
        // $query  .= "LIMIT 1000 \r\n";

        // @$_SESSION['sparql_query'] .= $query ."-----------------------------\n";

        // echo nl2br(htmlspecialchars($query));
      
        $results = $this->sparql->query($query);

        return $results;
    }


    /**
     * Query Sparql server for retrieve collections 
     *
     * @return Record_Set
     */
    public function retrieveCollections()
    {
        $query = $this->_getSparlPrefixes();

        $query  .= "SELECT ?collectionUrl ?nomCollection \r\n";
        $query  .= "WHERE { \r\n";
        $query  .= "?scheme \r\n";
        $query  .= " dcterms:creator \r\n";
        $query  .= "<" . NAKALA_ACCOUNT_PREFIX . $this->handle."> . \r\n";
        $query  .= "?collectionUrl skos:inScheme ?scheme . \r\n";
        $query  .= "?collectionUrl skos:prefLabel ?nomCollection . \r\n";
        $query  .= "} \r\n";
        $query  .= "ORDER BY DESC(?nomCollection) \r\n";

        //echo nl2br(htmlspecialchars($query));
      
        $results = $this->sparql->query($query);

        return $results;
    }



    /**
     * Query Sparql server for retrieve informations about a specific ressource
     *
     * @param string $resourceUrl The resource URL of the item (on Sparql Server)
     * @param string $dataUrl The data URL of the item (on Sparql Server)
     * @return array An array with informations retrieved from Sparl server
     */
    public function getInformationsFromServer($url)
    {
        $query = $this->_getSparlPrefixes();
        $query .= "SELECT ?term ?value \r\n";
        $query .= "WHERE { \r\n";
        $query .= "<$url> ?term ?value . \r\n";        
        $query .= "} \r\n";

        // @$_SESSION['sparql_query'] .= $query ."-----------------------------\n";

        //echo nl2br(htmlspecialchars($query));

        ///Zend_Debug::dump($this->sparql->query($query));

        $results = $this->sparql->query($query);

        return $results;
    }

       
    /**
     * Retrieve and format informations for a given data URL (like http://www.nakala.fr/data/11280/25c2a363)
     *
     * The function first call Sparql server for retrieve "data" informations, 
     * and then call server for "resource" informations.
     * Informations are stored in the $info array.
     * Dublin Core terms are prefixed by "dc_" in the array keys, and the values are stored in an internal array (preventing multiple DC values in NAKALA)
     *
     * @param string $dataUrl The data URL of the item (on Sparql Server)
     * @return array An array with informations retrieved from Sparl server
     */       
    public function getInformations($dataUrl)
    {
        /* DATA informations */

        $infos['dataUrl'] = $dataUrl;

        // Retrieve data informations for Sparql server
        $dataInformations = $this->getInformationsFromServer($dataUrl);

        // Formatting data informations 
        foreach ($dataInformations as $dataInformation) {

            $term   = (string)$dataInformation->term;
            $value  = (string)$dataInformation->value;

            if (strpos($term, self::DCTERMS_PREFIX) !== false) {  // Parsing dcterms term

                $tab = explode(self::DCTERMS_PREFIX.'/', $term);
                $infos['dc_'.$tab[1]][] = $value;

            } else { // Parsing other values

                if (array_key_exists($term, self::$_termsMapping)) {
                    $key = self::$_termsMapping[$term];
                    $infos[$key] = $value;
                }
            }
        }
        
        /* RESOURCE informations */

        // Building resource URL
        $infos['resourceUrl'] = str_replace('/data/', '/resource/', $dataUrl);

        // Retrieve resource informations for Sparql server
        $resourceInformations = $this->getInformationsFromServer($infos['resourceUrl']);

        // Formatting resource informations 
        foreach ($resourceInformations as $resourceInformation) {

            $term   = (string)$resourceInformation->term;
            $value  = (string)$resourceInformation->value;
            
            if (strpos($term, self::DCTERMS_PREFIX) !== false) {  // Parsing dcterms term

                $tab = explode(self::DCTERMS_PREFIX.'/', $term);
                $infos['dc_'.$tab[1]][] = $value;

            } else { // Parsing other values

                if (array_key_exists($term, self::$_termsMapping)) {
                    $key = self::$_termsMapping[$term];
                    $infos[$key] = $value;
                }
            }
        }

        ksort($infos);
       
        return $infos;
    }    


    /**
     * Retrieve and format informations for a given collection URL (like http://www.nakala.fr/collection/11280/25c2a363)
     *
     * @param string $collectionUrl The collection URL (on Sparql Server)
     * @return array An array with informations retrieved from Sparl server
     */       
    public function getCollectionInformations($collectionUrl)
    {
        $collectionInfos = $this->getInformationsFromServer($collectionUrl);

        // Formatting data informations 
        foreach ($collectionInfos as $collectionInfo) {

            $term   = (string)$collectionInfo->term;
            $value  = (string)$collectionInfo->value;

            if (strpos($term, self::DCTERMS_PREFIX) !== false) {  // Parsing dcterms term

                $tab = explode(self::DCTERMS_PREFIX.'/', $term);
                $infos['dc_'.$tab[1]][] = $value;

            } else { // Parsing other values

                if (array_key_exists($term, self::$_termsMapping)) {
                    $key = self::$_termsMapping[$term];
                    $infos[$key] = $value;
                }
            }
        }

        return $infos;
    }


    public function query($query)
    {
        return $this->sparql->query($query);
    }

}