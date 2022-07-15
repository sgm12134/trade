<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\AdminMoneyLog;
use app\admin\model\User;
use app\common\controller\Backend;
use app\common\model\Attachment;
use fast\Date;
use think\Db;

/**
 * 控制台
 *
 * @icon   fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        if($this->auth->isSuperAdmin()){
            $payed=   Db::name('order')->where('state',3)->sum('amount');
            $commission=  AdminMoneyLog::where('memo','佣金')->sum('money');
            $admin=Db::name('admin')->sum('money');

        }else{
            //已支付订单
            $payed=   Db::name('order')->where('state',3)->where('admin_id',$this->auth->id)->sum('amount');
            $commission=  AdminMoneyLog::where('user_id',$this->auth->id)->where('memo','佣金')->sum('money');
            $admin=Db::name('admin')->where('id',$this->auth->id)->sum('money');

        }
        $this->assign('payed',$payed);
        $this->assign('commission',$commission);
        $this->assign('admin_money',$admin);
        $this->assign('admin_id',$this->auth->id);

        return $this->view->fetch();
    }

}
