<?php


namespace app\api\model;


use think\Db;
use think\Model;

class Banner extends Model
{
    protected $hidden = ['id','update_time','delete_time'];

    public function items(){
        return $this->hasMany('BannerItem','banner_id','id');
    }

    public function items1(){

    }

    public static function getBannerByID($id){
        //TODO:根据banner id号获取banner信息
         $banner = self::with(['items','items.img'])
             ->find($id);
        return $banner;
    }
}