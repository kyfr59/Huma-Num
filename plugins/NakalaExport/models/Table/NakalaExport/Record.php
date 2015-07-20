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
            if( !$this->_isOmekaItem($item->id) && 
                !$this->_isExported($item->id) && 
                !$this->_isUpdatedSinceLastExport($item->id) 
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
        return get_db()->getTable('OaipmhHarvester_Record')->findByItemId($item_id);
    }


    public function _isExported($item_id)
    {
        return false;
    }

    public function _isUpdatedSinceLastExport($record)
    {
        return false;
        //Zend_Debug::dump($record->datestamp);
    }


    /**
     * Return records by harvest ID.
     * 
     * @param int $harvsetId
     * @return array An array of OaipmhHarvester_Record objects.
     */
    public function findByHarvestId($harvestId)
    {
        $tableAlias = $this->getTableAlias();
        $select = $this->getSelect();
        $select->where("$tableAlias.harvest_id = ?");
        return $this->fetchObjects($select, array($harvestId));
    }
    
    /**
     * Return records by OAI-PMH identifier.
     * 
     * @param string $identifier OAI-PMH identifier
     * @return array An array of OaipmhHarvester_Record objects.
     */
    public function findByOaiIdentifier($identifier)
    {
        $tableAlias = $this->getTableAlias();
        $select = $this->getSelect();
        $select->where("$tableAlias.identifier = ?");
        return $this->fetchObjects($select, array($identifier));
    }
    
    /**
     * Return records by item ID.
     * 
     * @param mixes $itemId Item ID
     * @return OaipmhHarvester_Record Record corresponding to item id.
     */
    public function findByItemId($itemId)
    {
        $tableAlias = $this->getTableAlias();
        $select = $this->getSelect();
        $select->where("$tableAlias.item_id = ?");
        return $this->fetchObject($select, array($itemId));
    }

    public function applySearchFilters($select, $params)
    {
        $tableAlias = $this->getTableAlias();
        $harvestTableAlias = $this->_db->getTable('OaipmhHarvester_Harvest')->getTableAlias();
        $harvestKeys = array(
            'base_url',
            'set_spec',
            'metadata_prefix',
        );
        if (array_intersect($harvestKeys, array_keys($params))) {
            $this->_join($select, 'Harvest');
            foreach ($harvestKeys as $key) {
                if (array_key_exists($key, $params)) {
                    if ($params[$key] === null) {
                        $select->where("$harvestTableAlias.$key IS NULL");
                    } else {
                        $select->where("$harvestTableAlias.$key = ?", $params[$key]);
                    }
                }
            }
        }
        if (array_key_exists('identifier', $params))
        {
            $select->where("$tableAlias.identifier = ?", $params['identifier']);
        }
    }

    private function _join($select, $tableName)
    {
        $tableAlias = $this->getTableAlias();
        $harvestTable = $this->_db->getTable('OaipmhHarvester_Harvest');
        $harvestTableAlias = $harvestTable->getTableAlias();
        switch ($tableName) {
            case 'Harvest':
                $select->joinInner(
                    array($harvestTableAlias => $harvestTable->getTableName()),
                    "$harvestTableAlias.id = $tableAlias.harvest_id",
                    array()
                );
                break;
            default:
                break;
        }
    }
}