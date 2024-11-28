<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Activity;
use App\Models\Address;
use App\Models\LoginTime;
use App\Models\Project;
use App\Models\SubProject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);


        $admin = User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('12345678'),
        ]);
        $admin->assignRole('admin');

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@gmail.com',
            'password' => bcrypt('12345678'),
        ]);
        $user->assignRole('user');
        $user2 = User::factory()->create([
            'name' => 'Test User',
            'email' => 'api@vim-solution.com',
            'password' => bcrypt('12345678'),
        ]);
        $user2->assignRole('user');
        // User::factory()->count(5)->admin()->create();
        // User::factory()->count(5)->user()->create();

        Project::factory(5)->create();
        SubProject::factory(5)->create();
        Address::factory(30)->create();
        // Activity::factory(50)->create();
        // LoginTime::factory(50)->create();
    }
}
