<?php
/**
 * Created by PhpStorm.
 * User: BrainWang
 * Date: 2015/12/5
 * Time: 19:18
 */

define("TOKEN", "weixin");
define("hostUrl", "http://qd.wangbaiyuan.cn");
require_once("./qd_config.php");
require_once("./db/Db_signIn.php");
$wechatObj = new wechatCallbackapiTest();

if (isset($_GET['echostr'])) {
    $wechatObj->valid();
} else {
    $wechatObj->responseMsg();
}

class wechatCallbackapiTest
{

    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if ($this->checkSignature()) {
            header('content-type:text');
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        if (!empty($postStr)) {
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->Content);
            $keywords = explode(' ', $keyword);
            date_default_timezone_set('PRC');
            $time = time();
            $msgType = "text";
            $simpleText = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                        <FuncFlag>0</FuncFlag>
                        </xml>";

            $Db_signIn = new Db_signIn();
            if ($keywords[0] == "认证") {

                if (isset($keywords[1])) {
                    $sNumber = $keywords[1];
                    $bound = $Db_signIn->authentic($fromUsername, $sNumber);
                    switch ($bound) {
                        case 0:
                            $contentStr = "学号{$sNumber}或者微信已经绑定，若要解绑请联系辅导员";
                            break;
                        case 1:
                            $contentStr = "认证成功：学号{$sNumber}和本微信号绑定成功！";
                            break;
                        case -1:
                            $contentStr = "认证失败：学号{$sNumber}和本微信号绑定失败！";
                            break;
                        case -2:
                            $contentStr = "学号有误！请检查输入";
                            break;
                        default:
                            $contentStr = "请发送：“认证 学号”，如 张三发送：“认证 2013300000”\n注意：认证后只能绑定一次，，若要解绑请联系辅导员";
                    }

                } else {
                    $contentStr = "请发送：“认证 学号”，如 张三发送：“认证 2013300000”
                            \n认证后只能绑定一次，，若要解绑请联系辅导员";
                }
            } else if ($keywords[0] == "发起签到") {
                $Sopnsor = $Db_signIn->isSopnsor($fromUsername);
                if ($Sopnsor != 0) {
                    $url = hostUrl . "/createSignEvent.php?wechatId=" . $fromUsername;
                    $contentStr = "<a href=\"{$url}\">点击发起签到</a>";
                } else {
                    $contentStr = "你不是辅导员，不能发起签到！";
                }
            } else if ($keywords[0] == "签到") {
                if ($Db_signIn->isUserAuthentic($fromUsername)) {
                    $eventIds = $Db_signIn->userHasSign_in($fromUsername);
                    $student = $Db_signIn->findStudentInfoByWechat($fromUsername);
                    $contentStr = $eventIds;
                    if (count($eventIds)) {
                        $eventId = $eventIds[0]['siId'];
                        $msg = $Db_signIn->findInfoByEventId($eventId);
                        $msg = $msg[0];
                        $isUserSigned_inArr = $Db_signIn->isUserSigned_in($eventId, $fromUsername);
                        $isUserSigned_in = (sizeof($isUserSigned_inArr) > 0 ? true : false);
                        $url = hostUrl . "/signin.php?&eventId={$eventId}&wechatId={$fromUsername}";
                        $temp = "<a href=\"{$url}\">点击签到</a>";
                        $contentStr = "开始时间：\n{$msg['startTime']}\n" .
                            "结束时间：\n{$msg['endTime']}\n" .
                            "发起人：{$msg['sponsor']}\n" .
                            "签到状态：" . $student['sName'] . ($isUserSigned_in == true ? '已签到' : '未签到' . $temp);

                    } else
                        $contentStr = "当前时间没有签到！";

                }else{
                    $contentStr = "你没有认证你的学号！\n请发送：“认证 学号”，如 张三发送：“认证 2013300000”\n认证后只能绑定一次，，若要解绑请联系辅导员";

                }

            } else {
                $contentStr = "未处理的消息:" . $keyword;
            }
            $resultStr = sprintf($simpleText, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            echo $resultStr;
        } else {
            echo "";
            exit;
        }
    }
}

?>