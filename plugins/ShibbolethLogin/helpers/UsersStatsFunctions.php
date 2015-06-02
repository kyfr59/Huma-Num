<?php
/**
 * Simple Pages
 *
 * @copyright Copyright 2008-2012 Roy Rosenzweig Center for History and New Media
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
function link_to_items_of_user($text = null, $props = array(), $action = 'browse', $user = null) 
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
