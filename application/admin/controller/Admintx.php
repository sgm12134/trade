<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Admintx extends Backend
{

    /**
     * Admintx模型对象
     * @var \app\admin\model\Admintx
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Admintx;

    }


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
//            Db::name('admin')->where('id',$this->auth->id)->setInc('money','39.37');
            if( $this->auth->isSuperAdmin()){
                $list = $this->model
                    ->with(['admin'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);
            }else{
                $list = $this->model
                    ->with(['admin'])
                    ->where($where)
                    ->whereIn('admintx.admin_id',$this->auth->id)
                    ->order($sort, $order)
                    ->paginate($limit);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        $this->assignconfig("admin", ['id' => $this->auth->id]);
        return $this->view->fetch();
    }
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function agree($ids){
        $row=$this->model->get($ids);
        $row->state=2;
        $row->update_time=time();
        $row->save();
        $this->success();
    }

    public function refuse($ids){
        if($this->request->isAjax()){
            $row=$this->model->get($ids);
            $row->state=3;
            $params = $this->request->post("row/a");
            $row->remark=$params['remark'];
            $row->update_time=time();
            Db::name('admin')->where('id',$row->admin_id)->setInc('money',$row->num);
            $row->save();
            $this->success();
        }
        return $this->view->fetch();
    }


}
