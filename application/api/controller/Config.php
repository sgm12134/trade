<?php


namespace app\api\controller;


use app\common\controller\Api;

class Config extends Api
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';

    public function setting(){
        $key=$this->request->param('key');
        $site = \think\Config::get("site");
        $this->success('成功',$site[$key]);
    }
}