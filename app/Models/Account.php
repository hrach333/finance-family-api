<?php

namespace App\Models;

use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id','user_id','name','type','currency',
        'initial_balance','current_balance','is_shared','is_active',
    ];

    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'is_shared' => 'boolean',
            'is_active' => 'boolean',
            'type' => AccountType::class,
        ];
    }

    public function group()
    {
        return $this->belongsTo(FinanceGroup::class, 'group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
