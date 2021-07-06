<?php

/***********************************************

安装分2个步骤：

1.上传代码到服务器，可以独立建站也可以是你已有网站的子目录；

2.宝塔创建数据库 faka_code，登录 phpmyadmin 执行以下一行 SQL 安装数据库；
CREATE TABLE `vpay_orders`.`cards` ( `id` INT NOT NULL AUTO_INCREMENT , `card` VARCHAR(30) NOT NULL , `issold` VARCHAR(30) NOT NULL , `mail` VARCHAR(30) NOT NULL , `date` VARCHAR(30) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;

安装完毕。

================================================

数据库结构：

数据库：vpay_orders
字符型：VARCHAR 30
表：cards

表内容：
id PRIMARY KEY int 自动递增（勾选AI）
card
issold
mail
date

*************************************************/


$set_pass = "qwer1234"; //设置管理员密码


// 填写数据库信息
$sql_host = 'localhost';
$sql_name = 'vpay_orders';
$sql_user = 'vpay_orders';
$sql_pass = 'qwer1234';


?>


<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>导入卡密</title>
<style type="text/css">
select,textarea,input{
    outline-style: none;
    border: 1px solid #ccc; 
    border-radius: 3px;
    padding: 13px 14px;
    width: 260px;
    font-size: 14px;
    font-weight: 700;
}
input:focus{
    border-color: #66afe9;
    outline: 0;
    -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6);
    box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6);
}
div{
    width: 260px;
    margin: 0 auto;
}
</style>
</head>
<body>
<div>
<form action="" method="post">
<p style="margin-top:3em;"><b>导入卡密(一行一个)：</b></p>
<p><textarea rows="16" name="cards" onclick="this.focus();this.select()"></textarea></p>
<p><input type="text" name="pass" placeholder="管理员密码"></p>
<p><input type="submit" value="导入以上卡密"></p>
</form>


<?php


$get_cards = $_POST["cards"];
if (empty($get_cards)) {die;} //导入卡密为空时终止程序

if ($_POST["pass"] != $set_pass) {echo "非法访问！"; die;};

// 连接数据库
try {
    $pdo = new PDO ("mysql:host=$sql_host;dbname=$sql_name",$sql_user,$sql_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $pdo->exec('set names "utf8"');
} catch (PDOException $e) {
    exit("<p>数据库连接失败:</p>".$e -> getMessage());
}


// 预处理卡密数组
$get_issold = "no"; //设置卡密初始状态为no
$array = explode("\r\n",$get_cards); //按照换行符 把字符串打散为数组
$array = array_filter($array); //去除数组内的空行
$get_num = count($array); //获取数组行数 卡密个数


foreach ($array as $value) { //遍历数组值赋给 value

    if (empty($value)) {die;}
    $sql = "REPLACE INTO cards ( card, issold, mail, date) VALUES ( '$value', '$get_issold', '', '');";
    $affected = $pdo->exec($sql);

    // 判断返回结果
    if ($affected == 1) {
        //echo "<p>导入 [".$value."] 成功!</p>";
    } elseif ($affected == 2) {
        echo "<p><b>卡密被重置</b> [".$value."]</p>";
    } else {
        echo "<p><b>卡密导入错误</b> [".$value."]</p>";
    }
}

echo "<p><b>本次导入合计: ".$get_num."枚 卡密</b></p>";

?>


</div>
</body>
</html>

