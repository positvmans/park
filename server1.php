<?php
/**
 * Created by PhpStorm.
 * User: fanweifeng
 * Date: 17/3/17
 * Time: 09:03
 */
//连接本地的 Redis 服务

define('REDISPATH', realpath(__DIR__ . '/..'));

require REDISPATH.'/async-swoole/Common/RedisSdk.class.php';
$redis = new RedisSdk();
//$redis->connect('127.0.0.1', 6379);
$redis->set("fd", "[]");    //每次第一次执行都需要先清空reids里面的标识

$serv = new Swoole\Websocket\Server("0.0.0.0", 9443);

$serv->set(
    [
        'worker_num' => 4,
        'task_worker_num' => 1,
        'daemonize' => true,
    ]);

//向redis中添加fd数据
$serv->on('Open', function ($server, $req) use($redis) {
    echo "\n connection open: " . $req->fd . "\n";
    $str = json_decode($redis->get("fd"), true);
    if($str == "") $str = [];
    if(!in_array($req->fd, $str)){
        array_push($str, $req->fd);  //压入数组
        $str = json_encode($str);
        $redis->set("fd", $str);
        //print_r($redis->get("fd"));
    }
});
//将redis中存的所有fd取出，然后push(admin本身不push)
$serv->on('Message', function ($server, $frame){
    echo "\n message: " . $frame->data . "\n";
    $server->task($frame->data);
});

$serv->on('task', function ($server, $worker_id, $task_id, $data)
{
//    echo 'worker_id----->'.$worker_id.'task_id------>'.$task_id.'\n';
    $server->finish($data);
});

$serv->on('finish', function ($server, $task_id, $result) use($redis)
{
    dataMessage($redis,$result);
    $str = json_decode($redis->get("fd"), true);
    foreach ($str as $key => $value) {
        $server->push($value, $result);
    }

});

//客户端连接关闭，删除其在redis对应的fd
$serv->on('Close', function ($server, $fd) use($redis) {
    //echo "\n connection close: \n" . $fd;
    $str = json_decode($redis->get("fd"), true);
    $point = array_keys($str, $fd, true);  //search key
    array_splice($str, $point['0'],1); //数组中删除
    $redis->set("fd", json_encode($str));
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


$serv->start();