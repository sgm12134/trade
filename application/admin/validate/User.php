<?php

namespace app\admin\validate;

use think\Validate;

class User extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'password' => 'regex:\S{6,30}',
        'email'    => 'require|email|unique:user',
        'mobile'   => 'unique:user'
    ];

    /**
     * 字段描述
     */
    protected $field = [
    ];
    /**
     * 提示消息
     */
    protected $message = [
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => [],
        'edit' => ['password', 'email', 'mobile'],
    ];

    public function __construct(array $rules = [], $message = [], $field = [])
    {
        $this->field = [
            'password' => __('Password'),
            'email'    => __('Email'),
            'mobile'   => __('Mobile')
        ];
        parent::__construct($rules, $message, $field);
    }

}
