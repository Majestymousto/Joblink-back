<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobOfferController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\BuildCVProController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\CvController;

// Routes publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);
Route::post('/auth/google', [AuthController::class, 'googleAuth']);

// Offres publiques
Route::get('/job-offers',      [JobOfferController::class, 'index']);
Route::get('/job-offers/{id}', [JobOfferController::class, 'show']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Profil
    Route::get('/profil',  [ProfileController::class, 'show']);
    Route::put('/profil',  [ProfileController::class, 'update']);

    // Offres — Entreprise
    Route::post('/job-offers',         [JobOfferController::class, 'store']);
    Route::put('/job-offers/{id}',     [JobOfferController::class, 'update']);
    Route::delete('/job-offers/{id}',  [JobOfferController::class, 'destroy']);

    // Candidatures — Candidat
    Route::post('/job-offers/{id}/apply', [ApplicationController::class, 'apply']);
    Route::get('/mes-candidatures',       [ApplicationController::class, 'myApplications']);
    Route::delete('/candidatures/{id}',   [ApplicationController::class, 'withdraw']);

    // Candidatures — Entreprise
    Route::get('/job-offers/{id}/candidats',  [ApplicationController::class, 'jobCandidates']);
    Route::put('/candidatures/{id}/statut',   [ApplicationController::class, 'updateStatus']);

    // Candidats — Entreprise
    Route::get('/candidats',      [ProfileController::class, 'candidates']);
    Route::get('/candidats/{id}', [ProfileController::class, 'candidateProfile']);

    // BuildCVPro
    Route::post('/buildcvpro/connect',      [BuildCVProController::class, 'connect']);
    Route::get('/buildcvpro/cvs',           [BuildCVProController::class, 'getCvs']);
    Route::delete('/buildcvpro/disconnect', [BuildCVProController::class, 'disconnect']);
    Route::get('/buildcvpro/check',         [BuildCVProController::class, 'check']);

    // Offres sauvegardées
    Route::post('/job-offers/{id}/save',   [JobOfferController::class, 'save']);
    Route::delete('/job-offers/{id}/save', [JobOfferController::class, 'unsave']);
    Route::get('/offres-sauvegardees',     [JobOfferController::class, 'savedJobs']);

    // Messages
    Route::get('/messages',                  [MessageController::class, 'index']);
    Route::get('/messages/{applicationId}',  [MessageController::class, 'show']);
    Route::post('/messages/{applicationId}', [MessageController::class, 'send']);

    // CV Upload
    Route::post('/profil/cv',    [CvController::class, 'upload']);
    Route::delete('/profil/cv',  [CvController::class, 'delete']);
    Route::get('/profil/cv',     [CvController::class, 'download']);

    // Admin
    Route::prefix('admin')->group(function () {
        Route::get('/employers/pending',        [AdminController::class, 'pendingEmployers']);
        Route::get('/employers',                [AdminController::class, 'allEmployers']);
        Route::post('/employers/{id}/validate', [AdminController::class, 'validateEmployer']);
        Route::post('/employers/{id}/reject',   [AdminController::class, 'rejectEmployer']);
        Route::get('/stats',                    [AdminController::class, 'stats']);
    });

});