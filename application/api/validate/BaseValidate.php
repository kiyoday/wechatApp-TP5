<?php


namespace app\api\validate;


use app\lib\exception\ParameterExcption;
use think\Exception;
use think\Request;
use think\Validate;

class BaseValidate extends Validate
{
    public function goCheck(){
        //获取http传入的参数
        //对这些参数做检验
        $request = Request::instance();
        $params = $request->param();//拿到参数

        $result = $this->batch()->check($params);
        if(!$result){
            $e = new ParameterExcption([
                'msg'=>$this->getError(),
            ]);
            throw $e;
        }else{
            return true;
        }
    }
}