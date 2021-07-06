<?php

/***********************************************

安装分为3个步骤：

1.把 codes.php add.php invite.php 三个文件上传至你网站的根目录；

include('codes.php');
2.将以上一行代码添加到你网站的index.php中；

3.宝塔创建数据库 faka_code，登录 phpmyadmin 执行以下一行 SQL 安装数据库；
CREATE TABLE `faka_code`.`codes` ( `code_num` VARCHAR(30) NOT NULL , `code_type` VARCHAR(30) NOT NULL , `cycle_date` VARCHAR(30) NOT NULL , `count_num` VARCHAR(30) NOT NULL , `used_time` VARCHAR(30) NOT NULL , PRIMARY KEY (`code_num`)) ENGINE = InnoDB;

安装完毕。在index.php中可以通过 <?php echo $tips; ?> 调用显示卡密期限


=================================================


删除过期的 日租/小时租
SELECT * FROM codes WHERE code_type = 'cycle' AND TO_DAYS(NOW()) - TO_DAYS(used_time) >= 2 AND cycle_date < 26;
DELETE FROM codes WHERE code_type = 'cycle' AND TO_DAYS(NOW()) - TO_DAYS(used_time) >= 2 AND cycle_date < 26;


删除过期的 次卡
SELECT * FROM codes WHERE code_type = 'count' AND count_num <1;
DELETE FROM codes WHERE code_type = 'count' AND count_num <1;


===================================================

数据库结构：

数据库：faka_code
字符型：VARCHAR 30
表：codes

表内容：
code_num PRIMARY KEY
code_type
count_num
cycle_date
used_time

*************************************************/


// 填写数据库信息
$sql_host = 'localhost';
$sql_name = 'faka_code';
$sql_user = 'faka_code';
$sql_pass = 'qwer1234';


ini_set('display_errors','off'); //关闭报错

// 验证来源密码
$from_set = md5("maoobai");
$get_from = md5($_POST["from"]);
if ($from_set != $get_from) {$get_from = $_COOKIE['get_from'];}
if ($from_set != $get_from) {header('Location: /invite.php'); die;}
setcookie('get_from',$get_from,time()+30*24*60*60,'/');


// 验证卡密是否空
$code_num = $_POST["code_num"];
if (empty($code_num)) {$code_num = $_COOKIE['code_num'];}
if (empty($code_num)) {header('Location: /invite.php'); die;}
// 去掉卡密多余空格
$code_num = str_replace(" ","",$code_num);
setcookie('code_num',$code_num,time()+24*60*60,'/');

try {
// 填写数据库信息 连接数据库
    $pdo = new PDO ("mysql:host=$sql_host;dbname=$sql_name",$sql_user,$sql_pass);

    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $pdo->exec('set names "utf8"');

} catch (PDOException $e) {
    exit("<p>数据库连接失败:</p>".$e -> getMessage());
}


// 开始判断 卡密是否为包日/月/年 类型
$code_num_b = "cycle_".$code_num;
// 要执行的sql
$sql = "SELECT * FROM codes WHERE code_num='$code_num_b';";
// 执行sql
$result = $pdo->prepare($sql);
// 将查询结果生成数组
if ($result->execute()) {
    while ($row = $result->fetch()) {
        $get_code_num = $row["code_num"];
        $get_code_type = $row["code_type"];
        $get_count_num = $row["count_num"];
        $get_cycle_date = $row["cycle_date"];
        $get_used_time = $row["used_time"];
    }
}

if ($get_code_type == 'cycle' && !empty($get_cycle_date)) {

    if (empty($get_used_time)) {
        $used_time = date("Y-m-d H:i:s");
        // 首次使用 数据库标记初次使用时间
        $sql = "REPLACE INTO codes (code_num, code_type, count_num, cycle_date, used_time) VALUES ('$get_code_num', '$get_code_type', '$get_count_num', '$get_cycle_date', '$used_time');";
        $affected = $pdo->exec($sql);
        $pass_hour = 1;

        $cycle_date = $get_cycle_date - 1;

        // 小米推送激活通知
        //$post_data[title] = "卡密已激活: ".$code_num;
        //$post_data[msg] = "[有效期:".$cycle_date."h] 时间:".$used_time;
        //curl_post_api($post_data);

    }else{
        // 非首次 计算是否到期
        $get_time_now = date("Y-m-d H:i:s");

        $pass_time = strtotime($get_time_now) - strtotime($get_used_time); //计算卡密已用时间
        $pass_hour = ceil($pass_time/(60*60)); //换算成小时 并取整

        if ($pass_hour > $get_cycle_date) {echo "<p>卡密已过期或可用次数已耗尽！<br>点我 <a href='./invite.php'>重新验证</a></p>"; setcookie('code_num','',0); die;}
    }

$shengyu = $get_cycle_date - $pass_hour;
$tips = "剩余:".$shengyu."小时";

return;
}


// 开始判断 卡密是否为 次卡
$code_num_c = "count_".$code_num;
// 要执行的sql
$sql = "SELECT * FROM codes WHERE code_num='$code_num_c';";
// 执行sql
$result = $pdo->prepare($sql);
// 将查询结果生成数组
if ($result->execute()) {
    while ($row = $result->fetch()) {
        $get_code_num = $row["code_num"];
        $get_code_type = $row["code_type"];
        $get_count_num = $row["count_num"];
        $get_cycle_date = $row["cycle_date"];
        $get_used_time = $row["used_time"];
    }
}

if ($get_code_type == 'count' && $get_count_num >= 0) {

    $set_tag = date("ymd"); //用于cookies标记
    $set_salt = getUserIp(); //用于cookies标记

    if (empty($get_used_time)) {
        $used_time = date("Y-m-d H:i:s");
        $count_num = $get_count_num - 1;
        // 首次使用 数据库标记初次使用时间 并减去1次
        $sql = "REPLACE INTO codes (code_num, code_type, count_num, cycle_date, used_time) VALUES ('$get_code_num', '$get_code_type', '$count_num', '$get_cycle_date', '$used_time');";
        $affected = $pdo->exec($sql);

        setcookie('count_type_tag',md5($set_tag.$set_salt),time()+24*60*60,'/');

        // 小米推送激活通知
        //$post_data[title] = "卡密已激活: ".$code_num;
        //$post_data[msg] = "[次卡:".$get_count_num."] 时间:".$used_time;
        //curl_post_api($post_data);

    }else{
        // 非首次 判断COOKIE
        if (isset($_COOKIE["count_type_tag"]) && $_COOKIE["count_type_tag"] == md5($set_tag.$set_salt)) {
            // echo "<p>次卡当天 重复登录免验证!</p>"; //COOKIE没过期 不减次数

            $shengyu = $get_count_num;
            $tips = "剩余:".$shengyu."次(天)";
            return;
        }

        if ($get_count_num > 0) {
            // 次卡当天 无COOKIE 首次登录 减去1次
            $count_num = $get_count_num - 1;
            $sql = "REPLACE INTO codes (code_num, code_type, count_num, cycle_date, used_time) VALUES ('$get_code_num', '$get_code_type', '$count_num', '$get_cycle_date', '$get_used_time');";
            $affected = $pdo->exec($sql);
            setcookie('count_type_tag',md5($set_tag.$set_salt),time()+24*60*60,'/');
        }

        if ($get_count_num == 0) {
            echo "<p>卡密已过期或可用次数已耗尽！<br>点我 <a href='./invite.php'>重新验证</a></p>"; setcookie('code_num','',0); die;
        }
    }

$shengyu = $count_num;
$tips = "剩余:".$shengyu."次(天)";

return;
}

echo "<p>错误：输入错误或卡密不存在!<br>点我 <a href='./invite.php'>重新验证</a></p>";
setcookie('code_num','',0); //cookie销毁 把时间设为0 就是过期了
die;


// 获取用户客户端IP
function getUserIp() {
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}


// 小米推送 有用户激活使用卡密时 站长会收到一条通知。这是另一个项目 默认已关闭
function curl_post_api($post_data) {

    $url = "https://xxxxx.com/MiPush/index.php"; //Post地址
    $post_data[pass] = "qwer1234";
    $post_data[id] = "xxxxxxxxxxxxxxxxxxxxxxxxxxxx"; //向date数组追加小米用户id

    $ch = curl_init(); //初始化
    curl_setopt($ch, CURLOPT_URL, $url); //设置选项，包括URL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1); //post数据
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data); //post变量
    $output = curl_exec($ch); //执行并获取HTML文档内容
    curl_close($ch); //释放curl句柄
    //print_r($output); //打印获得的数据
}

?>

