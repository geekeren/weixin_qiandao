<?php
/**
 * Created by PhpStorm.
 * User: BrainWang
 * Date: 2015/12/10
 * Time: 0:28
 */
$useragent = addslashes($_SERVER['HTTP_USER_AGENT']);
if (strpos($useragent, 'MicroMessenger') === false && strpos($useragent, 'Windows Phone') === false )
{header('Content-type: text/html;charset=utf-8');
    ?>
    <html>
    <title>禁止访问</title>
    <meta http-equiv="Refresh" content="2;URL= ">
    签到只能在微信中进行！
    <script>
        window.opener=null;window.open('','_self');window.close();
    </script>
    </html>
    <?
    exit();
}