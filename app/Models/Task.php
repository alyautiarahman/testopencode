<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    public const PRIORITY_LOW = 'low';

    public const PRIORITY_MEDIUM = 'medium';

    public const PRIORITY_HIGH = 'high';

    /**
     * Urutan prioritas dipakai untuk sorting (makin tinggi makin penting).
     *
     * @var array<string, int>
     */
    public const PRIORITY_ORDER = [
        self::PRIORITY_LOW => 1,
        self::PRIORITY_MEDIUM => 2,
        self::PRIORITY_HIGH => 3,
    ];

    /**
     * Label prioritas untuk ditampilkan di UI.
     *
     * @var array<string, string>
     */
    public const PRIORITY_LABELS = [
        self::PRIORITY_LOW => 'Low',
        self::PRIORITY_MEDIUM => 'Medium',
        self::PRIORITY_HIGH => 'High',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'priority',
        'due_date',
        'is_completed',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Scope: hanya task yang belum selesai.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_completed', false);
    }

    /**
     * Scope: hanya task yang sudah selesai.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope: cari berdasarkan judul / deskripsi.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * Apakah task ini sudah lewat jatuh tempo?
     *
     * Dibandingkan per tanggal saja (bukan waktu), karena cast `date`
     * selalu menghasilkan timestamp 00:00 sehingga `isPast()` akan
     * selalu true pada hari yang sama.
     */
    public function isOverdue(): bool
    {
        return ! $this->is_completed
            && $this->due_date !== null
            && $this->due_date->lt(today());
    }

    /**
     * Apakah task ini jatuh tempo hari ini?
     */
    public function isDueToday(): bool
    {
        return ! $this->is_completed
            && $this->due_date !== null
            && $this->due_date->isSameDay(today());
    }

    /**
     * Kunci warna badge prioritas.
     */
    public function priorityColor(): string
    {
        return match ($this->priority) {
            self::PRIORITY_HIGH => 'rose',
            self::PRIORITY_LOW => 'sky',
            default => 'amber',
        };
    }
}
