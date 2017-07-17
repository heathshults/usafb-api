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

    // Set Auth0 credentials

    public function setEnvParms()
    {
        putenv("AUTH_CLIENT_ID=ZE6CFuU1opzEeZ5WpDzl1CZZOFrpU3T7");
        putenv("AUTH_CLIENT_SECRET=NuCNaHRUMci8OZFKCKjvZXtAq5j14NZikKLlT - Uz1UE64acsCe7y3_o3tgsAk2Y5");
        putenv("AUTH_AUDIENCE=https://daylen.auth0.com/api/v2/");
        putenv("AUTH_ISS=https://daylen.auth0.com/");
        putenv("AUTH_DOMAIN=daylen . auth0 . com");
        putenv("AUTH_METADATA=http://soccer.com/metadata");
        putenv("AUTH_CONNECTION=Username - Password - Authentication");
    }




}
