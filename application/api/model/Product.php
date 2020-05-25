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

    public function imgs(){
        return $this->hasMany('ProductImage','product_id','id');
    }

    public function properties(){
        return $this->hasMany('ProductProperty','product_id','id');
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

    public static function getProductDetail($id){
        //链式方法其实是一个Query
        $products = self::with([
            'imgs'=>function($query){
                $query->with('imgUrl')
                ->order('order','asc');//关联
            }
        ])
            ->with(['properties'])
            ->find($id);
        return $products;
    }
}