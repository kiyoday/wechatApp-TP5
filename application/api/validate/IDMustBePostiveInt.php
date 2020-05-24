<?php


namespace app\api\validate;


class IDMustBePostiveInt extends BaseValidate
{
    protected $rule =[
        'id'=>'require|isPostiveInteger',
        'num'=>'in:1,2,3',
    ];

    protected $message=[
        'id'=>'id必须是正整数'
    ];
}