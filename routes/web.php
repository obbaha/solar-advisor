<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SolarController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ============================================
// الصفحة الرئيسية - تبدأ بالخطوة 1 مباشرة
// ============================================
Route::get('/', [SolarController::class, 'step1'])->name('home');

// ============================================
// الخطوة 1: إدخال الاستهلاك الشهري مباشرة
// ============================================
Route::get('/step1', [SolarController::class, 'step1'])->name('step1');
Route::post('/step1', [SolarController::class, 'saveStep1'])->name('save.step1');

// ============================================
// الخطوة 2: الحمل الأقصى
// ============================================
Route::get('/step2', [SolarController::class, 'step2'])->name('step2');
Route::post('/step2', [SolarController::class, 'saveStep2'])->name('save.step2');

// ============================================
// الخطوة 3: نمط الاستهلاك اليومي
// ============================================
Route::get('/step3', [SolarController::class, 'step3'])->name('step3');
Route::post('/step3', [SolarController::class, 'saveStep3'])->name('save.step3');

// ============================================
// الخطوة 4: ساعات توفر الكهرباء العمومية
// ============================================
Route::get('/step4', [SolarController::class, 'step4'])->name('step4');
Route::post('/step4', [SolarController::class, 'saveStep4'])->name('save.step4');

// ============================================
// الخطوة 5: عدد الطوابق
// ============================================
Route::get('/step5', [SolarController::class, 'step5'])->name('step5');
Route::post('/step5', [SolarController::class, 'saveStep5'])->name('save.step5');

// ============================================
// الخطوة 6: النتائج النهائية
// ============================================
Route::get('/results', [SolarController::class, 'results'])->name('results');

// ============================================
// أدوات مساعدة
// ============================================

// إعادة التعيين (مسح جميع البيانات)
Route::get('/reset', [SolarController::class, 'reset'])->name('reset');

// العودة لخطوة معينة (للشريط التفاعلي)
Route::post('/go-to-step', [SolarController::class, 'goToStep'])->name('go.to.step');