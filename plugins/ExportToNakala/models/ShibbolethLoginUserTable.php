<?php
/**
 * Shibboleth Login
 * 
 * @copyright Copyright 2015-2020 Limonade & Co (Paris)
 * @author Franck Dupont <kyfr59@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * Auth adapter that uses Omeka's users table for authentication.
 * 
 * @package Omeka\Auth
 */
class ShibbolethLogin_Auth_Adapter_UserTable extends Zend_Auth_Adapter_DbTable
{
    /**
     * @param Omeka_Db $db Database object.
     */
    public function __construct(Omeka_Db $db)
    {
        parent::__construct($db->getAdapter(), 
                            $db->User, 
                            'username', 
                            'password', 
                            'SHA1(CONCAT(salt, ?)) OR 1 = 1'); // 'OR 1 = 1' allows the user login whitout password
    }
    

    /**
     * Validate the identity returned from the database.
     *
     * Overrides the Zend implementation to provide user IDs, not usernames
     * upon successful validation.
     *
     * @param array $resultIdentity
     * @todo Should this instead override _authenticateCreateAuthResult()?
     */
    
    protected function _authenticateValidateResult($resultIdentity)
    {
        $authResult = parent::_authenticateValidateResult($resultIdentity);
        if (!$authResult->isValid()) {
            return $authResult;
        }
        // This auth result uses the username as the identity, what we need
        // instead is the user ID.
        $correctResult = new Zend_Auth_Result($authResult->getCode(), $this->_resultRow['id'], $authResult->getMessages());
        return $correctResult;
    }
    
}
