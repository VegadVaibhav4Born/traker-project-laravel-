<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {
    return view('welcome');
});


use App\Http\Controllers\frontend\add_projectcontroller;
use App\Http\Controllers\frontend\coming_sooncontroller;
use App\Http\Controllers\frontend\homecontroller;
use App\Http\Controllers\frontend\pricing_tablecontroller;
use App\Http\Controllers\frontend\project_detailcontroller;
use App\Http\Controllers\frontend\project_overviewscontroller;
use App\Http\Controllers\frontend\projectcontroller;

use App\Http\Controllers\frontend\user_reportcontroller;
use App\Http\Controllers\frontend\faqcontroller;
use App\Http\Controllers\frontend\SupportController;
use App\Http\Controllers\frontend\registercontroller;
use App\Http\Controllers\frontend\client_registercontroller;
use App\Http\Controllers\frontend\logincontroller;
use App\Http\Controllers\frontend\logoutcontroller;
use App\Http\Controllers\frontend\ResetPasswordController;
use App\Http\Controllers\frontend\ForgotPasswordController;
use App\Http\Controllers\frontend\UserProfileController;
use App\Http\Controllers\frontend\OtpVerificationController;
use App\Http\Controllers\frontend\My_ActivityController;
use Illuminate\Support\Facades\Mail;

use App\Http\Controllers\api\ActivityController;
use App\Http\Controllers\frontend\GoogleAuthController;
use App\Http\Controllers\frontend\selectBoxRefreshController;
use App\Http\Controllers\frontend\ProjectRequestController;
use App\Http\Controllers\frontend\headerController;
Route::get('googlelogin', [GoogleAuthController::class, 'redirect'])->name('google-auth');
Route::get('auth/google/callback', [GoogleAuthController::class, 'callbackgoogle'])->name('google-auth.callback');






Route::post('activities/store', [ActivityController::class, 'store']);
Route::post('activities', [ActivityController::class, 'index']);




// Route::get('/test-email', function () {
//     Mail::raw('This is a test email', function ($message) {
//         $message->to('recipient@example.com')
//                 ->subject('Test Email');
//     });

//     return 'Email sent successfully!';
// });

// Public routes
Route::get('/', [homecontroller::class, 'index']);
Route::get('index', [homecontroller::class, 'index']);
// Route::post('filter-data', [homecontroller::class, 'index'])->name('filterData');
Route::post('filter-data', [homecontroller::class, 'DateRange'])->name('filterData');

Route::post('/notifications/mark-all-as-read', [headerController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');

Route::get('add-project', [add_projectcontroller::class, 'index'])->name('add-project');
Route::post('store-project', [add_projectcontroller::class, 'store']);
Route::post('check-email', [add_projectcontroller::class, 'checkEmail']);

Route::get('/projects/accept/{projectId}/{userId}', [add_projectcontroller::class, 'acceptInvitation'])->name('projects.accept');
Route::get('/projects/reject/{projectId}/{userId}', [add_projectcontroller::class, 'rejectInvitation'])->name('projects.reject');

Route::get('project-details/{id}', [project_detailcontroller::class, 'index'])->name('project-details');

// Define a route to handle the GET request for deleting a member
Route::get('delete-member/{memberid}/{projectid}', [project_detailcontroller::class, 'DeleteMember'])->name('delete-member');
Route::post('change-status/{id}', [project_detailcontroller::class, 'ChangeStatus'])->name('change-status');


Route::get('projectrequest', [ProjectRequestController::class, 'index'])->name('projectrequest');

// Handle project request submission
// Route::post('projectrequest', [ProjectRequestController::class, 'postmethod'])->name('projectrequest.store');

Route::get('coming-soon', [coming_sooncontroller::class, 'index']);
Route::get('pricing-table', [pricing_tablecontroller::class, 'index']);

Route::get('project-overviews', [project_overviewscontroller::class, 'index']);
Route::get('projects', [projectcontroller::class, 'index']);
Route::get('project/delete/{id}', [projectcontroller::class, 'delete'])->name("project-delete");
Route::get('project/edit/{id}', [projectcontroller::class, 'edit'])->name("project-edit");
Route::post('project/update/{id}', [projectcontroller::class, 'update'])->name("project-update");
Route::get('user-profile', [UserProfileController::class, 'index'])->name('user-profile');
Route::post('update', [UserProfileController::class, 'update']);


Route::get('user-report', [user_reportcontroller::class, 'index'])->name('user-report');
Route::post('select-data', [user_reportcontroller::class, 'SelectData'])->name('select-data');
Route::get('select-data', [user_reportcontroller::class, 'SelectDataRedirect'])->name('index');
Route::post('get-members', [user_reportcontroller::class, 'getMembers'])->name('get-members');
Route::get('faq', [faqcontroller::class, 'index']);

Route::get('support', [SupportController::class, 'index']);

Route::get('test_selectBox', [selectBoxRefreshController::class, 'index'])->name('test_selectBox');
// web.php (routes file)
Route::post('test-select', [selectBoxRefreshController::class, 'SelectData'])->name('test-select');
Route::get('/fetch-data', [SelectBoxRefreshController::class, 'fetchData'])->name('fetch-data');

Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');

// Route to handle the password reset
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::get('register', [registercontroller::class, 'index']);
Route::get('re-send-otp', [OtpVerificationController::class, 'resendotp']);
Route::post('registration', [registercontroller::class, 'store']);
Route::get('client-register', [client_registercontroller::class, 'index']);
Route::post('client-registeration', [client_registercontroller::class, 'store']);

// Authentication routes
Route::get('login', [logincontroller::class, 'index'])->name('login');
Route::post('login', [logincontroller::class, 'store']); // Handle login form submission

Route::get('logout', [logoutcontroller::class, 'index'])->name('logout');

Route::get('otp-verify', [OtpVerificationController::class, 'index'])->name('otp-verify');
Route::post('otp-verification', [OtpVerificationController::class, 'verify']);

Route::get('my-activity', [My_ActivityController::class, 'index'])->name('my-activity');
Route::post('select_data', [My_ActivityController::class, 'SelectData'])->name('select_data');
Route::get('select_data', [My_ActivityController::class, 'SelectDataRedirect'])->name('select_data_redirect');


Route::get('activity/delete/{id}', [My_ActivityController::class, 'delete'])->name("activity-delete");
