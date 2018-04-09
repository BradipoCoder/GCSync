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
  /** @var resource */
  private $curl = NULL;

  /** @var array */
  private $curlInfo = array();

  /** @var array */
  private $curlOptions = [
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_SSL_VERIFYHOST => FALSE,
    CURLOPT_SSL_VERIFYPEER => FALSE,
    CURLOPT_COOKIESESSION => FALSE,
    CURLOPT_AUTOREFERER => TRUE,
    CURLOPT_VERBOSE => FALSE,
    CURLOPT_FRESH_CONNECT => TRUE
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
    if (file_exists($this->cookieFilePath))
    {
      $this->curlOptions[CURLOPT_COOKIEJAR] = $this->cookieFilePath;
      $this->curlOptions[CURLOPT_COOKIEFILE] = $this->cookieFilePath;
    }
    curl_setopt_array($this->curl, $this->curlOptions);
  }

  /**
   * @param string $strUrl
   * @param array  $arrParams
   * @param bool   $bolAllowRedirects
   * @return integer
   */
  public function get($strUrl, $arrParams = array(), $bolAllowRedirects = TRUE)
  {
    if (count($arrParams))
    {
      $strUrl .= '?' . http_build_query($arrParams);
    }

    curl_setopt($this->curl, CURLOPT_URL, $strUrl);
    curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, (bool) $bolAllowRedirects);
    curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'GET');
    $response = curl_exec($this->curl);
    $info = curl_getinfo($this->curl);

    $this->setCurlInfo($info, $response);

    return intval($info['http_code']);
  }

  /**
   * @param string $strUrl
   * @param array  $arrParams
   * @param array  $arrData
   * @param bool   $bolAllowRedirects
   * @return integer
   */
  public function post($strUrl, $arrParams = array(), $arrData = array(), $bolAllowRedirects = TRUE)
  {

    curl_setopt($this->curl, CURLOPT_HEADER, TRUE);
    //curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, TRUE);
    curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, (bool) $bolAllowRedirects);
    curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "POST");
    if (count($arrData))
    {
      curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
      curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($arrData));
    }
    $strUrl .= '?' . http_build_query($arrParams);
    curl_setopt($this->curl, CURLOPT_URL, $strUrl);
    $response = curl_exec($this->curl);
    $info = curl_getinfo($this->curl);

    $this->setCurlInfo($info, $response);

    return intval($info['http_code']);
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