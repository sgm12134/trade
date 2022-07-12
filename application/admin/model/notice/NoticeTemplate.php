<?php

namespace app\admin\model\notice;

use think\Model;


class NoticeTemplate extends Model
{

    

    

    // 表名
    protected $name = 'notice_template';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
    ];
    



    public function getVisibleSwitchList()
    {
        return ['0' => __('Visible_switch 0'), '1' => __('Visible_switch 1')];
    }


    public function getVisibleSwitchTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['visible_switch']) ? $data['visible_switch'] : '');
        $list = $this->getVisibleSwitchList();
        return isset($list[$value]) ? $list[$value] : '';
    }




    public function noticeevent()
    {
        return $this->belongsTo('NoticeEvent', 'notice_event_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function getUrlTypeList()
    {
        $type = $this['type'] ?? '';
        if ($type == 'msg') {
            return [1 => '链接', 2=>'弹窗', 3=>'新窗口'];
        }
        if ($type == 'mptemplate') {
            return [1 => '链接'];
        }
        return [1 => '链接', 2=>'弹窗', 3=>'新窗口'];
    }
}
