<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Auth::routes(['reset' => false, 'register' => false]); // Disable default register routes

// Custom register routes
Route::get('register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);

// Email verification routes - Token route FIRST (more specific)
Route::get('/email/verify/token/{token}', [App\Http\Controllers\Auth\VerificationController::class, 'verifyWithToken'])->name('verification.verify.token');
// Then general verification routes
Route::get('/email/verify', [App\Http\Controllers\Auth\VerificationController::class, 'show'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [App\Http\Controllers\Auth\VerificationController::class, 'verify'])->middleware(['signed'])->name('verification.verify');
Route::post('/email/resend', [App\Http\Controllers\Auth\VerificationController::class, 'resend'])->middleware(['throttle:6,1'])->name('verification.resend');

Route::get('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('forgot-password');
Route::post('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendOtp'])->name('forgot-password.send-otp');
Route::get('/verify-otp', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showVerifyOtpForm'])->name('forgot-password.verify');
Route::post('/verify-otp', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'verifyOtp'])->name('forgot-password.verify-otp');
Route::get('/reset-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showResetForm'])->name('forgot-password.reset');
Route::post('/reset-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'resetPassword'])->name('forgot-password.reset-submit');

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware(['auth'])->group(function () {
    // PAGASA time endpoint for live clock display
    Route::get('/api/pagasa-time/current', [App\Http\Controllers\PagasaTimeController::class, 'current'])->name('pagasa-time.current');

    Route::get('/notifications/{id}/read', function ($id) {
        $notification = \Illuminate\Support\Facades\DB::table('tbnotifications')
            ->where('id', $id)
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->first();

        if (!$notification) {
            return redirect()->back();
        }

        \Illuminate\Support\Facades\DB::table('tbnotifications')
            ->where('id', $id)
            ->update(['read_at' => now(), 'updated_at' => now()]);

        return redirect($notification->url ?: route('fund-utilization.index'));
    })->name('notifications.read');

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/dashboard', function () {
        try {
            return view('dashboard.index');
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Dashboard view error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    })->name('dashboard');
    
    // Profile routes
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    
    // Change password routes
    Route::get('/change-password', [App\Http\Controllers\ChangePasswordController::class, 'show'])->name('password.show');
    Route::put('/change-password', [App\Http\Controllers\ChangePasswordController::class, 'update'])->name('password.update');
    
    // User Management routes (superadmin only)
    Route::middleware('superadmin')->group(function () {
        Route::resource('users', App\Http\Controllers\UserManagementController::class);
    });

    // Fund Utilization Report routes
    Route::prefix('fund-utilization')->group(function () {
        Route::get('/', [App\Http\Controllers\FundUtilizationReportController::class, 'index'])->name('fund-utilization.index');
        Route::get('/export', [App\Http\Controllers\FundUtilizationReportController::class, 'export'])->name('fund-utilization.export');
        Route::get('/create', [App\Http\Controllers\FundUtilizationReportController::class, 'create'])->name('fund-utilization.create');
        Route::get('/get-municipalities/{province}', [App\Http\Controllers\FundUtilizationReportController::class, 'getMunicipalities'])->name('fund-utilization.get-municipalities');
        Route::post('/', [App\Http\Controllers\FundUtilizationReportController::class, 'store'])->name('fund-utilization.store');
        Route::get('/{projectCode}', [App\Http\Controllers\FundUtilizationReportController::class, 'show'])->name('fund-utilization.show');
        Route::get('/{projectCode}/edit', [App\Http\Controllers\FundUtilizationReportController::class, 'edit'])->name('fund-utilization.edit');
        Route::put('/{projectCode}', [App\Http\Controllers\FundUtilizationReportController::class, 'update'])->name('fund-utilization.update');
        Route::delete('/{projectCode}', [App\Http\Controllers\FundUtilizationReportController::class, 'deleteProject'])->name('fund-utilization.delete-project');
        Route::post('/{projectCode}/upload-mov', [App\Http\Controllers\FundUtilizationReportController::class, 'uploadMOV'])->name('fund-utilization.upload-mov');
        Route::post('/{projectCode}/upload-written-notice', [App\Http\Controllers\FundUtilizationReportController::class, 'uploadWrittenNotice'])->name('fund-utilization.upload-written-notice');
        Route::post('/{projectCode}/upload-fdp', [App\Http\Controllers\FundUtilizationReportController::class, 'uploadFDP'])->name('fund-utilization.upload-fdp');
        Route::post('/{projectCode}/save-posting-link', [App\Http\Controllers\FundUtilizationReportController::class, 'savePostingLink'])->name('fund-utilization.save-posting-link');
        Route::post('/{projectCode}/add-remark', [App\Http\Controllers\FundUtilizationReportController::class, 'addRemark'])->name('fund-utilization.add-remark');
        Route::post('/{projectCode}/approve/{uploadType}/{quarter}', [App\Http\Controllers\FundUtilizationReportController::class, 'approveUpload'])->name('fund-utilization.approve-upload');
        Route::post('/{projectCode}/save-remarks/{uploadType}/{quarter}', [App\Http\Controllers\FundUtilizationReportController::class, 'saveUserRemarks'])->name('fund-utilization.save-remarks');
        Route::get('/{projectCode}/view-document/{docType}/{quarter}', [App\Http\Controllers\FundUtilizationReportController::class, 'viewDocument'])->name('fund-utilization.view-document');
        Route::post('/{projectCode}/delete-document/{docType}/{quarter}', [App\Http\Controllers\FundUtilizationReportController::class, 'deleteDocument'])->name('fund-utilization.delete-document');
    });

    // Projects routes
    Route::get('/projects/locally-funded', [App\Http\Controllers\LocallyFundedProjectController::class, 'index'])->name('projects.locally-funded');

    Route::get('/projects/locally-funded/create', [App\Http\Controllers\LocallyFundedProjectController::class, 'create'])->name('locally-funded-project.create');
    Route::post('/projects/locally-funded', [App\Http\Controllers\LocallyFundedProjectController::class, 'store'])->name('locally-funded-project.store');
    Route::get('/projects/locally-funded/{project}', [App\Http\Controllers\LocallyFundedProjectController::class, 'show'])->name('locally-funded-project.show');
    Route::get('/projects/locally-funded/{project}/edit', [App\Http\Controllers\LocallyFundedProjectController::class, 'edit'])->name('locally-funded-project.edit');
    Route::put('/projects/locally-funded/{project}', [App\Http\Controllers\LocallyFundedProjectController::class, 'update'])->name('locally-funded-project.update');
    Route::delete('/projects/locally-funded/{project}', [App\Http\Controllers\LocallyFundedProjectController::class, 'destroy'])->name('locally-funded-project.destroy');
    
    // API routes for location data

    Route::get('/projects/rlip-lime', function () {
        return view('projects.rlip-lime');
    })->name('projects.rlip-lime');

    // Local Project Monitoring Committee routes
    Route::post('local-project-monitoring-committee/{lpmc}/upload', [App\Http\Controllers\LocalProjectMonitoringCommitteeController::class, 'upload'])
        ->name('local-project-monitoring-committee.upload');
    Route::post('local-project-monitoring-committee/{lpmc}/approve/{docId}', [App\Http\Controllers\LocalProjectMonitoringCommitteeController::class, 'approveDocument'])
        ->name('local-project-monitoring-committee.approve');
    Route::get('local-project-monitoring-committee/{lpmc}/document/{docId}', [App\Http\Controllers\LocalProjectMonitoringCommitteeController::class, 'viewDocument'])
        ->name('local-project-monitoring-committee.document');
    Route::resource('local-project-monitoring-committee', App\Http\Controllers\LocalProjectMonitoringCommitteeController::class)
        ->parameters(['local-project-monitoring-committee' => 'lpmc']);
});
