<?php


namespace app\api\model;


class Theme extends BaseModel
{
    //topic表和img表关联关系
    public function topicImg(){
        return $this->belongsTo('Image','topic_img_id','id');
    }

    public function headImg(){
        return $this->belongsTo('Image','head_img_id','id');
    }
}