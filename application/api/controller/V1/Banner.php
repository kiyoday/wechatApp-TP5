<?php


namespace app\api\controller\V1;

use app\api\model\Banner as BannerModel;
use app\api\validate\IDMustBePostiveInt;
use app\lib\exception\BannerMissException;
use app\lib\exception\BaseException;
use think\Exception;

class Banner
{
    /*获取指定id的banner信息
    @id banner的id号
    @url /banner/:id
    @http Get
    */
    public function getBanner($id){
        (new IDMustBePostiveInt())->goCheck();//拦截器
        $banner = BannerModel::getBannerByID($id);
        if($banner->isEmpty()){
            throw new BannerMissException();
        }
        $c = config('setting.img_prefix');
        return $banner;
    }
}