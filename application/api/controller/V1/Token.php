<?php


namespace app\api\controller\V1;


use app\api\service\UserToken;
use app\api\validate\TokenGet;

class Token
{
    public function getToken($code=''){
        (new TokenGet())->goCheck();
        $ut = new UserToken($code);
        $token = $ut->get();
        return [//返回json格式
            'token'=>$token
        ];
    }
}