<?php

namespace App\Http\Controllers;

use App\Services\StockManagementService;
use Illuminate\Http\Request;

class StockManagementController extends Controller
{
    protected $stockManagementService;

    public function __construct(StockManagementService $stockManagementService)
    {
        $this->stockManagementService = $stockManagementService;
    }

    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $report = $this->stockManagementService->getStockReport($startDate, $endDate);

        return view('stock.index', compact('report'));
    }
}
