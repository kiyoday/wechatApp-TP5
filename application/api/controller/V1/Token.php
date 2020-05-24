<?php


namespace app\api\controller\V1;


use app\api\service\UserToken;
use app\api\validate\TokenGet;

class Token
{
    public function getToken($code=''){
        (new TokenGet())->goCheck();
        $ut = new UserToken();
        $token = $ut->get($code);
        return $token;
    }
}