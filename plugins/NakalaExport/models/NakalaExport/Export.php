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
class NakalaExport_Export extends Omeka_Record_AbstractRecord
{
    const STATUS_QUEUED      = 'queued';
    const STATUS_IN_PROGRESS = 'in progress';
    const STATUS_COMPLETED   = 'completed';
    const STATUS_ERROR       = 'error';
    const STATUS_DELETED     = 'deleted';
    const STATUS_KILLED      = 'killed';
    
    
    public $id;
    public $status;
    public $message;
    public $start_from;
    public $completed_at;


    public function create($type = 'item') {

        $this->status       = self::STATUS_IN_PROGRESS;
        $this->message      = $type;
        $this->start_from   = date('Y:m:d H:i:s');
        $this->save();

        release_object($this);

        return $this->id;
    }


    /**
     * Close the export into the database.
     * 
     * @return void
     */
    public function close() {

        $this->status           = self::STATUS_COMPLETED;
        $this->completed_at     = date('Y:m:d H:i:s');
        $this->save();
    }
        
    


}