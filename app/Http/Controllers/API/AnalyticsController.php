<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Get Weekly Sales Data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function weeklySales()
    {
        // Define the start and end dates (e.g., current month)
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // Fetch sales data grouped by week
        $salesData = DB::table('invoices')
            ->select(DB::raw('WEEK(created_at, 1) as week_number'), DB::raw('SUM(total) as total_sales'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('week_number')
            ->orderBy('week_number')
            ->get();

        // Prepare labels and data for frontend
        $labels = $salesData->pluck('week_number')->map(function ($week) {
            return "Week {$week}";
        });
        $totals = $salesData->pluck('total_sales');

        return response()->json([
            'labels' => $labels,
            'totals' => $totals,
        ]);
    }

        /**
     * Get Monthly Sales Data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function monthlySales()
    {
        // Define the start and end dates (e.g., current year)
        $startDate = Carbon::now()->startOfYear();
        $endDate = Carbon::now()->endOfYear();

        // Fetch sales data grouped by month
        $salesData = DB::table('invoices')
            ->select(DB::raw('MONTH(created_at) as month_number'), DB::raw('SUM(total) as total_sales'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('month_number')
            ->orderBy('month_number')
            ->get();

        // Prepare labels and data for frontend
        $labels = $salesData->pluck('month_number')->map(function ($month) {
            return Carbon::create()->month($month)->format('F');
        });
        $totals = $salesData->pluck('total_sales');

        return response()->json([
            'labels' => $labels,
            'totals' => $totals,
        ]);
    }

        /**
     * Get Weekly Stock Levels Data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function weeklyStocks()
    {
        // Fetch current stock levels
        $stockData = DB::table('products')
            ->select('name', 'quantity')
            ->orderBy('name')
            ->get();

        $labels = $stockData->pluck('name');
        $quantities = $stockData->pluck('quantity');

        return response()->json([
            'labels' => $labels,
            'quantities' => $quantities,
        ]);
    }



        /**
     * Get Monthly Stock Levels Data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function monthlyStocks()
    {
        // Fetch current stock levels
        $stockData = DB::table('products')
            ->select('name', 'quantity')
            ->orderBy('name')
            ->get();

        $labels = $stockData->pluck('name');
        $quantities = $stockData->pluck('quantity');

        return response()->json([
            'labels' => $labels,
            'quantities' => $quantities,
        ]);
    }



}
