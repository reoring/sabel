<?php

/**
 * Sabel_Response_Status
 *
 * @category   Response
 * @package    org.sabel.response
 * @author     Ebine Yutaka <ebine.yutaka@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Sabel_Response_Status extends Sabel_Object
{
  protected $statuses = array(
    // 1xx Informational
    Sabel_Response::COUNTINUE                       => "Countinue",
    Sabel_Response::SWITCHING_PROTOCOLS             => "Switching Protocols",
    // 2xx Successful
    Sabel_Response::OK                              => "OK",
    Sabel_Response::CREATED                         => "Created",
    Sabel_Response::ACCEPTED                        => "Accepted",
    Sabel_Response::NON_AUTHORITATIVE_INFORMATION   => "Non-Authoritative Information",
    Sabel_Response::NO_CONTENT                      => "No Content",
    Sabel_Response::RESET_CONTENT                   => "Reset Content",
    Sabel_Response::PARTIAL_CONTENT                 => "Partial Content",
    Sabel_Response::MULTI_STATUS                    => "Multi-Status",
    // 3xx Redirection
    Sabel_Response::MULTIPLE_CHOICES                => "Multiple Choices",
    Sabel_Response::MOVED_PERMANENTLY               => "Moved Permanently",
    Sabel_Response::FOUND                           => "Found",
    Sabel_Response::SEE_OTHER                       => "See Other",
    Sabel_Response::NOT_MODIFIED                    => "Not Modified",
    Sabel_Response::USE_PROXY                       => "Use Proxy",
    // 4xx Client Errors
    Sabel_Response::BAD_REQUEST                     => "Bad Request",
    Sabel_Response::UNAUTHORIZED                    => "Unauthorized",
    Sabel_Response::PAYMENT_REQUIRED                => "Payment Required",
    Sabel_Response::FORBIDDEN                       => "Forbidden",
    Sabel_Response::NOT_FOUND                       => "Not Found",
    Sabel_Response::METHOD_NOT_ALLOWED              => "Method Not Allowed",
    Sabel_Response::NOT_ACCEPTABLE                  => "Not Acceptable",
    Sabel_Response::PROXY_AUTHENTICATION_REQUIRED   => "Proxy Authentication Required",
    Sabel_Response::REQUEST_TIMEOUT                 => "Request Timeout",
    Sabel_Response::CONFLICT                        => "Conflict",
    Sabel_Response::GONE                            => "Gone",
    Sabel_Response::LENGTH_REQUIRED                 => "Length Required",
    Sabel_Response::PRECONDITION_FAILED             => "Precondition Failed",
    Sabel_Response::REQUEST_ENTITY_TOO_LARGE        => "Request Entity Too Large",
    Sabel_Response::REQUEST_URI_TOO_LONG            => "Request-Uri Too Long",
    Sabel_Response::UNSUPPORTED_MEDIA_TYPE          => "Unsupported Media Type",
    Sabel_Response::REQUESTED_RANGE_NOT_SATISFIABLE => "Requested Range Not Satisfiable",
    Sabel_Response::EXPECTATION_FAILED              => "Expectation Failed",
    Sabel_Response::IM_A_TEAPOT                     => "I'm a teapot",
    // 5xx Server Errors
    Sabel_Response::INTERNAL_SERVER_ERROR           => "Internal Server Error",
    Sabel_Response::NOT_IMPLEMENTED                 => "Not Implemented",
    Sabel_Response::BAD_GATEWAY                     => "Bad Gateway",
    Sabel_Response::SERVICE_UNAVAILABLE             => "Service Unavailable",
    Sabel_Response::GATEWAY_TIMEOUT                 => "Gateway Timeout",
    Sabel_Response::HTTP_VERSION_NOT_SUPPORTED      => "HTTP Version Not Supported",
    Sabel_Response::VARIANT_ALSO_NEGOTIATES         => "Variant Also Negotiates",
    Sabel_Response::INSUFFICIENT_STORAGE            => "Insufficient Storage",
    Sabel_Response::NOT_EXTENDED                    => "Not Extended"
  );
  
  protected $statusCode = Sabel_Response::OK;
  
  public function __construct($statusCode = Sabel_Response::OK)
  {
    $this->statusCode = $statusCode;
  }
  
  public function __toString()
  {
    return $this->statusCode . " " . $this->getReason();
  }
  
  public function getCode()
  {
    return $this->statusCode;
  }
  
  public function setCode($statusCode)
  {
    $this->statusCode = $statusCode;
  }
  
  public function getReason()
  {
    if (isset($this->statuses[$this->statusCode])) {
      return $this->statuses[$this->statusCode];
    } else {
      return "";
    }
  }
  
  public function isSuccess()
  {
    return ((int)floor($this->statusCode / 100) === 2);
  }
  
  public function isRedirect()
  {
    return ((int)floor($this->statusCode / 100) === 3);
  }
  
  public function isClientError()
  {
    return ((int)floor($this->statusCode / 100) === 4);
  }
  
  public function isServerError()
  {
    return ((int)floor($this->statusCode / 100) === 5);
  }
  
  public function isFailure()
  {
    return ($this->statusCode >= 400 && $this->statusCode <= 500);
  }
}
