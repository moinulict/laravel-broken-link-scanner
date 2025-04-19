<?php

namespace Moinul\LinkScanner\Http\Controllers;

use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Moinul\LinkScanner\Exports\BrokenLinksExport;
use Moinul\LinkScanner\Models\BrokenLink;

class BrokenLinkController extends Controller
{
    public function index()
    {
        $links = BrokenLink::latest()->paginate(25);

        return view('linkscanner::broken-links.index', compact('links'));
    }

    public function export()
    {
        return Excel::download(new BrokenLinksExport(), 'broken-links.xlsx');
    }
}
