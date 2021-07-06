<?php

/***********************************************

安装分3个步骤：

1.把bindip.php上传到你网站根目录；

include('bindip.php');
2.将以上一行代码添加到你网站的index.php中；

3.宝塔创建数据库 faka_code，登录 phpmyadmin 执行以下一行 SQL 安装数据库；
CREATE TABLE `faka_code`.`bindip` ( `code_num` VARCHAR(30) NOT NULL , `bind_time` VARCHAR(30) NOT NULL , `bind_ip` VARCHAR(30) NOT NULL , PRIMARY KEY (`code_num`)) ENGINE = InnoDB;

安装完毕。

================================================

数据库结构如下：

数据库：faka_code
字符型：VARCHAR 30
表：bindip

表内容：
code_num PRIMARY KEY
bind_time
bind_ip

*************************************************/


// 填写数据库信息
$sql_host = 'localhost';
$sql_name = 'faka_code';
$sql_user = 'faka_code';
$sql_pass = 'qwer1234';


// 关闭报错
ini_set('display_errors','off');

$bind_num = $_POST["code_num"];
$lock_time = 3; //IP锁定时间 小时
$clean_cache = 2; //清除 n天 以前的数据库

if (empty($bind_num)) {$bind_num = $_COOKIE['code_num'];}
if (empty($bind_num)) {header('Location: /invite.php'); die;}


try {
// 填写数据库信息 连接数据库
    $pdo = new PDO ("mysql:host=$sql_host;dbname=$sql_name",$sql_user,$sql_pass);

    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $pdo->exec('set names "utf8"');

} catch (PDOException $e) {
    exit("<p>数据库连接失败:</p>".$e -> getMessage());
}


// 清除 n天 以前的数据库
if ($bind_num == "del") {
    $sql = "DELETE FROM bindip WHERE TO_DAYS(NOW()) - TO_DAYS(bind_time) >= $clean_cache;"; //只计算天 小时抹零
    $affected = $pdo->exec($sql);
    echo "<p>清除超过".$clean_cache."天的数据库缓存；<br>本次共清理 [".$affected."行] 缓存！</p>";
    die;
}


// 开始查询卡密使用情况
$sql = "SELECT * FROM bindip WHERE code_num='$bind_num';";
$result = $pdo->prepare($sql);
if ($result->execute()) {
    while ($row = $result->fetch()) {
        $get_bind_num = $row["code_num"];
        $get_bind_time = $row["bind_time"];
        $get_bind_ip = $row["bind_ip"];
    }
}


if (empty($get_bind_num)) { // 1.首次使用，写入时间和IP 放行

    $bind_time = date("Y-m-d H:i:s");
    $bind_ip = getUserIpAddr();
    $sql = "REPLACE INTO bindip (code_num, bind_time, bind_ip) VALUES ('$bind_num', '$bind_time', '$bind_ip');";
    $affected = $pdo->exec($sql);

    return;
} else {

    $get_time_now = date("Y-m-d H:i:s");
    $bind_ip_now = getUserIpAddr();
    $pass_time = strtotime($get_time_now) - strtotime($get_bind_time); //计算卡密已用时间
    $pass_hour = ceil($pass_time/(60*60)); //换算成小时 并取整

        if ($pass_hour > $lock_time) { // 2.非首次使用 大于锁定时间，写入新的时间和IP 放行

            $sql = "REPLACE INTO bindip (code_num, bind_time, bind_ip) VALUES ('$bind_num', '$get_time_now', '$bind_ip_now');";
            $affected = $pdo->exec($sql);

            return;
            }

        if ($get_bind_ip !== $bind_ip_now) { // 3.非首次使用 小于锁定时间 且 IP不同，不放行
            echo "<p>检测到异地登陆或频繁切换IP，请在 ".$lock_time."小时 后再尝试验证。<br><b>请勿将卡密泄漏给他人使用！</b></p>";
            echo "<p>已登录IP：".$get_bind_ip."<br>您当前IP：".$bind_ip_now."</p>";
            echo "<p>点我 <a href='./invite.php'>重新验证</a></p>";
            setcookie('code_num','',0); //cookie销毁
            die;
        } else { // 4.非首次使用 小于锁定时间 IP相同，放行
            return;
        }
}


// 获取用户客户端IP
function getUserIpAddr(){
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


?>
