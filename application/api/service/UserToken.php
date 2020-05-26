<?php


namespace app\api\service;


use app\api\model\User;
use app\lib\enum\ScopeEnum;
use app\lib\exception\TokenException;
use app\lib\exception\WeChatException;
use think\Exception;
use app\api\model\User as UserModel;

class UserToken extends Token
{
    protected $code;
    protected $wxAppID;
    protected $wxAppSecret;
    protected $wxLoginUrl;

    function __construct($code){
        $this->code = $code;
        $this->wxAppID = config('wx.app_id');
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
                $this->processLoginError($wxResult);
            }else{
                return $this->grantToken($wxResult);
            }
        }
    }

    private function grantToken($wxResult){
        //拿到openid
        //数据库里看一下，是否已经存在
        //不存在则新增一条数据
        //生成令牌，准备缓存数据，写入缓存
        //把令牌返回到客户端去
        //value = wxResult, uid, scope
        $openid = $wxResult['openid'];
        $user = UserModel::getByOpenID($openid);
        if($user){
            $uid = $user->id;
        }else{
            $uid = $this->newUser($openid);
        }
        $cacheValue = $this->prepareCacheValue($wxResult,$uid);
        $token = $this->saveToCache($cacheValue);
        return $token;
    }

    private function saveToCache($cacheValue){
        $key = self::generateToken();//生成令牌
        $value = json_encode($cacheValue);
        $expire_in = config('setting.token_expire_in');//过期时间
        //TP5自带缓存,统一cache配置缓存|文件缓存|redis等
        $request = cache($key, $value, $expire_in);
        if(!$request){
            throw new TokenException([
                'msg' => '服务器缓存异常',
                '$errorCode' => 10005,
            ]);
        }
        return $key;//返回的是token
    }

    //准备缓存openid,封装$cacheValue
    private function prepareCacheValue($wxResult, $uid){
        $cacheValue = $wxResult;
        $cacheValue['uid'] = $uid;
        $cacheValue['scope'] = ScopeEnum::User;//权限
        return $cacheValue;
    }

    //没有openid则新增用户
    private function newUser($openid){
        $user = UserModel::create([
            'openid'=>$openid,
        ]);
        return $user->id;
    }

    //处理登录异常
    private function processLoginError($wxResult){
        throw new WeChatException([
            'msg'=>$wxResult['errmsg'],
            'errorCode'=>$wxResult['errcode'],
        ]);
    }
}