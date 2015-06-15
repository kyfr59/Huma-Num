<?php
/**
 * ShibbolethLogin plugi
 *
 * @copyright Copyright 2015-2020 Limonade & Co (Paris)
 * @author Franck Dupont <kyfr59@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */


/**
 * Get a link to the collection items browse page.
 *
 * @package Omeka\Function\View\Navigation
 * @uses link_to()
 * @param string|null $text
 * @param array $props
 * @param string $action
 * @param Collection $collectionObj
 * @return string
 */
function isShibbolethSessionActive() 
{
    if ($user) {
        
        $nb = get_db()->getTable('Item')->count(array('owner_id' => $user->id) );

        if ($text === null) {
            $text = $nb;
        }

        $queryParams["user_id"] = $user->id;

        return '<a href="users-stats/index/show?user_id='.$user->id.'">'.$text.'</a>';//, $action, $text, $props, $queryParams);
    }
    
    return false;
   
}
