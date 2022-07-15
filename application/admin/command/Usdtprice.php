<?php


namespace app\admin\command;


use app\admin\model\User;
use think\Config;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use think\Exception;
use Utils\SpotApi;

class Usdtprice extends Command
{
    protected function configure(){
        $this->setName('usdtprice')->setDescription("usdtprice");
    }
    protected function execute(Input $input, Output $output){

        try {
           $dat= http_get('https://www.okx.com/v3/c2c/otc-ticker?t=1657883505409&baseCurrency=USDT&quoteCurrency=CNY');
            Db::name('config')->where('name','usdtprice')->update([
                'value'=>$dat['data']['otcTicker']
            ]);
        } catch (Exception $e) {
            echo $e->getMessage(); // 返回自定义的异常信息
        }


    }
}