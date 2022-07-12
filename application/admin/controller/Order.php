<?php

namespace app\admin\controller;

use app\admin\model\User;
use app\common\controller\Backend;
use app\common\library\Email;
use think\Validate;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Order extends Backend
{

    /**
     * Order模型对象
     * @var \app\admin\model\Order
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Order;

    }

    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

           if( $this->auth->isSuperAdmin()){
               $list = $this->model
                   ->with(['user','admin'])
                   ->where($where)
                   ->order($sort, $order)
                   ->paginate($limit);
           }else{
               $list = $this->model
                   ->with(['user','admin'])
                   ->where($where)
                   ->whereIn('user.admin_id',$this->auth->id)
                   ->order($sort, $order)
                   ->paginate($limit);
           }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }




    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function agree($ids){
        if($this->request->isAjax()){
        $row=$this->model->get($ids);
        $params = $this->request->post("row/a");
        if($row->state !=2){
            $this->error('这个状态不能操作');
        }
        $row->state=3;
        if(empty($row->admin_id)){
            $this->error('未分配打款员不能操作');
        }
        $row->time=time();
        $row->payment_voucher=$params['payment_voucher'];
        $row->save();
        $user=User::find($row->user_id);
          if(is_valid_email($user->username)){
              $receiver=$user->username;
              if ($receiver) {
                  if (!Validate::is($receiver, "email")) {
                      $this->error(__('Please input correct email'));
                  }
                  $email = new Email;
                  $result = $email
                      ->to($receiver)
                      ->subject(__("打款成功"))
                      ->message('')
                      ->send();
                  if ($result) {
                      $this->success();
                  } else {
                      $this->error($email->getError());
                  }
              } else {
                  $this->error(__('Invalid parameters'));
              }
          }else{

          }


        }
        return $this->view->fetch();
    }

    public function entrust($ids){
        if($this->request->isAjax()){
            $row=$this->model->get($ids);
            if(!empty($row->admin_id)){
                $this->error('不能重复分配');
            }
            if($row->state!=1){
                $this->error('这个状态不能操作');
            }
            $params = $this->request->post("row/a");
            $row->admin_id=$params['admin_id'];
            $row->save();
            $row->state=2;
            $this->success();
        }
        return $this->view->fetch();

    }

    public function refuse($ids){
        if($this->request->isAjax()){
            $row=$this->model->get($ids);
            if($row->state !=2){
                $this->error('这个状态不能操作');
            }
            $row->state=4;
            $params = $this->request->post("row/a");
            $row->remark=$params['remark'];
            $row->update_time=time();
            \app\common\model\User::money($row->allusdt,$row->user_id,'拒绝打款');
            $row->save();
            $this->success();
        }
        return $this->view->fetch();
    }

}
