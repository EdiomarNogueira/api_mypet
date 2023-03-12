<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRelationPet extends Model
{
    public $timestamps = false;
    protected $tables = "user_relations_pets";

}
