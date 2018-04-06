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
  /** @var null|resource */
  private $objCurl = NULL;

  /** @var array */
  private $arrCurlInfo = array();

  /** @var array */
  private $arrCurlOptions = [
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_SSL_VERIFYHOST => FALSE,
    CURLOPT_SSL_VERIFYPEER => FALSE,
    CURLOPT_COOKIESESSION => FALSE,
    CURLOPT_AUTOREFERER => TRUE,
    CURLOPT_VERBOSE => FALSE,
    CURLOPT_FRESH_CONNECT => TRUE
  ];

  /**
   * @var int
   */
  private $intLastResponseCode = -1;

  /**
   * @var string
   */
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
    $this->objCurl = curl_init();
    $this->arrCurlOptions[CURLOPT_COOKIEJAR] = $this->cookieFilePath;
    $this->arrCurlOptions[CURLOPT_COOKIEFILE] = $this->cookieFilePath;
    curl_setopt_array($this->objCurl, $this->arrCurlOptions);
  }

  /**
   * @param string $strUrl
   * @param array  $arrParams
   * @param bool   $bolAllowRedirects
   * @return mixed
   */
  public function get($strUrl, $arrParams = array(), $bolAllowRedirects = TRUE)
  {
    if (count($arrParams))
    {
      $strUrl .= '?' . http_build_query($arrParams);
    }

    curl_setopt($this->objCurl, CURLOPT_URL, $strUrl);
    curl_setopt($this->objCurl, CURLOPT_FOLLOWLOCATION, (bool) $bolAllowRedirects);
    curl_setopt($this->objCurl, CURLOPT_CUSTOMREQUEST, 'GET');

    $strResponse = curl_exec($this->objCurl);
    $arrCurlInfo = curl_getinfo($this->objCurl);
    $this->intLastResponseCode = $arrCurlInfo['http_code'];
    return $strResponse;
  }

  /**
   * @param string $strUrl
   * @param array  $arrParams
   * @param array  $arrData
   * @param bool   $bolAllowRedirects
   * @return mixed
   */
  public function post($strUrl, $arrParams = array(), $arrData = array(), $bolAllowRedirects = TRUE)
  {

    curl_setopt($this->objCurl, CURLOPT_HEADER, TRUE);
    curl_setopt($this->objCurl, CURLOPT_FRESH_CONNECT, TRUE);
    curl_setopt($this->objCurl, CURLOPT_FOLLOWLOCATION, (bool) $bolAllowRedirects);
    curl_setopt($this->objCurl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($this->objCurl, CURLOPT_VERBOSE, FALSE);
    if (count($arrData))
    {
      curl_setopt($this->objCurl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
      curl_setopt($this->objCurl, CURLOPT_POSTFIELDS, http_build_query($arrData));
    }
    $strUrl .= '?' . http_build_query($arrParams);

    curl_setopt($this->objCurl, CURLOPT_URL, $strUrl);

    $strResponse = curl_exec($this->objCurl);
    $this->arrCurlInfo = curl_getinfo($this->objCurl);
    $this->intLastResponseCode = (int) $this->arrCurlInfo['http_code'];
    return $strResponse;
  }

  /**
   * @return array
   */
  public function getCurlInfo()
  {
    return $this->arrCurlInfo;
  }

  /**
   * @return int
   */
  public function getLastResponseCode()
  {
    return $this->intLastResponseCode;
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
    curl_close($this->objCurl);
    $this->clearCookie();
  }
}