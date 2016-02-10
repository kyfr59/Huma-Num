<?php
/**
* @copyright Copyright 2015-2020 Limonade & Co (Paris)
* @author Franck Dupont <kyfr59@gmail.com>
* @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
* @package NakalaImport
* @subpackage Models
*/

/**
 * Record that keeps track of contributions; links items to contributors.
 */

class NakalaImportsRecord extends Omeka_Record_AbstractRecord
{
    public $id;
    public $import_id;
    public $handle;
    public $item_id;
    public $date;
    public $logs;

/*    
    protected $_related = array(
        'Item' => 'getItem',
        'Contributor' => 'getContributor'
        );
    
    public function getItem()
    {
        return $this->getDb()->getTable('Item')->find($this->item_id);
    }

    public function makeNotPublic()
    {
        $this->public = false;
        $item = $this->Item;
        $item->public = false;
        $item->save();
        release_object($item);
    }
    
    public function getContributor()
    {
        $owner = $this->Item->getOwner();
        //if the user has been deleted, make a fake user called "Deleted User"
        if(!$owner) {
            $owner = new User();
            $owner->name = '[' . __('Unknown User') . ']';
            return $owner;
        }
        $user = current_user();
        if($user && $user->id == $owner->id) {
            return $owner;
        }
        //mimic an actual user, but anonymous if user doesn't have access
        if($this->anonymous == 1 && !is_allowed('Contribution_Items', 'view-anonymous')) {
            $owner = new User();
            $owner->name = __('Anonymous');
        }
        return $owner;
    }
*/
}
