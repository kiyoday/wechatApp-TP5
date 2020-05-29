<?php


namespace app\api\validate;


use app\lib\exception\ParameterExcption;

class OrderPlace extends BaseValidate
{

    protected $rule = [
        'products' => 'checkProducts',
    ];

    protected $singleRule = [
        'product_id' => 'require|isPositiveInteger',
        'count' => 'require|isPositiveInteger',
    ];

    protected function checkProducts($values){
        if(!is_array($values)){
            throw new ParameterExcption([
                'msg' => $values.'需要是数组',
            ]);
        }
        if(empty($values)){
            throw new ParameterExcption([
                'msg' => '商品列表不能为空',
            ]);
        }
        foreach ($values as $value){
            $this->checkProduct($value);
        }
        return true;
    }

    //内部单个验证方法
    protected  function CheckProduct($value){
        $validate = new BaseValidate($this->singleRule);
        $result = $validate->check($value);
        if(!$result){
            throw new ParameterExcption([
                'msg' => '商品列表参数错误',
            ]);
        }
    }


}