<?php


namespace app\api\model;


class Product extends BaseModel
{
    protected $hidden = ['create_time', 'main_img_id',
        'pivot','from','category_id',
        'update_time','delete_time'];

    public function getMainImgUrlAttr($value,$data){
        return $this->prefixImgUrl($value,$data);
    }

    public static function getMostRecent($count){
        $product = self::limit($count)
            ->order('create_time desc')
            ->select();
        return $product;
    }

    public static function getProductsByCategoryID($categoryID){
        $products = self::where('category_id','=',$categoryID)
            ->select();
        return $products;
    }
}