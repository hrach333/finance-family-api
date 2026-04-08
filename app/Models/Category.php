<?php

namespace App\Models;

use App\Enums\CategoryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['group_id', 'type', 'name', 'icon_key'];

    protected function casts(): array
    {
        return ['type' => CategoryType::class];
    }
}
