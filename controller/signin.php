<?php
/**
 * Created by PhpStorm.
 * User: BrainWang
 * Date: 2015/12/5
 * Time: 21:19
 */
require_once("../qd_config.php");
require_once("../db/Db_signIn.php");
define('apiUrl', 'http://api.map.baidu.com/geocoder/v2/?ak='.ak.'&');
/**
 * @param $longitude 经度
 * @param $latitude 纬度
 * @return 地理位置
 */
function AddresByGeocoding($longitude,$latitude){
    $url = apiUrl."location=".$latitude.",".$longitude."&output=json&pois=0";
    $result= file_get_contents($url);
    $jsondata = json_decode($result,true);
    $address =$jsondata['result']['formatted_address'];
    return $address;
}

$latitude=$_POST["latitude"];
$longitude=$_POST["longitude"];
$latitude=$latitude+0.004543; // 纬度，浮点数，范围为90 ~ -90
$longitude=$longitude+0.011054;
//$speed=$_POST["speed"]; // 速度，以米/每秒计
$accuracy=$_POST["accuracy"];
$sNumber=$_POST["sNumber"];
$siId=$_POST["siId"];
$address= AddresByGeocoding($longitude,$latitude);
$address=($address==""||$address==null? "":($address."（精度：{$accuracy}米）"));
$Db_signIn=new Db_signIn();
$result=$Db_signIn->createSignInRecord($siId,$sNumber,$longitude,$latitude,$address);
header('Content-type: text/json');
    echo json_encode($result);