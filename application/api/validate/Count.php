<?php


namespace app\api\validate;


class Count extends BaseValidate
{
    protected $rule = [
        'count' => 'isPositiveInteger|between:1,15',
    ];

    protected $message = [
        'count' => 'count数目需要为1~15之间的正整数',
    ];
}