<?php
/**
 * Created by PhpStorm.
 * User: BrainWang
 * Date: 2015/12/7
 * Time: 10:43
 */
require_once ("function/onlyInwechat.php");
require_once("qd_config.php");
require_once("db/Db_signIn.php");
require_once ("controller/JSSDK.php");

$wechatId=$_GET["wechatId"];
$Db_signIn=new Db_signIn();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>发起签到</title>
    <!-- 新 Bootstrap 核心 CSS 文件 -->
    <link rel="stylesheet" href="assets/include/css/bootstrap.min.css">
    <script type="application/javascript" src="assets/javascript/jquery.js"></script>
    <script type="application/javascript" src="assets/javascript/jweixin-1.0.0.js"></script>
<!--    <script type="application/javascript" src="assets/javascript/signin.js"></script>-->
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script type = "text/javascript" language="JavaScript">
        wx.hideOptionMenu();
        function CheckTime(){
            var starttime = document.getElementById("startTime").value;
            var endtime = document.getElementById("endTime").value;
            var js_starttime= Date.parse(starttime);//  月/日/年 时间（以冒号分割）
            var js_endtime = Date.parse(endtime );//  年/月/日 时间（以冒号分割）
            var today = Date.parse(new Date());
            if (js_starttime>=js_endtime||today>=js_endtime){
                alert("开始时间必须早于结束时间和晚于当前时间！");
                return false;
            }
            return true;

        }
        });
    </script>
</head>
<body>

<div class="panel panel-default">
    <!-- Default panel contents -->
    <div class="panel-heading"><h2>发起签到</h2></div>
    <form method="POST" action="controller/createSignEvent.php">
        <div class="input-group"  style="margin: 10px;height:40px">
            <span class="input-group-addon">开始时间：</span>
            <input id="startTime" style="height:40px" name="startTime" type="datetime-local" class="form-control" required>
        </div>

        <div class="input-group" style="margin: 10px;height:40px">
            <span class="input-group-addon">结束时间：</span>
            <input id="endTime" style="height:40px" name="endTime"  type="datetime-local" class="form-control" required>
        </div>

        <div class="input-group" style="text-align: center;width: 100%;padding-top: 30px;margin: 0px auto;">
            <button onclick = "return  CheckTime()" type="submit" name="wechatId" class="btn btn-success" value="<?php echo $wechatId ?>" style="margin: 0px auto;width: 90%;text-align: center;">发起签到<tton>
        </div>
    </form>


</div>
</body>
</html>
