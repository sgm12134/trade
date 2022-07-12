<?php
/**
 * User: hnh0000
 * Time: 2021/2/21 3:11 下午
 * QQ: 1123416584
 * Blog: blog@hnh117.com
 */

namespace app\admin\controller\notice;


use app\common\controller\Backend;
use think\Cache;

class Admin extends Backend
{

    protected $noNeedRight = ['mark', 'statistical'];

    public function index()
    {
        $user = $this->auth->getUserInfo();
        $list = \app\admin\model\notice\Notice::where('to_id', $user['id'])
            ->where('platform', 'admin')
            ->where('type','msg')
            ->order('id', 'desc')
            ->paginate(20);

        $config = get_addon_config('notice');
        // 是否有未读的
        $haveUnread = false;
        // 判断是否需要自动标记为已读
        $auto_read = $config['auto_read'] ?? false;
        if ($auto_read) {
            \app\admin\model\notice\Notice::where('id', 'in',array_column($list->items(), 'id'))
                ->update(['readtime' => time()]);
        } else {
            foreach ($list->items() as $item) {
                if ($item['readtime'] === null) {
                    $haveUnread = true;
                    break;
                }
            }
        }

        $this->assign('haveUnread', $haveUnread);
        $this->assign('list', $list);
        $this->assign('title', '我的消息');

        return $this->view->fetch();
    }

    // 标记为已读
    public function mark()
    {
        $user = $this->auth->getUserInfo();

        $where = [];
        if (input('id')) {
            $where['id'] = input('id');
        }

        $count = \app\admin\model\notice\Notice::where('to_id', $user['id'])
            ->where('platform', 'admin')
            ->where('type','msg')
            ->where($where)
            ->order('id', 'desc')
            ->whereNull('readtime')
            ->update(['readtime' => time()]);

        $this->success('', $count);
    }

    // 统计
    public function statistical()
    {
        $user = $this->auth->getUserInfo();
        $statisticalTime = Cache::get('notice_admin_statistical_time_'.$user['id'], 0);
        $new = \app\admin\model\notice\Notice::where('to_id', $user['id'])
            ->where('platform', 'admin')
            ->where('type','msg')
            ->order('id', 'desc')
            ->where('createtime','>', $statisticalTime)
            ->whereNull('readtime')
            ->find();
        if ($new) {
            Cache::set('notice_admin_statistical_time_'.$user['id'], time());
        }
        $data = [
            'num' => \app\admin\model\notice\Notice::where('to_id', $user['id'])
                ->where('platform', 'admin')
                ->where('type','msg')
                ->order('id', 'desc')
                ->whereNull('readtime')
                ->count()
            ,
            'new' => $new,
        ];

        $this->success('', '', $data);
    }
}