<?php

namespace app\admin\model\notice;

use think\Model;
use think\db\Query;
use traits\model\SoftDelete;

class NoticeEvent extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'notice_event';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
//        'platform_text',
//        'type_text',
//        'visible_switch_text'
    ];

    public function getContentArrAttr()
    {
        $value = $this['content'];
        $value = (array) json_decode($value);
        return $value;
    }

    public function getPlatformList()
    {
        return ['user' => __('Platform user'), 'admin' => __('Platform admin')];
    }

    public function getTypeList()
    {
        return ['msg' => __('Type msg'), 'email' => __('Type email'), 'mptemplate' => '模版消息(公众号)'];
    }

    public function getVisibleSwitchList()
    {
        return ['0' => __('Visible_switch 0'), '1' => __('Visible_switch 1')];
    }


    public function getPlatformTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['platform']) ? $data['platform'] : '');
        $valueArr = explode(',', $value);
        $list = $this->getPlatformList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $valueArr = explode(',', $value);
        $list = $this->getTypeList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }


    public function getVisibleSwitchTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['visible_switch']) ? $data['visible_switch'] : '');
        $list = $this->getVisibleSwitchList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setPlatformAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    protected function setTypeAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }


    public function scopeFrontend(Query $query, $params = [])
    {
        $query->where('__TABLE__.visible_switch', 1)
            ->order(['__TABLE__.id'=>'desc']);
    }

}
