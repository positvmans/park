<?php

define('REDISPATH', realpath(__DIR__ . '/..'));

require REDISPATH.'/async-swoole/Common/RedisSdk.class.php';

$server =   new swoole_websocket_server("0.0.0.0", 9443, SWOOLE_BASE);
$redisMessage =   new RedisSdk();

/*监听UDP包*/
//$server->addlistener('0.0.0.0', 9502, SWOOLE_SOCK_UDP);
$server->set(
    [
        'worker_num' => 1,
        'task_worker_num' => 1,
        //'daemonize' => true,
    ]);

$server->on('open', function (swoole_websocket_server $_server, swoole_http_request $request) {
    echo "server#{$_server->worker_pid}: handshake success with fd#{$request->fd}\n";

});

$server->on('message', function (swoole_websocket_server $server, $frame) {
    $server->task($frame->data);
});

$server->on('task', function ($server, $worker_id, $task_id, $data) use($redisMessage)
{
    echo "\n message: " . $data . "\n";
    echo  "\n task_id:" .$task_id. "\n";
    dataMessage($redisMessage,$data);
    $server->finish($data);
});

$server->on('finish', function ($server, $task_id, $result)
{
    foreach($server->connections as $fd)
    {
        $server->push($fd, $result);
    }
});

//$server->on('packet', function ($_server, $data, $client) {
//    echo "#".posix_getpid()."\tPacket {$data}\n";
//    var_dump($client);
//});


$server->on('close', function ($_server, $fd) {
    echo "client {$fd} closed\n";
});


function dataMessage($redisMessage,$data){

    $token    =   '1b234e95bbca';
    $redis_key    =   ['key'=>'id'    ,   'field'=>'fieldvalue'];
    $result_decode  =   json_decode($data,  true);
    foreach($result_decode  as  $k=>$v){
        $ParkEndArray[$k]['d']   =   $v['d'];
        $ParkEndArray[$k]['f']   =   $v['f'];
        $ParkEndArray[$k]['x']   =   $v['x'];
        $ParkEndArray[$k]['y']   =   $v['y'];
        $ParkEndArray[$k]['o']   =   $v['o'];
        $ParkEndArray[$k]['t']   =   $v['t'];
    }
    //redis存储
    $numbers    =   $redisMessage->get('auto_increment'.$ParkEndArray[0]['d']);
    if($numbers) {
        $redisMessage->pipeline();
        $redisMessage->hget_json($redis_key, $token . $ParkEndArray[0]['d'].$numbers, $ParkEndArray[0]['d'].$numbers);
        $redis_hget_json = $redisMessage->redisexec();
        if (false === $redis_hget_json[0]) {
            $redisMessage->hset_json($redis_key, $token . $ParkEndArray[0]['d'].$numbers, $ParkEndArray[0]['d'].$numbers, $ParkEndArray);
            $redisMessage->redisexec();
        } else {
            $redisMessage->hset_json($redis_key, $token . $ParkEndArray[0]['d'].$numbers, $ParkEndArray[0]['d'].$numbers, array_merge(json_decode($redis_hget_json[1],true), $ParkEndArray));
            $redisMessage->redisexec();
        }
    }
}

$server->start();