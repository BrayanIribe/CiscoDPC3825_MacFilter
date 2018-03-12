<?php

class HTTP_POST_REQUEST{
  public $url;
  public $params;
  public $result;
  public $cookies;

  public function getHeader($header,$headers) {
    $response = explode("\n",$headers);
    foreach ($response as $key => $r) {
      // Match the header name up to ':', compare lower case
      if (stripos($r, $header . ':') === 0) {
        list($headername, $headervalue) = explode(":", $r, 2);
        return trim($headervalue);
      }
    }
  }

  public function execute(){
    $url = $this->url;
    $fields = $this->params;
    $fields_string = '';
    //url-ify the data for the POST
    foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
    $fields_string = rtrim($fields_string,'&');

    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_POST,count($fields));
    curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, false);
    if (strlen($this->cookies) > 0)
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: " . $this->cookies));

    $response = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    preg_match_all('/^Set-Cookie:\s*([^\r\n]*)/mi', $response, $ms);
    $cookies = array();
    foreach ($ms[1] as $m) {
      list($name, $value) = explode('=', $m, 2);
      $cookies[$name] = $value;
    }
    $this->result = array(
      'header' => $header,
      'body' => $body,
      'cookies' => $cookies
    );
  }

  public function __construct($url){
    $this->url = $url;
  }
}
