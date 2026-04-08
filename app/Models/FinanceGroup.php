<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceGroup extends Model
{
    use HasFactory;

    protected $table = 'finance_groups';

    protected $fillable = [
        'name',
        'base_currency',
        'owner_id',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function groupMembers()
    {
        return $this->hasMany(GroupMember::class, 'group_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'group_members', 'group_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }
}
