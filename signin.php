<?php
/**
 * Created by PhpStorm.
 * User: BrainWang
 * Date: 2015/12/5
 * Time: 21:19
 */
//require_once ("function/onlyInwechat.php");
require_once("qd_config.php");
require_once("db/Db_signIn.php");
require_once("controller/JSSDK.php");
$wechatId = $_GET["wechatId"];
$eventId = $_GET["eventId"];
$Db_signIn = new Db_signIn();;
$msg = $Db_signIn->findInfoByEventId($eventId);
$msg = $msg[0];
$student = $Db_signIn->findStudentInfoByWechat($wechatId);
$isUserSigned_inArr = $Db_signIn->isUserSigned_in($eventId, $wechatId);
$isSignOutOfdate = $Db_signIn->checkSignOutOfData($eventId);
//var_dump(sizeof($isUserSigned_inArr));
$isUserSigned_in = (sizeof($isUserSigned_inArr) > 0 ? true : false);
//echo $isUserSigned_in;
$canStudentSignIn = true;
if (!isset($student) || $isUserSigned_in&&!$isSignOutOfdate) {
    $canStudentSignIn = false;
}
//echo $canStudentSignIn;
$jssdk = new JSSDK(wx_appid, wx_appkey);
$signPackage = $jssdk->GetSignPackage();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>微信签到</title>
    <!-- 新 Bootstrap 核心 CSS 文件 -->
    <link rel="stylesheet" href="assets/include/css/bootstrap.min.css">
    <script type="application/javascript" src="assets/javascript/jquery.js"></script>
    <script type="application/javascript" src="assets/javascript/jweixin-1.0.0.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
</head>
<body>

<script type="application/javascript">
    wx.config({
        debug: false,
        appId: '<?php echo $signPackage["appId"];?>',
        timestamp: <?php echo $signPackage["timestamp"];?>,
        nonceStr: '<?php echo $signPackage["nonceStr"];?>',
        signature: '<?php echo $signPackage["signature"];?>',
        jsApiList: [
            // 所有要调用的 API 都要加到这个列表中
            'checkJsApi',
            'openLocation',
            'getLocation',
            'hideOptionMenu'
        ]
    });
    var ilatitude;
    var ilongitude;
    var ispeed;
    var iaccuracy;
$(document).ready(function(){
    $("#postSignMsg").click(function () {
        if ($("#postSignMsg").val() == "已签到")
            alert("<?php echo $student["sName"] ?>，你已经签过到了！");
        else if ($("#postSignMsg").val() == "签到") {
            $("#postSignMsg").val("定位中");
            wx.getLocation({
                type: 'wgs84', // 默认为wgs84的gps坐标，如果要返回直接给openLocation用的火星坐标，可传入'gcj02'
                success: function (res) {
                    ilatitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
                    ilongitude = res.longitude; // 经度，浮点数，范围为180 ~ -180。
                    ispeed = res.speed; // 速度，以米/每秒计
                    iaccuracy = res.accuracy;
                    postSignMsg();
                    //document.write("http://api.map.baidu.com/geocoder/v2/?ak=5a5527fee7c2d3e6fee75ebf10e01bcf&location="+ilatitude+","+ilongitude+"&output=json&pois=0");// 位置精度
                }

            });


        } else if ($("#postSignMsg").val() == "签到过期") {
            alert("<?php echo $student["sName"] ?>，签到已经过期了！");
        }


    });
    wx.ready(function () {
        wx.hideOptionMenu();

        });
    });
    function postSignMsg() {

        $.post("controller/signin.php",
            {
                latitude: ilatitude,
                longitude: ilongitude,
                speed: ispeed, // 速度，以米/每秒计
                accuracy: iaccuracy,
                sNumber:<?php echo $student["sId"]?>,
                siId:<?php echo $eventId ?>
            }, function (data) {
                if (data["code"] == true) {
                    $("#postSignMsg").val("已签到");
                    $("#postSignMsg").removeClass("btn-danger");
                    $("#postSignMsg").addClass("btn-success");
                    alert("签到成功：<?php echo $student["sClass"]."班 ".$student["sName"] ?> " + '\n' + "签到地点：" + data["msg"]);
                    wx.closeWindow();
                } else {

                    $("#postSignMsg").val("签到");
                    alert("签到失败！" + data['msg']);
                }

            }
        );
    }
    ;


</script>

<div class="panel panel-default">
    <!-- Default panel contents -->
    <div class="panel-heading"><h2> <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>微信签到</h2></div>
    <div class="panel-body">
        <p style="margin: 0px auto;text-align: center;">
            <input type="button" id="postSignMsg"
                   class="btn <?php echo($canStudentSignIn == false ? "btn-success" : "btn-danger") ?> "
                <?

                    if ($canStudentSignIn) {

                        if ($isSignOutOfdate)
                            $signStatus = "签到过期";
                        else {
                            $signStatus = "签到";
                        }
                    } else {
                        $signStatus = "已签到";


                }
                ?>
                   value="<?php echo $signStatus ?>"
                   style="font-size: 30px; width: 150px; border-radius: 75px;height: 150px;"></p>
    </div>
    <ul class="list-group">
        <li class="list-group-item list-group-item-success">
            <span class="glyphicon glyphicon-time" aria-hidden="true"></span>
            开始时间：<?php echo $msg["startTime"] ?></li>
        <li class="list-group-item list-group-item-info">
            <span class="glyphicon glyphicon-dashboard" aria-hidden="true"></span>
            结束时间： <?php echo $msg["endTime"] ?> </li>
        <li class="list-group-item list-group-item-warning">
            <span class="glyphicon glyphicon-send" aria-hidden="true"></span>
            发起人：<?php echo $msg["sponsor"] ?></li>
        <li class="list-group-item list-group-item-danger">
            <span class="glyphicon glyphicon-user" aria-hidden="true"></span>
            签到人：<?php echo $student["sId"] . " " . $student["sClass"] . "班 " . $student["sName"] ?></li>
        <li class="list-group-item list-group-item-success">
            <span class="glyphicon glyphicon-flag" aria-hidden="true"></span>
            签到状态：<?php echo ($isUserSigned_in==true)? "已签到":"未签到" ?></li>
    </ul>

</div>
</body>
</html>
