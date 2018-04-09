<?php
/**
 * Created by Adam Jakab.
 * Date: 06/04/18
 * Time: 11.56
 *
 *
 * RESOURCES:
 * http://sergeykrasnov.ru/subsites/dev/garmin-connect-statisics/
 *
 * https://connect.garmin.com/proxy/user-service-1.0/
 * https://github.com/cpfair/tapiriik
 * https://github.com/cpfair/tapiriik/blob/master/tapiriik/services/GarminConnect/garminconnect.py
 *
 */

namespace Abj\Platform\Garmin;

use Abj\Logger\ConsoleLogger;
use Abj\Platform\Platform;
use Abj\Platform\PlatformInterface;
use Abj\Platform\Garmin\Transport\GarminTransport;


class GarminConnect extends Platform implements PlatformInterface
{
  const DATA_TYPE_TCX = 'tcx';
  const DATA_TYPE_GPX = 'gpx';
  const DATA_TYPE_GOOGLE_EARTH = 'kml';

  /**
   * @var GarminTransport
   */
  private $transport;


  /**
   * GarminConnect constructor.
   *
   * @param $username
   * @param $password
   *
   * @throws \Exception
   */
  public function __construct($username, $password)
  {

    if (!$username || empty($username))
    {
      throw new \Exception("Username is missing");
    }

    if (!$password || empty($password))
    {
      throw new \Exception("Password is missing");
    }

    $this->transport = new GarminTransport(md5($username));

    // If we can validate the cached auth, we don't need to do anything else
    if (!$this->checkCookieAuth())
    {
      $this->authenticate($username, $password);
    }
  }



  /**
   * Because there doesn't appear to be a nice "API" way to authenticate with Garmin Connect, we have to effectively spoof
   * a browser session using some pretty high-level scraping techniques. The connector object does all of the HTTP
   * work, and is effectively a wrapper for CURL-based session handler (via CURLs in-built cookie storage).
   *
   * @param string $username
   * @param string $password
   * @throws \Exception
   */
  private function authenticate($username, $password)
  {

    /*
     * service=https://connect.garmin.com/modern/
     * webhost=https://connect.garmin.com
     * source=https://connect.garmin.com/it-IT/signin
     * redirectAfterAccountLoginUrl=https://connect.garmin.com/modern/
     * redirectAfterAccountCreationUrl=https://connect.garmin.com/modern/
     * gauthHost=https://sso.garmin.com/sso
     * locale=it_IT
     * id=gauth-widget
     * cssUrl=https://static.garmincdn.com/com.garmin.connect/ui/css/gauth-custom-v1.2-min.css
     * privacyStatementUrl=//connect.garmin.com/it-IT/privacy/
     * clientId=GarminConnect
     * rememberMeShown=true
     * rememberMeChecked=false
     * createAccountShown=true
     * openCreateAccount=false
     * displayNameShown=false
     * consumeServiceTicket=false
     * initialFocus=true
     * embedWidget=false
     * generateExtraServiceTicket=false
     * generateNoServiceTicket=false
     * globalOptInShown=true
     * globalOptInChecked=false
     * mobile=false
     * connectLegalTerms=true
     * locationPromptShown=true
     */

    $authUri = "https://sso.garmin.com/sso";

    $serviceUri = "https://connect.garmin.com/modern/";

    $urlParams = [
      'service' => $serviceUri,
      'redirectAfterAccountLoginUrl' => $authUri. "/login",
      'gauthHost' => $authUri,
      'clientId' => 'GarminConnect',
      'consumeServiceTicket' => 'false'
    ];

    $postData = [
      "username" => $username,
      "password" => $password,
      "_eventId" => "submit",
      "displayNameRequired" => "false"
    ];

    $resCode = $this->transport->post($authUri . "/login", $urlParams, $postData, FALSE);

    if ($resCode != 302)
    {
      throw new \Exception("SSO network error - expected 302");
    }

    $info = $this->transport->getCurlInfo();
    ConsoleLogger::log($info);


    if (!preg_match('#Set-Cookie: GARMIN-SSO=1; Domain=garmin.com; Path=/#i', $this->transport->getCurlInfo("response")))
    {
      throw new \Exception("SSO sign in failed.");
    }

    ConsoleLogger::log("logged in with username: " . $username);
  }

  //  //Request URL: https://connect.garmin.com/sso-signout/?_=1523023088138

  /**
   * Try to read the username from the API - if successful, it means we have a valid cookie, and we don't need to auth
   * @todo: find a more solid check for this
   *
   * @return bool
   */
  protected function checkCookieAuth()
  {
    $answer = FALSE;

    /*
    if (!empty(trim($this->getUsername()))) {
      $answer = true;
    } else {
      $this->transport->cleanupSession();
      $this->transport->refreshSession();
    }
    */

    return $answer;
  }

  //https://connect.garmin.com/proxy/workout-service-1.0/json/workoutlist

  /**
   * @return mixed
   * @throws \Exception
   */
  public function getUsername()
  {
    $strResponse = $this->transport->get('https://connect.garmin.com/user/username');

    if ($this->transport->getLastResponseCode() != 200)
    {
      throw new \Exception($this->transport->getLastResponseCode());
    }
    $objResponse = json_decode($strResponse);
    return $objResponse->username;
  }
}