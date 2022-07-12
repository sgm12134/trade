<?php

namespace app\admin\controller\notice;

use addons\notice\library\NoticeClient;
use app\common\controller\Backend;
use think\Db;
use think\Hook;

/**
 * 消息事件
 *
 * @icon fa fa-circle-o
 */
class Event extends Backend
{
    
    /**
     * NoticeEvent模型对象
     * @var \app\admin\model\notice\NoticeEvent
     */
    protected $model = null;

    protected $searchFields = ['id','name','event'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\notice\NoticeEvent;
        $this->view->assign("platformList", $this->model->getPlatformList());
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->assignconfig("typeList", $this->model->getTypeList());
        $this->view->assign("visibleSwitchList", $this->model->getVisibleSwitchList());
    }

    public function import()
    {
        parent::import();
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    public function test($ids)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isAjax()) {
            $params = input('row/a');
            $params = $this->preExcludeFields($params);
            $params = array_only($params, '*');
            $params['field']['receiver_admin_ids']  = input('row.receiver_admin_ids');
            $params['field']['receiver_admin_group_ids']  = input('row.receiver_admin_group_ids');
            $params['field'] = array_filter($params['field'], function ($v) {
                return $v != '';
            });
            if (!$params) {
                $this->error(__('Parameter %s can not be empty', ''));
            }
            Db::startTrans();
            try{
                // 函数发送
//                $is = NoticeClient::instance()->trigger($row['event'], $params['field']);
                // 行为发送
                $noticeParams = [
                    'event' => $row['event'],
                    'params' => $params['field']
                ];
                $is = Hook::listen('send_notice', $noticeParams, null, true);
                Db::commit();
            }catch (\Exception $e) {
                $this->error($e->getMessage());
                Db::rollback();
            }
            if ($is) {
                $this->success('操作成功');
            }
            $this->error('发送失败:'.NoticeClient::instance()->getError());
        }

        $row->content_arr2 = array_merge(['receiver_admin_ids' => '', 'receiver_admin_group_ids' => '',], $row['content_arr']);
        $this->assign('row', $row);

        return $this->fetch();
    }


    public function edit($ids = null)
    {
        // copy数据
        if ($this->request->isPost() && input('is_copy')) {
            $params = $this->request->post("row/a");
            $params = $this->preExcludeFields($params);
            $this->model->save($params);
            $this->success('添加成功');
        }
        return parent::edit($ids);
    }

}
