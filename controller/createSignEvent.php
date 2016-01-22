<?php
/**
 * Created by PhpStorm.
 * User: BrainWang
 * Date: 2015/12/7
 * Time: 10:43
 */

require_once("../qd_config.php");
require_once("../db/Db_signIn.php");
$wechatId=$_POST["wechatId"];

$startTime=$_POST["startTime"];
$endTime=$_POST["endTime"];

$Db_signIn=new Db_signIn();
$canCreate= $Db_signIn->CreateSignInEvent($wechatId, $startTime, $endTime);
//var_dump($canCreate) ;
switch($canCreate){
    case -1:
        $msg="你不是辅导员，没有权限！";
        break;
    case 0:
        $msg="数据库错误！";
        break;
    case 1:
        $msg="";
        break;
    case 2:
        $msg="签到时间与已有的签到冲突！";
        break;
    case 3:
        $msg=" 签到开始时间晚于或等于结束时间！";
        break;
    default:
        $msg=$canCreate;
}
//echo $canCreate;
//$Db_signIn->createSignInEvent($wechatId,$startTime,$endTime);
$teacher=$Db_signIn->isSopnsor($wechatId);
$wechatId=$teacher['teacher'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>发起签到结果：</title>
    <!-- 新 Bootstrap 核心 CSS 文件 -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <script type="application/javascript" src="../assets/javascript/jquery.js"></script>
    <script type="application/javascript" src="../assets/javascript/CreateSign.js"></script>
    <script type="application/javascript" src="../assets/javascript/jweixin-1.0.0.js"></script>
<!--    <script type="application/javascript" src="assets/javascript/signin.js"></script>-->
    <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>
<body>

<div class="panel panel-default">
    <!-- Default panel contents -->
    <div class="panel-heading"><h2>签到详情：</h2></div>

    <ul class="list-group">
        <li class="list-group-item list-group-item-success">开始时间：<? echo $startTime ?></li>
        <li class="list-group-item list-group-item-info">结束时间：<? echo $endTime ?></li>
        <li class="list-group-item list-group-item-warning">发起人：<? echo $wechatId ?></li>
    </ul>
    <div class="panel-heading"><h3><? echo ($canCreate==1? "发起成功！":"发起失败:")."</br>".$msg ?></h3></div>
<? echo ($canCreate==1?
    '<button class="btn btn-success" onclick="wx.closeWindow()"
            style="margin: 10px auto;width:100%;height:40px;text-align: center;">
        退出</button>':'<button class="btn btn-success" onclick="history.back(-1)"
            style="margin: 10px auto;width:100%;height:40px;text-align: center;">
        返回</button>') ?>

</div>
</body>
