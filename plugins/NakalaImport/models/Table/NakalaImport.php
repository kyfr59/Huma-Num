<?php
/**
 * @version $Id$
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @copyright Center for History and New Media, 2010
 * @package Contribution
 * @subpackage Models
 */

/**
 * Record that keeps track of contributions; links items to contributors.
 */
class Table_NakalaImport extends Omeka_Db_Table
{

    /**
     * Get the date/time (in xsd:dateTime format) of the last import (based on 'initiated' field)
     * If there's no records in database, returns "1970-01-01"
     * Example of xsd:dateTime format : "2016-01-14T14:51:15.532+01:00"
     *
     * @return String the xsd:dateTime of the last import
     */
    public function getLastImportDateXsd($collectionUrl = null) {

        $imports =  $this->findBy(array('sort_field' => 'initiated', 'sort_dir' => 'd', 'collection' => $collectionUrl));

        if (count($imports) == 0)
            return date("Y-m-d\TH:i:sP", 0);

        foreach ($imports as $key => $import) {

            if ($import->completed != NULL) {
                $date = $import->initiated;
                break;
            }
        }

        return @date("Y-m-d\TH:i:sP", strtotime($date));
    }
}
