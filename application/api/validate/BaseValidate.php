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

        $result = $this->check($params);
        if(!$result){
            $e = new ParameterExcption([
                'msg'=>$this->getError(),
            ]);
//            $e->msg = $this->getError();
//            $e->errorCode = 10002;
            throw $e;
//            $error = $this->getError();
//            throw new Exception($error);
        }else{
            return true;
        }
    }
}