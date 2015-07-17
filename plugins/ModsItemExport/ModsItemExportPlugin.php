<?php
/**
 * MODS item export
 * 
 * @copyright Copyright 2015-2020 Limonade & Co (Paris)
 * @author Franck Dupont <kyfr59@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The MODS item export plugin.
 * 
 * @package Omeka\Plugins\ModsItemExport
 */
class ModsItemExportPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_filters = array(
        'response_contexts',
        'action_contexts'
    );

    public function filterResponseContexts($contexts)
    {
        $contexts['mods'] = array(
            'suffix'  => 'mods',
            'headers' => array('Content-Type' => 'text/xml'),
        );

        return $contexts;
    }

    public function filterActionContexts($contexts, $controller)
    {
    	$request = Zend_Controller_Front::getInstance()->getRequest();
   	
        if ($controller['controller'] instanceof ItemsController) {
            $contexts['show'][] = 'mods';
        }

        return $contexts;
    }
}
