<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Task::query()->delete();

        Task::factory()->create([
            'title' => 'Rancang struktur database aplikasi',
            'description' => 'Menentukan tabel, relasi, dan indeks sebelum mulai coding.',
            'priority' => 'high',
            'due_date' => now()->subDay()->toDateString(),
        ]);

        Task::factory()->create([
            'title' => 'Buat halaman login dan register',
            'priority' => 'high',
            'due_date' => now()->toDateString(),
        ]);

        Task::factory()->create([
            'title' => 'Tulis dokumentasi API',
            'description' => 'Contoh request/response untuk setiap endpoint.',
            'priority' => 'medium',
            'due_date' => now()->addDays(3)->toDateString(),
        ]);

        Task::factory()->create([
            'title' => 'Perbaiki bug notifikasi email',
            'priority' => 'medium',
        ]);

        Task::factory()->completed()->create([
            'title' => 'Setup environment Laravel 13',
            'priority' => 'low',
        ]);

        Task::factory()->completed()->create([
            'title' => 'Install dependency lewat Composer',
            'priority' => 'low',
        ]);

        Task::factory()->count(6)->create();
    }
}
