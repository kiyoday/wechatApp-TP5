<?php


namespace app\api\controller\V1;
use app\api\validate\IDMustBePostiveInt;
use app\api\validate\TestValidate;
use think\Validate;


class Banner
{
    /*获取指定id的banner信息
    @id banner的id号
    @url /banner/:id
    @http Get
    */
    public function getBanner($id){
        (new IDMustBePostiveInt())->goCheck();//拦截器
        $c = 1;
    }
}