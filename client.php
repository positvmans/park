<?php
//define('REDISPATH', realpath(__DIR__ . '/..'));
//
//require REDISPATH.'/swoole/Common/RedisSdk.class.php';
//$token    =   '1b234e95bbca';
//$redis_key    =   ['key'=>'id'    ,   'field'=>'fieldvalue'];
//$redisMessage =   new RedisSdk();
//
//$numbers    =   $redisMessage->get('auto_increment7');
//$data   =   $redisMessage->hget_json($redis_key, $token . '7'.$numbers, '7'.$numbers);
//echo $data;
//echo "<pre>";print_r($data);
require __DIR__ . "/Common/WebSocketClient.php";
$host = '127.0.0.1';
$prot = 9443;
$jsonarray  =   '[{"f":0,"x":10045,"y":7487,"d":"7"},{"f":1,"x":10041,"y":7486,"d":"7"},{"f":1,"x":10037,"y":7484,"d":"7"},
{"f":1,"x":10031,"y":7482,"d":"7"},{"f":1,"x":10023,"y":7480,"d":"7"},{"f":1,"x":10013,"y":7476,"d":"7"},{"f":1,"x":10000,"y":7471,"d":"7"},
{"f":1,"x":9984,"y":7466,"d":"7"},{"f":1,"x":9965,"y":7460,"d":"7"},{"f":1,"x":9947,"y":7454,"d":"7"}]';
$client = new WebSocketClient($host, $prot);
$data = $client->connect();
//echo $data;
$json_piop  =   json_decode($jsonarray,true);
echo "<pre>";print_r($json_piop);die;
$time_a =   microtime_float();
foreach($json_piop as $k=>$v) {
    $client->send("hello swoole, number:" . 1 . " data:" . $data);
}
$time_b = microtime_float();
echo $time_a    -   $time_b;


function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

//    $recvData = "";
//
//    $tmp = $client->recv();
//    $recvData .= $tmp;
//    echo $recvData . "size:" . strlen($recvData) . PHP_EOL;

