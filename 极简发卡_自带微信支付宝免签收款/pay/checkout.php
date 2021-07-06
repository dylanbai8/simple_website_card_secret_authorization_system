<?php


// 填写数据库信息
$sql_host = 'localhost';
$sql_name = 'vpay_orders';
$sql_user = 'vpay_orders';
$sql_pass = 'qwer1234';


include("pay_page/pay.php"); //引入收银台html页面函数

// 获取get过来的订单信息
$set_isorder = $_GET["isorder"];
$time_sign = $_GET["sign"];

$get_paytype = $_GET["paytype"];
$get_mail = $_GET["mail"];
$get_orderid = $_GET["orderid"];
$return_shop_url = $_GET["return"];

$get_price = $_GET["price"];
$random_price = $_GET["random"];
if (empty($random_price)) {$random_price = "0.0".rand(0,9);} //随机立减 用于区分订单，如订单量大可将随机数增加至2位

$set_money = $_GET["money"];
if (empty($set_money)) {$set_money = $get_price - $random_price;}

$time_now = date("U");
$set_salt = "okduang"; //设置md5混淆参数


if (empty($set_isorder)) { //向url添加 isorder 和 sign 参数。isorder用来回调验证防止伪造，sign用来计时3分钟
    $set_isorder = md5($time_now.$set_salt);
    header("Refresh:0;url=checkout.php?isorder=$set_isorder&sign=$time_now&mail=$get_mail&orderid=$get_orderid&paytype=$get_paytype&money=$set_money&random=$random_price&return=$return_shop_url");
    die;
}

if (md5($time_sign.$set_salt) != $set_isorder) {echo "非法访问！"; die;} //验证时间戳是否被窜改

if (($time_now-$time_sign)/60 > 3) { //超时3分钟 则停止支付 抛出html
    $paytips = "已超时，请勿付款！<br>如已支付请联系客服退款。";
    $erweima = "pay_page/zfcs.png";
    paypage_echo_html($set_money,$random_price,$erweima,$paytips); //收银台超时页
    die;
}

// 连接数据库
try {
    $pdo = new PDO ("mysql:host=$sql_host;dbname=$sql_name",$sql_user,$sql_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $pdo->exec('set names "utf8"');
} catch (PDOException $e) {
    exit("<p>数据库连接失败:</p>".$e -> getMessage());
}

// 每3秒钟 检测数据库是否有 等金额 的新收款
$sql = "SELECT * FROM orders WHERE money=$set_money AND isorder=\"no\" LIMIT 1;";
$result = $pdo->prepare($sql);

if ($result->execute()) {
    while ($row = $result->fetch()) {
        $get_id = $row["id"];
        $get_money = $row["money"];
        $get_isorder = $row["isorder"];
        //$get_orderid = $row["orderid"];
        $get_type = $row["type"];
        $get_time = $row["time"];
        $get_title = $row["title"];
        $get_content = $row["content"];
        $get_deviceid = $row["deviceid"];
    }
}

// 检测是否有 等金额 的无订单收款（大于3分钟），有则标记。防止错误发卡
if (!empty($get_money) && ($time_now-strtotime($get_time))/60 > 3) {

    $sql = "REPLACE INTO orders ( id, money, isorder, orderid, type, time, title, content, deviceid) VALUES ( '$get_id', '$get_money', '无订单收款', '$get_orderid', '$get_type', '$get_time', '$get_title', '$get_content', '$get_deviceid');";
    $affected = $pdo->exec($sql);

    header("Refresh:0;url=checkout.php?isorder=$set_isorder&sign=$time_sign&mail=$get_mail&orderid=$get_orderid&return=$return_shop_url"); 
    die;
}

// 检测到 等金额收款，数据库标记该订单 并回调通知shop发卡
if ($get_isorder == "no" && $get_money == $set_money && $get_type == $get_paytype) {

    $sql = "REPLACE INTO orders ( id, money, isorder, orderid, type, time, title, content, deviceid) VALUES ( '$get_id', '$get_money', '$set_isorder', '$get_orderid', '$get_type', '$get_time', '$get_title', '$get_content', '$get_deviceid');";
    $affected = $pdo->exec($sql);

    // 判断返回结果
    if ($affected == 2) {

        $paytips = "支付成功，即将跳转！";
        $erweima = "pay_page/zfcg.png";
        paypage_echo_html($set_money,$random_price,$erweima,$paytips); //收银台支付成功页

        $ispayment = md5(md5($get_mail.$set_salt).$get_orderid);
        header("Refresh:3;url=$return_shop_url?mail=$get_mail&orderid=$get_orderid&ispayment=$ispayment");
        die;
    }

echo "支付异常，请联系管理员。";
die;
}


// 收银台主页 每3秒刷新一次 检测数据库是否有新收款
$paytips = "为了您正常支付 请务必付款 ".$set_money." 元<br>备注说明无需填写";

if ($get_paytype == "wechat") {$erweima = "pay_page/wx_pay.png";} //微信收款
if ($get_paytype == "alipay") {$erweima = "pay_page/ali_pay.png";} //支付宝收款
if ($get_paytype == "123") {$erweima = "pay_page/123.png";} //云闪付 预留

paypage_echo_html($set_money,$random_price,$erweima,$paytips); // 收银台主页

header("Refresh:3;url=checkout.php?isorder=$set_isorder&sign=$time_sign&mail=$get_mail&orderid=$get_orderid&paytype=$get_paytype&money=$set_money&random=$random_price&return=$return_shop_url");
die;

?>
