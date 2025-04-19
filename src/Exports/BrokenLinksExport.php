<?php

namespace Moinul\LinkScanner\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Moinul\LinkScanner\Models\BrokenLink;

class BrokenLinksExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        return BrokenLink::select('url', 'status_code', 'reason', 'checked_at')->get();
    }

    public function headings(): array
    {
        return ['URL', 'Status Code', 'Reason', 'Checked At'];
    }
}
