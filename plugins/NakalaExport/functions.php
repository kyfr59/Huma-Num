<?php

/**
 * Returns the Nakala URI of an item
 * 
 * @param Item item An OMEKA Item object
 * @return string The correct Nakala URI for this item (for example http://nakala.fr/data/11280/279ad9eb)
 */
function isNakala($item)
{
    $identifiers = $item->getElementTexts("Dublin Core", "Identifier");
    
    foreach ($identifiers as $identifier) {
        if (is_integer(strpos($identifier->getText(), NAKALA_DATA_PREFIX)))
            return $identifier->getText();
    }
    return false;
}
  

