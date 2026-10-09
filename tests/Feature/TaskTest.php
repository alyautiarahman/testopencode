<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_with_tasks(): void
    {
        Task::factory()->count(3)->create();

        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertSee('Tasks')
            ->assertSee(Task::first()->title);
    }

    public function test_home_page_handles_empty_state(): void
    {
        $this->get('/')->assertStatus(200)->assertSee('Belum ada task');
    }

    public function test_user_can_create_a_task(): void
    {
        $response = $this->post('/tasks', [
            'title' => 'Membuat laporan mingguan',
            'priority' => 'high',
            'due_date' => now()->addDays(2)->toDateString(),
            'description' => 'Ringkasan sprint terakhir.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'title' => 'Membuat laporan mingguan',
            'priority' => 'high',
        ]);
    }

    public function test_task_title_is_required(): void
    {
        $response = $this->from('/')->post('/tasks', [
            'title' => '',
            'priority' => 'medium',
        ]);

        $response->assertSessionHasErrors('title');
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_priority_must_be_valid(): void
    {
        $response = $this->post('/tasks', [
            'title' => 'Task valid',
            'priority' => 'urgent',
        ]);

        $response->assertSessionHasErrors('priority');
    }

    public function test_user_can_toggle_completion(): void
    {
        $task = Task::factory()->create(['is_completed' => false]);

        $this->post("/tasks/{$task->id}/toggle")->assertRedirect();

        $this->assertTrue($task->fresh()->is_completed);
        $this->assertNotNull($task->fresh()->completed_at);

        $this->post("/tasks/{$task->id}/toggle")->assertRedirect();

        $this->assertFalse($task->fresh()->is_completed);
        $this->assertNull($task->fresh()->completed_at);
    }

    public function test_user_can_update_a_task(): void
    {
        $task = Task::factory()->create([
            'title' => 'Judul lama',
            'priority' => 'low',
        ]);

        $response = $this->put("/tasks/{$task->id}", [
            'title' => 'Judul baru',
            'description' => 'Deskripsi baru',
            'priority' => 'high',
            'due_date' => '2030-01-15',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Judul baru',
            'priority' => 'high',
        ]);
    }

    public function test_user_can_delete_a_task(): void
    {
        $task = Task::factory()->create();

        $this->delete("/tasks/{$task->id}")->assertRedirect();

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_clear_completed_only_removes_finished_tasks(): void
    {
        Task::factory()->count(2)->completed()->create();
        Task::factory()->count(3)->create(['is_completed' => false]);

        $this->delete('/tasks/completed')->assertRedirect();

        $this->assertSame(0, Task::completed()->count());
        $this->assertSame(3, Task::count());
    }

    public function test_index_filters_by_status(): void
    {
        Task::factory()->count(2)->completed()->create();
        Task::factory()->count(3)->create(['is_completed' => false]);

        $this->get('/?filter=active')
            ->assertStatus(200)
            ->assertSee('filter <span class="font-semibold">Aktif</span>', false);

        $this->get('/?filter=completed')->assertStatus(200);

        $this->get('/?filter=invalid')->assertStatus(200);
    }

    public function test_index_searches_tasks(): void
    {
        Task::factory()->create(['title' => 'Belanja bahan makanan']);
        Task::factory()->create(['title' => 'Rakit rak buku']);

        $response = $this->get('/?q=belanja');

        $response->assertStatus(200)
            ->assertSee('Belanja bahan makanan')
            ->assertDontSee('Rakit rak buku');
    }

    public function test_priority_sort_puts_high_first(): void
    {
        Task::factory()->create(['title' => 'Prioritas rendah', 'priority' => 'low']);
        Task::factory()->create(['title' => 'Prioritas tinggi', 'priority' => 'high']);

        $response = $this->get('/?sort=priority');

        $response->assertStatus(200)->assertSeeInOrder([
            'Prioritas tinggi',
            'Prioritas rendah',
        ]);
    }

    public function test_task_due_today_is_not_marked_overdue(): void
    {
        $dueToday = Task::factory()->create([
            'due_date' => today()->toDateString(),
            'is_completed' => false,
        ]);
        $overdue = Task::factory()->create([
            'due_date' => today()->subDay()->toDateString(),
            'is_completed' => false,
        ]);
        $finishedLate = Task::factory()->completed()->create([
            'due_date' => today()->subDay()->toDateString(),
        ]);

        $this->assertFalse($dueToday->fresh()->isOverdue(), 'Task jatuh tempo hari ini bukan terlambat.');
        $this->assertTrue($dueToday->fresh()->isDueToday());
        $this->assertTrue($overdue->fresh()->isOverdue());
        $this->assertFalse($finishedLate->fresh()->isOverdue(), 'Task selesai tidak pernah dianggap terlambat.');
    }

    public function test_edit_modal_is_present_on_the_page(): void
    {
        Task::factory()->completed()->create();

        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Edit task', false)
            ->assertSee('Bersihkan yang selesai', false);
    }
}
