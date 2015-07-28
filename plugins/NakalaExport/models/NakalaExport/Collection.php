<?php
/**
 * @package NakalaExport
 * @subpackage Models
 * @copyright Copyright (c) 2009-2011 Roy Rosenzweig Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Model class for a collection export.
 *
 * @package NakalaExport
 * @subpackage Models
 */
class NakalaExport_Collection extends Omeka_Record_AbstractRecord
{
	const STATUS_IN_PROGRESS 	= 'in progress';
    const STATUS_OK   			= 'ok';
    const STATUS_ERROR       	= 'error';
    

    public $id;
    public $export_id;
    public $collection_id;
    public $handle;
    public $status;
    public $message;
    public $start_from;
    public $completed_at;

    /**
     * Insert a record into the database.
     * 
     * @param Collection $collection The collection object corresponding to the record.
     * @param integer $export_id The ID of the corresponding export process.
     * @return integer ID of the created record
     */
    public function create($collection, $export_id) {

        $this->export_id   	    = $export_id;
        $this->collection_id    = $collection->id;
        $this->status      	    = self::STATUS_IN_PROGRESS;
        $this->start_from       = date('Y:m:d H:i:s');
        $this->save();

        release_object($this);

        return $this->id;
    }


	/**
     * Stop a record export
     * 
     * @param string $error The log message
     * @return void
     */
    public function stopRecord($error) {

    	$this->message 			= $error;
    	$this->status      		= self::STATUS_ERROR;
    	$this->completed_at    	= date('Y:m:d H:i:s');
    	$this->save();
    }


	/**
     * Update the status of a record export
     * 
     * @param string $status The status of the record
     * @param string $error The log message
     * @return void
     */
    public function update($status, $message) 
    {

    	if ($status == self::STATUS_OK) {
    		$message  = NakalaConsole_Helper::readOutputFile($this->item_id);
    	}

    	$this->status      	 	= $status;
		$this->message 			= $message;
		$this->completed_at    	= date('Y:m:d H:i:s');
    	$this->save();
    }


    /**
     * Delete the ZIP archive from the server for this item
     * 
     * @return void
     */
    public function deleteZip()
    {
        NakalaConsole_Helper::deleteZip($this->collection_id, 'collection');
    }
    

}
