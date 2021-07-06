<?php


// 设置商品名称、价格、Tips
$set_products_name = "Pandownload日租F码1枚";
$set_products_price = "0.5";
$set_tips = '售后微信：okduang';

// 设置收银台地址、支付后回调地址
$pay_checkout_url = "https://xxxxx.com/pay/checkout.php";
$return_shop_url = "https://xxxxx.com/shop.php";


// 填写数据库信息
$sql_host = 'localhost';
$sql_name = 'vpay_orders';
$sql_user = 'vpay_orders';
$sql_pass = 'qwer1234';


$get_mail = $_GET["mail"];
$get_ispayment = $_GET["ispayment"];
$get_orderid = $_GET["orderid"];
$set_salt = "okduang"; //设置md5混淆参数


// 连接数据库
try {
    $pdo = new PDO ("mysql:host=$sql_host;dbname=$sql_name",$sql_user,$sql_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $pdo->exec('set names "utf8"');
} catch (PDOException $e) {
    exit("<p>数据库连接失败:</p>".$e -> getMessage());
}


if (empty($get_ispayment)) { //判断是否来自回调。如果是否，则创建新订单

$get_orderid = date("YmdHis").rand(1000,9999); //创建订单号

// 查询是否有库存
$sql = "SELECT * FROM cards WHERE issold=\"no\" LIMIT 1;";
$result = $pdo->prepare($sql);
if ($result->execute()) {
    while ($row = $result->fetch()) {
        $get_id = $row["id"];
    }
}
if (empty($get_id)) {$set_submit = 'disabled="disabled" value="库存不足"';} else {$set_submit = 'value="立刻购买"';}


echo '
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>迷你小商店</title>
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
<form action="'.$pay_checkout_url.'" method="get">
<p style="margin-top:3em;"><b>商品：</b>'.$set_products_name.'<br><b>价格：</b>'.$set_products_price.'元（随机立减）</p>
<p><input type="hidden" required="required" readonly="readonly" name="return" value="'.$return_shop_url.'"></p>
<p><input type="hidden" required="required" readonly="readonly" name="orderid" value="'.$get_orderid.'"></p>
<p><input type="hidden" required="required" readonly="readonly" name="price" value="'.$set_products_price.'"></p>
<p><input type="email" required="required" name="mail" placeholder="收货邮箱"></p>
<select name="paytype" required="required">
<option value="">▼选择付款方式</option>
<option value="wechat">微信付款</option>
<option value="alipay">支付宝付款</option>
</select>
<p><input type="submit" '.$set_submit.'></p>
</form>
</div>
</body>
</html>';
die;}


// 如果来自"查询历史订单" 则开始查询订单信息

if (!empty($get_orderid) && !is_numeric($get_orderid)) {echo "非法访问！"; die;} //验证传入的订单号是否为纯数字
if (!empty($get_mail) && !filter_var($get_mail, FILTER_VALIDATE_EMAIL)) {echo "非法访问！"; die;} //验证传入的邮箱是否为邮箱格式

if (!empty($get_orderid)) {

$sql = "SELECT * FROM cards WHERE issold=$get_orderid LIMIT 1;";
$result = $pdo->prepare($sql);
if ($result->execute()) {
    while ($row = $result->fetch()) {
        $get_id = $row["id"];
        $get_card = $row["card"];
        $get_issold = $row["issold"];
        $get_mail = $row["mail"];
        $get_date = $row["date"];
    }
}

// 查询结果非空 存在历史订单 抛出html给用户
if (!empty($get_id)) {echo_html($get_orderid,$get_mail,$get_card,$set_tips);}
}


// 如果来自回调，则开始发卡流程
$set_ispayment = md5(md5($get_mail.$set_salt).$get_orderid);
if ($get_ispayment == $set_ispayment) { //验证回调链接参数是否合法

$sql = "SELECT * FROM cards WHERE issold=\"no\" LIMIT 1;"; //从库存提出1张卡密
$result = $pdo->prepare($sql);
if ($result->execute()) {
    while ($row = $result->fetch()) {
        $get_id = $row["id"];
        $get_card = $row["card"];
    }
}

if (empty($get_id)) {$get_card ="缺货,请联系售后!"; echo_html($get_orderid,$get_mail,$get_card,$set_tips);} //无库存 缺货 抛出html给用户


// 库存正常 开始发卡
$get_date = date("YmdHis");
$sql = "REPLACE INTO cards ( id, card, issold, mail, date) VALUES ( '$get_id', '$get_card', '$get_orderid', '$get_mail', '$get_date');";
$affected = $pdo->exec($sql);

echo_html($get_orderid,$get_mail,$get_card,$set_tips); //发卡给用户 抛出html给用户 发卡结束
}


// 自定义html函数
function echo_html($get_orderid,$get_mail,$get_card,$set_tips) {

echo '
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>订单中心</title>
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
<p style="margin-top:3em;">订单号：</p>
<p><input type="text" value="'.$get_orderid.'"></p>
<p>收货邮箱：</p>
<p><input type="text" value="'.$get_mail.'"></p>
<p>已购卡密：</p>
<p><input type="text" value="'.$get_card.'"></p>
<p style="margin-top:2em;">'.$set_tips.' 请收藏此网页以便日后查询订单。</p>
</div>
</body>
</html>';
die;}

echo "非法访问！";
die;
?>
