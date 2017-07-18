<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

   /**
    * Define custom actions here
    */

   // Set HTTP Headers

    public function setHeaders()
    {
        $this->haveHttpHeader('Content-Type', 'application/json');
        $this->haveHttpHeader('Accept', 'application/json');
        $this->haveHttpHeader('Accept-Language', 'en-US');

    }

    //Clear Http Headers

    public function clearHeaders()
    {
        $this->deleteHeader('Content-Type');
        $this->deleteHeader('Accept');
        $this->deleteHeader('Accept-Language');

    }


}
