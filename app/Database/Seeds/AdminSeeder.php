<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $users = auth()->getProvider();

        // Check if admin already exists
        $existingUser = $users->findByCredentials(['email' => 'rto@yopmail.com']);

        if ($existingUser) {
            echo "Admin user already exists.\n";
            return;
        }

        // Create admin user
        $user = new User([
            'username' => 'rtoadmin',
            'email'    => 'rto@yopmail.com',
            'password' => 'Admin@123',
            'active'   => 1,
        ]);

        $users->save($user);

        // Get the inserted user
        $user = $users->findById($users->getInsertID());

        // Add to admin group
        $user->addGroup('admin');

        // Activate the user (skip email verification for seeded admin)
        $user->activate();

        echo "Admin user created successfully: rto@yopmail.com\n";
    }
}
