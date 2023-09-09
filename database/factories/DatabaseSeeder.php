<?php
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        \App\Models\UserAuthentication::factory(10)->create(); // Example: Create 10 UserAuthentication instances
        // Other seeders if any
    }
}

