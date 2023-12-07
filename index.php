<?php
require_once __DIR__.'/vendor/autoload.php';

if(!empty($_REQUEST['auth']['application_token']) && $_REQUEST['auth']['application_token'] == '568oohxhjj20bk6igh8hrr6skcw2xef7'){
  if(in_array($_REQUEST['event'], ['0' => 'ONCRMCONTACTADD'])){
    $result = invokeBitrixMethod('crm.contact.get.json', ['ID' => $_REQUEST['data']['FIELDS']['ID']]);

    $client = new \Google_Client();
    $client->setApplicationName('Google Sheets API');
    $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
    $client->setAccessType('offline');
    $path = 'credentials.json';
    $client->setAuthConfig($path);

    $service = new \Google_Service_Sheets($client);

    $spreadsheetId = '1ES_Hqy4EAREQPirqYs64fsyUUd7q5Xngz7x6y2ZyGw';
    $spreadsheet = $service->spreadsheets->get($spreadsheetId);
    var_dump($spreadsheet);

    $newRow = [
      $result['result']['NAME'],
      $result['result']['LAST_NAME'] ?: '',
      $result['result']['PHONE'] ? $result['result']['PHONE'][0]['VALUE'] : '',
      $result['result']['EMAIL'] ? $result['result']['EMAIL'][0]['VALUE'] : ''
    ];
    $rows = [$newRow];
    $valueRange = new \Google_Service_Sheets_ValueRange();
    $valueRange->setValues($rows);
    $range = 'Лист1';
    $options = ['valueInputOption' => 'USER_ENTERED'];
    $service->spreadsheets_values->append($spreadsheetId, $range, $valueRange, $options);
  }
}

function invokeBitrixMethod($method, $data){
  $webhook_uri = 'https://b24-lh8sdx.bitrix24.ru/rest/1/exrkgjtych79wv1p/'.$method;
  $query_params = http_build_query($data);
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_POST => 1,
    CURLOPT_HEADER => 0,
    CURLOPT_SSL_VERIFYPEER => 0,
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => $webhook_uri,
    CURLOPT_POSTFIELDS => $query_params,
  ));
  $result = curl_exec($curl);
  curl_close($curl);
  return json_decode($result, 1);
}
