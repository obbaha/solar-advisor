<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SolarCalculationService;

class SolarController extends Controller
{
    /**
     * @var SolarCalculationService
     */
    protected $solarService;
    
    /**
     * @var array قواعد التحقق لكل خطوة
     */
    protected $validationRules = [
        1 => ['monthly_consumption' => 'required|numeric|min:1|max:5000'],
        2 => ['max_load' => 'required|numeric|min:0.1|max:50'],
        3 => ['consumption_pattern' => 'required|in:day,night'],
        4 => ['grid_hours' => 'required|integer|min:0|max:24'],
        5 => ['floors' => 'required|integer|min:1|max:50'],
    ];
    
    /**
     * @var array رسائل الخطأ الافتراضية
     */
    protected $stepMessages = [
        1 => 'يجب إدخال الاستهلاك الشهري أولاً',
        2 => 'يجب إدخال الحمل الأقصى أولاً',
        3 => 'يجب اختيار نمط الاستهلاك أولاً',
        4 => 'يجب تحديد ساعات الكهرباء العمومية أولاً',
        5 => 'يجب تحديد عدد الطوابق أولاً',
    ];
    
    /**
     * @var array القيم الافتراضية لكل خطوة
     */
    protected $defaultValues = [
        1 => ['monthly_consumption' => null],
        2 => ['max_load' => null],
        3 => ['consumption_pattern' => 'day'],
        4 => ['grid_hours' => 12],
        5 => ['floors' => 1],
    ];
    
    /**
     * إنشاء مثيل جديد للكنترولر
     */
    public function __construct(SolarCalculationService $solarService)
    {
        $this->solarService = $solarService;
    }
    
    /**
     * التحقق من إكمال الخطوات السابقة
     */
    private function validateSteps(int $targetStep): ?int
    {
        // التحقق من الخطوات 1 إلى targetStep-1
        for ($step = 1; $step < $targetStep; $step++) {
            if (!session("solar_data.step{$step}.completed")) {
                return $step; // رقم الخطوة غير المكتملة
            }
        }
        return null; // جميع الخطوات مكتملة
    }
    
    /**
     * إعادة توجيه لخطوة مع رسالة خطأ
     */
    private function redirectToIncompleteStep(int $step): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route("step{$step}")
            ->withErrors(['message' => $this->stepMessages[$step] ?? 'يجب إكمال هذه الخطوة أولاً']);
    }
    
    /**
     * حفظ بيانات خطوة مع التحقق
     */
    private function saveStepData(Request $request, int $step): \Illuminate\Http\RedirectResponse
    {
        // التحقق من الخطوات السابقة
        if ($incompleteStep = $this->validateSteps($step)) {
            return $this->redirectToIncompleteStep($incompleteStep);
        }
        
        // التحقق من صحة البيانات
        $validated = $request->validate($this->validationRules[$step]);
        
        // حفظ البيانات في الجلسة
        session([
            "solar_data.step{$step}" => array_merge($validated, ['completed' => true]),
            'current_step' => $step,
        ]);
        
        // بعد حفظ الخطوة 5، نوجه إلى صفحة النتائج مباشرة
if ($step < 5) {
    return redirect()->route("step" . ($step + 1));
} else {
    return redirect()->route('results'); // هذا هو التعديل المهم
}
    }
    
    /**
     * عرض صفحة خطوة مع التحقق
     */
    private function showStepPage(int $step, string $view): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        // التحقق من الخطوات السابقة
        if ($incompleteStep = $this->validateSteps($step)) {
            return $this->redirectToIncompleteStep($incompleteStep);
        }
        
        // جلب البيانات المحفوظة أو القيم الافتراضية
        $stepData = session("solar_data.step{$step}", $this->defaultValues[$step]);
        
        return view($view, [
            'data' => $stepData,
            'currentStep' => $step,
        ]);
    }
    
    /**
     * ============================================
     * معالجة جميع الخطوات بنفس النمط
     * ============================================
     */
    
    // الخطوة 1: الاستهلاك الشهري
    public function step1()
    {
        return $this->showStepPage(1, 'steps.step1');
    }
    
    public function saveStep1(Request $request)
    {
        return $this->saveStepData($request, 1);
    }
    
    // الخطوة 2: الحمل الأقصى
    public function step2()
    {
        return $this->showStepPage(2, 'steps.step2');
    }
    
    public function saveStep2(Request $request)
    {
        return $this->saveStepData($request, 2);
    }
    
    // الخطوة 3: نمط الاستهلاك
    public function step3()
    {
        return $this->showStepPage(3, 'steps.step3');
    }
    
    public function saveStep3(Request $request)
    {
        return $this->saveStepData($request, 3);
    }
    
    // الخطوة 4: ساعات الكهرباء
    public function step4()
    {
        return $this->showStepPage(4, 'steps.step4');
    }
    
    public function saveStep4(Request $request)
    {
        return $this->saveStepData($request, 4);
    }
    
    // الخطوة 5: عدد الطوابق
    public function step5()
    {
        return $this->showStepPage(5, 'steps.step5');
    }
    
    public function saveStep5(Request $request)
    {
        return $this->saveStepData($request, 5);
    }
    
    /**
     * ============================================
     * صفحة النتائج
     * ============================================
     */
    public function results()
    {
        // التحقق من إكمال جميع الخطوات
        if ($incompleteStep = $this->validateSteps(6)) {
            return $this->redirectToIncompleteStep($incompleteStep);
        }
        
        try {
            // تجميع بيانات الإدخال من جميع الخطوات
            $input = $this->prepareCalculationInput();
            
            // استدعاء خدمة الحسابات
            $calculation = $this->solarService->calculate($input);
            
            // حفظ النتائج في الجلسة
            session([
                'solar_calculation' => $calculation,
                'current_step' => 6,
            ]);
            
            return view('steps.results', [
                'calculation' => $calculation,
                'input' => $input,
                'stepsData' => $this->getAllStepsData(),
            ]);
            
        } catch (\Exception $e) {
            // في حالة حدوث خطأ، نعيد للخطوة الأولى مع رسالة الخطأ
            return redirect()->route('step1')
                ->withErrors(['message' => 'حدث خطأ في الحساب: ' . $e->getMessage()])
                ->withInput();
        }
    }
    
    /**
     * تجميع بيانات الإدخال للحساب
     */
    private function prepareCalculationInput(): array
    {
        return [
            'monthly_consumption' => session('solar_data.step1.monthly_consumption'),
            'max_load' => session('solar_data.step2.max_load'),
            'consumption_pattern' => session('solar_data.step3.consumption_pattern', 'day'),
            'grid_hours' => session('solar_data.step4.grid_hours', 12),
            'floors' => session('solar_data.step5.floors', 1),
        ];
    }
    
    /**
     * الحصول على بيانات جميع الخطوات
     */
    private function getAllStepsData(): array
    {
        $data = [];
        for ($i = 1; $i <= 5; $i++) {
            $data[$i] = session("solar_data.step{$i}", $this->defaultValues[$i]);
        }
        return $data;
    }
    
    /**
     * ============================================
     * الأدوات المساعدة
     * ============================================
     */
    
    /**
     * إعادة التعيين (مسح جميع البيانات)
     */
    public function reset()
    {
        // مسح جميع بيانات الجلسة
        session()->forget([
            'solar_data',
            'solar_calculation',
            'current_step',
        ]);
        
        // إعادة التوجيه للصفحة الرئيسية
        return redirect()->route('home')
            ->with('success', 'تم مسح جميع البيانات بنجاح، يمكنك البدء من جديد');
    }
    
    /**
     * الانتقال لخطوة معينة (للشريط التفاعلي)
     */
    public function goToStep(Request $request)
    {
        $request->validate([
            'step' => 'required|integer|min:1|max:6',
        ]);
        
        $targetStep = $request->input('step');
        
        // إذا كانت الخطوة المطلوبة هي النتائج
        if ($targetStep == 6) {
            return redirect()->route('results');
        }
        
        // التحقق من إمكانية الانتقال للخطوة المطلوبة
        $currentStep = session('current_step', 1);
        
        // يمكن الانتقال لأي خطوة سابقة أو الخطوة الحالية فقط
        if ($targetStep > $currentStep + 1) {
            return redirect()->route("step{$currentStep}")
                ->withErrors(['message' => 'لا يمكن تخطي الخطوات غير المكتملة']);
        }
        
        // التحقق من إكمال الخطوات السابقة للخطوة المطلوبة
        if ($incompleteStep = $this->validateSteps($targetStep)) {
            return $this->redirectToIncompleteStep($incompleteStep);
        }
        
        // التوجيه للخطوة المطلوبة
        return redirect()->route("step{$targetStep}");
    }
    
    /**
     * استعادة الحساب السابق (إذا كان موجوداً)
     */
    public function restorePrevious()
    {
        // إذا كان هناك نتائج محفوظة، نذهب مباشرة للنتائج
        if (session('solar_calculation')) {
            session(['current_step' => 6]);
            return redirect()->route('results');
        }
        
        // إذا كان هناك بيانات خطوات، نذهب لأخر خطوة مكتملة
        $lastCompleted = 0;
        for ($i = 1; $i <= 5; $i++) {
            if (session("solar_data.step{$i}.completed")) {
                $lastCompleted = $i;
            }
        }
        
        if ($lastCompleted > 0) {
            session(['current_step' => $lastCompleted]);
            return redirect()->route("step" . min($lastCompleted + 1, 6));
        }
        
        // لا توجد بيانات، نبدأ من الأول
        return redirect()->route('step1');
    }
    
    /**
     * عرض صفحة المساعدة/الإرشادات
     */
    public function help()
    {
        return view('steps.help', [
            'currentStep' => session('current_step', 1),
            'hasData' => session('solar_data') !== null,
        ]);
    }
    
    /**
     * تصدير النتائج كـ PDF أو Excel
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:pdf,excel',
        ]);
        
        if (!session('solar_calculation')) {
            return redirect()->route('results')
                ->withErrors(['message' => 'لا توجد نتائج للتصدير']);
        }
        
        $format = $request->input('format');
        $calculation = session('solar_calculation');
        $input = $this->prepareCalculationInput();
        
        return back()->with('success', "سيتم تحميل الملف بصيغة {$format} قريباً");
    }
}