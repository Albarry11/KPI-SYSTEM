<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KpiWeightController;
use App\Http\Controllers\TaskProgressController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ChatController;

// ──────────────────────────────────────────────────────────
// PUBLIC ROUTES
// ──────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/setup-manager', [AuthController::class, 'setupManager']);

// Diagnostic endpoint (hapus setelah debugging selesai)
Route::get('/health', function () {
    $checks = [];
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $checks['database'] = 'connected';
    } catch (\Exception $e) {
        $checks['database'] = 'FAILED: ' . $e->getMessage();
    }
    try {
        $tables = ['users', 'personal_access_tokens', 'periods', 'kpi_categories', 'kpi_weights', 'tasks', 'evaluations', 'task_progresses', 'reports'];
        foreach ($tables as $t) {
            $checks['table_' . $t] = \Illuminate\Support\Facades\Schema::hasTable($t) ? 'exists' : 'MISSING';
        }
    } catch (\Exception $e) {
        $checks['tables_check'] = 'FAILED: ' . $e->getMessage();
    }
    try {
        $cols = \Illuminate\Support\Facades\Schema::getColumnListing('users');
        $checks['users_columns'] = $cols;
    } catch (\Exception $e) {
        $checks['users_columns'] = 'FAILED: ' . $e->getMessage();
    }
    try {
        $user = \App\Models\User::where('username', 'manager')->first();
        $checks['manager_user'] = $user ? [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => $user->is_active,
            'password_length' => strlen($user->password),
            'password_starts' => substr($user->password, 0, 7),
            'password_verify' => \Illuminate\Support\Facades\Hash::check('password123', $user->password),
        ] : 'NOT FOUND';
    } catch (\Exception $e) {
        $checks['manager_user'] = 'FAILED: ' . $e->getMessage();
    }
    try {
        $tokenCols = \Illuminate\Support\Facades\Schema::getColumnListing('personal_access_tokens');
        $checks['pat_columns'] = $tokenCols;
    } catch (\Exception $e) {
        $checks['pat_columns'] = 'FAILED: ' . $e->getMessage();
    }
    return response()->json($checks);
});

// ──────────────────────────────────────────────────────────
// PROTECTED ROUTES
// ──────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/kpi-categories', [KpiWeightController::class, 'categories']);
    Route::get('/employees', [KpiWeightController::class, 'employees']);
    Route::get('/periods', [PeriodController::class, 'index']);

    // MANAGER ONLY
    Route::middleware('role:manager')->group(function () {
        Route::get('/kpi-weights',         [KpiWeightController::class, 'index']);
        Route::post('/kpi-weights',        [KpiWeightController::class, 'store']);
        Route::put('/kpi-weights/{id}',    [KpiWeightController::class, 'update']);
        Route::delete('/kpi-weights/{id}', [KpiWeightController::class, 'destroy']);

        Route::get('/evaluations',               [EvaluationController::class, 'index']);
        Route::post('/evaluations/generate',     [EvaluationController::class, 'generate']);
        Route::post('/evaluations/{id}/approve', [EvaluationController::class, 'approve']);

        Route::post('/periods',       [PeriodController::class, 'store']);
        Route::put('/periods/{id}',   [PeriodController::class, 'update']);
        Route::delete('/periods/{id}',[PeriodController::class, 'destroy']);

        Route::get('/reports',            [ReportController::class, 'index']);
        Route::post('/reports/generate',  [ReportController::class, 'generate']);
        Route::get('/reports/{id}',       [ReportController::class, 'show']);
        Route::delete('/reports/{id}',    [ReportController::class, 'destroy']);
    });

    // EMPLOYEE & MIXED
    Route::get('/tasks',                          [TaskProgressController::class, 'myTasks']);
    Route::post('/task-progress',                 [TaskProgressController::class, 'store']);
    Route::get('/task-progress/history/{taskId}', [TaskProgressController::class, 'history']);

    Route::get('/evaluations/my', [EvaluationController::class, 'myEvaluation']);

    Route::get('/notifications',             [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all',   [NotificationController::class, 'readAll']);

    Route::get('/chats',           [ChatController::class, 'index']);
    Route::get('/chats/{threadId}',[ChatController::class, 'show']);
    Route::post('/chats',          [ChatController::class, 'store']);
});
