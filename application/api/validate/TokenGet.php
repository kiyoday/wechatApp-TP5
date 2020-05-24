<?php


namespace app\api\validate;


class TokenGet extends BaseValidate
{
    protected $rule = [
        'code' => 'require|isNotEmpty',
    ];

    protected $message = [
         'code' => '需要code才能获取Token',
    ];

}