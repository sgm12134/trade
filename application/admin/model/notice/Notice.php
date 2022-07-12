<?php

namespace app\admin\model\notice;

use think\Model;
use traits\model\SoftDelete;

class Notice extends Model
{

    use SoftDelete;


    // 表名
    protected $name = 'notice';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
//        'type_text',
//        'platform_text',
//        'readtime_text'
    'ext_arr',
    ];
    

    
    public function getTypeList()
    {
        return ['msg' => __('Type msg'), 'email' => __('Type email'), 'mptemplate' => '模版消息(公众号)'];
    }

    public function getPlatformList()
    {
        return ['user' => __('Platform user'), 'admin' => __('Platform admin')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getPlatformTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['platform']) ? $data['platform'] : '');
        $list = $this->getPlatformList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getReadtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['readtime']) ? $data['readtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setReadtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function noticetemplate()
    {
        return $this->belongsTo('NoticeTemplate', 'notice_template_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function getExtArrAttr($value, $data)
    {
        $value = $data['ext'] ?? '';
        $value = json_decode($value, true) ?? [];
        if (!isset($value['url'])) {
            $value['url'] = null;
        }
        if (!isset($value['url_type'])) {
            $value['url_type'] = null;
        }
        if (!isset($value['url_title'])) {
            $value['url_title'] = '';
        }
        if ($value['url']) {
            if (0 === stripos($value['url'], 'http')) {

            } else if (0 === stripos($value['url'], '/')) {

            } else {
                $value['url'] = url($value['url']);
            }
        }

        return $value;
    }
}
