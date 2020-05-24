<?php


namespace app\api\controller\V2;

use app\api\model\Banner as BannerModel;
use app\api\validate\IDMustBePostiveInt;
use app\lib\exception\BannerMissException;

class Banner
{
    /*获取指定id的banner信息
    @id banner的id号
    @url /banner/:id
    @http Get
    */
    public function getBanner($id){
        return 'this is v2 version';
    }
}