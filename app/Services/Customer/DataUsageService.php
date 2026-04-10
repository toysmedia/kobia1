<?php

namespace App\Services\Customer;

use App\Models\Radacct;
use Illuminate\Support\Collection;

class DataUsageService
{
    /**
     * Return aggregated data usage for the given RADIUS username.
     *
     * @return array{
     *   total_download_bytes: int,
     *   total_upload_bytes: int,
     *   total_bytes: int,
     *   download_formatted: string,
     *   upload_formatted: string,
     *   total_formatted: string,
     *   session_count: int,
     * }
     */
    public function getUsageSummary(string $username): array
    {
        $row = Radacct::where('username', $username)
            ->selectRaw('SUM(acctinputoctets) as total_upload, SUM(acctoutputoctets) as total_download, COUNT(*) as session_count')
            ->first();

        $download = (int) ($row->total_download ?? 0);
        $upload   = (int) ($row->total_upload ?? 0);
        $total    = $download + $upload;

        return [
            'total_download_bytes' => $download,
            'total_upload_bytes'   => $upload,
            'total_bytes'          => $total,
            'download_formatted'   => $this->formatBytes($download),
            'upload_formatted'     => $this->formatBytes($upload),
            'total_formatted'      => $this->formatBytes($total),
            'session_count'        => (int) ($row->session_count ?? 0),
        ];
    }

    /**
     * Return recent RADIUS accounting sessions for the given username.
     */
    public function getRecentSessions(string $username, int $limit = 10): Collection
    {
        return Radacct::where('username', $username)
            ->orderByDesc('acctstarttime')
            ->limit($limit)
            ->get();
    }

    /**
     * Convert bytes to a human-readable string (KB / MB / GB).
     */
    public function formatBytes(int $bytes): string
    {
        if ($bytes >= 1_073_741_824) {
            return number_format($bytes / 1_073_741_824, 2) . ' GB';
        }

        if ($bytes >= 1_048_576) {
            return number_format($bytes / 1_048_576, 2) . ' MB';
        }

        if ($bytes >= 1_024) {
            return number_format($bytes / 1_024, 2) . ' KB';
        }

        return $bytes . ' B';
    }
}
