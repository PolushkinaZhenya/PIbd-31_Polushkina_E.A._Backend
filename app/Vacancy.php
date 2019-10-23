<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vacancy extends Model
{
    protected $fillable = [
        'position',
        'description',
        'salary',
    ];

    public function images()
    {
        return $this->hasMany(Image::class);
    }
}
