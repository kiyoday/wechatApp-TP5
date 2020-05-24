<?php


namespace app\api\validate;


class IDCollection extends BaseValidate
{
    protected $rule=[
        'ids'=>'require|checkIDs'
    ];
    protected $message = [
        'ids'=>'ids参数必须以逗号分隔的多个正整数',
    ];
    //ids=id1,id2,id3
    protected function checkIDs($value){
        $value = explode(',',$value);
        if(empty($value)){
            return false;
        }
        foreach ($value as $id){
            if(!$this->isPostiveInteger($id)){
                return false;
            }
        }
        return true;
    }
}