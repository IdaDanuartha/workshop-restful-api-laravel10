<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Author;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $admin = User::create([
            "username" => "admin1",
            "email" => "admin@gmail.com",
            "password" => bcrypt("admin123"),
            "role" => "admin"
        ]);

        Admin::create([
            "user_id" => $admin->id, 
            "name" => "Admin 1"
        ]);

        $author = User::create([
            "username" => "author1",
            "email" => "author1@gmail.com",
            "password" => bcrypt("author123"),
            "role" => "author"
        ]);

        Author::create([
            "user_id" => $author->id,
            "name" => "Author 1"
        ]);

        $post = Post::create([
            "title" => "Post 1",
            "slug" => "post-1",
            "content" => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed vel ipsum ac nisi tincidunt feugiat. Sed vel ipsum ac nisi tincidunt feugiat.",
            "user_id" => 1,
            "status" => "published"
        ]);

        Tag::create([
            "name" => "Entertainment",
            "slug" => "entertainment"
        ]);
        Tag::create([
            "name" => "Sport",
            "slug" => "sport"
        ]);
        Tag::create([
            "name" => "Academic",
            "slug" => "academic"
        ]);
        
        $post->tags()->attach([1, 2]);
    }
}