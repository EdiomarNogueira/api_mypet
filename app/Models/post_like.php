<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post_Like extends Model
{
    public $timestamps = false;
    protected $tables = "post_likes";

}
