<?php


namespace app\api\model;


class Theme extends BaseModel
{
    protected $hidden = ['topic_img_id', 'head_img_id',
        'update_time','delete_time'];

    //topic表和img表关联关系
    public function topicImg(){
        return $this->belongsTo('Image','topic_img_id','id');
    }

    public function headImg(){
        return $this->belongsTo('Image','head_img_id','id');
    }
    //（表名，中间表，中间表对应外表关系，对应内表关系）
    public function products(){
        return $this->belongsToMany('Product','theme_product',
            'product_id','theme_id');
    }

    public static function getThemeWithProducts($id){
        $theme = self::with('Products,topicImg,headImg')
            ->find($id);
        return $theme;
    }
}