<?php

/***********************************************

安装教程：

宝塔创建数据库 faka_code，登录 phpmyadmin 执行以下一行 SQL 安装数据库；

安装表：
CREATE TABLE `vpay_orders`.`orders` ( `id` INT NOT NULL AUTO_INCREMENT , `money` VARCHAR(30) NOT NULL , `isorder` VARCHAR(30) NOT NULL , `orderid` VARCHAR(30) NOT NULL , `type` VARCHAR(30) NOT NULL , `time` VARCHAR(30) NOT NULL , `title` VARCHAR(30) NOT NULL , `content` VARCHAR(30) NOT NULL , `deviceid` VARCHAR(30) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;

===============================================

数据库结构：

数据库：vpay_orders
字符型：VARCHAR 30
表：orders

表内容：
id PRIMARY KEY int 自动递增（勾选AI）
money
isorder
orderid
type
time
title
content
deviceid

*************************************************/


// 填写数据库信息
$sql_host = 'localhost';
$sql_name = 'vpay_orders';
$sql_user = 'vpay_orders';
$sql_pass = 'qwer1234';


// 接收来自监控APP的post数据并转为数组
$get_post_json = file_get_contents('php://input');
$array_date = json_decode($get_post_json, true);

// 将数组项赋值给变量 方便调用
$get_money = $array_date["money"];
$get_type = $array_date["type"];
$get_time = $array_date["time"];
$get_title = $array_date["title"];
$get_content = $array_date["content"];
$get_deviceid = $array_date["deviceid"];

// 给接收到的post收款 加2个初始值
$get_isorder = "no";
$get_orderid = "";

// 给监控APP额外加1个推送自定义项目 防止被伪造推送 pass:qwer1234
$set_pass = "qwer1234";
$get_pass = $array_date["pass"];
if ($get_pass != $set_pass) {echo "<p>错误：禁止访问!</p>"; die;}

// 验证推送金额是否为空
if ($get_money == "" || $get_money == "null") {echo "<p>错误：禁止访问!</p>"; die;}

// 连接数据库
try {
    $pdo = new PDO ("mysql:host=$sql_host;dbname=$sql_name",$sql_user,$sql_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $pdo->exec('set names "utf8"');
} catch (PDOException $e) {
    exit("<p>数据库连接失败:</p>".$e -> getMessage());
}

$sql = "REPLACE INTO orders ( money, isorder, orderid, type, time, title, content, deviceid) VALUES ( '$get_money', '$get_isorder', '$get_orderid', '$get_type', '$get_time', '$get_title', '$get_content', '$get_deviceid');";
$affected = $pdo->exec($sql);

// 判断返回结果
if ($affected == 1) {
        echo "POST成功";
} elseif ($affected == 2) {
        echo "POST重复";
} else {
        echo "POST失败";
}

?>
