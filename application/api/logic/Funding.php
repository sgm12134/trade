<?php


namespace app\index\logic;


use think\Db;

class Funding
{


      public   static  function insert($money, $user_id,$type,$before,$after,$memo){
        $data['money']=$money;
        $data['createtime']=time();
        $data['user_id']=$user_id;
        $data['type']=$type;
        $data['before']=$before;
        $data['after']=$after;
        $data['memo']=$memo;
        return  Db::name('funding')->insertGetId($data);
    }
}