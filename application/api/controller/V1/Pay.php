<?php


namespace app\api\controller\V1;


use app\api\controller\BaseController;
use app\api\validate\IDMustBePostiveInt;

class Pay extends BaseController
{
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'getPreOrder'],
    ];

    //请求预订单信息
    public function getPreOrder($id=''){
        (new IDMustBePostiveInt())->goCheck();
    }
}