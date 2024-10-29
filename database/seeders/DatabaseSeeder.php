<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Call the RoleSeeder to ensure roles exist before assigning
     // $this->call(RoleSeeder::class);
     //   $this->call(RolesAndPermissionsSeeder::class);

      // Create Admin User
    /*  $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.ma',
            'password' => bcrypt('12345678'), // Use a secure password
            // Add other fields as necessary
        ]);
       */ 
       // Assign Admin Role
       // $admin->assignRole('admin');

        // Seed Products and Clients
       
     //$this->call(ClientSeeder::class);
      //  $this->call(ProductSeeder::class);
     // $this->call(CountrySeeder::class);

       
    }
}
