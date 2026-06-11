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
