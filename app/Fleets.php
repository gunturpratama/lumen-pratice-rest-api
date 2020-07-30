<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Fleets extends Model
{
    protected $guarded = [];
    protected $appends = ['photo_url'];


    public function getPhotoUrlAttribute(){
        return url('Fleets/'.$this->photo);
    }
}
