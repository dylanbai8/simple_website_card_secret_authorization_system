<?php


// 设置管理员密码
$pass_word_set = "qwer1234";


// 填写数据库信息
$sql_host = 'localhost';
$sql_name = 'faka_code';
$sql_user = 'faka_code';
$sql_pass = 'qwer1234';


// 不显示报错
ini_set('display_errors','off');

// 获取post信息
$code_type = $_POST["code_type"];
$count_num = $_POST["count_num"];
$cycle_date = $_POST["cycle_date"];

$code_num = $_POST["code_num"];
$pass_word = $_POST["pass_word"];

// 设置卡密信息
$amount = 20; //单次生成卡密的数量
$pwdLen = 4; //卡密中随机字的长度

$sNumArr = range(0,9); //生成包含数字0-9的数组
$sPwdArr = array_merge($sNumArr,range('A','Z')); //生成包含a-z的数组并与数字数组合并

$cards = array(); //定义cards数组
for ($x=0;$x<$amount;$x++) { //for循环生成卡密

    $tempPwdStr = array();
    for ($i=0;$i<$pwdLen;$i++) {
    $tempPwdStr[] = $sPwdArr[array_rand($sPwdArr)]; //返回一个包含随机键名的数组
    }
    $cards[$x] = implode('',$tempPwdStr); //返回一个由数组元素组合成的字符串 间隔符为空
}
array_unique($cards); //卡密数组去重

// html中引用 echo textarea_value($cards);
function textarea_value($cards) { //自定义函数生成 textarea value

    $time_tag = date("U"); //时间戳
    foreach ($cards as $value) { //遍历数组值赋给 value
        $string = '';
        $string = rtrim('F'.$time_tag.$value.'&#10;'); //添加换行符
        echo $string; //把数组数据写入1个字符串
    }
return;
}

?>


<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>会员盒子-添加卡密</title>
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
    text-align: center;
    width: 360px;
    margin: 0 auto;
}
</style>
</head>
<body>
<div>
<form action="" method="post">

<p style="margin-left:-185px;"><b>导入F码：</b></p>

<p <?php if(!empty($code_num)){echo 'style="display:none;"';} ?>>
<select name="code_type" <?php if(empty($code_num)){echo 'required';} ?>>
<option value=''>▼选择卡密类型</option>
<option value='cycle'>限制-有效期</option>
<option value='count'>限制-使用次数</option>
</select>
</p>

<p <?php if(!empty($code_num)){echo 'style="display:none;"';} ?>>
<select name="cycle_date">
<option value=''>▼选择卡密有效期</option>
<option value='2'>有效期 1小时</option>
<option value='4'>有效期 3小时</option>
<option value='25'>有效期 1天</option>
<option value='721'>有效期 1月</option>
<option value='8641'>有效期 1年</option>
</select>
</p>

<p <?php if(!empty($code_num)){echo 'style="display:none;"';} ?>>
<input type="text" name="count_num" placeholder="  ▶可用次数">
</p>

<p><textarea rows="21" name="code_num" readonly="readonly" onclick="this.focus();this.select()"><?php if(empty($code_num)){echo textarea_value($cards);} ?></textarea></p>

<p><input type="<?php if(!empty($code_num)){echo "hidden";}else{echo "text";} ?>" name="pass_word" placeholder="管理员账号"></p>

<p><input type="submit" value="<?php if(!empty($code_num)){echo "生成随机卡密";}else{echo "导入以上卡密";} ?>"></p>

</form>


<?php

// 判断值是否为空
if (empty($code_num)) {echo "<p>已生成随机卡密，请复制保存后导入!</p>"; die;}

// 判断卡密类型
if (empty($code_type)) {echo "<p>错误：请选择 [卡密类型]!</p>"; die;}
if ($code_type == "cycle") {
    if (empty($cycle_date)) {echo "<p>错误：请选择 [卡密有效期]!</p>"; die;}
    $count_num = "";
}

// 判断卡密类型
if ($code_type == "count") {
    if (empty($count_num)) {echo "<p>错误：次卡须填写 [可用次数]!</p>"; die;}
    $cycle_date = "";
}

// 验证密码
if ($pass_word != $pass_word_set) {echo "<p>错误：禁止访问!</p>"; die;}

try {
// 填写数据库信息 连接数据库
    $pdo = new PDO ("mysql:host=$sql_host;dbname=$sql_name",$sql_user,$sql_pass);

    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $pdo->exec('set names "utf8"');

} catch (PDOException $e) {
    exit("<p>数据库连接失败:</p>".$e -> getMessage());
}


$array = explode("\r\n",$code_num); //按照换行符 把字符串打散为数组
$array = array_filter($array); //去除数组内的空行
$get_num = count($array); //获取数组行数 卡密个数
echo "<p><b>本次导入合计: ".$get_num."枚卡密</b></p>";

$file_name = "46fbdc20-0b31-4687-881d-5611d58578b8.temp.add"; //备份卡密到临时文件
if (file_exists($file_name)) {unlink($file_name);}
foreach ($array as $value) {file_put_contents($file_name, $value.PHP_EOL, FILE_APPEND | LOCK_EX);}

foreach ($array as $value) { //遍历数组值赋给 value

    if (empty($value)) {die;}
    if ($code_type == "cycle") {$value = "cycle_".$value;}
    if ($code_type == "count") {$value = "count_".$value;}

    // 填写要执行的sql
    $sql = "REPLACE INTO codes (code_num, code_type, count_num, cycle_date, used_time) VALUES ('$value', '$code_type', '$count_num', '$cycle_date', '');";

    // 执行sql
    $affected = $pdo->exec($sql);
    // 判断返回结果
    if ($affected == 1) {
        //echo "<p>导入 [".$value."] 成功!</p>";
    } elseif ($affected == 2) {
        echo "<p><b>重置</b> [".$value."] 成功!</p>";
    } else {
        echo "<p>错误：添加失败!</p>";
    }
}

?>

</div>
</body>
</html>

