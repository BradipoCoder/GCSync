<?php
/**
 * Created by Adam Jakab.
 * Date: 06/04/18
 * Time: 12.02
 */

namespace Abj\Platform\Garmin\Transport;


use Abj\Logger\ConsoleLogger;

class GarminTransport
{
  const AUTH_URL = 'https://sso.garmin.com/sso';
  const BASE_URL = 'https://connect.garmin.com/proxy';

  /** @var resource */
  private $curl = NULL;

  /** @var array */
  private $curlInfo = array();

  /** @var array */
  private $curlOptions = [
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_SSL_VERIFYHOST => FALSE,
    CURLOPT_SSL_VERIFYPEER => FALSE,
    CURLOPT_COOKIESESSION => TRUE,
    CURLOPT_AUTOREFERER => TRUE,
    CURLOPT_VERBOSE => FALSE,
    /*CURLOPT_FRESH_CONNECT => TRUE*/
  ];


  /** @var string */
  private $cookieFilePath = '';


  /**
   * GarminTransport constructor.
   * @param $uniqueIdentifier
   * @throws \Exception
   */
  public function __construct($uniqueIdentifier)
  {
    if (empty(trim($uniqueIdentifier)))
    {
      throw new \Exception("Invalid identifier");
    }

    $this->cookieFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "GarminCookie_" . $uniqueIdentifier . ".txt";
    ConsoleLogger::log("CookiePath: " . $this->cookieFilePath);

    $this->refreshSession();
  }

  /**
   * Create a new curl instance
   */
  public function refreshSession()
  {
    $this->curl = curl_init();
    //if (file_exists($this->cookieFilePath))
    //{
      $this->curlOptions[CURLOPT_COOKIEJAR] = $this->cookieFilePath;
      $this->curlOptions[CURLOPT_COOKIEFILE] = $this->cookieFilePath;
    //}
    curl_setopt_array($this->curl, $this->curlOptions);
  }


  /**
   * @param string $uri
   * @param string $service
   * @param string $mountPoint
   * @param array  $params
   * @param string $mimeType
   * @return string
   */
  protected function buildUri($uri, $service, $mountPoint, $params = [], $mimeType = 'json')
  {
    $answer = $uri;

    $answer .= $service ? '/' . $service : '';

    $answer .= $mimeType ? '/' . $mimeType : '';

    $answer .= '/' . $mountPoint;

    if (count($params))
    {
      $answer .= '?' . http_build_query($params);
    }

    return $answer;
  }

  /**
   * @param string $service
   * @param string $mountPoint
   * @param array  $params
   * @param bool   $allowRedirects
   * @param string $mimeType
   *
   * @return integer
   */
  public function get($service, $mountPoint, $params = [], $allowRedirects = TRUE, $mimeType = 'json')
  {
    $uri = $this->buildUri(self::BASE_URL, $service, $mountPoint, $params, $mimeType);

    echo "using uri: " . $uri;

    curl_setopt($this->curl, CURLOPT_URL, $uri);
    curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, (bool) $allowRedirects);
    curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'GET');
    $response = curl_exec($this->curl);
    $info = curl_getinfo($this->curl);

    $this->setCurlInfo($info, $response);

    return intval($info['http_code']);
  }

  /**
   * @param string $service
   * @param string $mountPoint
   * @param array  $params
   * @param array  $data
   * @param bool   $allowRedirects
   * @param string $mimeType
   *
   * @return int
   */
  public function post($service, $mountPoint, $params = [], $data = [], $allowRedirects = TRUE, $mimeType = 'json')
  {
    $uri = $this->buildUri(self::BASE_URL, $service, $mountPoint, $params, $mimeType);

    curl_setopt($this->curl, CURLOPT_URL, $uri);
    curl_setopt($this->curl, CURLOPT_HEADER, TRUE);
    //curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, TRUE);
    curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, (bool) $allowRedirects);
    curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "POST");
    if (count($data))
    {
      curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
      curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    $response = curl_exec($this->curl);
    $info = curl_getinfo($this->curl);

    $this->setCurlInfo($info, $response);

    return intval($info['http_code']);
  }

  /**
   * @param string $username
   * @param string $password
   *
   * @throws \Exception
   */
  public function authenticate($username, $password)
  {
    $urlParams = [
      'service' => 'https://connect.garmin.com/modern/',
      'redirectAfterAccountLoginUrl' => 'https://connect.garmin.com/modern/',
      'gauthHost' => self::AUTH_URL,
      'clientId' => 'GarminConnect',
      'consumeServiceTicket' => 'false'
    ];

    $postData = [
      "username" => $username,
      "password" => $password,
      "_eventId" => "submit",
      "displayNameRequired" => "false"
    ];

    $uri = $this->buildUri(self::AUTH_URL, NULL, "login", $urlParams, NULL);

    curl_setopt($this->curl, CURLOPT_URL, $uri);
    curl_setopt($this->curl, CURLOPT_HEADER, TRUE);
    //curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, TRUE);
    curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, FALSE);
    curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($postData));
    $response = curl_exec($this->curl);
    $info = curl_getinfo($this->curl);

    $this->setCurlInfo($info, $response);
    $resCode = intval($info['http_code']);

    if ($resCode != 302)
    {
      throw new \Exception("SSO network error - expected 302");
    }

    if (!preg_match('#Set-Cookie: GARMIN-SSO=1; Domain=garmin.com; Path=/#i', $response))
    {
      throw new \Exception("SSO sign in failed.");
    }
  }

  /**
   * @param string $key
   * @param mixed  $default
   * @return mixed
   */
  public function getCurlInfo($key = NULL, $default = NULL)
  {
    $answer = $this->curlInfo;
    if ($key)
    {
      $answer = array_key_exists($key, $this->curlInfo) ? $this->curlInfo[$key] : $default;
    }

    return $answer;
  }

  /**
   * @param array  $info
   * @param string $response
   */
  protected function setCurlInfo($info, $response = "")
  {
    $this->curlInfo = $info;
    $this->curlInfo["response"] = $response;
  }

  /**
   * Removes the cookie
   */
  public function clearCookie()
  {
    if (file_exists($this->cookieFilePath))
    {
      unlink($this->cookieFilePath);
    }
  }

  /**
   * Closes curl and then clears the cookie.
   */
  public function cleanupSession()
  {
    curl_close($this->curl);
    $this->clearCookie();
  }
}