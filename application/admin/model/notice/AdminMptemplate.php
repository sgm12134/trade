<?php

namespace app\admin\model\notice;

use think\Model;
use traits\model\SoftDelete;

class AdminMptemplate extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'notice_admin_mptemplate';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [

    ];
    

    







    public function admin()
    {
        return $this->belongsTo('app\admin\model\Admin', 'admin_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
