<?php


namespace app\api\validate;


class AddressNew extends BaseValidate
{
    //uid不能在这里传递，防止其他用户修改
    protected $rule =[
        'name' => 'require|isNotEmpty',
        'mobile' => 'require|isMoblie' ,
        'province' => 'require|isNotEmpty',
        'city' => 'require|isNotEmpty',
        'country' => 'require|isNotEmpty',
        'detail'=> 'require|isNotEmpty' ,
     ];

}