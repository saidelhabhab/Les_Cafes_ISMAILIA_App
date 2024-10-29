<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        
            // Create roles
            Role::create(['name' => 'admin']);
            Role::create(['name' => 'client']); // Add other roles as necessary
        
    }
}
