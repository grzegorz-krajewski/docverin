<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\RetrievalController;
use App\Http\Controllers\AskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('workspaces.index');
});

Route::get('/workspaces', [WorkspaceController::class, 'index'])->name('workspaces.index');
Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('workspaces.store');
Route::get('/workspaces/{workspace}', [WorkspaceController::class, 'show'])->name('workspaces.show');

Route::post('/workspaces/{workspace}/documents', [DocumentController::class, 'store'])
    ->name('workspaces.documents.store');
    
Route::get('/workspaces/{workspace}/ask', [AskController::class, 'show'])
    ->name('workspaces.ask.show');

Route::post('/workspaces/{workspace}/ask', [AskController::class, 'store'])
    ->name('workspaces.ask.store');