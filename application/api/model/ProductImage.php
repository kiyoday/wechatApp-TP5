<?php


namespace app\api\model;


class ProductImage extends BaseModel
{
    protected $hidden = ['img_id','product_id','delete_time'];

    public function ImgUrl(){
        return $this->belongsTo('Image','img_id','id');
    }
}