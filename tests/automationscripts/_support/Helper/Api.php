<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Api extends \Codeception\Module
{
    /**
     * Function to clear Authorization header
     */
    public function clearAuthorizationHeaders()
    {
        unset($this->getModule('REST')->headers['Authorization']);
        unset($this->getModule('PhpBrowser')->headers['Authorization']);

    }


}
