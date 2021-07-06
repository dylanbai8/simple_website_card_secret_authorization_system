<?php
$get_bind_num = $_COOKIE['code_num'];
if (!empty($get_bind_num)) {header('Location: /index.php'); die;}
?>

<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>会员盒子-验证 F·码</title>
<style type="text/css">
textarea,input{
    outline-style: none;
    border: 1px solid #ccc; 
    border-radius: 3px;
    padding: 13px 14px;
    width: 260px;
    font-size: 14px;
    font-weight: 700;
    font-family: "Microsoft soft";
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
<form action="index.php" method="post">
<p><input type="hidden" name="from" value="maoobai"></p>
<p style="margin-top:90px"><input type="text" name="code_num" autocomplete="off"></p>
<p><input type="submit" value="验证 F·码" onclick="daoJiShi()" id="btnSub"></p>
</form>
<script type="text/javascript">
var s=30;
function daoJiShi(){
  var btnSub=document.getElementById("btnSub");
  if(btnSub){
    if(s<=0){
      btnSub.value="验证 F·码";
      btnSub.disabled=false;
      clearInterval(id);
    }
    else{
      btnSub.value="正在验证("+s+")";
      s--;
      var id = setInterval('daoJiShi()',1000)
    }
  }
}
</script>
</div>
</body>
</html>
