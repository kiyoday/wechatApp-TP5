<?php

namespace app\api\model;

use think\Model;

class Image extends Model
{
    protected $hidden = ['id','from','update_time','delete_time'];

}
