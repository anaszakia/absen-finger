<?php

namespace App\Exports;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AuditLogExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = AuditLog::with('user')->latest('performed_at');
        
        // Filter berdasarkan tanggal
        if ($this->request->filled('date_from')) {
            $query->whereDate('performed_at', '>=', $this->request->date_from);
        }
        
        if ($this->request->filled('date_to')) {
            $query->whereDate('performed_at', '<=', $this->request->date_to);
        }
        
        // Filter berdasarkan action
        if ($this->request->filled('action')) {
            $query->where('action', $this->request->action);
        }
        
        // Filter berdasarkan user
        if ($this->request->filled('user_id')) {
            $query->where('user_id', $this->request->user_id);
        }
        
        // Search
        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function($q) use ($search) {
                $q->where('user_email', 'like', "%{$search}%")
                  ->orWhere('ip', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Waktu',
            'Nama User',
            'Email User',
            'Role',
            'Action',
            'Controller',
            'Route',
            'Method',
            'URL',
            'IP Address',
            'User Agent',
            'Status Code',
            'Request Data'
        ];
    }

    public function map($log): array
    {
        return [
            $log->performed_at->format('d/m/Y'),
            $log->performed_at->format('H:i:s'),
            $log->user?->name ?? 'Unknown',
            $log->user_email,
            $log->user_role ? ucfirst($log->user_role) : '-',
            ucfirst($log->action),
            $log->controller,
            $log->route,
            $log->method,
            $log->url,
            $log->ip,
            $log->user_agent,
            $log->status_code,
            $log->request_data ? json_encode($log->request_data, JSON_PRETTY_PRINT) : '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function title(): string
    {
        return 'Audit Log';
    }
}
