<?php

use App\Http\Controllers\BoardController;
use App\Http\Controllers\FunctionTestController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\ProjectApiController;
use App\Http\Controllers\Api\CardApiController;
use App\Http\Controllers\Api\TimeEntryController;
use App\Http\Controllers\Api\CardCommentController;
use App\Http\Controllers\SubtaskTimerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// New API v1 for Flutter integration
Route::prefix('v1')->group(function () {
    // Healthcheck base route
    Route::get('/', function () {
        return response()->json([
            'name' => 'Manajemen Project API',
            'version' => 'v1',
            'status' => 'ok',
            'time' => now()->toISOString(),
        ]);
    });
    // Public auth
    Route::post('auth/login', [ApiAuthController::class, 'login']);
    Route::post('auth/register', [ApiAuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        // Me and logout
        Route::get('auth/me', [ApiAuthController::class, 'me']);
        Route::post('auth/logout', [ApiAuthController::class, 'logout']);

        // Projects
        Route::get('projects', [ProjectApiController::class, 'index']);
        Route::post('projects', [ProjectApiController::class, 'store']);
        Route::get('projects/{id}', [ProjectApiController::class, 'show']);
        Route::patch('projects/{id}', [ProjectApiController::class, 'update']);
        Route::delete('projects/{id}', [ProjectApiController::class, 'destroy']);
        Route::get('projects/{id}/members', [ProjectApiController::class, 'members']);
        Route::post('projects/{id}/members', [ProjectApiController::class, 'addMember']);
        Route::delete('projects/{id}/members/{userId}', [ProjectApiController::class, 'removeMember']);

        // Cards & nested
        Route::get('projects/{projectId}/cards', [CardApiController::class, 'listByProject']);
        Route::post('projects/{projectId}/cards', [CardApiController::class, 'store']);
        Route::get('cards/{cardId}', [CardApiController::class, 'show']);
        Route::patch('cards/{cardId}', [CardApiController::class, 'update']);
        Route::delete('cards/{cardId}', [CardApiController::class, 'destroy']);

        // Subtasks
        Route::get('cards/{cardId}/subtasks', [CardApiController::class, 'listSubtasks']);
        Route::post('cards/{cardId}/subtasks', [CardApiController::class, 'addSubtask']);
        Route::patch('subtasks/{subtaskId}', [CardApiController::class, 'updateSubtask']);
        Route::delete('subtasks/{subtaskId}', [CardApiController::class, 'deleteSubtask']);

        // Comments (legacy - CardApiController)
        Route::get('cards/{cardId}/comments-old', [CardApiController::class, 'listComments']);
        Route::post('cards/{cardId}/comments-old', [CardApiController::class, 'addComment']);
        Route::delete('comments/{commentId}', [CardApiController::class, 'deleteComment']);

        // Card Comments (NEW - CardCommentController with business rules)
        Route::get('cards/{cardId}/comments', [CardCommentController::class, 'index']);
        Route::post('cards/{cardId}/comments', [CardCommentController::class, 'store']);
        Route::patch('cards/{cardId}/comments/{commentId}', [CardCommentController::class, 'update']);
        Route::delete('cards/{cardId}/comments/{commentId}', [CardCommentController::class, 'destroy']);

        // Time logs (legacy)
        Route::get('cards/{cardId}/timelogs-old', [CardApiController::class, 'listTimeLogs']);
        Route::post('cards/{cardId}/timelogs-old', [CardApiController::class, 'addTimeLog']);
        Route::patch('timelogs/{logId}', [CardApiController::class, 'updateTimeLog']);
        Route::delete('timelogs-old/{logId}', [CardApiController::class, 'deleteTimeLog']);

        // Time Entries (NEW - TimeEntryController with business rules)
        Route::get('time-entries', [TimeEntryController::class, 'index']);
        Route::post('time-entries', [TimeEntryController::class, 'store']);
        Route::get('time-entries/today', [TimeEntryController::class, 'today']);
        Route::get('time-entries/statistics', [TimeEntryController::class, 'statistics']);
        Route::get('time-entries/active-timer', [TimeEntryController::class, 'activeTimer']);
        Route::post('time-entries/start-timer', [TimeEntryController::class, 'startTimer']);
        Route::post('time-entries/{id}/stop-timer', [TimeEntryController::class, 'stopTimer']);
        Route::patch('time-entries/{id}', [TimeEntryController::class, 'update']);
        Route::delete('time-entries/{id}', [TimeEntryController::class, 'destroy']);

        // Subtask Timer Routes
        Route::post('tasks/{taskId}/subtasks/{subtaskId}/start-timer', [SubtaskTimerController::class, 'startTimer']);
        Route::post('tasks/{taskId}/subtasks/{subtaskId}/stop-timer', [SubtaskTimerController::class, 'stopTimer']);
    });
});

// Existing routes kept for backward compatibility
Route::middleware('auth:sanctum')->group(function () {
    // User route
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Project API routes
    Route::apiResource('projects', ProjectController::class)->names([
        'index' => 'api.projects.index',
        'store' => 'api.projects.store', 
        'show' => 'api.projects.show',
        'update' => 'api.projects.update',
        'destroy' => 'api.projects.destroy'
    ]);
    Route::get('projects/{id}/statistics', [ProjectController::class, 'getProjectStatistics']);
    
    // Board routes
    Route::apiResource('boards', BoardController::class);
    Route::post('boards/update-positions', [BoardController::class, 'updatePositions']);
    
    // Function Test routes (untuk testing)
    Route::get('test/completion-rate/{projectId}', [FunctionTestController::class, 'testCompletionRate']);
    Route::get('test/total-hours/{projectId}', [FunctionTestController::class, 'testTotalHours']);
    Route::post('test/trigger-assignment', [FunctionTestController::class, 'testTriggerAssignment']);
    Route::get('test/all-functions', [FunctionTestController::class, 'testAllFunctions']);
});

// Public API routes (for dashboard features)
Route::get('tasks/{id}/details', [App\Http\Controllers\CardController::class, 'getTaskDetails']);

// User task checking API
Route::get('users/{userId}/active-tasks-count', [App\Http\Controllers\Api\UserTaskCheckController::class, 'getActiveTasksCount']);
