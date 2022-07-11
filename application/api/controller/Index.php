<?php

namespace app\api\controller;

use app\admin\model\Order;
use app\admin\model\Recharge;
use app\admin\model\Tx;
use app\admin\model\Userloginlog;
use app\common\controller\Api;
use think\Config;
use think\Db;
use think\Validate;

/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     */
    public function index()
    {
        $this->success('请求成功');
    }

    public function  recharge(){
        $amount = $this->request->param('amount');
        $hash_id = $this->request->param('hash_id');
        $image = $this->request->param('image');
        $recharge_min=Config::get('site.recharge_min');
        $recharge_max=Config::get('site.recharge_max');
        if(empty($image)){
            $this->error(__('上传截图不能为空'));
        }
        if($amount<$recharge_min){
            $this->error(__('充值金额不能小于'.$recharge_min.'USDT'));
        }
        if($recharge_max<$amount){
            $this->error(__('充值数量不能大于'.$recharge_max.'USDT'));
        }
        if(empty($amount)){
            $this->error(__('数量不能为空'));
        }
        $ret= Recharge::create([
            'user_id'=>$this->auth->id,
            'amount'=>$amount,
            'image'=>$image,
            'time'=>time(),
            'state'=>1,
            'tx_id'=>$hash_id
        ]);
        if ($ret) {
            $this->success(__('提交成功,等待审核'));
        } else {
            $this->error('提交失败');
        }


    }

    public function tx(){
        $amount = $this->request->param('amount');
        $address= $this->request->param('address');
        $remark= $this->request->param('remark');
        $type= $this->request->param('type');
        $account= $this->request->param('account');
        $code= $this->request->param('code');
        $pay_password= $this->request->param('pay_password');
        if(empty($pay_password)){
            $this->error(__('支付密码不能为空'));
        }
        $tx_min=Config::get('site.tx_min');
        $tx_max=Config::get('site.tx_max');
        $tx_fee=Config::get('site.tx_fee');
        $tx_times=Config::get('site.tx_times');
        if($amount<$tx_min){
            $this->error(__('提现数量不能小于'.$tx_min.'USDT'));
        }
        if($tx_max>$amount){
            $this->error(__('提现数量不能大于'.$tx_max.'USDT'));
        }
        if(empty($address)){
            $this->error(__('提币地址不能为空'));
        }
        $tx_count=Db::name('tx') ->where('user_id',$this->auth->id)->whereTime('time', 'today')->count();
        if($tx_count>=$tx_times){
            $this->error('今日提现次数已满');
        }

        if ($type == 'mobile') {
            if (!Validate::regex($account, "^1\d{10}$")) {
                $this->error(__('Mobile is incorrect'));
            }
            $user = \app\common\model\User::getByMobile($account);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Sms::check($account, $code, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Sms::flush($account, 'tx');
        } else {
            if (!Validate::is($account, "email")) {
                $this->error(__('Email is incorrect'));
            }
            $user = \app\common\model\User::getByEmail($account);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Ems::check($account, $code, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Ems::flush($account, 'tx');
        }
        $user=$this->auth->getUser();
        if(empty($user['pay_password'])){
            $this->error(__('请现设置支付密码'));
        }
        if($pay_password !=$user['pay_password']){
            $this->error(__('支付密码不正确'));
        }
        if($amount>$user['money']){
            $this->error(__('余额不足'));
        }
        $ret= Tx::create([
            'user_id'=>$this->auth->id,
            'amount'=>$amount,
            'real_amount'=>bcsub($amount,bcmul($tx_fee,$amount,2),2),
            'time'=>time(),
            'remark'=>$remark,
            'state'=>1,
            'address'=>$address
        ]);
        if ($ret) {
            \app\common\model\User::money(-$amount,$this->auth->id,'提笔申请成功');
            $this->success(__('提交成功,等待审核'));
        } else {
            $this->error('提交失败');
        }
    }

    public function submit(){
        $par=$this->request->param();
        $data=$par['data'];
        $type=$par['type'];
        foreach ($data  as $k=>$v){
              foreach ($v as $k1=>$v1){
                  if(empty($v1)){
                      $this->error('请填写完整');
                  }
              }
        }
        $total=array_sum(array_column($data, 'total')); //总余额
        $user=$this->auth->getUserinfo();
        $order_fee=Config::get('site.order_fee');
        $order_max=Config::get('site.order_max');
        $order_min=Config::get('site.order_min');
        if($user['money']<$total){
            $this->error('余额不足');
        }
        $inser_data=[];
        foreach ($data as  $k=>$v){
            if($v['amount']<$order_min){
                $this->error('请正确提交订单');
            }
            if($v['amount']>$order_max){
                $this->error('请正确提交订单');
            }
            if($type =='银行卡')
                sleep(1);
                $inser_data[]=[
                    'pay_way'=>1,//银行卡
                    'username'=>$v['name'],
                    'bank'=>$v['bank'],
                    'order_no'=>'A'.time(),
                    'bankaccount'=>$v['banknum'],
                    'state'=>1,
                    'submit_time'=>time(),
                    'amount'=>$v['amount'],
                    'fee'=>bcmul($v['amount'],$order_fee,2),
                    'all'=>$v['total'],
                ];
        }
        $res=db('order')->insertAll($inser_data,'true');
        if($res){
            return $this->success('提交成功');
        }else{
            return $this->error('提交失败');
        }


    }

    public function txlog(){
        $data= Tx::with(['user'=>function($query){
            $query->field('username');
        }])->where('user_id',$this->auth->id)->select();
        foreach ($data as $k=>$v){
            $v->time_str=date('Y-h-d m:s:i',$v->time);
        }
        $this->success(__('成功'),$data);

    }


    public function czlog(){
        $data= Recharge::with(['user'=>function($query){
            $query->field('username');
        }])->where('user_id',$this->auth->id)->select();
        foreach ($data as $k=>$v){
            $v->time_str=date('Y-h-d m:s:i',$v->time);
        }
        $this->success(__('成功'),$data);

    }

    public function userlog(){
        $data=Userloginlog::where('user_id',$this->auth->id)->select();

        foreach ($data as $k=>$v){
            $v->time_str=date('Y-h-d m:s:i',$v->time);
        }
        $this->success(__('成功'),$data);
    }

    public function fund(){
        $data=  Db::name('user_money_log')->where('user_id',$this->auth->id)->select();
        foreach ($data as $k=>$v){
            $data[$k]['createtime_str']=date('Y-h-d m:s:i',$v['createtime']);
        }
        $this->success(__('成功'),$data);
    }

    public function userprice(){
        $this->success(__('成功'),6.5);
    }

    public function bank(){
        $bank=  Db::name('bank')->where('state',1)->select();
        $this->success(__('成功'),$bank);
    }
}
