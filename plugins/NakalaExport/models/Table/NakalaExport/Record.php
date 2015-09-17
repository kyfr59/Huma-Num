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
class Table_NakalaExport_Record extends Omeka_Db_Table
{

     
     /**
     * Return all the items ready for exportation
     * 
     * @return array An array of items IDs.
     */  
    public function getItemsToExport()
    {
        $items = get_db()->getTable('Item')->findAll();

        $res = array();
        foreach($items as $item)
        {
            if( $this->_isOmekaItem($item->id) && 
                $this->hasNeverBeenExported($item->id) &&
                $item->public == 1 &&
                $item->getProperty('has_files')
              )
                $res[] = $item->id;
        }

        return $res;
    }

    /**
     * Return true if the item is an Omeka item (item added manually by the user, not imported from Nakala Import plugin), otherwise false
     * 
     * @param int $item_id The ID of the item
     * @return bool 
     */
    private function _isOmekaItem($item_id)
    {
        return !get_db()->getTable('OaipmhHarvester_Record')->findByItemId($item_id);
    }


    /**
     * Return the last Table_NakalaExport_Record record if the item has already been exported to Nakala, otherwise false
     * 
     * @param int $item_id The ID of the item
     * @return bool 
     */
    private function _getLastExportedRow($item_id, $status = NakalaConsole_Helper::RESPONSE_OK)
    {
        $tableAlias = $this->getTableAlias();
        $select = $this->getSelect();
        $select->where("$tableAlias.item_id = ?");
        $select->where("$tableAlias.status = ?");
        $select->order("completed_at desc");
        $select->limit(1);
        $res = $this->fetchObjects($select, array($item_id, $status));
        
        if($res)
            return $res[0];
        return false;
    }

    public function hasNeverBeenExported($item_id)
    {
        return !(bool)$this->_getLastExportedRow($item_id);
    }

    public function hasAlwaysBeenExported($item_id, $status = null)
    {
        return $this->_getLastExportedRow($item_id, $status);
    }

    private function _isUpdatedSinceLastExport($item_id)
    {
        if ($lastExport = $this->_getLastExportedRow($item_id))
        {
            $item = get_record_by_id("Item", $item_id);
            if (strtotime($item->modified) > strtotime($lastExport->datestamp) )
                return true;    
        }
        
        return false;
        
    }

}