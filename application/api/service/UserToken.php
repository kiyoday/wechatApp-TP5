<?php


namespace app\api\service;


use think\Exception;

class UserToken
{
    protected $code;
    protected $wxAppID;
    protected $wxAppSecret;
    protected $wxLoginUrl;

    function __construct($code){
        $this->code = $code;
        $this->wxAppID = config('wx.appid');
        $this->wxAppSecret = config('wx.app_sercet');
        $this->wxLoginUrl = sprintf(config('wx.login_url'),
            $this->wxAppID,$this->wxAppSecret,$this->code);
    }

    public function get(){
        $result = curl_get($this->wxLoginUrl);
        $wxResult = json_decode($result, true);
        if(empty($wxResult)){
            throw new Exception('获取session_key及openID时异常，微信内部错误');
        }else{
            $loginFail = array_key_exists('errcode',$wxResult);//登录失败
            if($loginFail){

            }else{

            }
        }
    }
}