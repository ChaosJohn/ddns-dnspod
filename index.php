<?php

require __DIR__ . '/vendor/autoload.php';

use Curl\Curl;
$curl = new Curl();

$ini_array = parse_ini_file('config.ini', true);

$token = $ini_array['config']['token']; // dnspod token
$required_key = $ini_array['config']['key'];

//echo $_SERVER['HTTP_X_REAL_IP'];
$address = $_SERVER['REMOTE_ADDR'];
$isIPv6 = strpos($address, ':') > -1;
$type = $isIPv6 ? 'AAAA' : 'A';
$domain = $_GET['domain'];
$sub = $_GET['sub'];
$key = $_GET['key'];

if (!$domain || !$sub || 0 != strcmp($key, $required_key)) exit;

$output = [
  'domain' => $domain,
  'sub' => $sub,
  'type' => $type,
  'address' => $address
];

$form = "login_token=${token}&format=json&domain=${domain}&sub_domain=${sub}&record_type=${type}&record_line_id=0";
$response = $curl->post('https://dnsapi.cn/Record.List', $form);
if ($curl->error) {
  $output['msg'] = 'Record.List Error' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
} else {
  $records = $response->records;  
  if (null == $records) $records = [];
  $records = array_filter($records, function($record) use($type) { 
    return 0 == strcmp($record->type, $type); 
  });
  
  if (count($records) > 0) { // if record exists
    $record = $records[0];
    if (0 == strcmp($record->value, $address)) { // skip if same
      $output['msg'] = 'Same record. Skipped';
      echo json_encode($output);
      exit;
    }

    // update record
    $record_id = $record->id;
    $form = "login_token=${token}&format=json&domain=${domain}&record_id=${record_id}&sub_domain=${sub}&value=${address}&record_type=${type}&record_line_id=0";
    $response = $curl->post('https://dnsapi.cn/Record.Modify', $form);
    if ($curl->error) {
      $output['msg'] = 'Record.Modify Error' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
    } else {
      if (null != $response->status && null != $response->status->code && '1' == $response->status->code) {
        $output['msg'] = 'Modification succeeded';
      } else {
        $output = $response;
        $output->action = 'Record.Modify';
      }
    }
  } else { // record not exists => create 
    $form = "login_token=${token}&format=json&domain=${domain}&sub_domain=${sub}&record_type=${type}&record_line_id=0&value=${address}";
    $response = $curl->post('https://dnsapi.cn/Record.Create', $form);
    if ($curl->error) {
      $output['msg'] = 'Record.Create Error' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
    } else {
      if (null != $response->status && null != $response->status->code && '1' == $response->status->code) {
        $output['msg'] = 'Creation succeeded';
      } else {
        $output = $response;
        $output->action = 'Record.Create';
      }
    }
  }
}
echo json_encode($output, JSON_UNESCAPED_UNICODE);
