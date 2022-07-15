<?php


namespace app\admin\command;


use app\admin\model\User;
use think\Config;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use Utils\SpotApi;

class Usdtprice extends Command
{
    protected function configure(){
        $this->setName('usdtprice')->setDescription("usdtprice");
    }
    protected function execute(Input $input, Output $output){

        try {
            $price=SpotApi::getExchangeRate()['data'][0]['usdCny'];
            Db::name('config')->where('name','usdtprice')->update([
                'value'=>$price
            ]);
        } catch (Exception $e) {
            echo $e->getMessage(); // 返回自定义的异常信息
        }


    }
}