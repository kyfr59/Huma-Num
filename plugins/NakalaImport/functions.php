<?php

function huma_num_harvester_config($key, $default = null)
{
    $config = Zend_Registry::get('bootstrap')->getResource('Config');
    if (isset($config->plugins->OaipmhHarvester->$key)) {
        return $config->plugins->OaipmhHarvester->$key;
    } else if ($default) {
        return $default;
    } else {
        return null;
    }
}



/** Huma-Num functions **/


/**
 * Returns a handle ID from a Nakala URL
 * 
 * @param string An URL (for example : http://nakala.fr/data/11280/279ad9eb)
 * @return string A Handle identifier (for example : 11280/279ad9eb)
 */
function getHandleFormNakalaUrl($url) 
{
    return ltrim($url, NAKALA_DATA_PREFIX);
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
        if (is_integer(strpos($identifier->getText(), NAKALA_DATA_PREFIX)))
            return $identifier->getText();
    }
    return false;
}
  

