<?php
/**
 * @package OaipmhHarvester
 * @subpackage Models
 * @copyright Copyright (c) 2009-2011 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Model class for a record table.
 *
 * @package OaipmhHarvester
 * @subpackage Models
 */
class Table_NakalaExport_Collection extends Omeka_Db_Table
{

     
     /**
     * Return all the collections ready for exportation
     * 
     * @return array An array of collection IDs.
     */  
    public function getCollectionsToExport()
    {
        $collections = get_db()->getTable('Collection')->findAll();

        $res = array();
        foreach($collections as $collection)
        {
            // Zend_Debug::dump( $collection->id." / ".$this->_isOmekaCollection($collection). " / ". $this->hasNeverBeenExported($collection));
            if( $this->_isOmekaCollection($collection) && 
                $this->hasNeverBeenExported($collection) 
              )
                $res[] = $collection->id;
        }

        return $res;
    }

    /**
     * Return true if the collection is an Omeka collection (collection added manually by the user, not imported, and so whitout Nakala identifier), otherwise false
     * 
     * @param Collection $collection The collection
     * @return bool 
     */
    private function _isOmekaCollection($collection)
    {
        // return !get_db()->getTable('OaipmhHarvester_Record')->findByItemId($item_id);
        if ($identifier = metadata($collection, array("Dublin Core", "Identifier"))) {
            if (getHandleFormCollectionUrl($identifier))
                return false;
        }
        return true;
    }


    /**
     * Return the last Table_NakalaExport_Record record if the item has already been exported to Nakala, otherwise false
     * 
     * @param int $item_id The ID of the item
     * @return bool 
     */
    private function _getLastExportedRow($collection, $status = NakalaConsole_Helper::RESPONSE_OK)
    {
        $tableAlias = $this->getTableAlias();
        $select = $this->getSelect();
        $select->where("$tableAlias.collection_id = ?");
        $select->where("$tableAlias.status = ?");
        $select->order("completed_at desc");
        $select->limit(1);
        $res = $this->fetchObjects($select, array($collection->id, $status));
        
        if($res)
            return $res[0];
        return false;
    }

    public function hasNeverBeenExported($collection)
    {
        return !(bool)$this->_getLastExportedRow($collection);
    }


}