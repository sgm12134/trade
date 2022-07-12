<?php

namespace app\index\controller;

use app\common\controller\Frontend;

/**
 * 消息通知
 * Class Notice
 */
class Notice extends Frontend
{

    protected $layout = 'default';

    protected $noNeedRight = ['*'];


    public function index()
    {
        $user = $this->auth->getUser();
        $list = \app\admin\model\notice\Notice::where('to_id', $user['id'])
            ->where('platform', 'user')
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

        return $this->fetch();
    }
}