<?php

function paypage_echo_html($set_money,$random_price,$erweima,$paytips) {

echo '
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Content-Language" content="zh-cn">
    <meta name="apple-mobile-web-app-capable" content="no"/>
    <meta name="apple-touch-fullscreen" content="yes"/>
    <meta name="format-detection" content="telephone=no,email=no"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="white">
    <meta name="renderer" content="webkit"/>
    <meta name="force-rendering" content="webkit"/>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1"/>
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Cache-control" content="no-cache">
    <meta http-equiv="Cache" content="no-cache">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <title>[收银台]-每3秒刷新</title>
    <link href="pay_page/pay.css" rel="stylesheet" media="screen">
</head>

<body>
<div class="body">
    <h1 class="mod-title">
        <span class="ico_log ico-1" v-if="payType == 1"></span>
        <span class="ico_log ico-2" v-if="payType == 2"></span>
    </h1>

    <div class="mod-ct">

        <div class="amount">￥'.$set_money.'</div>
        <p>随机立减'.$random_price.'元</p>
        <div class="qrcode-img-wrapper" data-role="qrPayImgWrapper">
            <div data-role="qrPayImg" class="qrcode-img-area">
                <div style="position: relative;display: inline-block;">
                    <img alt="加载中..." src="'.$erweima.'" width="210" height="210">
                </div>
            </div>
        </div>

        <div class="time-item">
            <div class="time-item">
                <h1  v-if="price != reallyPrice">
                    <span>'.$paytips.'</span><br>
                </h1>
            </div>
            <strong>订单 3分钟 内有效</strong>
        </div>

        <div class="tip">
            <div class="ico-scan"></div>
            <div class="tip-text">
                <p v-if="isAuto == 0">微信 / 支付宝扫一扫</p>
                <p v-if="isAuto == 1">扫码后输入金额支付</p>
            </div>
        </div>

    </div>

    <div class="foot">
        <div class="inner">
            <p>手机用户可保存上方二维码到手机中</p>
            <p>微信/支付宝扫一扫中选择"相册"即可</p>
        </div>
    </div>

</div>

</body>
</html>';

}
?>
