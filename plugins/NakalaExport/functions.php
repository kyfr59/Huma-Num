<?php

/**
 * Returns a handle ID from a Nakala URL
 * 
 * @param string An URL (for example : http://nakala.fr/data/11280/279ad9eb)
 * @return string A Handle identifier (for example : 11280/279ad9eb)
 */
function getHandleFormCollectionUrl($url) 
{
    return ltrim($url, NAKALA_COLLECTION_PREFIX);
}
  

