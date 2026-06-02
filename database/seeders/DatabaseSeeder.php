<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a deterministic test user and a small set of notes owned by them.
 *
 * The seeder is idempotent: re-running `php artisan db:seed` will not
 * duplicate the test user (it is matched on email via
 * {@see User::firstOrCreate()}) and will only top up the user's notes
 * if they have none.
 *
 * Test credentials (documented in the README):
 *   - email:    test@example.com
 *   - password: password
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        if ($user->notes()->doesntExist()) {
            $user->notes()->createMany([
                [
                    'title' => 'Welcome to the Notes API',
                    'content' => "This is a seeded note for the test user.\n"
                        ."Log in with test@example.com / password to try it out.",
                ],
                [
                    'title' => 'Grocery list',
                    'content' => "- milk\n- eggs\n- bread\n- coffee",
                ],
                [
                    'title' => 'Meeting notes',
                    'content' => 'Quarterly review: ship v1, plan v2 scope, '
                        .'follow up on outstanding tickets.',
                ],
            ]);

            // A handful of factory-generated notes so pagination/search
            // demos have something to chew on.
            Note::factory()->count(7)->for($user)->create();
        }
    }
}
