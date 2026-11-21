<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Team;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams official competition data as CSV. Only published categories are
 * ever exported, so unverified/draft results can never leak.
 */
class ResultExportService
{
    public function __construct(private readonly StandingsService $standings) {}

    /** Official standings for every published category of an event. */
    public function standings(Event $event): StreamedResponse
    {
        $categories = $event->categories->filter(fn (Category $c) => $c->isPublished())->sortBy('level');

        $rows = [];
        foreach ($categories as $category) {
            foreach ($this->standings->forCategory($category) as $row) {
                // A time-mode category has no meaningful points; export the
                // official stopwatch value instead so the CSV stays truthful.
                $official = ($row['mode'] ?? 'jury') === 'time'
                    ? StandingsService::formatMs($row['time_ms'])
                    : $row['best_total'];

                $rows[] = [
                    $category->level->publicLabel(),
                    $category->name,
                    $row['rank'],
                    $row['team']->name,
                    $row['team']->school?->name ?? '',
                    $official,
                    ($row['mode'] ?? 'jury') === 'time' ? 'Waktu' : 'Poin',
                    $row['runs'],
                ];
            }
        }

        return $this->stream(
            'hasil-resmi-'.$event->slug.'.csv',
            ['Level', 'Kategori', 'Peringkat', 'Tim', 'Sekolah', 'Nilai Resmi', 'Satuan', 'Jumlah Run'],
            $rows,
        );
    }

    /** Participation roster: every team and its verification status. */
    public function participation(Event $event): StreamedResponse
    {
        $categoryIds = $event->categories->pluck('id');

        $teams = Team::with(['school', 'category'])
            ->whereIn('category_id', $categoryIds)
            ->orderBy('name')
            ->get();

        $registrations = Registration::whereIn('category_id', $categoryIds)
            ->get()
            ->keyBy('team_id');

        $rows = $teams->map(function (Team $team) use ($registrations) {
            $registration = $registrations->get($team->id);

            return [
                $team->category?->level->publicLabel(),
                $team->category?->name,
                $team->name,
                $team->school?->name ?? '',
                $registration?->status->label() ?? 'Belum terdaftar',
                $team->status?->label() ?? '—',
            ];
        })->all();

        return $this->stream(
            'partisipasi-'.$event->slug.'.csv',
            ['Level', 'Kategori', 'Tim', 'Sekolah', 'Status Registrasi', 'Terverifikasi'],
            $rows,
        );
    }

    /**
     * @param  array<int, string>  $headers
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    private function stream(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM so Excel opens Indonesian characters correctly.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers);

            foreach ($rows as $row) {
                fputcsv($out, $row);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
