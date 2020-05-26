<?php


namespace app\api\model;


class User extends BaseModel
{
    //拥有外键的一方用belongsto
    public function address(){
        return $this->hasOne('UserAddress','user_id','id');
    }

    public static function getByOpenID($openid){
        $user = self::where('openid','=',$openid)
            ->find();
        return $user;
    }
    

}