<?php

function cut_string($string, $length = 50)
{
    $string = preg_replace('#<br\s*/?>#i', "\n", $string);

    if (strlen($string) <=  $length)
        return $string;

    // return substr($string, 0, strpos(wordwrap($string, $length), "\n"));
    $cut = substr($string, 0, $length);
    $res = substr($cut, 0, strrpos($cut, ' '));
    return $res.'...';
}



/**
 * Returns a handle ID from a Nakala URL
 *
 * Remove the www. if exists in URL
 * 
 * @param string An URL (for example : http://nakala.fr/data/11280/279ad9eb)
 * @return string A Handle identifier (for example : 11280/279ad9eb)
 */
function getHandleFormNakalaUrl($url) 
{
    return ltrim(str_replace('www.', '', $url), NAKALA_DATA_PREFIX);
}

/**
 * Returns a handle ID from a Nakala collection URL
 *
 * Remove the www. if exists in URL
 * 
 * @param string A collection URL (for example : http://nakala.fr/collection/11280/279ad9eb)
 * @return string A Handle identifier (for example : 11280/279ad9eb)
 */
function getHandleFormNakalaCollectionUrl($url) 
{
    return ltrim(str_replace('www.', '', $url), NAKALA_COLLECTION_PREFIX);
}

/**
 * Returns the Nakala URI of an item
 * 
 * @param Item item An OMEKA Item object
 * @return string The correct Nakala URI for this item (for example http://nakala.fr/data/11280/279ad9eb)
 */
function getNakalaUriFromItem($item)
{
    $identifiers = $item->getElementTexts("Dublin Core", "Identifier");

    foreach ($identifiers as $identifier) { 
        if (is_integer(strpos($identifier->getText(), '/')))
            return $identifier->getText();
    }
    return false;
}
  

/**
 * Check if a NAKALA item exists in database
 * 
 * The link between NAKALA and OMEKA items is the DC:identifer field (witch contains handle identifier)
 * 
 * @param Array $elementTexts An array containing all elements texts, including one or multiple values for ["Dublin Core"]["Identifier"]
 * @return Item|false 
 */
function itemExists($elementTexts)
{
    if (isset($elementTexts['Dublin Core']['Identifier'])) {
        
        foreach ($elementTexts['Dublin Core']['Identifier'] as $identifier) { 
            if (is_integer(strpos($identifier['text'], '/')))
                $handle = $identifier['text'];
        }
    }

    $db = get_db();
    $sql = "SELECT record_id FROM `".$db->ElementTexts."` WHERE element_id = 43 and text = \"".$handle."\" AND record_type = 'Item' LIMIT 1";
    $record_id = $db->fetchOne($sql);
    if (is_numeric($record_id))
        return get_record_by_id("Item", $record_id);

    return false;
}
 

 /**
 * Check if a NAKALA collection exists in database
 * 
 * The link between NAKALA and OMEKA items is the DC:identifer field (witch contains handle identifier)
 * 
 * @param String $collectionUrl The collection URL (for example http://nakala.fr/collection/11280/279ad9eb)
 * @return Collection|false 
 */
function collectionExists($collectionUrl)
{
    $collectionHandle = getHandleFormNakalaCollectionUrl($collectionUrl);
    $db = get_db();
    $sql = "SELECT record_id FROM `".$db->ElementTexts."` WHERE element_id = 43 and text = \"".$collectionHandle."\" AND record_type = 'Collection' LIMIT 1";
    $record_id = $db->fetchOne($sql);
    if (is_numeric($record_id))
        return get_record_by_id("Collection", $record_id);

    return false;
} 


