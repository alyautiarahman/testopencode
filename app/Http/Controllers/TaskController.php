<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Daftar task dengan filter, pencarian, pengurutan, dan statistik.
     */
    public function index(Request $request): View
    {
        $filters = ['all', 'active', 'completed'];
        $sorts = ['created', 'due', 'priority', 'title'];

        $filter = in_array($request->query('filter'), $filters, true)
            ? $request->query('filter')
            : 'all';

        $sort = in_array($request->query('sort'), $sorts, true)
            ? $request->query('sort')
            : 'created';

        $search = (string) $request->query('q', '');

        $query = Task::query()->search($search);

        $query->when($filter === 'active', fn ($q) => $q->active())
            ->when($filter === 'completed', fn ($q) => $q->completed());

        match ($sort) {
            'due' => $query->orderByRaw('due_date is null, due_date asc'),
            'priority' => $query->orderByRaw('case priority when \'high\' then 1 when \'medium\' then 2 else 3 end')
                ->orderByDesc('is_completed')
                ->orderByDesc('id'),
            'title' => $query->orderBy('title'),
            default => $query->orderByDesc('id'),
        };

        $tasks = $query->get();

        // Statistik dihitung dari seluruh task tanpa filter/search.
        $total = Task::count();
        $completed = Task::completed()->count();
        $overdue = Task::query()
            ->active()
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->count();

        return view('tasks.index', [
            'tasks' => $tasks,
            'filter' => $filter,
            'sort' => $sort,
            'search' => $search,
            'total' => $total,
            'completed' => $completed,
            'active' => $total - $completed,
            'overdue' => $overdue,
            'progress' => $total > 0 ? (int) round($completed / $total * 100) : 0,
        ]);
    }

    /**
     * Simpan task baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'priority' => ['required', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
        ]);

        Task::create($data);

        return $this->back('Task berhasil ditambahkan.');
    }

    /**
     * Perbarui task yang sudah ada.
     */
    public function update(Request $request, Task $task): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'priority' => ['required', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
        ]);

        $wasCompleted = $task->is_completed;
        $isCompleted = (bool) $request->boolean('is_completed');

        $task->fill($data);
        $task->is_completed = $isCompleted;
        $task->completed_at = $isCompleted
            ? ($task->completed_at ?? now())
            : null;
        $task->save();

        if ($wasCompleted !== $isCompleted) {
            return $this->back($isCompleted ? 'Task ditandai selesai.' : 'Task dibuka kembali.');
        }

        return $this->back('Task berhasil diperbarui.');
    }

    /**
     * Tandai task selesai / belum selesai.
     */
    public function toggle(Task $task): RedirectResponse
    {
        $task->is_completed = ! $task->is_completed;
        $task->completed_at = $task->is_completed ? now() : null;
        $task->save();

        return $this->back($task->is_completed ? 'Kerja bagus! Task selesai. 🎉' : 'Task dibuka kembali.');
    }

    /**
     * Hapus satu task.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return $this->back('Task berhasil dihapus.');
    }

    /**
     * Hapus semua task yang sudah selesai.
     */
    public function clearCompleted(Request $request): RedirectResponse
    {
        $deleted = Task::completed()->delete();

        return $this->back($deleted > 0
            ? "{$deleted} task selesai dihapus."
            : 'Belum ada task yang selesai.');
    }

    /**
     * Kembali ke halaman sebelumnya dengan pesan sukses,
     * query string (filter/search/sort) tetap dipertahankan.
     */
    private function back(string $message): RedirectResponse
    {
        return redirect()
            ->back()
            ->with('status', $message)
            ->withInput(request()->only(['_token']));
    }
}
