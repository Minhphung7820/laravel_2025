<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $subQuery = DB::table('users')
                ->join('events as e1', function ($join) {
                    $join->on('e1.user_id', '=', 'users.id')
                        ->where('e1.event_type', '=', 'view_video');
                })
                ->leftJoin('events as e2', function ($join) {
                    $join->on('e2.user_id', '=', 'users.id')
                        ->where('e2.event_type', '=', 'comment');
                })
                ->leftJoin('events as e3', function ($join) {
                    $join->on('e3.user_id', '=', 'users.id')
                        ->whereIn('e3.event_type', ['like', 'share']);
                })
                ->leftJoin('events as e4', function ($join) {
                    $join->on('e4.user_id', '=', 'users.id')
                        ->where('e4.event_type', '=', 'login');
                })
                ->whereBetween('users.created_at', [now()->subDays(30), now()])
                ->groupBy('users.id', 'users.created_at')
                ->havingRaw("SUM(CASE WHEN e1.created_at <= users.created_at + INTERVAL 1 DAY THEN 1 ELSE 0 END) >= 3")
                ->havingRaw("SUM(CASE WHEN e2.created_at <= users.created_at + INTERVAL 2 DAY THEN 1 ELSE 0 END) >= 1")
                ->havingRaw("SUM(CASE WHEN e3.created_at IS NOT NULL THEN 1 ELSE 0 END) >= 1")
                ->havingRaw("SUM(CASE WHEN e4.created_at BETWEEN users.created_at + INTERVAL 2 DAY AND users.created_at + INTERVAL 7 DAY THEN 1 ELSE 0 END) >= 1")
                ->select([
                    'users.id',
                    DB::raw("DATE(users.created_at) as register_day")
                ]);

            $engagementStats = DB::table(DB::raw("({$subQuery->toSql()}) as engaged_users"))
                ->mergeBindings($subQuery)
                ->rightJoin('users', DB::raw('DATE(users.created_at)'), '=', 'engaged_users.register_day')
                ->whereBetween('users.created_at', [now()->subDays(30), now()])
                ->groupBy(DB::raw("DATE_FORMAT(users.created_at, '%d/%m/%Y')"))
                ->select([
                    DB::raw("DATE_FORMAT(users.created_at, '%d/%m/%Y') as day"),
                    DB::raw('COUNT(DISTINCT users.id) as total_users'),
                    DB::raw('COUNT(DISTINCT engaged_users.id) as engaged_users'),
                    DB::raw('ROUND((COUNT(DISTINCT engaged_users.id) / COUNT(DISTINCT users.id)) * 100, 2) as engagement_rate')
                ])
                ->orderBy('day')
                ->get();

            return response()->json($engagementStats);
        } catch (\Exception $e) {
            return response()->json($e, 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
