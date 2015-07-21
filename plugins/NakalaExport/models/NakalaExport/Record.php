<?php
/**
 * @package NakalaExport
 * @subpackage Models
 * @copyright Copyright (c) 2009-2011 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Model class for a export.
 *
 * @package NakalaExport
 * @subpackage Models
 */
class NakalaExport_Record extends Omeka_Record_AbstractRecord
{
	const STATUS_IN_PROGRESS 	= 'in progress';
    const STATUS_OK   			= 'ok';
    const STATUS_ERROR       	= 'error';
    

    public $id;
    public $export_id;
    public $item_id;
    public $status;
    public $error_message;
    public $datestamp;

	/**
     * Insert a record into the database.
     * 
     * @param Item $item The item object corresponding to the record.
     * @param integer $export_id The ID of the corresponding export process.
     * @return integer ID of the created record
     */
    public function create($item, $export_id) {

        $this->export_id   	= $export_id;
        $this->item_id      = $item->id;
        $this->status      	= self::STATUS_IN_PROGRESS;
        $this->datestamp    = date('Y:m:d H:i:s');
        $this->save();

        release_object($this);

        return $this->id;
    }


	/**
     * Stop an export into the database.
     * 
     * @param string $error The error message
     * @return void
     */
    public function stopExport($error) {

    	$this->error_message = $error;
    	$this->status      	 = self::STATUS_ERROR;
    	$this->save();
    }

}
