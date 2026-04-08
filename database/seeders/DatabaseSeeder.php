<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\CategoryType;
use App\Enums\GroupRole;
use App\Models\Account;
use App\Models\Category;
use App\Models\FinanceGroup;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'hrach@example.com'],
            ['name' => 'Hrach', 'password' => 'password']
        );

        $group = FinanceGroup::query()->firstOrCreate(
            ['name' => 'Family Budget'],
            ['base_currency' => 'RUB', 'owner_id' => $user->id]
        );

        GroupMember::query()->firstOrCreate(
            ['group_id' => $group->id, 'user_id' => $user->id],
            ['role' => GroupRole::OWNER->value]
        );

        Account::query()->firstOrCreate(
            ['group_id' => $group->id, 'name' => 'Наличные'],
            [
                'user_id' => $user->id,
                'type' => AccountType::CASH,
                'currency' => 'RUB',
                'initial_balance' => 0,
                'current_balance' => 0,
                'is_shared' => true,
                'is_active' => true,
            ]
        );

        Category::query()->firstOrCreate(
            ['group_id' => $group->id, 'name' => 'Еда'],
            ['type' => CategoryType::EXPENSE]
        );

        Category::query()->firstOrCreate(
            ['group_id' => $group->id, 'name' => 'Зарплата'],
            ['type' => CategoryType::INCOME]
        );
    }
}
