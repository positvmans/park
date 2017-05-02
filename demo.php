<?php if (!defined('APP_PATH')) exit(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>在线答题</title>
    <script src='http://cdn.bootcss.com/socket.io/1.3.7/socket.io.js'></script>
    <script src="__JS__jquery-1.9.1.js"></script>
    <style>
        body{
            width: 100%;
            height: 100%;
            background-image: url('__IMAGE__bluetooth/bj.jpg');
            -webkit-background-size:cover;
        }
        .cvsdiv{
            display:inline-block;
            /*margin-right: 120px;*/
            margin-top:20px;
            /*margin-left: calc((100% - 306*4px)/5);*/
            border-width: 4px;
            border-color: #d6e1e5;
            border-style:solid;
            padding-bottom: -30px;
        }
        span{
            font-size:28px;
            font-family: "Microsoft YaHei" !important;
            display:block;
            text-align: center;
            background-color: #d6e1e5;
        }
        .cvscount{
            background-color: white;
        }
    </style>
</head>
<body>
<volist name="logindata" id="vo">
    <div class="cvsdiv">
        <span width="100%">{$vo.class_name}</span>
        <canvas id="cvs{$vo.id}" class="cvscount">第{$vo.id}个画布</canvas>
    </div>
</volist>
</body>

<script type="text/javascript">

    var lastX = 0;
    var lastY = 0;
    var canvas_h=[];
    var pagewidth = $('body').width();
    var pageheight = $('body').height();
    console.log(pagewidth+'-----'+pageheight);

    function meu_conert(){
        var widths  =  $('body').width();
        var height  =  $('body').height();
        var new_h   =   widths/0.57;
        if(height>new_h){
            canvas_h.push(wdiths);
            canvas_h.push(new_h);
            canvas_h.push(wdiths/8190);
        }else if(height<new_h){
            var new_w   =   height*0.57;
            canvas_h.push(new_w);
            canvas_h.push(height);
            canvas_h.push(new_w/8190);
        }
        return canvas_h;
    }
    console.log(meu_conert());
    <?php foreach($logindata as $kp=>$vp){?>
    if(pagewidth==1920 && pageheight==1080) {
        var width = 298;
        var height = 483;
        $('.cvsdiv').css('margin-left','calc((100% - 306*4px)/5)');
        getwidth('cvs<?php echo $vp['id'];?>',width,height);
    }else if(pagewidth==1280 && pageheight==720){
        var width = 195;
        var height = 340;
        $('.cvsdiv').css('margin-left','calc((100% - 203*4px)/5)');
        getwidth('cvs<?php echo $vp['id'];?>',width,height);
    }else{
        var width = 298;
        var height = 483;
        $('.cvsdiv').css('margin-left','calc((100% - 306*4px)/5)');
        getwidth('cvs<?php echo $vp['id'];?>',width,height);
    }
    <?php }?>
    function getwidth(cvs,width,height) {
        cvs = document.getElementById(cvs);
        cvs.height = height;
        cvs.width = width;
    }

    //演示
    //    var socket = io('http://120.27.150.70:2122');
    //线上
    //var socket=io('http://120.27.161.243:2122');
    //本地
    var socket = io('http://192.168.1.20:2122');

    socket.on('connect', function () {});

    socket.on('new_msg', function (obj) {

        var json1 = eval("(" + obj + ")");

        for(var i=0; i<json1.length; i++ ) {
            var json=json1[i];
            var fg = parseInt(json['f']);
            var id = parseInt(json['d']);
            var ox = parseInt(json['x']*0.036);
            var oy = parseInt(json['y']*0.036);
            ox=483-ox;
            if(fg == 0){
                lastX = oy;
                lastY = ox;

                draw(fg,oy,ox,id);
                console.log("f = 0 :"+lastX+"-"+lastY);

            }else{
                if(ox == lastX && oy == lastY){
                    return;
                }
                console.log("fg = 1 :"+ox+"-"+oy+"-"+id);
                draw(fg,oy,ox,id);
            }
        }
    });

    function draw(fg , dx, dy, id) {
        <?php foreach($logindata as $k=>$v){?>
        if(id == <?php echo $v['id']?>){
            var cvs<?php echo $v['id']?> = document.getElementById('cvs<?php echo $v['id']?>');
            var context<?php echo $v['id']?> = cvs<?php echo $v['id']?>.getContext('2d');
            review(fg,dx,dy,context<?php echo $v['id']?>)
        }
        <?php }?>
    }

    function review(fg,dx,dy,context){
        context.lineWidth = 1;
        context.strokeStyle = "#000000"; //线条颜色
        context.beginPath();
        if(fg==0){
            context.moveTo(dx, dy);
        }else {
            context.moveTo(lastX, lastY);
        }
        context.lineTo(dx, dy);
        context.closePath();
        context.stroke();

        lastX = dx;
        lastY = dy;
    }

</script>

</html>