<?php
/**
 * Created by PhpStorm.
 * User: BrainWang
 * Date: 2015/12/5
 * Time: 20:21
 */

class Db_signIn
{
    private $software_db;

    public function __construct()
    {
        $this->software_db = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME) OR die('无法连接到软件数据库' . mysqli_connect_error());
        mysqli_set_charset($this->software_db, DB_CHARSET);
    }

    //通过微信号获取学生信息
    public function findStudentInfoByWechat($fromuser)
    {
        $sql = "select * from student s,user_bound_wechat ubw
where s.sId=ubw.sId and ubw.WeChatId=\"{$fromuser}\";";
        $result = @mysqli_query($this->software_db, $sql);
        $parts = @mysqli_fetch_array($result, MYSQLI_ASSOC);
        return $parts;
    }

    //判断是否认证,通过微信id
    public function isUserAuthentic($fromuser)
    {
        $sql = "select * from user_bound_wechat ub where WeChatId=\"{$fromuser}\";";
        $result = @mysqli_query($this->software_db, $sql);
     $array = @mysqli_fetch_array($result, MYSQLI_ASSOC);
     if(count($array)>0)
        return true;
        return false;
    }
    //判断是否签到,通过微信id
    public function isUserSigned_in($eventId, $fromuser)
    {
        $sql = "select si.siId
from signin_record si,student s,user_bound_wechat ub
where si.siId={$eventId}  and si.sId=s.sId and s.sId=ub.sId and ub.WeChatId=\"{$fromuser}\";";
        $result = @mysqli_query($this->software_db, $sql);
        $parts = array();
        while ($array = @mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            array_push($parts, array('siId' => $array["siId"]));
        }
        return $parts;
    }


    //判断是否签到,通过学号
    public function isUserSigned_inBySnum($eventId, $sNum)
    {
        $sql = "select *
                from signin_record
                where si.siId={$eventId} sId={$sNum}";
        $result = @mysqli_query($this->software_db, $sql);
        $parts = array();
        while ($array = @mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            array_push($parts, array('siId' => $array["siId"]));
        }

        if(sizeof($parts)>0)
            return true;
        return false;
    }

    //认证
    public function authentic($fromuser, $snum)
    {
        $sql="select * from user_bound_wechat where sid = {$snum} or wechatId='{$fromuser}'";

        $isauth =  @mysqli_query($this->software_db,$sql);//学生绑定
        //$isauth = @mysqli_query($this->software_db, "select * from counsellor where sid = {$snum}");//教工绑定
        if (mysqli_num_rows($isauth)) {
            return 0;//已绑定
        } else {
            $sql="select * from student where sId = {$snum}";
            $result =@mysqli_query($this->software_db,$sql);
            $array=@mysqli_fetch_array($result, MYSQLI_ASSOC);
            if (count($array) > 0) {
                $result = @mysqli_query($this->software_db, "INSERT INTO user_bound_wechat(sId,WeChatId) VALUES({$snum}, '{$fromuser}')");
                if ($result) {
                    return 1;//绑定成功
                } else {
                    return -1;//绑定失败
                }
            }
        }
        return -2;//学号错误
    }

    public function closeConnect()
    {
        @mysqli_close($this->software_db);
    }

    //获取发起签到者的教工号
    public function isSopnsor($fromuser)
    {
        $result = @mysqli_query($this->software_db, 'select *  from counsellor where WeChatId = "' . $fromuser . '"');
        if ($result) {
            $array = @mysqli_fetch_array($result, MYSQLI_ASSOC);
            return $array;
        }
        return false;
    }

    public function findTidBywechat($wechat){

}



    //添加签到事件 param 教工号.开始时间，结束时间
    public function CreateSignInEvent($fromuser, $startTime, $endTime)
    {$teacher=$this->isSopnsor($fromuser);
        if($teacher==false)
            return -1;
        else{
            date_default_timezone_set('PRC');
            $tmptime1=strtotime($startTime);
            $tmptime2=strtotime($endTime);

            if($tmptime1>=$tmptime2){
                return 3;
            }
            $sponsor=$teacher["tId"];
            $sql="select * from signin_event
where sponsor = \"{$sponsor}\" and ('{$startTime}' < starttime
and '{$endTime}'> starttime or '{$startTime}'< endtime
and '{$endTime}'> endtime or '{$startTime}'> starttime and '{$endTime}'< endtime)";
            $result = @mysqli_query($this->software_db,$sql);
//return sizeof(@mysqli_fetch_array($result, MYSQLI_ASSOC));
            if (sizeof(@mysqli_fetch_array($result, MYSQLI_ASSOC)) <= 0)
            {
                $sql= "insert into signin_event(sponsor,startTime,endTime) values({$sponsor}, '{$startTime}', '{$endTime}')";
                $result = @mysqli_query($this->software_db,$sql);
                    if($result)
                        return 1;
                else
                    return 0;
            }else{
                return 2;
            }
        }


    }

    //当前签到所有事件
    public function userHasSign_in($fromuser)
    {date_default_timezone_set('PRC');
        $datatime = date("Y-m-d H:i:s");
        $sql= "select * from signin_event where  startTime < '" . $datatime . "' and '" . $datatime
            . '\'< endTime and sponsor in (select tid from counsellor_manage_class where sClass in '
            . '(select sClass from student where sId in '
            . '(select sid from user_bound_wechat where wechatid = "' . $fromuser . '")))';
        $result = @mysqli_query($this->software_db,$sql);
        //return $sql;
        $parts = array();
        while ($array = @mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            array_push($parts, array(
                'siId' => $array["siId"],
                'sponsor' => $array["sponsor"],
                'startTime'=> $array["startTime"],
                 'startTime'=> $array["endTime"]
                )
            );
        }
        return $parts;
    }


    //判断签到是否过期
    public function checkSignOutOfData($siId){
        date_default_timezone_set('PRC');
        $sql="select * from signin_event where endTime<\"2015-12-10 13:12:00\" and siId={$siId};";
        $result = @mysqli_query($this->software_db, $sql);
       $array = @mysqli_fetch_array($result, MYSQLI_ASSOC);
        if(count($array)>0)
            return true;
        else
            return false;
    }
    //添加签到记录
    public function createSignInRecord($siId, $sunm, $longitude, $latitude, $address)
    {  date_default_timezone_set('PRC');
        $msg = array();
        if(!$this->checkSignOutOfData($siId)){

            if(!$this->isUserSigned_inBySnum($siId, $sunm)){
                $datatime = date("Y-m-d H:i:s");

                if ($address != "") {
                    $result = @mysqli_query($this->software_db,
                        "insert into signin_record values({$siId},{$sunm},'{$datatime}','{$address}',{$longitude},{$latitude})");
//       $sql="insert into signin_record values({$siId},{$sunm},'{$datatime}','{$address}',{$longitude},{$latitude})";
//         echo $sql;
                    if (!$result) {
                        $msg["code"] = false;
                        $msg["msg"] = "系统错误！";
                    } else {
                        $msg["code"] = true;
                        $msg["msg"] = $address;
                    }
                } else {
                    $msg["code"] = false;
                    $msg["msg"] = "可能因为：你的位置获取失败，请检查系统设置或手机安全软件打开位置共享、并授予微信访问位置权限！";
                }


            }else{
                $msg["code"] = true;
                $msg["msg"] = "你已经签过到了！";
            }
        }else{
            $msg["code"] = false;
            $msg["msg"] = "签到过期或签到时间不存在！";
        }


        return $msg;
    }

    //签到事件详情
    public function findInfoByEventId($siId)
    {
        $sql = "select * from signin_event se,counsellor cs
 where se.siid ={$siId} and cs.tId=se.sponsor;";
        $result = @mysqli_query($this->software_db, $sql);
        $parts = array();
        while ($array = @mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            array_push($parts, array('siId' => $array['siId'],
                "sponsor" => $array['teacher'],
                "startTime" => $array['startTime'],
                "endTime" => $array['endTime']));
        }
        return $parts;
    }

    public function unSigninStudent($siId)
    {
        $result = @mysqli_query($this->software_db, "select * from student where sid "
            . "not in (select sid from signin_record where siid = " . $siId
            . ") and sclass in (select sclass from counsellor_manage_class"
            . "where tid=(select sponsor from signin_event where siid = " . $siId . ")"
        );
        $parts = array();
        while ($array = @mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            array_push($parts, array(
                "sCollege" => $array['sCollege'],
                "sMajor" => $array['sMajor'],
                "class" => $array['class'],
                "sId" => $array['sId'],
                "name" => $array['name']
            ));
        }
        return $parts;

    }

    public function findStudentInfoByEventId($siId)
    {
        $result = @mysqli_query($this->software_db, "select * from student where sid "
            . " in (select sid from signin_record where siid = " . $siId . ")");

        $parts = array();
        while ($array = @mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            array_push($parts, array(
                "sCollege" => $array['sCollege'],
                "sMajor" => $array['sMajor'],
                "class" => $array['class'],
                "sId" => $array['sId'],
                "name" => $array['name'],
                "time" => $array['time'],
                "location" => $array['location'],
            ));
        }
        return $parts;
    }

    public function counsellor_manage_class($sopnsor, $class)
    {
        $result = @mysqli_query($this->software_db, "insert into counsellor_manage_class values(" . $sopnsor . "," . $class . ")");
        if (mysql_num_rows($result) > 0)
            return true;
        return false;
    }

    public function changePassward($passward, $sopnsor)
    {
        $result = @mysqli_query($this->software_db, "update counsellor set password = " . $passward . " where tid = " . $sopnsor);
        if (mysql_num_rows($result) > 0)
            return true;
        return false;
    }

}