<?php

namespace app\admin\controller\notice;

use app\common\controller\Backend;
use think\Cache;

/**
 * 管理员绑定微信(模版消息公众号)
 *
 * @icon fa fa-circle-o
 */
class AdminMptemplate extends Backend
{

    /**
     * AdminMptemplate模型对象
     * @var \app\admin\model\notice\AdminMptemplate
     */
    protected $model = null;

    protected $noNeedRight = ['bind'];

    protected $searchFields = ['id','admin.nickname','openid'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\notice\AdminMptemplate;

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


    // 绑定模版消息(公众号)
    public function bind()
    {
        $exist = \app\admin\model\notice\AdminMptemplate::where('admin_id', $this->auth->id)->find();

        if ($this->request->isPost()) {
            $exist->delete();
            $this->success('解绑成功');
        }

        $this->assign('exist', $exist);

        $url = '';
        // 5分钟有效的绑定连接
        if (!$exist){
            $mark = 'notice_bmp'.uniqid();
            Cache::set($mark, $this->auth->id, 60*5);
            $url = addon_url('notice/index/mpauth', ['mark' => $mark], false, true);
        }

        $this->assignconfig('url', $url);
        return $this->fetch();
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
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->with(['admin'])
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['admin'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $row) {

                $row->getRelation('admin')->visible(['id','nickname']);
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }
}
