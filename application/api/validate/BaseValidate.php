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
        $request = Request::instance();
        $params = $request->param();//拿到参数
        //对这些参数做检验
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

    protected function isPostiveInteger($value, $rule='',
                                        $data='',$field=''){
        if(is_numeric($value) && is_int($value + 0) && ($value + 0) > 0){
            return true;
        }else{
            return false;
        }
    }

    protected function isNotEmpty($value, $rule='',
                                        $data='',$field=''){
        if(empty($value)){
            return false;
        }else{
            return true;
        }
    }

    public function getDataByRule($arrays){
        if(array_key_exists('user_id',$arrays)|
            array_key_exists('uid',$arrays)){
            throw new ParameterExcption([
                'msg'=>'参数中包含有非法的参数名user_id或uid',
            ]);
        }
        $newArray = [];
        //按规则获取参数
        foreach ($this->rule as $key =>$value){
            $newArray[$key] = $arrays[$key];
        }
        return $newArray;
    }

    public function isMoblie($value){
        $rule = '^1(3|4|5|7|8)[0-9]\d{8}$^';
        $result = preg_match($rule, $value);
        if($result){
            return true;
        }else{
            return false;
        }
    }
}