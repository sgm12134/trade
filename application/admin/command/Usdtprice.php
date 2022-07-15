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
        $price=SpotApi::getExchangeRate()['data'][0]['usdCny'];
        echo $price;
       Config::set('usdtprice',$price);
    }
}