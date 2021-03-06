<?php


namespace app\api\service;


use app\lib\enum\ScopeEnum;
use app\lib\exception\ForbiddenException;
use app\lib\exception\TokenException;
use think\Cache;
use think\Exception;
use think\Request;

class Token
{
    public static function generateToken(){
        //32个字符组成随机字符串
        $randChars = getRandChars(32);
        //用三组字符串进行md5加密
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        //salt 盐
        $salt = config('secure.token_salt');
        return md5($randChars.$timestamp.$salt);
    }

    public static function getCurrentTokenVar($key){
        $token = Request::instance()
            ->header('token');
        $vars = Cache::get($token);
        if(!$vars){
            throw new TokenException();
        }else{
            if(!is_array($vars)){
                $vars = json_decode($vars,true);
            }
            if(array_key_exists($key,$vars)){
                return $vars[$key];
            }else{
                throw new Exception('尝试获取的Token不存在');
            }
        }
    }

    public static function getCurrentUid(){
        //token
        $uid = self::getCurrentTokenVar('uid');
        return $uid;
    }

    //需用户和管理员访问级别的权限
    public static function needPrimaryScope(){
        $scope = self::getCurrentTokenVar('scope');
        if(!$scope){
            throw new TokenException();
        }
        if($scope >= ScopeEnum::User){
            return true;
        }else{
            throw new ForbiddenException();
        }
    }

    //只有用户 才可以访问的接口权限
    public static function needExclusiveScope(){
        $scope = self::getCurrentTokenVar('scope');
        if(!$scope){
            throw new TokenException();
        }
        if($scope == ScopeEnum::User){
            return true;
        }else{
            throw new ForbiddenException();
        }
    }
    /**
     * 检查操作UID是否合法
     * @param $checkedUID
     * @return bool
     * @throws Exception
     * @throws ParameterException
     */
    public static function isValidOperate($checkedUID){
        if(!$checkedUID){
            throw new Exception('检测UID时必须传入一个UID');
        }
        $currentOperateUID = self::getCurrentUid();
        if($currentOperateUID){
            return true;
        }
        return false;
    }

    public static function verifyToken($token)
    {
        $exist = Cache::get($token);
        if($exist){
            return true;
        }else{
            return false;
        }
    }
}