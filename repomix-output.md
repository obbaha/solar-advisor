This file is a merged representation of a subset of the codebase, containing specifically included files and files not matching ignore patterns, combined into a single document by Repomix.

# File Summary

## Purpose
This file contains a packed representation of a subset of the repository's contents that is considered the most important context.
It is designed to be easily consumable by AI systems for analysis, code review,
or other automated processes.

## File Format
The content is organized as follows:
1. This summary section
2. Repository information
3. Directory structure
4. Repository files (if enabled)
5. Multiple file entries, each consisting of:
  a. A header with the file path (## File: path/to/file)
  b. The full contents of the file in a code block

## Usage Guidelines
- This file should be treated as read-only. Any changes should be made to the
  original repository files, not this packed version.
- When processing this file, use the file path to distinguish
  between different files in the repository.
- Be aware that this file may contain sensitive information. Handle it with
  the same level of security as you would the original repository.

## Notes
- Some files may have been excluded based on .gitignore rules and Repomix's configuration
- Binary files are not included in this packed representation. Please refer to the Repository Structure section for a complete list of file paths, including binary files
- Only files matching these patterns are included: app/services/SolarCalculationService.php, app/Http/Controllers/SolarController.php, routes/web.php, resources/views/**/*
- Files matching these patterns are excluded: resources/views/welcome.blade.php
- Files matching patterns in .gitignore are excluded
- Files matching default ignore patterns are excluded
- Files are sorted by Git change count (files with more changes are at the bottom)

# Directory Structure
```
app/Http/Controllers/SolarController.php
app/services/SolarCalculationService.php
resources/views/components/progress-bar.blade.php
resources/views/layouts/app.blade.php
resources/views/Steps/results.blade.php
resources/views/Steps/step1.blade.php
resources/views/Steps/step2.blade.php
resources/views/Steps/step3.blade.php
resources/views/Steps/step4.blade.php
resources/views/Steps/step5.blade.php
routes/web.php
```

# Files

## File: app/Http/Controllers/SolarController.php
```php
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
```

## File: app/services/SolarCalculationService.php
```php
<?php

namespace App\Services;

/**
 * خدمة حساب مكونات النظام الشمسي الهجين
 * 
 * تقوم هذه الخدمة بحساب المكونات التقنية لنظام شمسي هجين بناءً على:
 * - الاستهلاك الشهري للطاقة
 * - الحمل الأقصى
 * - نمط الاستهلاك (نهاري/ليلي)
 * - ساعات توفر الكهرباء العمومية
 * 
 * @package App\Services
 */
class SolarCalculationService
{
    /* ==================== الثوابت التقنية ==================== */
    
    // مواصفات الألواح الشمسية
    private const PANEL_WATTAGE = 400;               // قوة اللوح الواحد بالواط
    private const PANEL_AREA_M2 = 2;                 // مساحة اللوح الواحد بالمتر المربع
    
    // مواصفات البطاريات
    private const BATTERY_VOLTAGE = 48;              // جهد البطارية الواحدة بالفولت
    private const BATTERY_CAPACITIES = [             // السعات القياسية للبطاريات (أمبير-ساعة)
        100, 200, 250, 300, 350, 400, 500, 600, 800,1000,
    ];
    
    // معايير تقسيم النظام
    private const MAX_BATTERY_CAPACITY_SINGLE = 1000; // الحد الأقصى لسعة البطارية في نظام واحد (أمبير)
    
    // مواصفات النظام
    private const SYSTEM_VOLTAGE = 48;               // جهد النظام الكلي بالفولت
    private const INVERTER_CAPACITIES = [            // القدرات القياسية للإنفرترات (واط)
        1000, 1500, 2000, 3000, 4000, 5000, 6000, 
        8000, 10000, 12000, 15000, 20000, 25000
    ];
    
    // ربط قدرة الشاحن بقدرة الإنفرتر
    private const INVERTER_CHARGER_WATTAGE_MAPPING = [
        // إنفرتر (واط) => شاحن (واط)
        1000 => 300,      // 1KW إنفرتر ← 300W شاحن
        1500 => 500,      // 1.5KW إنفرتر ← 500W شاحن  
        2000 => 800,      // 2KW إنفرتر ← 800W شاحن
        3000 => 1200,     // 3KW إنفرتر ← 1200W شاحن
        4000 => 1500,     // 4KW إنفرتر ← 1500W شاحن
        5000 => 1700,     // 5KW إنفرتر ← 1700W شاحن
        6000 => 2000,     // 6KW إنفرتر ← 2000W شاحن
        8000 => 2500,     // 8KW إنفرتر ← 2500W شاحن
        10000 => 3000,    // 10KW إنفرتر ← 3000W شاحن
        12000 => 4000,    // 12KW إنفرتر ← 4000W شاحن
        15000 => 5000,    // 15KW إنفرتر ← 5000W شاحن
        20000 => 7000,    // 20KW إنفرتر ← 7000W شاحن
        25000 => 9000,    // 25KW إنفرتر ← 9000W شاحن
    ];
    
    // إضافة المقاطع القياسية للكابلات (مم²)
    private const STANDARD_CABLE_SECTIONS = [
        1.5, 2.5, 4, 6, 10, 16, 25, 35, 50, 70
    ];

    // معايير تحميل الكابلات (أمبير لكل مم²) حسب نوع التطبيق
    private const CABLE_CURRENT_CAPACITY = [
        'solar_dc' => 6,     // 6 أمبير لكل مم² للتيار المستمر الشمسي
        'battery_dc' => 8,   // 8 أمبير لكل مم² لكابلات البطاريات
        'ac_output' => 5,  // 5 أمبير لكل مم² للتيار المتردد
    ];
    
    /* ==================== معاملات الكفاءة ==================== */
    
    private const SYSTEM_EFFICIENCY = 0.75;          // كفاءة النظام مع الخسائر
    private const BATTERY_EFFICIENCY = 0.90;         // كفاءة البطاريات
    private const DEPTH_OF_DISCHARGE = 0.80;         // عمق تفريغ البطارية المسموح
    private const PEAK_SUN_HOURS = 4.5;              // ساعات الذروة الشمسية اليومية
    private const INVERTER_SURGE_FACTOR = 1.25;      // معامل الحمل الزائد للإنفرتر
    private const SAFETY_MARGIN = 1.05;              // هامش أمان إضافي 5%
    
    /* ==================== معاملات الطاقة الهجينة ==================== */
    
    private const MIN_SOLAR_RATIO = 0.50;            // الحد الأدنى لنسبة الطاقة الشمسية
    private const MAX_SOLAR_RATIO = 0.70;            // الحد الأقصى لنسبة الطاقة الشمسية
    private const SOLAR_BATTERY_CHARGE_RATIO = 0.70; // نسبة شحن البطاريات من الطاقة الشمسية
    private const BATTERY_OVERSIZE_FACTOR = 1.50;    // زيادة حجم البطارية بنسبة %100
    
    /* ==================== الدوال الرئيسية ==================== */
    
    /**
     * الدالة الرئيسية - تقوم بحساب جميع مكونات النظام الشمسي الهجين
     * 
     * @param array $input بيانات الإدخال تحتوي على:
     *   - monthly_consumption: الاستهلاك الشهري (كيلوواط/ساعة)
     *   - max_load: الحمل الأقصى (كيلوواط)
     *   - consumption_pattern: نمط الاستهلاك ('day' أو 'night')
     *   - floors: عدد الطوابق (لحساب طول الكابلات)
     *   - grid_hours: ساعات توفر الكهرباء العمومية يومياً
     * 
     * @return array تقرير مفصل بمكونات النظام وأدائه المتوقع
     */
    public function calculate(array $input): array
    {
        // استخراج بيانات الإدخال
        $monthlyConsumption = $input['monthly_consumption'];
        $maxLoad = $input['max_load'];
        $consumptionPattern = $input['consumption_pattern'];
        $floors = $input['floors'];
        $gridHours = $input['grid_hours'];
        
        // حساب الاستهلاك اليومي
        $dailyConsumption = $monthlyConsumption / 30;
        
        // تسلسل الحسابات
        $consumptionAnalysis = $this->analyzeConsumptionPattern($dailyConsumption, $consumptionPattern);
        $energyMix = $this->calculateEnergyMix($dailyConsumption, $gridHours);
        $panels = $this->calculateSolarPanels($energyMix, $consumptionAnalysis, $monthlyConsumption);
        $batteries = $this->calculateBatteries($consumptionAnalysis['night_kwh'], $energyMix);
        
        // التحقق من الحاجة لتقسيم النظام (إذا كان هناك أكثر من بطارية واحدة)
        $isSplitSystem = $batteries['total_units'] > 1;
        
        if ($isSplitSystem) {
            // تقسيم النظام إلى عدة أجهاز (إنفرتر لكل بطارية)
            return $this->calculateSplitSystem(
                $input,
                $consumptionAnalysis,
                $energyMix,
                $panels,
                $batteries,
                $floors
            );
        } else {
            // نظام واحد عادي
            $inverter = $this->calculateHybridInverter($maxLoad, $panels['total_kw']);
            $charger = $this->calculateCharger($inverter['rated_w'], $batteries['capacity_ah'], $gridHours);
            $cables = $this->calculateCables($panels['total_kw'], $inverter['rated_kw'], $floors);
            $performance = $this->analyzePerformance($panels['monthly_kwh'], $monthlyConsumption, $energyMix);
            
            return $this->formatSingleSystemResults(
                $input,
                $consumptionAnalysis,
                $energyMix,
                $panels,
                $batteries,
                $inverter,
                $charger,
                $cables,
                $performance,
                $dailyConsumption,
                $maxLoad,
                $consumptionPattern,
                $floors,
                $gridHours
            );
        }
    }
    
    /**
     * حساب نظام منقسم إلى جهازين
     */
        /**
     * حساب نظام منقسم إلى عدة أجهزة (إنفرتر لكل بطارية)
     */
    private function calculateSplitSystem(
        array $input,
        array $consumptionAnalysis,
        array $energyMix,
        array $panels,
        array $batteries,
        int $floors
    ): array {
        $monthlyConsumption = $input['monthly_consumption'];
        $maxLoad = $input['max_load'];
        $consumptionPattern = $input['consumption_pattern'];
        $gridHours = $input['grid_hours'];
        $dailyConsumption = $monthlyConsumption / 30;
        
        // عدد البطاريات الكلي
        $totalBatteries = $batteries['total_units'];
        
        // تقسيم الألواح بالتساوي بين البطاريات
        $panelsPerBatteryCount = ceil($panels['count'] / $totalBatteries);
        $panelsPerSystem = [
            'wattage' => $panels['wattage'],
            'count' => $panelsPerBatteryCount,
            'total_kw' => round(($panelsPerBatteryCount * self::PANEL_WATTAGE) / 1000, 2),
            'area_m2' => $panelsPerBatteryCount * self::PANEL_AREA_M2,
            'daily_kwh' => round(($panelsPerBatteryCount * self::PANEL_WATTAGE / 1000) * self::PEAK_SUN_HOURS * self::SYSTEM_EFFICIENCY, 2),
            'monthly_kwh' => round(($panelsPerBatteryCount * self::PANEL_WATTAGE / 1000) * self::PEAK_SUN_HOURS * self::SYSTEM_EFFICIENCY * 30, 2),
        ];
        
        // إعداد البطاريات (بطارية واحدة لكل نظام)
        $batteriesPerSystem = [
            'voltage' => self::BATTERY_VOLTAGE,
            'capacity_ah' => $batteries['capacity_ah'],
            'total_units' => 1, // بطارية واحدة لكل نظام
            'system_voltage' => self::SYSTEM_VOLTAGE,
            'total_ah' => $batteries['capacity_ah'],
            'total_kwh' => round(($batteries['capacity_ah'] * self::SYSTEM_VOLTAGE) / 1000, 2),
            'usable_kwh' => round(($batteries['capacity_ah'] * self::SYSTEM_VOLTAGE * self::DEPTH_OF_DISCHARGE) / 1000, 2),
            'parallel' => 1, // بطارية واحدة فقط
            'series' => 1,
        ];
        
        // حساب الحمل على كل إنفرتر (إجمالي الحمل مقسوماً على عدد البطاريات)
        $loadPerInverter = $maxLoad / $totalBatteries;
        
        // إنشاء الأنظمة حسب عدد البطاريات
        $systems = [];
        for ($i = 1; $i <= $totalBatteries; $i++) {
            $inverter = $this->calculateHybridInverter($loadPerInverter, $panelsPerSystem['total_kw']);
            $charger = $this->calculateCharger($inverter['rated_w'], $batteriesPerSystem['capacity_ah'], $gridHours);
            $cables = $this->calculateCables($panelsPerSystem['total_kw'], $inverter['rated_kw']*2.5, $floors);
            
            $systems["system_$i"] = [
                'name' => "النظام رقم $i",
                'panels' => $panelsPerSystem,
                'batteries' => $batteriesPerSystem,
                'inverter' => $inverter,
                'charger' => $charger,
                'cables' => $cables,
            ];
        }
        
        $performance = $this->analyzePerformance($panels['monthly_kwh'], $monthlyConsumption, $energyMix);
        
        return [
            'input' => $this->formatInputData($monthlyConsumption, $dailyConsumption, $maxLoad, $consumptionPattern, $floors, $gridHours),
            'consumption' => $consumptionAnalysis,
            'energy_mix' => $energyMix,
            'panels' => $panels, // الإجمالي
            'batteries' => $batteries, // الإجمالي
            'system_type' => 'split',
            'system_configuration' => [
                'is_split' => true,
                'number_of_systems' => $totalBatteries,
                'split_reason' => 'النظام يتطلب ' . $totalBatteries . ' بطاريات',
                'total_batteries' => $totalBatteries . ' بطارية',
                'batteries_per_system' => 'بطارية واحدة لكل نظام',
                'inverters_per_system' => 'إنفرتر واحد لكل بطارية',
            ],
            'systems' => $systems,
            'performance' => $performance,
            'notes' => [
                'system_note' => 'تم تقسيم النظام إلى ' . $totalBatteries . ' جهاز إنفرتر (جهاز لكل بطارية)',
                'operation_mode' => 'يمكن تشغيل الأنظمة بشكل متوازي أو بعضها كاحتياطي',
                'benefits' => 'توزيع الحمل، زيادة الموثوقية، سهولة الصيانة',
                'important_note' => 'يجب توصيل كل إنفرتر على بطاريته الخاصة فقط',
            ],
        ];
    }
    
    /**
     * تنسيق نتائج النظام الواحد
     */
    private function formatSingleSystemResults(
        array $input,
        array $consumptionAnalysis,
        array $energyMix,
        array $panels,
        array $batteries,
        array $inverter,
        array $charger,
        array $cables,
        array $performance,
        float $dailyConsumption,
        float $maxLoad,
        string $pattern,
        int $floors,
        int $gridHours
    ): array {
        return [
            'input' => $this->formatInputData($input['monthly_consumption'], $dailyConsumption, $maxLoad, $pattern, $floors, $gridHours),
            'consumption' => $consumptionAnalysis,
            'energy_mix' => $energyMix,
            'panels' => $panels,
            'batteries' => $batteries,
            'system_type' => 'single',
            'system_configuration' => [
                'is_split' => false,
                'number_of_systems' => 1,
            ],
            'inverter' => $inverter,
            'charger' => $charger,
            'cables' => $cables,
            'performance' => $performance,
        ];
    }
    
    /* ==================== دوال تحليل الاستهلاك ==================== */
    
    /**
     * تحليل نمط الاستهلاك اليومي إلى استهلاك نهاري وليلي
     * 
     * @param float $dailyConsumption الاستهلاك اليومي (كيلوواط/ساعة)
     * @param string $pattern نمط الاستهلاك ('day' أو 'night')
     * 
     * @return array تحليل مفصل للاستهلاك مع النسب
     */
    private function analyzeConsumptionPattern(float $dailyConsumption, string $pattern): array
    {
        // تحديد نسبة الاستهلاك النهاري بناءً على النمط
        $dayRatio = ($pattern === 'day') ? 0.70 : 0.30;
        $nightRatio = 1 - $dayRatio;
        
        return [
            'day_kwh' => round($dailyConsumption * $dayRatio, 2),
            'night_kwh' => round($dailyConsumption * $nightRatio, 2),
            'day_ratio' => $dayRatio,
            'night_ratio' => $nightRatio,
        ];
    }
    
    /**
     * حساب مزيج الطاقة بين الشمسي والعمومي بناءً على ساعات الكهرباء
     * 
     * @param float $dailyConsumption الاستهلاك اليومي (كيلوواط/ساعة)
     * @param int $gridHours ساعات توفر الكهرباء العمومية
     * 
     * @return array نسب وتفاصيل مزيج الطاقة
     */
    private function calculateEnergyMix(float $dailyConsumption, int $gridHours): array
    {
        // تحديد نسبة الطاقة الشمسية بناءً على توفر الكهرباء العمومية
        $solarRatio = $this->determineSolarRatio($gridHours);
        $gridRatio = 1 - $solarRatio;
        
        return [
            'solar_ratio' => round($solarRatio, 2),
            'grid_ratio' => round($gridRatio, 2),
            'solar_kwh' => round($dailyConsumption * $solarRatio, 2),
            'grid_kwh' => round($dailyConsumption * $gridRatio, 2),
            'grid_hours' => $gridHours,
        ];
    }
    
    /**
     * تحديد نسبة الطاقة الشمسية المثلى بناءً على ساعات الكهرباء
     * 
     * @param int $gridHours ساعات توفر الكهرباء العمومية
     * @return float نسبة الطاقة الشمسية الموصى بها
     */
    private function determineSolarRatio(int $gridHours): float
    {
        if ($gridHours >= 20) return 0.50;      // كهرباء متوفرة بكثرة
        if ($gridHours >= 12) return 0.60;      // كهرباء متوسطة
        if ($gridHours >= 6) return 0.65;       // كهرباء محدودة
        if ($gridHours >= 2) return 0.70;
        if ($gridHours <= 1) return 1.00;
        return 0.70;
    }
    
    /* ==================== دوال حساب المكونات ==================== */
    
    /**
     * حساب متطلبات الألواح الشمسية
     * 
     * @param array $energyMix مزيج الطاقة
     * @param array $consumptionAnalysis تحليل الاستهلاك
     * @param float $monthlyConsumption الاستهلاك الشهري الكلي
     * 
     * @return array تفاصيل الألواح الشمسية المطلوبة
     */
    private function calculateSolarPanels(array $energyMix, array $consumptionAnalysis, float $monthlyConsumption): array
    {
        // استخراج البيانات المطلوبة
        $targetSolarEnergy = $energyMix['solar_kwh'];
        $solarRatio = $energyMix['solar_ratio'];
        $dayEnergy = $consumptionAnalysis['day_kwh'];
        $nightEnergy = $consumptionAnalysis['night_kwh'];
        
        // حساب الطاقة اللازمة لشحن البطاريات من الشمس
        $solarBatteryChargeRatio = $solarRatio * self::SOLAR_BATTERY_CHARGE_RATIO;
        $solarBatteryCharge = $nightEnergy * $solarBatteryChargeRatio;
        $batteryChargeEnergy = $solarBatteryCharge / (self::BATTERY_EFFICIENCY * self::DEPTH_OF_DISCHARGE);
        
        // إجمالي الطاقة المطلوبة من الألواح (مع هامش الأمان)
        $totalEnergy = ($dayEnergy + $batteryChargeEnergy) * self::SAFETY_MARGIN;
        
        // حساب القدرة والعدد المطلوبين
        $requiredPower = $totalEnergy / self::PEAK_SUN_HOURS;
        $panelCount = ceil(($requiredPower * 1000) / self::PANEL_WATTAGE);
        $totalPower = $panelCount * self::PANEL_WATTAGE / 1000;
        
        // حساب الإنتاج المتوقع
        $dailyProduction = $totalPower * self::PEAK_SUN_HOURS * self::SYSTEM_EFFICIENCY;
        $monthlyProduction = $dailyProduction * 30;
        
        // تعديل النظام لضمان تغطية واقعية
        $adjustedData = $this->adjustSolarSystemForRealisticCoverage(
            $panelCount, 
            $totalPower, 
            $monthlyProduction, 
            $monthlyConsumption, 
            $solarRatio
        );
        
        return [
            'wattage' => self::PANEL_WATTAGE,
            'count' => $adjustedData['panel_count'],
            'total_kw' => $adjustedData['total_power'],
            'area_m2' => $adjustedData['panel_count'] * self::PANEL_AREA_M2,
            'daily_kwh' => round($adjustedData['daily_production'], 2),
            'monthly_kwh' => round($adjustedData['monthly_production'], 2),
            'required_power_kw' => round($requiredPower, 2),
        ];
    }
    
    /**
     * تعديل حجم النظام الشمسي لضمان نسبة تغطية واقعية
     * 
     * الهدف: الحصول على نسبة تغطية بين 100% و120% من الهدف الشمسي
     */
    private function adjustSolarSystemForRealisticCoverage(
        int $panelCount,
        float $totalPower,
        float $monthlyProduction,
        float $monthlyConsumption,
        float $solarRatio
    ): array {
        // حساب نسبة التغطية الفعلية مقارنة بالاستهلاك الكلي
        $actualCoverageRatio = $monthlyProduction / $monthlyConsumption;
        
        // تحديد حدود التغطية المطلوبة
        $targetCoverageMin = $solarRatio * 1.00;  // 100% من الهدف الشمسي
        $targetCoverageMax = $solarRatio * 1.20;  // 120% من الهدف الشمسي
        
        $adjustedPanelCount = $panelCount;
        $adjustedTotalPower = $totalPower;
        
        // إذا كانت التغطية أعلى من الحد الأقصى، نقلل عدد الألواح
        if ($actualCoverageRatio > $targetCoverageMax) {
            $reductionFactor = ($targetCoverageMin + $targetCoverageMax) / 2 / $actualCoverageRatio;
            $adjustedPanelCount = max(1, ceil($panelCount * $reductionFactor));
        }
        // إذا كانت التغطية أقل من الحد الأدنى، نزيد عدد الألواح
        elseif ($actualCoverageRatio < $targetCoverageMin) {
            $increaseFactor = $targetCoverageMin / $actualCoverageRatio;
            $adjustedPanelCount = ceil($panelCount * $increaseFactor);
        }
        
        // إعادة حساب القيم بناءً على العدد المعدل
        $adjustedTotalPower = $adjustedPanelCount * self::PANEL_WATTAGE / 1000;
        $dailyProduction = $adjustedTotalPower * self::PEAK_SUN_HOURS * self::SYSTEM_EFFICIENCY;
        $monthlyProduction = $dailyProduction * 30;
        
        return [
            'panel_count' => $adjustedPanelCount,
            'total_power' => round($adjustedTotalPower, 2),
            'daily_production' => $dailyProduction,
            'monthly_production' => $monthlyProduction,
        ];
    }
    
   /**
 * حساب متطلبات البطاريات
 * 
 * @param float $nightConsumption الاستهلاك الليلي (كيلوواط/ساعة)
 * @param array $energyMix مزيج الطاقة
 * 
 * @return array تفاصيل البطاريات المطلوبة
 */
private function calculateBatteries(float $nightConsumption, array $energyMix): array
{
    // حساب السعة المطلوبة نظراً للاستهلاك الليلي
    $requiredCapacityWh = $nightConsumption * 1000;
    $requiredCapacityAh = $requiredCapacityWh / self::SYSTEM_VOLTAGE;
    
    // تطبيق معاملات الكفاءة وعمق التفريغ
    $actualCapacityAh = $requiredCapacityAh / (self::BATTERY_EFFICIENCY * self::DEPTH_OF_DISCHARGE);
    
    // تطبيق عامل زيادة حجم البطارية بنسبة 100%
    $oversizedCapacityAh = $actualCapacityAh * self::BATTERY_OVERSIZE_FACTOR;
    
    // اختيار السعة القياسية المناسبة (مع زيادة 100%)
    $batteryCapacity = $this->findStandardBatteryCapacity($oversizedCapacityAh);
    
    // حساب عدد البطاريات المطلوبة
    $parallel = ceil($oversizedCapacityAh / $batteryCapacity);
    $totalUnits = $parallel; // لأننا نستخدم توصيل على التوازي فقط
    
    // حساب السعات الإجمالية
    $totalCapacityAh = $batteryCapacity * $parallel;
    $totalCapacityKwh = ($totalCapacityAh * self::SYSTEM_VOLTAGE) / 1000;
    $usableCapacityKwh = $totalCapacityKwh * self::DEPTH_OF_DISCHARGE;
    
    // حساب الاستهلاك الليلي الذي يمكن تغطيته
    $nightCoverageHours = ($usableCapacityKwh * self::BATTERY_EFFICIENCY) / ($nightConsumption / 24) * 24;
    
    return [
        'voltage' => self::BATTERY_VOLTAGE,
        'capacity_ah' => $batteryCapacity,
        'parallel' => $parallel,
        'series' => 1, // توازي فقط
        'total_units' => $totalUnits,
        'system_voltage' => self::SYSTEM_VOLTAGE,
        'total_ah' => $totalCapacityAh,
        'total_kwh' => round($totalCapacityKwh, 2),
        'usable_kwh' => round($usableCapacityKwh, 2),
        'required_capacity_ah' => round($actualCapacityAh, 2),
        'oversized_capacity_ah' => round($oversizedCapacityAh, 2),
        'oversize_factor' => self::BATTERY_OVERSIZE_FACTOR,
        'night_coverage_hours' => round($nightCoverageHours, 1),
    ];
}
    
    /**
     * حساب الإنفرتر الهجين المناسب
     * 
     * @param float $maxLoad الحمل الأقصى (كيلوواط)
     * @param float $solarPower القدرة الشمسية الإجمالية (كيلوواط)
     * 
     * @return array مواصفات الإنفرتر المطلوب
     */
   private function calculateHybridInverter(float $maxLoad, float $solarPower): array
{
    $maxLoadW = $maxLoad * 1000;
    $solarPowerW = $solarPower * 1000;
    
    // حساب القدرة المطلوبة مع اعتبار الحمل الزائد
    $requiredPower = $maxLoadW * self::INVERTER_SURGE_FACTOR;
    $requiredPower = max($requiredPower, $solarPowerW);
    
    // اختيار الإنفرتر القياسي المناسب
    $inverterPower = $this->findStandardInverterCapacity($requiredPower);
    
    return [
        'rated_w' => $inverterPower,
        'rated_kw' => round($inverterPower / 1000, 2),
        'input_v' => self::SYSTEM_VOLTAGE,  // 48 فولت
        'output_v' => 220,
        'frequency_hz' => 50,
        'type' => 'Hybrid Grid-Tie',
        'note' => 'نظام 48 فولت',
    ];
}

/**
 * حساب شاحن البطاريات بناءً على قدرة الإنفرتر
 */
private function calculateCharger(int $inverterPower, float $batteryCapacityAh, int $gridHours): array
{
    // حالة عدم وجود كهرباء عمومية
    if ($gridHours == 0) {
        return [
            'power_w' => 0,
            'power_kw' => 0,
            'current_a' => 0,
            'required' => false,
            'voltage' => self::BATTERY_VOLTAGE,
            'note' => 'لا توجد كهرباء عمومية متاحة',
        ];
    }
    
    // الحصول على قدرة الشاحن المرتبطة بالإنفرتر
    $chargerPower = self::INVERTER_CHARGER_WATTAGE_MAPPING[$inverterPower] ?? 0;
    
    // إذا لم يتم العثور على قدرة شاحن مطابقة
    if ($chargerPower == 0) {
        return [
            'power_w' => 0,
            'power_kw' => 0,
            'current_a' => 0,
            'required' => true,
            'voltage' => self::BATTERY_VOLTAGE,
            'note' => 'لم يتم العثور على شاحن متوافق للإنفرتر',
        ];
    }
    
    // حساب تيار الشحن المتوفر من الشاحن (باستخدام 48V)
    $chargingCurrent = $chargerPower / self::SYSTEM_VOLTAGE; // 48V
    
    return [
        'power_w' => $chargerPower,
        'power_kw' => round($chargerPower / 1000, 2),
        'current_a' => round($chargingCurrent, 1),
        'required' => true,
        'voltage' => self::SYSTEM_VOLTAGE,
        'note' => 'شاحن لنظام بطارية 48 فولت',
    ];
}
    

    
   /**
 * حساب متطلبات الكابلات مع المقاطع القياسية
 * 
 * @param float $solarPowerKw القدرة الشمسية (كيلوواط)
 * @param float $inverterPowerKw سعة البطاريات (أمبير-ساعة)
 * @param int $floors عدد الطوابق
 * 
 * @return array تفاصيل الكابلات المطلوبة
 */
private function calculateCables(float $solarPowerKw, float $inverterPowerKw, int $floors): array
{
    // حساب التيار في دوائر الألواح الشمسية
    $solarCurrent = ($solarPowerKw * 1000) / self::SYSTEM_VOLTAGE;
    
    // حساب تيار البطارية (أقصى تيار تفريغ)
    $maxBatteryCurrent = (($inverterPowerKw * 1000) * 0.8) / self::SYSTEM_VOLTAGE;
    
    // حساب تيار الخرج AC (بناءً على قدرة الإنفرتر)
    $acCurrent = (($inverterPowerKw * 1000) * 0.8) / 220;
    
    // حساب أطوال الكابلات (تزيد مع عدد الطوابق)
    $solarCableLength = ($floors * 3) + 10;
    $batteryCableLength = 5;
    $acCableLength = 10;
    
    // اختيار المقاطع القياسية المناسبة
    $solarCableSection = $this->findStandardCableSection(
        $solarCurrent, 
        self::CABLE_CURRENT_CAPACITY['solar_dc']
    );
    
    $batteryCableSection = $this->findStandardCableSection(
        $maxBatteryCurrent, 
        self::CABLE_CURRENT_CAPACITY['battery_dc']
    );
    
    $acCableSection = $this->findStandardCableSection(
        $acCurrent, 
        self::CABLE_CURRENT_CAPACITY['ac_output']
    );
    
    // حساب هبوط الجهد لكل نوع
    $solarVoltageDrop = $this->calculateVoltageDrop($solarCurrent, $solarCableSection, self::SYSTEM_VOLTAGE);
    $batteryVoltageDrop = $this->calculateVoltageDrop($maxBatteryCurrent, $batteryCableSection, self::SYSTEM_VOLTAGE);
    $acVoltageDrop = $this->calculateVoltageDrop($acCurrent, $acCableSection, 220);
    
    return [
        'solar_dc' => [
            'section_mm2' => $solarCableSection,
            'length_m' => $solarCableLength,
            'current_a' => round($solarCurrent, 1),
            'voltage_drop_percent' => round($solarVoltageDrop, 2),
            'type' => 'كابل شمسي DC',
        ],
        'battery_dc' => [
            'section_mm2' => $batteryCableSection,
            'length_m' => $batteryCableLength,
            'current_a' => round($maxBatteryCurrent, 1),
            'voltage_drop_percent' => round($batteryVoltageDrop, 2),
            'type' => 'كابل بطارية DC',
        ],
        'ac_output' => [
            'section_mm2' => $acCableSection,
            'length_m' => $acCableLength,
            'current_a' => round($acCurrent, 1),
            'voltage_drop_percent' => round($acVoltageDrop, 2),
            'type' => 'كابل خرج AC',
        ],
    ];
}


    /* ==================== دوال تحليل الأداء ==================== */
    
    /**
     * تحليل أداء النظام المتوقع
     * 
     * @param float $monthlyProduction الإنتاج الشهري المتوقع (كيلوواط/ساعة)
     * @param float $monthlyConsumption الاستهلاك الشهري (كيلوواط/ساعة)
     * @param array $energyMix مزيج الطاقة
     * 
     * @return array مؤشرات الأداء
     */
    private function analyzePerformance(float $monthlyProduction, float $monthlyConsumption, array $energyMix): array
    {
        $coveragePercent = ($monthlyProduction / $monthlyConsumption) * 100;
        $deficit = max(0, $monthlyConsumption - $monthlyProduction);
        $surplus = max(0, $monthlyProduction - $monthlyConsumption);
        
        return [
            'production_kwh' => round($monthlyProduction, 2),
            'consumption_kwh' => round($monthlyConsumption, 2),
            'coverage_percent' => round($coveragePercent, 1),
            'solar_percent' => round($energyMix['solar_ratio'] * 100, 1),
            'grid_percent' => round($energyMix['grid_ratio'] * 100, 1),
            'deficit_kwh' => round($deficit, 2),
            'surplus_kwh' => round($surplus, 2),
        ];
    }
    
    
    /* ==================== دوال مساعدة ==================== */
    
    /**
     * تنسيق بيانات الإدخال للعرض
     */
    private function formatInputData(
        float $monthlyConsumption,
        float $dailyConsumption,
        float $maxLoad,
        string $pattern,
        int $floors,
        int $gridHours
    ): array {
        return [
            'monthly_consumption_kwh' => $monthlyConsumption,
            'daily_consumption_kwh' => round($dailyConsumption, 2),
            'max_load_kw' => $maxLoad,
            'consumption_pattern' => $pattern,
            'pattern_description' => ($pattern === 'day') ? 'day' : 'night',
            'floors' => $floors,
            'grid_hours' => $gridHours,
        ];
    }
    
    /**
     * العثور على سعة بطارية قياسية مناسبة
     */
    private function findStandardBatteryCapacity(float $requiredCapacity): int
    {
        foreach (self::BATTERY_CAPACITIES as $standard) {
            if ($requiredCapacity <= $standard) {
                return $standard;
            }
        }
        return max(self::BATTERY_CAPACITIES);
    }
    
    /**
     * العثور على قدرة إنفرتر قياسية مناسبة
     */
    private function findStandardInverterCapacity(float $requiredPower): int
    {
        foreach (self::INVERTER_CAPACITIES as $standard) {
            if ($requiredPower <= $standard) {
                return $standard;
            }
        }
        return max(self::INVERTER_CAPACITIES);
    }
    /**
 * العثور على مقطع كابل قياسي مناسب
 * 
 * @param float $current التيار المطلوب (أمبير)
 * @param float $currentPerMm2 كثافة التيار المسموحة (أمبير/مم²)
 * @return float مقطع الكابل القياسي (مم²)
 */
private function findStandardCableSection(float $current, float $currentPerMm2): float
{
    // إضافة هامش أمان 25%
    $safeCurrent = $current * 1.25;
    
    // حساب المقطع النظري المطلوب
    $requiredSection = $safeCurrent / $currentPerMm2;
    
    // اختيار المقطع القياسي الأكبر أو المساوي
    foreach (self::STANDARD_CABLE_SECTIONS as $section) {
        if ($section >= $requiredSection) {
            return $section;
        }
    }
    
    // إذا كان المطلوب أكبر من جميع المقاطع القياسية، نأخذ أكبر مقطع
    return max(self::STANDARD_CABLE_SECTIONS);
}

/**
 * حساب هبوط الجهد في الكابل
 * 
 * @param float $current التيار (أمبير)
 * @param float $section مقطع الكابل (مم²)
 * @param float $voltage جهد النظام (فولت)
 * @return float نسبة هبوط الجهد (٪)
 */
private function calculateVoltageDrop(float $current, float $section, float $voltage): float
{
    // مقاومة النحاس: 0.0175 أوم·مم²/م (عند 20°C)
    $copperResistivity = 0.0175;
    
    // حساب المقاومة للكابل (طول ذهاب وإياب × 2)
    $resistance = ($copperResistivity * 10) / $section; // 10 متر طول
    
    // حساب هبوط الجهد
    $voltageDrop = $current * $resistance;
    
    // حساب النسبة المئوية
    $voltageDropPercent = ($voltageDrop / $voltage) * 100;
    
    return $voltageDropPercent;
}
}
```

## File: resources/views/components/progress-bar.blade.php
```php
<div class="glass-effect rounded-2xl p-6 shadow-lg animate-slide-in">
    <div class="flex justify-between items-center mb-4">
        <h5 class="text-xl font-bold text-gray-800 flex items-center gap-2">
            <i class="bi bi-list-check text-green-600"></i>
            خطوات التصميم
        </h5>
        <span class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-4 py-2 rounded-full font-bold text-sm">
            الخطوة {{ session('current_step', 1) }} من 6
        </span>
    </div>

    @php
        $currentStep = session('current_step', 1);
        $percent = ($currentStep / 6) * 100;
    @endphp

    <!-- Progress Bar -->
    <div class="relative h-3 bg-gray-200 rounded-full overflow-hidden mb-6">
        <div class="absolute h-full gradient-green rounded-full transition-all duration-700 ease-out"
             style="width: {{ $percent }}%;">
            <div class="h-full w-full animate-pulse"></div>
        </div>
    </div>

    <!-- Steps -->
    <form id="goToStepForm" action="{{ route('go.to.step') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="step" id="targetStep">
    </form>

    <div class="grid grid-cols-6 gap-2">
        @for($i = 1; $i <= 6; $i++)
            @php
                $completed = session("solar_data.step{$i}.completed", false);
                $isCurrent = $i == $currentStep;
                $stepNames = ['الاستهلاك', 'الحمل', 'النمط', 'الكهرباء', 'الطوابق', 'النتائج'];
            @endphp
            
            <div class="text-center">
                <button type="button"
                        onclick="goToStep({{ $i }})"
                        class="w-12 h-12 rounded-full font-bold text-lg transition-all duration-300 mb-2
                               {{ $completed ? 'bg-gradient-to-br from-green-500 to-emerald-600 text-white shadow-lg scale-110' : 
                                  ($isCurrent ? 'bg-gradient-to-br from-yellow-400 to-yellow-500 text-gray-900 shadow-lg animate-pulse-success' : 
                                  'bg-gray-200 text-gray-500') }}
                               {{ $i > $currentStep + 1 ? 'opacity-50 cursor-not-allowed' : 'hover:scale-110 hover:shadow-xl cursor-pointer' }}"
                        {{ $i > $currentStep + 1 ? 'disabled' : '' }}>
                    @if($completed)
                        <i class="bi bi-check-lg"></i>
                    @else
                        {{ $i }}
                    @endif
                </button>
                <div class="text-xs font-semibold text-gray-700">
                    {{ $stepNames[$i-1] }}
                </div>
            </div>
        @endfor
    </div>
</div>

<script>
    function goToStep(step) {
        const currentStep = {{ $currentStep }};
        if (step <= currentStep + 1) {
            document.getElementById('targetStep').value = step;
            document.getElementById('goToStepForm').submit();
        } else {
            alert('لا يمكن تخطي الخطوات غير المكتملة');
        }
    }
</script>
```

## File: resources/views/layouts/app.blade.php
```php
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'المستشار الشمسي')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes pulse-success {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.8;
                transform: scale(1.05);
            }
        }
        
        .animate-slide-in {
            animation: slideInRight 0.5s ease-out;
        }
        
        .animate-pulse-success {
            animation: pulse-success 0.6s ease-in-out;
        }
        
        .gradient-green {
            background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
        }
        
        .gradient-green-soft {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        
        .input-glow:focus {
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50">
    <!-- Header -->
    <header class="gradient-green shadow-lg no-print">
        <div class="container mx-auto px-4 py-8">
            <div class="text-center text-white animate-slide-in">
                <div class="flex items-center justify-center mb-3">
                    <i class="bi bi-sun text-6xl"></i>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold mb-2">
                    المستشار الشمسي
                </h1>
                <p class="text-lg md:text-xl text-green-100">
                    حاسبة متكاملة لتصميم أنظمة الطاقة الشمسية الهجينة
                </p>
            </div>
        </div>
    </header>

    <!-- Progress Bar -->
    @if(!request()->is('results') && !request()->is('/'))
        <div class="container mx-auto px-4 mt-6 no-print">
            @include('components.progress-bar')
        </div>
    @endif

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        @if(session('success'))
            <div class="mb-6 glass-effect rounded-2xl p-4 border-r-4 border-green-500 shadow-lg animate-slide-in">
                <div class="flex items-center">
                    <i class="bi bi-check-circle-fill text-green-500 text-3xl ml-3"></i>
                    <p class="text-gray-800 font-semibold">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 glass-effect rounded-2xl p-4 border-r-4 border-red-500 shadow-lg animate-slide-in">
                <div class="flex items-start">
                    <i class="bi bi-exclamation-triangle-fill text-red-500 text-3xl ml-3"></i>
                    <ul class="list-disc list-inside text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="gradient-green text-white mt-12 py-6 no-print">
        <div class="container mx-auto px-4 text-center">
            <p class="text-lg mb-2">© {{ date('Y') }} المستشار الشمسي. جميع الحقوق محفوظة.</p>
            <div class="flex items-center justify-center gap-4 text-sm">
                <a href="/reset" class="hover:text-green-200 transition-colors underline">
                    إعادة التعيين
                </a>
                <span>|</span>
                <a href="/" class="hover:text-green-200 transition-colors underline">
                    الرئيسية
                </a>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
```

## File: resources/views/Steps/results.blade.php
```php
@extends('layouts.app')
@section('title', 'النتائج النهائية')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Success Header -->
<div class="glass-effect rounded-3xl shadow-2xl overflow-hidden mb-6 animate-slide-in">
    <div class="gradient-green p-8">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-center md:text-right text-white">
                <div class="flex items-center justify-center md:justify-start gap-4 mb-3">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center">
                        <i class="bi bi-check-circle-fill text-green-600 text-5xl"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold mb-2">تم إنشاء التقرير بنجاح!</h1>
                        <p class="text-green-100 text-lg">تقرير مفصل لمكونات النظام الشمسي الهجين الموصى به</p>
                    </div>
                </div>
            </div>
            <div class="flex gap-3 no-print">
                <button onclick="window.print()" 
                        class="px-6 py-3 bg-white text-green-600 rounded-xl font-bold shadow-lg 
                               hover:shadow-xl transition-all duration-300 hover:scale-105 flex items-center gap-2">
                    <i class="bi bi-printer"></i>
                    طباعة
                </button>
                <!-- زر تعديل البيانات - تمت إضافته هنا -->
                <a href="{{ route('step1') }}" 
                   class="px-6 py-3 bg-white/20 text-white rounded-xl font-bold 
                          hover:bg-white/30 transition-all duration-300 flex items-center gap-2">
                    <i class="bi bi-pencil"></i>
                    تعديل البيانات
                </a>
                <button onclick="startNewCalculation()" 
                        class="px-6 py-3 bg-white/20 text-white rounded-xl font-bold 
                               hover:bg-white/30 transition-all duration-300 flex items-center gap-2">
                    <i class="bi bi-plus-circle"></i>
                    حساب جديد
                </button>
            </div>
        </div>
    </div>
</div>


    <!-- Performance Summary -->
    <div class="glass-effect rounded-3xl shadow-2xl p-8 mb-6 animate-slide-in">
        <h2 class="text-3xl font-bold text-gray-800 mb-6 flex items-center gap-3">
            <i class="bi bi-graph-up-arrow text-green-600"></i>
            ملخص الأداء
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-2xl p-6 border-2 border-green-200 shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <i class="bi bi-patch-check text-4xl text-green-600"></i>
                    <span class="text-sm font-semibold text-green-700 bg-green-200 px-3 py-1 rounded-full">نسبة التغطية</span>
                </div>
                <div class="text-5xl font-bold text-green-700 mb-2">
                    {{ $calculation['performance']['coverage_percent'] }}%
                </div>
                <p class="text-gray-700 font-semibold">من إجمالي الاستهلاك</p>
            </div>

            <div class="bg-gradient-to-br from-yellow-50 to-amber-100 rounded-2xl p-6 border-2 border-yellow-200 shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <i class="bi bi-sun text-4xl text-yellow-600"></i>
                    <span class="text-sm font-semibold text-yellow-700 bg-yellow-200 px-3 py-1 rounded-full">طاقة شمسية</span>
                </div>
                <div class="text-5xl font-bold text-yellow-700 mb-2">
                    {{ $calculation['performance']['solar_percent'] }}%
                </div>
                <p class="text-gray-700 font-semibold">من مزيج الطاقة</p>
            </div>

            <div class="bg-gradient-to-br from-blue-50 to-cyan-100 rounded-2xl p-6 border-2 border-blue-200 shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <i class="bi bi-lightning-charge text-4xl text-blue-600"></i>
                    <span class="text-sm font-semibold text-blue-700 bg-blue-200 px-3 py-1 rounded-full">كهرباء عمومية</span>
                </div>
                <div class="text-5xl font-bold text-blue-700 mb-2">
                    {{ $calculation['performance']['grid_percent'] }}%
                </div>
                <p class="text-gray-700 font-semibold">من مزيج الطاقة</p>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-pink-100 rounded-2xl p-6 border-2 border-purple-200 shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <i class="bi bi-speedometer2 text-4xl text-purple-600"></i>
                    <span class="text-sm font-semibold text-purple-700 bg-purple-200 px-3 py-1 rounded-full">إنتاج يومي</span>
                </div>
                <div class="text-5xl font-bold text-purple-700 mb-2">
                    {{ $calculation['panels']['daily_kwh'] }}
                </div>
                <p class="text-gray-700 font-semibold">كيلوواط/ساعة</p>
            </div>
        </div>
    </div>

    <!-- Components Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Solar Panels -->
<div class="glass-effect rounded-3xl shadow-xl overflow-hidden animate-slide-in">
    <div class="bg-gradient-to-r from-yellow-400 to-orange-500 p-6">
        <h3 class="text-2xl font-bold text-white flex items-center gap-3">
            <i class="bi bi-sun-fill"></i>
            الألواح الشمسية
        </h3>
    </div>






    <div class="p-6">
        <div class="flex items-center gap-6 mb-6">
            <div class="w-24 h-24 bg-gradient-to-br from-yellow-100 to-orange-100 rounded-2xl flex items-center justify-center">
                <i class="bi bi-sun text-5xl text-yellow-600"></i>
            </div>
            <div>
                <div class="text-4xl font-bold text-gray-800 mb-1">
                    {{ $calculation['panels']['total_kw'] }} kW
                </div>
                <p class="text-gray-600 font-semibold">القدرة الإجمالية</p>
                @if(isset($calculation['system_type']) && $calculation['system_type'] == 'split')
                <p class="text-sm text-blue-600 mt-1">
                    <i class="bi bi-info-circle"></i>
                    مقسمة بالتساوي بين الأنظمة
                </p>
                @endif
            </div>
        </div>


            <div class="mb-6 p-4 flex items-center justify-between p-4 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl border-2 border-yellow-200">
                <div class="flex items-start gap-3">
                <i class="bi bi-diagram-3 text-yellow-600 text-xl mt-1"></i>
                <div>
                    <h4 class="font-bold text-yellow-600 mb-1">ملاحظة فنية</h4>
                    <p class="text-gray-700 text-sm">
                        يمكن اعتماد ألواح ذات قدرة أكبر مع المحافظة على القدرة الإجمالية ذاتها
                    </p>
                </div>
            </div>
        </div>





        
        <div class="space-y-4">
            <!-- 1. السعة بالواط -->
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                <span class="text-gray-700 font-semibold flex items-center gap-2">
                    <i class="bi bi-grid-3x3 text-yellow-600"></i>
                    عدد الألواح
                </span>
                <span class="text-2xl font-bold text-gray-800">{{ $calculation['panels']['count'] }} لوح</span>
            </div>
            
            <!-- 2. قوة اللوح الواحد مع الملاحظة -->
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                <span class="text-gray-700 font-semibold flex items-center gap-2">
                    <i class="bi bi-lightning text-yellow-600"></i>
                    قوة اللوح الواحد
                </span>
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-800">{{ $calculation['panels']['wattage'] }} W</div>
                    <!-- الملاحظة الجديدة هنا -->
                    <div class="text-sm text-gray-500 mt-1">لوح ضوئي PV Panel</div>
                </div>
            </div>
            
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                <span class="text-gray-700 font-semibold flex items-center gap-2">
                    <i class="bi bi-rulers text-yellow-600"></i>
                    المساحة المطلوبة
                </span>
                <span class="text-2xl font-bold text-gray-800">{{ $calculation['panels']['area_m2'] }} م²</span>
            </div>
            <div class="flex items-center justify-between p-4 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl border-2 border-yellow-200">
                <span class="text-gray-700 font-semibold flex items-center gap-2">
                    <i class="bi bi-calendar-month text-yellow-600"></i>
                    الإنتاج الشهري
                </span>
                <span class="text-2xl font-bold text-yellow-700">{{ $calculation['panels']['monthly_kwh'] }} kWh</span>
            </div>
        </div>
    </div>
</div>
                <!-- Batteries -->
<div class="glass-effect rounded-3xl shadow-xl overflow-hidden animate-slide-in">
    <div class="bg-gradient-to-r from-blue-500 to-cyan-600 p-6">
        <h3 class="text-2xl font-bold text-white flex items-center gap-3">
            <i class="bi bi-battery-charging"></i>
            @if(isset($calculation['system_type']) && $calculation['system_type'] == 'split')
                البطاريات (إجمالي الأنظمة)
            @else
                البطاريات
            @endif
        </h3>
    </div>
    <div class="p-6">
        <!-- تم نقل مستطيل "نظام منقسم إلى جهازين" هنا تحت الصورة الكبيرة -->
        <div class="flex items-center gap-6 mb-6">
            <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-cyan-100 rounded-2xl flex items-center justify-center">
                <i class="bi bi-battery-full text-5xl text-blue-600"></i>
            </div>
            <div>
                <div class="text-4xl font-bold text-gray-800 mb-1">
                    {{ $calculation['batteries']['usable_kwh'] }} kWh
                </div>
                <p class="text-gray-600 font-semibold">السعة القابلة للاستخدام</p>
                @if(isset($calculation['system_type']) && $calculation['system_type'] == 'split')
                <p class="text-sm text-blue-600 mt-1">
                    <i class="bi bi-exclamation-triangle"></i>
                    نظام منقسم إلى عدة أجهزة
                </p>
                @endif
            </div>
        </div>
        


        <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl border-2 border-blue-300">
            <div class="flex items-start gap-3">
                <i class="bi bi-diagram-3 text-blue-600 text-xl mt-1"></i>
                <div>
                    <h4 class="font-bold text-blue-800 mb-1">ملاحظة فنية</h4>
                    <p class="text-gray-700 text-sm">
                        يمكن استبدال البطارية الواحدة ببطاريتين 24 فولت موصولتين على التسلسل مع المحافظة على السعة ذاتها
                    </p>
                </div>
            </div>
        </div>

        
        
        <div class="space-y-4">
            <!-- 1. السعة بالامبير (ليثيوم) -->
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                <span class="text-gray-700 font-semibold flex items-center gap-2">
                    <i class="bi bi-battery text-blue-600"></i>
                    السعة (ليثيوم)
                </span>
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-800">{{ $calculation['batteries']['capacity_ah'] }} Ah</div>
                    <div class="text-sm text-gray-500 mt-1">بطارية ليثيوم LiFePO4</div>
                </div>
            </div>
            
            <!-- 2. عدد البطاريات -->
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                <span class="text-gray-700 font-semibold flex items-center gap-2">
                    <i class="bi bi-stack text-blue-600"></i>
                    عدد البطاريات
                </span>
                <span class="text-2xl font-bold text-gray-800">{{ $calculation['batteries']['total_units'] }} بطارية</span>
            </div>
            
            <!-- 3. التوصيل -->
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                <span class="text-gray-700 font-semibold flex items-center gap-2">
                    <i class="bi bi-diagram-3 text-blue-600"></i>
                    التوصيل
                </span>
                <span class="text-2xl font-bold text-gray-800">{{ $calculation['batteries']['series'] }}S × {{ $calculation['batteries']['parallel'] }}P</span>
            </div>
            <div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl border-2 border-blue-200">
                <span class="text-gray-700 font-semibold flex items-center gap-2">
                    <i class="bi bi-lightning-charge text-blue-600"></i>
                    جهد النظام
                </span>
                <span class="text-2xl font-bold text-blue-700">{{ $calculation['batteries']['system_voltage'] }}V</span>
            </div>
        </div>
    </div>
</div>

                <!-- Inverter & Charger -->
        @if(isset($calculation['system_type']) && $calculation['system_type'] == 'split')
        <!-- نظام منقسم - عرض الإنفرترات -->
        <div class="glass-effect rounded-3xl shadow-xl overflow-hidden animate-slide-in">
            <div class="bg-gradient-to-r from-red-500 to-pink-600 p-6">
                <h3 class="text-2xl font-bold text-white flex items-center gap-3">
                    <i class="bi bi-cpu-fill"></i>
                    الإنفرترات ({{ $calculation['system_configuration']['number_of_systems'] }} جهاز)
                </h3>
            </div>
            <div class="p-6">
                <!-- إضافة صورة الإنفرتر الكبيرة في النظام المنقسم -->
                <div class="flex items-center gap-6 mb-6">
                    <div class="w-24 h-24 bg-gradient-to-br from-red-100 to-pink-100 rounded-2xl flex items-center justify-center">
                        <i class="bi bi-cpu text-5xl text-red-600"></i>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-gray-800 mb-1">
                            {{ $calculation['systems']['system_1']['inverter']['rated_kw'] }} kW   
                        </div>
                        <p class="text-gray-600 font-semibold">قدرة الإنفرتر الواحد</p>
                        <!-- ملاحظة معلومات الإنفرتر تحت الصورة الكبيرة -->
                        <div class="mt-3 p-3 bg-gradient-to-r from-red-50 to-pink-50 rounded-xl border-2 border-red-200">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-info-circle text-red-600"></i>
                                <p class="text-sm text-gray-700 font-semibold">
                                    النظام يحتوي على {{ $calculation['system_configuration']['number_of_systems'] }} إنفرتر (إنفرتر لكل بطارية)
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- عرض إنفرترين كحد أقصى مع إمكانية عرض المزيد -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($calculation['systems'] as $key => $system)
                    @if($loop->index < 2) <!-- عرض أول إنفرترين فقط -->
                    <div class="bg-gradient-to-br {{ $loop->index % 2 == 0 ? 'from-red-50 to-pink-50 border-red-200' : 'from-purple-50 to-pink-50 border-purple-200' }} rounded-2xl p-5 border-2">
                        <h4 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="bi bi-cpu {{ $loop->index % 2 == 0 ? 'text-red-600' : 'text-purple-600' }}"></i>
                            {{ $system['name'] }}
                        </h4>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-700 font-semibold">عدد الإنفرترات:</span>
                                <span class="text-2xl font-bold text-gray-800">1 جهاز</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-700 font-semibold">قدرة الإنفرتر الواحد:</span>
                                <span class="text-2xl font-bold text-gray-800">{{ $system['inverter']['rated_kw'] }} kW</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-700 font-semibold">قدرة الإنفرتر بالواط:</span>
                                <span class="text-xl font-bold text-gray-800">{{ $system['inverter']['rated_w'] }} W</span>
                            </div>
                            @if(isset($system['charger']) && $system['charger']['required'])
                            <div class="flex items-center justify-between">
                                <span class="text-gray-700 font-semibold">الشاحن:</span>
                                <span class="text-xl font-bold text-gray-800">{{ $system['charger']['power_w'] }} W</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
                
                @if($calculation['system_configuration']['number_of_systems'] > 2)
                <div class="mt-6 p-4 bg-blue-50 rounded-xl border-2 border-blue-200">
                    <div class="flex items-center gap-3">
                        <i class="bi bi-info-circle text-blue-600 text-xl"></i>
                        <div>
                            <p class="text-blue-800 font-semibold">
                                هناك {{ $calculation['system_configuration']['number_of_systems'] - 2 }} أنفرترات إضافية
                            </p>
                            <p class="text-gray-700 text-sm mt-1">
                                جميع الإنفرترات الإضافية لها نفس المواصفات: {{ $calculation['systems']['system_1']['inverter']['rated_kw'] }} kW لكل إنفرتر
                            </p>
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- ملاحظة التوصيل -->
                <div class="mt-6 p-4 bg-gradient-to-r from-yellow-50 to-amber-50 rounded-xl border-2 border-yellow-300">
                    <div class="flex items-start gap-3">
                        <i class="bi bi-lightbulb text-yellow-600 text-2xl mt-1"></i>
                        <div>
                            <h4 class="font-bold text-yellow-800 mb-2">ملاحظة هامة للتوصيل:</h4>
                            <p class="text-gray-700">
                                يجب توصيل كل إنفرتر على بطاريته الخاصة. لا يتم توصيل الإنفرترات على نفس البطارية.
                                كل نظام (إنفرتر + بطارية) مستقل عن الأنظمة الأخرى.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <!-- نظام واحد - الإنفرتر والشاحن -->
        <div class="glass-effect rounded-3xl shadow-xl overflow-hidden animate-slide-in">
            <div class="bg-gradient-to-r from-red-500 to-pink-600 p-6">
                <h3 class="text-2xl font-bold text-white flex items-center gap-3">
                    <i class="bi bi-cpu-fill"></i>
                    الإنفرتر والشاحن
                </h3>
            </div>
            <div class="p-6">
                <!-- إضافة صورة الإنفرتر الكبيرة -->
                <div class="flex items-center gap-6 mb-6">
                    <div class="w-24 h-24 bg-gradient-to-br from-red-100 to-pink-100 rounded-2xl flex items-center justify-center">
                        <i class="bi bi-cpu text-5xl text-red-600"></i>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-gray-800 mb-1">
                            {{ $calculation['inverter']['rated_kw'] }} kW   
                        </div>
                        <p class="text-gray-600 font-semibold">إنفرتر هجين</p>
                        <!-- ملاحظة معلومات الإنفرتر تحت الصورة الكبيرة -->
                        <div class="mt-3 p-3 bg-gradient-to-r from-red-50 to-pink-50 rounded-xl border-2 border-red-200">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-info-circle text-red-600"></i>
                                <p class="text-sm text-gray-700 font-semibold">
                                    النظام يحتوي على إنفرتر واحد
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                        <span class="text-gray-700 font-semibold flex items-center gap-2">
                            <i class="bi bi-cpu-fill text-red-600"></i>
                            عدد الإنفرترات
                        </span>
                        <span class="text-2xl font-bold text-gray-800">1 جهاز</span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                        <span class="text-gray-700 font-semibold flex items-center gap-2">
                            <i class="bi bi-lightning text-red-600"></i>
                            قدرة الإنفرتر الواحد
                        </span>
                        <span class="text-2xl font-bold text-gray-800">{{ $calculation['inverter']['rated_kw'] }} kW</span>
                    </div>
                    @if(isset($calculation['charger']) && $calculation['charger']['required'])
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                        <span class="text-gray-700 font-semibold flex items-center gap-2">
                            <i class="bi bi-plug text-red-600"></i>
                            قدرة الشاحن
                        </span>
                        <span class="text-2xl font-bold text-gray-800">{{ $calculation['charger']['power_w'] }} W</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Cables -->
        @if(isset($calculation['system_type']) && $calculation['system_type'] == 'split')
        <!-- نظام منقسم - عرض الكابلات للنظام الأول -->
        <div class="glass-effect rounded-3xl shadow-xl overflow-hidden animate-slide-in">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-6">
                <h3 class="text-2xl font-bold text-white flex items-center gap-3">
                    <i class="bi bi-bezier2"></i>
                    الكابلات (النظام الواحد)
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl p-5 border-2 border-yellow-200">
                        <div class="flex items-center gap-4 mb-3">
                            <i class="bi bi-sun text-3xl text-yellow-600"></i>
                            <h4 class="font-bold text-lg text-gray-800">كابلات DC للألواح</h4>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">المقطع</span>
                            <span class="text-xl font-bold text-gray-800">{{ $calculation['systems']['system_1']['cables']['solar_dc']['section_mm2'] }} مم²</span>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-gray-600">الطول</span>
                            <span class="text-xl font-bold text-gray-800">{{ $calculation['systems']['system_1']['cables']['solar_dc']['length_m'] }} متر</span>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl p-5 border-2 border-blue-200">
                        <div class="flex items-center gap-4 mb-3">
                            <i class="bi bi-battery text-3xl text-blue-600"></i>
                            <h4 class="font-bold text-lg text-gray-800">كابلات DC للبطاريات</h4>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">المقطع</span>
                            <span class="text-xl font-bold text-gray-800">{{ $calculation['systems']['system_1']['cables']['battery_dc']['section_mm2'] }} مم²</span>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-gray-600">الطول</span>
                            <span class="text-xl font-bold text-gray-800">{{ $calculation['systems']['system_1']['cables']['battery_dc']['length_m'] }} متر</span>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-red-50 to-pink-50 rounded-xl p-5 border-2 border-red-200">
                        <div class="flex items-center gap-4 mb-3">
                            <i class="bi bi-outlet text-3xl text-red-600"></i>
                            <h4 class="font-bold text-lg text-gray-800">كابلات AC للخرج</h4>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">المقطع</span>
                            <span class="text-xl font-bold text-gray-800">{{ $calculation['systems']['system_1']['cables']['ac_output']['section_mm2'] }} مم²</span>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-gray-600">الطول</span>
                            <span class="text-xl font-bold text-gray-800">{{ $calculation['systems']['system_1']['cables']['ac_output']['length_m'] }} متر</span>
                        </div>
                    </div>
                </div>
                <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-sm text-blue-700">
                        <i class="bi bi-info-circle"></i>
                        الأنظمة الأخرى تتطلب نفس مواصفات الكابلات
                    </p>
                </div>
            </div>
        </div>
        @else
        <!-- نظام واحد - الكابلات -->
        <div class="glass-effect rounded-3xl shadow-xl overflow-hidden animate-slide-in">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-6">
                <h3 class="text-2xl font-bold text-white flex items-center gap-3">
                    <i class="bi bi-bezier2"></i>
                    الكابلات
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl p-5 border-2 border-yellow-200">
                        <div class="flex items-center gap-4 mb-3">
                            <i class="bi bi-sun text-3xl text-yellow-600"></i>
                            <h4 class="font-bold text-lg text-gray-800">كابلات DC للألواح</h4>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">المقطع</span>
                            <span class="text-xl font-bold text-gray-800">{{ $calculation['cables']['solar_dc']['section_mm2'] }} مم²</span>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-gray-600">الطول</span>
                            <span class="text-xl font-bold text-gray-800">{{ $calculation['cables']['solar_dc']['length_m'] }} متر</span>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl p-5 border-2 border-blue-200">
                        <div class="flex items-center gap-4 mb-3">
                            <i class="bi bi-battery text-3xl text-blue-600"></i>
                            <h4 class="font-bold text-lg text-gray-800">كابلات DC للبطاريات</h4>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">المقطع</span>
                            <span class="text-xl font-bold text-gray-800">{{ $calculation['cables']['battery_dc']['section_mm2'] }} مم²</span>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-gray-600">الطول</span>
                            <span class="text-xl font-bold text-gray-800">{{ $calculation['cables']['battery_dc']['length_m'] }} متر</span>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-red-50 to-pink-50 rounded-xl p-5 border-2 border-red-200">
                        <div class="flex items-center gap-4 mb-3">
                            <i class="bi bi-outlet text-3xl text-red-600"></i>
                            <h4 class="font-bold text-lg text-gray-800">كابلات AC للخرج</h4>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">المقطع</span>
                            <span class="text-xl font-bold text-gray-800">{{ $calculation['cables']['ac_output']['section_mm2'] }} مم²</span>
                        </div>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-gray-600">الطول</span>
                            <span class="text-xl font-bold text-gray-800">{{ $calculation['cables']['ac_output']['length_m'] }} متر</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Input Data Summary -->
    <div class="mb-6 glass-effect rounded-3xl shadow-xl overflow-hidden animate-slide-in">
        <div class="bg-gradient-to-r from-gray-700 to-gray-900 p-6">
            <h3 class="text-2xl font-bold text-white flex items-center gap-3">
                <i class="bi bi-clipboard-data"></i>
                بيانات الإدخال
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="text-sm text-gray-600 mb-1">الاستهلاك الشهري</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $calculation['input']['monthly_consumption_kwh'] }} kWh</div>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="text-sm text-gray-600 mb-1">الاستهلاك اليومي</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $calculation['input']['daily_consumption_kwh'] }} kWh</div>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="text-sm text-gray-600 mb-1">الحمل الأقصى</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $calculation['input']['max_load_kw'] }} kW</div>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="text-sm text-gray-600 mb-1">نمط الاستهلاك</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $calculation['input']['pattern_description'] == 'day' ? 'نهاري' : 'ليلي' }}</div>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="text-sm text-gray-600 mb-1">ساعات الكهرباء</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $calculation['input']['grid_hours'] }} ساعة/يوم</div>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="text-sm text-gray-600 mb-1">عدد الطوابق</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $calculation['input']['floors'] }} طابق</div>
                </div>
            </div>
        </div>
    </div>



        <!-- System Configuration Alert -->
    @if(isset($calculation['system_type']) && $calculation['system_type'] == 'split')
    <div class="mb-6 glass-effect rounded-3xl shadow-2xl p-8 mb-6 border-2 border-blue-300 animate-slide-in">
        <div class="flex items-start gap-4">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-cyan-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                <i class="bi bi-diagram-3 text-3xl text-blue-600"></i>
            </div>
            <div class="flex-grow">
                <h2 class="text-2xl font-bold text-gray-800 mb-2 flex items-center gap-2">
                    <i class="bi bi-info-circle text-blue-600"></i>
                    النظام منقسم إلى {{ $calculation['system_configuration']['number_of_systems'] }} أجهزة
                </h2>
                <div class="bg-blue-50 rounded-xl p-4 mb-4">
                    <p class="text-gray-700 mb-2">
                        <strong>سبب التقسيم:</strong> 
                     المنظومة كبيرة جداً والتقسيم أفضل في هذه الحالات
                    </p>
                    <p class="text-gray-700 mb-2">
                        <strong>عدد البطاريات الإجمالي:</strong> 
                        {{ $calculation['system_configuration']['total_batteries'] }}
                    </p>
                    <p class="text-gray-700 mb-2">
                        <strong>عدد الإنفرترات:</strong> 
                        {{ $calculation['system_configuration']['number_of_systems'] }} إنفرتر
                    </p>
                    <p class="text-gray-700">
                        <strong>التوزيع:</strong> 
                        {{ $calculation['system_configuration']['inverters_per_system'] }}
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ min($calculation['system_configuration']['number_of_systems'], 4) }} gap-6">
                    @foreach($calculation['systems'] as $key => $system)
                    @if($loop->index < 4) <!-- عرض أول 4 أنظمة فقط -->
                    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl p-6 border-2 border-blue-200">
                        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="bi bi-cpu text-blue-600"></i>
                            {{ $system['name'] }}
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-700">الإنفرتر:</span>
                                <span class="font-bold text-gray-800">{{ $system['inverter']['rated_kw'] }} kW</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-700">البطاريات:</span>
                                <span class="font-bold text-gray-800">{{ $system['batteries']['total_units'] }} بطارية</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-700">السعة:</span>
                                <span class="font-bold text-gray-800">{{ $system['batteries']['capacity_ah'] }} Ah</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-700">الألواح:</span>
                                <span class="font-bold text-gray-800">{{ $system['panels']['count'] }} لوح</span>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
                
                @if($calculation['system_configuration']['number_of_systems'] > 4)
                <div class="mt-4 p-4 bg-yellow-50 rounded-xl border-2 border-yellow-200">
                    <div class="flex items-center gap-3">
                        <i class="bi bi-info-circle text-yellow-600 text-xl"></i>
                        <p class="text-yellow-800 text-sm">
                            يتم عرض {{ min($calculation['system_configuration']['number_of_systems'], 4) }} أنظمة من أصل {{ $calculation['system_configuration']['number_of_systems'] }}. جميع الأنظمة متطابقة في المواصفات.
                        </p>
                    </div>
                </div>
                @endif
                
                <!-- Benefits -->
                <div class="mt-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border-2 border-green-200">
                    <h4 class="font-bold text-green-800 mb-2 flex items-center gap-2">
                        <i class="bi bi-check-circle text-green-600"></i>
                        مميزات النظام المنقسم:
                    </h4>
                    <ul class="text-green-700 space-y-1">
                        <li class="flex items-center gap-2"><i class="bi bi-check text-green-600"></i> إنفرتر مستقل لكل بطارية</li>
                        <li class="flex items-center gap-2"><i class="bi bi-check text-green-600"></i> زيادة الموثوقية (إذا تعطل نظام يظل الآخر يعمل)</li>
                        <li class="flex items-center gap-2"><i class="bi bi-check text-green-600"></i> توزيع الحمل مما يطيل عمر المكونات</li>
                        <li class="flex items-center gap-2"><i class="bi bi-check text-green-600"></i> سهولة الصيانة والاستبدال</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif




        <!-- Important Installation Note -->
    @if(isset($calculation['system_type']) && $calculation['system_type'] == 'split')
    <div class="glass-effect rounded-3xl shadow-xl overflow-hidden animate-slide-in mt-6">
        <div class="bg-gradient-to-r from-yellow-500 to-amber-600 p-6">
            <h3 class="text-2xl font-bold text-white flex items-center gap-3">
                <i class="bi bi-exclamation-triangle-fill"></i>
                ملاحظة هامة للتركيب ({{ $calculation['system_configuration']['number_of_systems'] }} أنظمة)
            </h3>
        </div>
        <div class="p-8">
            <div class="flex flex-col md:flex-row items-start gap-6">
                <div class="flex-shrink-0">
                    <div class="w-20 h-20 bg-gradient-to-br from-yellow-100 to-amber-100 rounded-2xl flex items-center justify-center">
                        <i class="bi bi-plug text-4xl text-yellow-600"></i>
                    </div>
                </div>
                <div class="flex-grow">
                    <h4 class="text-xl font-bold text-gray-800 mb-4">تعليمات توصيل النظام المنقسم:</h4>
                    
                    <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl border-2 border-blue-200">
                        <div class="flex items-center gap-3">
                            <i class="bi bi-info-circle text-blue-600 text-xl"></i>
                            <div>
                                <h5 class="font-bold text-blue-800 mb-1">معلومات النظام:</h5>
                                <p class="text-gray-700">
                                    النظام يتكون من {{ $calculation['system_configuration']['number_of_systems'] }} أنظمة مستقلة، كل نظام يحتوي على:
                                </p>
                                <ul class="text-gray-700 mt-2 space-y-1">
                                    <li class="flex items-center gap-2"><i class="bi bi-check-circle text-green-500"></i> إنفرتر واحد</li>
                                    <li class="flex items-center gap-2"><i class="bi bi-check-circle text-green-500"></i> بطارية واحدة</li>
                                    <li class="flex items-center gap-2"><i class="bi bi-check-circle text-green-500"></i> {{ $calculation['systems']['system_1']['panels']['count'] }} لوح شمسي</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- النظام الأول -->
                        <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl p-5 border-2 border-blue-200">
                            <h5 class="font-bold text-blue-800 mb-3 flex items-center gap-2">
                                <i class="bi bi-cpu text-blue-600"></i>
                                النظام الأول (مثال)
                            </h5>
                            <ul class="text-gray-700 space-y-2">
                                <li class="flex items-start gap-2">
                                    <i class="bi bi-check-circle text-green-500 mt-1"></i>
                                    <span>توصيل الإنفرتر الأول على البطارية الأولى فقط</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="bi bi-check-circle text-green-500 mt-1"></i>
                                    <span>توصيل {{ $calculation['systems']['system_1']['panels']['count'] }} لوح شمسي على هذا النظام</span>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- النظام الثاني -->
                        <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-5 border-2 border-purple-200">
                            <h5 class="font-bold text-purple-800 mb-3 flex items-center gap-2">
                                <i class="bi bi-cpu text-purple-600"></i>
                                النظام الثاني (مثال)
                            </h5>
                            <ul class="text-gray-700 space-y-2">
                                <li class="flex items-start gap-2">
                                    <i class="bi bi-check-circle text-green-500 mt-1"></i>
                                    <span>توصيل الإنفرتر الثاني على البطارية الثانية فقط</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i class="bi bi-check-circle text-green-500 mt-1"></i>
                                    <span>توصيل {{ $calculation['systems']['system_2']['panels']['count'] }} لوح شمسي على هذا النظام</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-red-50 to-pink-50 rounded-xl p-5 border-2 border-red-300">
                        <div class="flex items-start gap-3">
                            <i class="bi bi-exclamation-octagon text-red-600 text-2xl mt-1"></i>
                            <div>
                                <h5 class="font-bold text-red-800 mb-2">تحذير هام:</h5>
                                <p class="text-gray-700">
                                    <strong>لا تقم بتوصيل الإنفرترات على بطاريات غير مخصصة لها.</strong> 
                                    يجب أن يكون لكل إنفرتر بطاريته الخاصة. التوصيل الخاطئ قد يؤدي إلى:
                                </p>
                                <ul class="text-gray-700 mt-2 space-y-1">
                                    <li class="flex items-center gap-2"><i class="bi bi-x-circle text-red-500"></i> تلف البطاريات والإنفرترات</li>
                                    <li class="flex items-center gap-2"><i class="bi bi-x-circle text-red-500"></i> عدم توازن في الشحن والتفريغ</li>
                                    <li class="flex items-center gap-2"><i class="bi bi-x-circle text-red-500"></i> انخفاض كفاءة النظام</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border-2 border-green-300">
                        <div class="flex items-start gap-3">
                            <i class="bi bi-lightbulb text-green-600 text-2xl mt-1"></i>
                            <div>
                                <h5 class="font-bold text-green-800 mb-2">نصيحة فنية:</h5>
                                <p class="text-gray-700">
                                    نوصي بتعيين فني متخصص في أنظمة الطاقة الشمسية لتركيب النظام المنقسم. 
                                    يجب توزيع الحمل الكهربائي بالتساوي بين الأنظمة المختلفة.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif



<script>
    function startNewCalculation() {
        if (confirm('هل تريد بدء حساب جديد؟ سيتم مسح جميع البيانات الحالية.')) {
            window.location.href = "{{ route('reset') }}";
        }
    }
</script>

@endsection
```

## File: resources/views/Steps/step1.blade.php
```php
@extends('layouts.app')
@section('title', 'الخطوة 1: إدخال الاستهلاك الشهري')

@section('content')
<div class="max-w-6xl mx-auto min-h-screen flex items-center">
    <div class="glass-effect rounded-3xl shadow-2xl overflow-hidden animate-slide-in w-full">
        <div class="flex flex-col lg:flex-row h-full">
            <!-- Left Column: Header and Form -->
            <div class="lg:w-1/2 p-8 flex flex-col">
                <!-- Header -->
                <div class="gradient-green-soft rounded-2xl p-6 text-center mb-8">
                    <div class="inline-block p-3 bg-white rounded-full shadow-lg mb-4">
                        <i class="bi bi-lightning-charge text-4xl text-green-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">
                        الخطوة 1: الاستهلاك الشهري
                    </h3>
                    <p class="text-gray-600 text-sm">
                        أدخل قيمة استهلاكك الشهري للكهرباء
                    </p>
                </div>

                <!-- Form -->
                <form action="{{ route('save.step1') }}" method="POST" id="step1Form" class="flex-1">
                    @csrf
                    
                    <div class="mb-6">
                        <label for="monthly_consumption" class="block text-lg font-bold text-gray-800 mb-3">
                            الاستهلاك الشهري (كيلوواط/ساعة)
                        </label>
                        
                        <div class="relative">
                            <input type="number"
                                   id="monthly_consumption"
                                   name="monthly_consumption"
                                   value="{{ old('monthly_consumption', $monthly_consumption ?? '') }}"
                                   min="1"
                                   max="5000"
                                   step="1"
                                   required
                                   placeholder="مثال: 350"
                                   class="w-full px-5 py-4 text-center text-2xl font-bold border-3 border-gray-300 rounded-2xl 
                                          focus:border-green-500 focus:outline-none input-glow transition-all duration-300">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-500 font-semibold text-sm">
                                ك.و.س
                            </span>
                        </div>
                        
                        <p class="mt-2 text-xs text-gray-600">
                            القيمة يجب أن تكون بين 1 و 5000 كيلوواط/ساعة
                        </p>
                    </div>

                    <!-- Quick Examples -->
                    <div class="mb-6">
                        <h6 class="font-bold text-gray-800 mb-3 text-sm">أمثلة سريعة:</h6>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" onclick="setConsumption(225)" 
                                    class="bg-white rounded-xl p-3 shadow-sm hover:shadow-md transition-all duration-300 
                                           hover:scale-105 border border-gray-200 hover:border-green-400 cursor-pointer">
                                <div class="text-xl font-bold text-green-600">225</div>
                                <div class="text-xs text-gray-600 mt-1">منزل صغير</div>
                            </button>
                            <button type="button" onclick="setConsumption(450)" 
                                    class="bg-white rounded-xl p-3 shadow-sm hover:shadow-md transition-all duration-300 
                                           hover:scale-105 border border-gray-200 hover:border-green-400 cursor-pointer">
                                <div class="text-xl font-bold text-green-600">450</div>
                                <div class="text-xs text-gray-600 mt-1">منزل متوسط</div>
                            </button>
                            <button type="button" onclick="setConsumption(800)" 
                                    class="bg-white rounded-xl p-3 shadow-sm hover:shadow-md transition-all duration-300 
                                           hover:scale-105 border border-gray-200 hover:border-green-400 cursor-pointer">
                                <div class="text-xl font-bold text-green-600">800</div>
                                <div class="text-xs text-gray-600 mt-1">منزل كبير</div>
                            </button>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-100">
                        <a href="{{ route('reset') }}" 
                           class="px-4 py-2 bg-red-50 text-red-700 rounded-lg font-medium 
                                  hover:bg-red-100 transition-all duration-300 flex items-center gap-2 text-sm">
                            <i class="bi bi-arrow-clockwise"></i>
                            إعادة تعيين
                        </a>
                        
                        <button type="submit" id="submitBtn"
                                class="px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl 
                                       font-medium hover:shadow-lg transition-all duration-300 hover:scale-105 
                                       flex items-center gap-2">
                            التالي
                            <i class="bi bi-arrow-left"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Column: Info and Alternative -->
            <div class="lg:w-1/2 bg-gradient-to-br from-blue-50 to-cyan-50 p-8 flex flex-col">
              

                <!-- Info Box -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-2xl p-6 border-2 border-green-200 shadow-lg flex-1">
                    <div class="flex items-center gap-4 mb-4">
                        <i class="bi bi-info-circle text-2xl text-green-600"></i>
                        <h6 class="font-bold text-gray-800">معلومات مهمة</h6>
                    </div>
                    <ul class="space-y-3 text-sm text-gray-700">
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-green-500 mt-1"></i>
                            <span>يمكنك العثور على الاستهلاك الشهري في فاتورة الكهرباء</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-green-500 mt-1"></i>
                            <span>الاستهلاك المتوسط للمنزل بين 300-600 كيلوواط/ساعة</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-green-500 mt-1"></i>
                            <span>تؤثر هذه القيمة مباشرة على حجم النظام الشمسي المطلوب</span>
                        </li>
                    </ul>
                </div>

                <!-- Footer Note -->
                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-600 flex items-center justify-center gap-2">
                        <i class="bi bi-shield-check text-green-600"></i>
                        بياناتك محفوظة بشكل مؤقت في الجلسة
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const input = document.getElementById('monthly_consumption');
    const submitBtn = document.getElementById('submitBtn');

    function setConsumption(value) {
        input.value = value;
        input.focus();
        addPulseAnimation(input);
    }

    function addPulseAnimation(element) {
        element.classList.add('animate-pulse-success');
        setTimeout(() => {
            element.classList.remove('animate-pulse-success');
        }, 600);
    }

    input.addEventListener('input', function() {
        const value = parseInt(this.value);
        if (value >= 1 && value <= 5000) {
            this.classList.remove('border-red-500');
            this.classList.add('border-green-500');
        } else if (this.value) {
            this.classList.remove('border-green-500');
            this.classList.add('border-red-500');
        }
    });

    document.getElementById('step1Form').addEventListener('submit', function(e) {
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split animate-spin"></i> جاري المعالجة...';
        submitBtn.disabled = true;
    });
</script>

@endsection
```

## File: resources/views/Steps/step2.blade.php
```php
@extends('layouts.app')
@section('title', 'الخطوة 2: الحمل الأقصى')

@section('content')
<div class="max-w-6xl mx-auto min-h-screen flex items-center">
    <div class="glass-effect rounded-3xl shadow-2xl overflow-hidden animate-slide-in w-full">
        <div class="flex flex-col lg:flex-row h-full">
            <!-- Left Column: Header and Form -->
            <div class="lg:w-1/2 p-8 flex flex-col">
                <!-- Header -->
                <div class="gradient-green-soft rounded-2xl p-6 text-center mb-8">
                    <div class="inline-block p-3 bg-white rounded-full shadow-lg mb-4">
                        <i class="bi bi-lightning-fill text-4xl text-yellow-500"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">
                        الخطوة 2: الحمل الأقصى
                    </h3>
                    <p class="text-gray-600 text-sm">
                        أعلى حمل كهربائي يعمل في نفس الوقت
                    </p>
                </div>

                <!-- Form -->
                <form action="{{ route('save.step2') }}" method="POST" id="step2Form" class="flex-1">
                    @csrf
                    
                    <div class="mb-6">
                        <label for="max_load" class="block text-lg font-bold text-gray-800 mb-3">
                            الحمل الأقصى المتزامن (كيلوواط)
                        </label>
                        
                        <div class="relative">
                            <input type="number"
                                   id="max_load"
                                   name="max_load"
                                   value="{{ old('max_load', $max_load ?? '') }}"
                                   min="0.1"
                                   max="50"
                                   step="0.1"
                                   required
                                   placeholder="مثال: 3.5"
                                   class="w-full px-5 py-4 text-center text-2xl font-bold border-3 border-gray-300 rounded-2xl 
                                          focus:border-yellow-500 focus:outline-none input-glow transition-all duration-300">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-500 font-semibold text-sm">
                                كيلوواط
                            </span>
                        </div>
                        
                        <p class="mt-2 text-xs text-gray-600">
                            القيمة يجب أن تكون بين 0.1 و 50 كيلوواط
                        </p>
                    </div>

                    <!-- Quick Examples -->
                    <div class="mb-6">
                        <h6 class="font-bold text-gray-800 mb-3 text-sm">أمثلة سريعة:</h6>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" onclick="setLoad(1.8)" 
                                    class="bg-white rounded-xl p-3 shadow-sm hover:shadow-md transition-all duration-300 
                                           hover:scale-105 border border-gray-200 hover:border-yellow-400 cursor-pointer text-center">
                                <i class="bi bi-snow2 text-xl text-blue-500 mb-1"></i>
                                <div class="font-medium text-gray-800 text-sm">مكيف 1.5 طن</div>
                                <div class="text-xs text-gray-600">1.8 ك.و</div>
                            </button>
                            <button type="button" onclick="setLoad(2.5)" 
                                    class="bg-white rounded-xl p-3 shadow-sm hover:shadow-md transition-all duration-300 
                                           hover:scale-105 border border-gray-200 hover:border-yellow-400 cursor-pointer text-center">
                                <i class="bi bi-fire text-xl text-red-500 mb-1"></i>
                                <div class="font-medium text-gray-800 text-sm">فرن كهربائي</div>
                                <div class="text-xs text-gray-600">2.5 ك.و</div>
                            </button>
                            <button type="button" onclick="setLoad(0.8)" 
                                    class="bg-white rounded-xl p-3 shadow-sm hover:shadow-md transition-all duration-300 
                                           hover:scale-105 border border-gray-200 hover:border-yellow-400 cursor-pointer text-center">
                                <i class="bi bi-droplet text-xl text-cyan-500 mb-1"></i>
                                <div class="font-medium text-gray-800 text-sm">غسالة</div>
                                <div class="text-xs text-gray-600">0.8 ك.و</div>
                            </button>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-100">
                        <a href="{{ route('step1') }}" 
                           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium 
                                  hover:bg-gray-200 transition-all duration-300 flex items-center gap-2 text-sm">
                            <i class="bi bi-arrow-right"></i>
                            السابق
                        </a>
                        
                        <button type="submit" id="submitBtn"
                                class="px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl 
                                       font-medium hover:shadow-lg transition-all duration-300 hover:scale-105 
                                       flex items-center gap-2">
                            التالي
                            <i class="bi bi-arrow-left"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Column: Info and Notes -->
            <div class="lg:w-1/2 bg-gradient-to-br from-yellow-50 to-amber-50 p-8 flex flex-col">
                <!-- Important Note -->
                <div class="bg-white rounded-2xl p-6 border-2 border-yellow-200 shadow-lg mb-6">
                    <div class="flex items-start gap-4">
                        <i class="bi bi-exclamation-triangle text-2xl text-yellow-600"></i>
                        <div>
                            <h6 class="font-bold text-lg mb-2 text-gray-800">ملاحظة هامة</h6>
                            <p class="text-gray-700 text-sm">
                                الحمل الأقصى هو مجموع قدرات الأجهزة التي تعمل في نفس اللحظة، 
                                وليس مجموع جميع الأجهزة في المنزل
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Load Meter -->
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-6 border-2 border-gray-200 shadow-lg mb-6">
                    <h6 class="font-bold text-gray-800 mb-4">مؤشر الحمل</h6>
                    <div id="loadMeter" class="hidden">
                        <div class="flex justify-between text-xs mb-2">
                            <span class="text-gray-600">منخفض</span>
                            <span class="text-gray-600">متوسط</span>
                            <span class="text-gray-600">عالي</span>
                        </div>
                        <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                            <div id="loadBar" class="h-full transition-all duration-500 rounded-full"></div>
                        </div>
                        <div class="mt-3 text-center">
                            <span id="loadValue" class="text-lg font-bold text-gray-800">0</span>
                            <span class="text-gray-600 text-sm"> كيلوواط</span>
                        </div>
                    </div>
                    <div id="noLoad" class="text-center text-gray-500 text-sm">
                        أدخل قيمة لرؤية المؤشر
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-gradient-to-br from-blue-50 to-cyan-100 rounded-2xl p-6 border-2 border-blue-200 shadow-lg flex-1">
                    <div class="flex items-center gap-4 mb-4">
                        <i class="bi bi-lightbulb text-2xl text-blue-600"></i>
                        <h6 class="font-bold text-gray-800">نصائح للحساب</h6>
                    </div>
                    <ul class="space-y-3 text-sm text-gray-700">
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-blue-500 mt-1"></i>
                            <span>احسب الأجهزة التي تعمل معاً (مكيف + ثلاجة + إضاءة)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-blue-500 mt-1"></i>
                            <span>المحركات الكهربائية تستهلك طاقة أعلى عند بدء التشغيل</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-check-circle text-blue-500 mt-1"></i>
                            <span>تؤثر هذه القيمة على حجم الإنفرتر المطلوب</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const input = document.getElementById('max_load');
    const loadMeter = document.getElementById('loadMeter');
    const loadBar = document.getElementById('loadBar');
    const loadValue = document.getElementById('loadValue');
    const noLoad = document.getElementById('noLoad');
    const submitBtn = document.getElementById('submitBtn');

    function setLoad(value) {
        input.value = value;
        input.focus();
        updateLoadMeter();
        addPulseAnimation(input);
    }

    function updateLoadMeter() {
        const value = parseFloat(input.value) || 0;
        
        if (value >= 0.1 && value <= 50) {
            loadMeter.classList.remove('hidden');
            noLoad.classList.add('hidden');
            
            const percent = (value / 50) * 100;
            loadBar.style.width = percent + '%';
            loadValue.textContent = value;
            
            if (value < 2) {
                loadBar.className = 'h-full transition-all duration-500 rounded-full bg-gradient-to-r from-green-400 to-green-600';
            } else if (value < 5) {
                loadBar.className = 'h-full transition-all duration-500 rounded-full bg-gradient-to-r from-yellow-400 to-yellow-600';
            } else if (value < 10) {
                loadBar.className = 'h-full transition-all duration-500 rounded-full bg-gradient-to-r from-orange-400 to-orange-600';
            } else {
                loadBar.className = 'h-full transition-all duration-500 rounded-full bg-gradient-to-r from-red-400 to-red-600';
            }
        } else {
            loadMeter.classList.add('hidden');
            noLoad.classList.remove('hidden');
        }
    }

    function addPulseAnimation(element) {
        element.classList.add('animate-pulse-success');
        setTimeout(() => {
            element.classList.remove('animate-pulse-success');
        }, 600);
    }

    input.addEventListener('input', function() {
        updateLoadMeter();
        const value = parseFloat(this.value);
        if (value >= 0.1 && value <= 50) {
            this.classList.remove('border-red-500');
            this.classList.add('border-yellow-500');
        } else if (this.value) {
            this.classList.remove('border-yellow-500');
            this.classList.add('border-red-500');
        }
    });

    // Initialize on load
    if (input.value) {
        updateLoadMeter();
    }

    document.getElementById('step2Form').addEventListener('submit', function(e) {
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split animate-spin"></i> جاري المعالجة...';
        submitBtn.disabled = true;
    });
</script>

@endsection
```

## File: resources/views/Steps/step3.blade.php
```php
@extends('layouts.app')
@section('title', 'الخطوة 3: نمط الاستهلاك')

@section('content')
<div class="max-w-6xl mx-auto min-h-screen flex items-center">
    <div class="glass-effect rounded-3xl shadow-2xl overflow-hidden animate-slide-in w-full">
        <div class="flex flex-col lg:flex-row h-full">
            <!-- Left Column: Header and Options -->
            <div class="lg:w-1/2 p-8 flex flex-col">
                <!-- Header -->
                <div class="gradient-green-soft rounded-2xl p-6 text-center mb-8">
                    <div class="inline-block p-3 bg-white rounded-full shadow-lg mb-4">
                        <i class="bi bi-clock-history text-4xl text-blue-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">
                        الخطوة 3: نمط الاستهلاك
                    </h3>
                    <p class="text-gray-600 text-sm">
                        اختر الوقت الذي يكون فيه استهلاكك للكهرباء أعلى
                    </p>
                </div>

                <!-- Form -->
                <form action="{{ route('save.step3') }}" method="POST" id="step3Form" class="flex-1">
                    @csrf
                    
                    <div class="mb-6">
                        <label class="block text-lg font-bold text-gray-800 mb-6 text-center">
                            متى يكون معظم استهلاكك اليومي؟
                        </label>
                        
                        <div class="space-y-4">
                            <!-- Day Option -->
                            <div class="pattern-option">
                                <input type="radio" 
                                       name="consumption_pattern" 
                                       id="pattern_day" 
                                       value="day"
                                       {{ old('consumption_pattern', $consumption_pattern ?? '') == 'day' ? 'checked' : '' }}
                                       required
                                       class="hidden pattern-radio">
                                <label for="pattern_day" 
                                       class="pattern-card block cursor-pointer bg-gradient-to-r from-yellow-50 to-orange-50 rounded-2xl p-6 border-4 border-gray-200 
                                              transition-all duration-300 hover:shadow-xl hover:scale-105 flex items-center gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-16 h-16 bg-gradient-to-br from-yellow-200 to-orange-200 rounded-full flex items-center justify-center">
                                            <i class="bi bi-sun-fill text-2xl text-yellow-600"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <h5 class="text-xl font-bold mb-1 text-gray-800">نهاري</h5>
                                        <p class="text-gray-600 text-sm mb-2">
                                            الإستهلاك نهاري فقظ (8 صباحاً - 6 مساءً)
                                        </p>
                                        <div class="flex flex-wrap gap-1">
                                            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-medium">
                                                شركات
                                            </span>
                                            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-medium">
                                                مكاتب
                                            </span>
                                            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-medium">
                                                صالات عرض
                                            </span>
                                        </div>
                                    </div>
                                    <div class="checkmark-container hidden">
                                        <i class="bi bi-check-circle-fill text-green-500 text-2xl"></i>
                                    </div>
                                </label>
                            </div>

                            <!-- Night Option -->
                            <div class="pattern-option">
                                <input type="radio" 
                                       name="consumption_pattern" 
                                       id="pattern_night" 
                                       value="night"
                                       {{ old('consumption_pattern', $consumption_pattern ?? '') == 'night' ? 'checked' : '' }}
                                       required
                                       class="hidden pattern-radio">
                                <label for="pattern_night" 
                                       class="pattern-card block cursor-pointer bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl p-6 border-4 border-gray-200 
                                              transition-all duration-300 hover:shadow-xl hover:scale-105 flex items-center gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-16 h-16 bg-gradient-to-br from-indigo-200 to-purple-200 rounded-full flex items-center justify-center">
                                            <i class="bi bi-moon-stars-fill text-2xl text-indigo-600"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <h5 class="text-xl font-bold mb-1 text-gray-800">ليلي</h5>
                                        <p class="text-gray-600 text-sm mb-2">
                                            الاستهلاك خلال النهار والليل معاً 
                                        </p>
                                        <div class="flex flex-wrap gap-1">
                                            <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded text-xs font-medium">
                                                مساجد
                                            </span>
                                            <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded text-xs font-medium">
                                                منازل
                                            </span>
                                            <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded text-xs font-medium">
                                                محلات
                                            </span>
                                        </div>
                                    </div>
                                    <div class="checkmark-container hidden">
                                        <i class="bi bi-check-circle-fill text-green-500 text-2xl"></i>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-100">
                        <a href="{{ route('step2') }}" 
                           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium 
                                  hover:bg-gray-200 transition-all duration-300 flex items-center gap-2 text-sm">
                            <i class="bi bi-arrow-right"></i>
                            السابق
                        </a>
                        
                        <button type="submit" id="submitBtn"
                                class="px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl 
                                       font-medium hover:shadow-lg transition-all duration-300 hover:scale-105 
                                       flex items-center gap-2">
                            التالي
                            <i class="bi bi-arrow-left"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Column: Info and Impact -->
            <div class="lg:w-1/2 bg-gradient-to-br from-cyan-50 to-blue-50 p-8 flex flex-col">
                <!-- Impact Info -->
                <div class="bg-white rounded-2xl p-6 border-2 border-cyan-200 shadow-lg mb-6">
                    <div class="flex items-start gap-4">
                        <i class="bi bi-lightbulb text-2xl text-cyan-600"></i>
                        <div>
                            <h6 class="font-bold text-lg mb-2 text-gray-800">تأثير الاختيار</h6>
                            <p class="text-gray-700 text-sm">
                                سيؤثر اختيارك على تصميم النظام الشمسي وحجم البطاريات المطلوبة.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Comparison -->
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-6 border-2 border-gray-200 shadow-lg mb-6">
                    <h6 class="font-bold text-gray-800 mb-4 text-center">مقارنة بين النمطين</h6>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gradient-to-br from-yellow-50 to-amber-100 rounded-xl p-4 text-center">
                            <i class="bi bi-sun text-2xl text-yellow-600 mb-2"></i>
                            <div class="font-bold text-gray-800 text-sm mb-1">نهاري</div>
                            <div class="text-xs text-gray-600">بطاريات أقل</div>
                            <div class="text-xs text-gray-600 mt-1">كفاءة أعلى</div>
                        </div>
                        <div class="bg-gradient-to-br from-indigo-50 to-purple-100 rounded-xl p-4 text-center">
                            <i class="bi bi-moon text-2xl text-indigo-600 mb-2"></i>
                            <div class="font-bold text-gray-800 text-sm mb-1">ليلي</div>
                            <div class="text-xs text-gray-600">بطاريات أكبر</div>
                            <div class="text-xs text-gray-600 mt-1">استقلالية أعلى</div>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-2xl p-6 border-2 border-green-200 shadow-lg flex-1">
                    <div class="flex items-center gap-4 mb-4">
                        <i class="bi bi-info-circle text-2xl text-green-600"></i>
                        <h6 class="font-bold text-gray-800">معلومات تقنية</h6>
                    </div>
                    <ul class="space-y-3 text-sm text-gray-700">
                        <li class="flex items-start gap-2">
                            <i class="bi bi-lightning text-green-500 mt-1"></i>
                            <span>النمط النهاري: يستفيد مباشرة من الطاقة الشمسية</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-battery text-green-500 mt-1"></i>
                            <span>النمط الليلي: يحتاج بطاريات لتخزين الطاقة</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-currency-dollar text-green-500 mt-1"></i>
                            <span>النمط النهاري: تكلفة أقل لوجود بطاريات أقل</span>
                        </li>
                    </ul>
                </div>

                <!-- Footer Note -->
                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-600 flex items-center justify-center gap-2">
                        <i class="bi bi-clock text-blue-600"></i>
                        هذا الاختيار يساعد في تحسين أداء النظام الشمسي
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .pattern-radio:checked + .pattern-card {
        border-color: #10b981;
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        box-shadow: 0 10px 25px rgba(16, 185, 129, 0.2);
        transform: scale(1.02);
    }
    
    .pattern-radio:checked + .pattern-card .checkmark-container {
        display: block !important;
        animation: pulse-success 0.6s ease-in-out;
    }
</style>

<script>
    const radioButtons = document.querySelectorAll('.pattern-radio');
    const submitBtn = document.getElementById('submitBtn');

    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            // Add animation to the selected card
            const label = this.nextElementSibling;
            label.classList.add('animate-pulse-success');
            setTimeout(() => {
                label.classList.remove('animate-pulse-success');
            }, 600);
        });
    });

    document.getElementById('step3Form').addEventListener('submit', function(e) {
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split animate-spin"></i> جاري المعالجة...';
        submitBtn.disabled = true;
    });
</script>

@endsection
```

## File: resources/views/Steps/step4.blade.php
```php
@extends('layouts.app')
@section('title', 'الخطوة 4: ساعات الكهرباء العمومية')

@section('content')
<div class="max-w-6xl mx-auto min-h-screen flex items-center">
    <div class="glass-effect rounded-3xl shadow-2xl overflow-hidden animate-slide-in w-full">
        <div class="flex flex-col lg:flex-row h-full">
            <!-- Left Column: Header and Slider -->
            <div class="lg:w-1/2 p-8 flex flex-col">
                <!-- Header -->
                <div class="gradient-green-soft rounded-2xl p-6 text-center mb-8">
                    <div class="inline-block p-3 bg-white rounded-full shadow-lg mb-4">
                        <i class="bi bi-lightning-charge text-4xl text-blue-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">
                        الخطوة 4: ساعات الكهرباء
                    </h3>
                    <p class="text-gray-600 text-sm">
                        كم ساعة تتوفر فيها الكهرباء العمومية يومياً؟
                    </p>
                </div>

                <!-- Form -->
                <form action="{{ route('save.step4') }}" method="POST" id="step4Form" class="flex-1">
                    @csrf
                    
                    <div class="mb-6">
                        <label class="block text-lg font-bold text-gray-800 mb-6 text-center">
                            عدد الساعات المتاحة يومياً
                        </label>

                        <!-- Visual Display -->
                        <div class="text-center mb-6">
                            <div class="inline-block bg-gradient-to-br from-blue-500 to-cyan-600 text-white rounded-3xl px-10 py-5 shadow-xl">
                                <div class="text-5xl font-bold mb-2" id="hoursDisplay">
                                    {{ old('grid_hours', $grid_hours ?? 12) }}
                                </div>
                                <div class="text-lg">ساعة / يوم</div>
                            </div>
                        </div>

                        <!-- Range Slider -->
                        <div class="mb-6">
                            <input type="range"
                                   id="grid_hours_range"
                                   name="grid_hours"
                                   min="0"
                                   max="24"
                                   step="1"
                                   value="{{ old('grid_hours', $grid_hours ?? 12) }}"
                                   class="w-full h-3 rounded-full appearance-none cursor-pointer"
                                   style="background: linear-gradient(to left, #10b981 0%, #10b981 50%, #e5e7eb 50%, #e5e7eb 100%);">
                            
                            <div class="flex justify-between mt-3 text-xs font-semibold text-gray-600">
                                <span>0 س</span>
                                <span>6</span>
                                <span>12</span>
                                <span>18</span>
                                <span>24 س</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Buttons -->
                    <div class="mb-6">
                        <h6 class="font-bold text-gray-800 mb-3 text-sm">اختيار سريع:</h6>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" onclick="setHours(4)" 
                                    class="bg-gradient-to-br from-red-50 to-rose-100 rounded-xl p-3 border-2 border-red-200 
                                           hover:shadow-md transition-all duration-300 hover:scale-105 cursor-pointer text-center">
                                <i class="bi bi-exclamation-triangle text-lg text-red-500 mb-1"></i>
                                <div class="text-red-700 font-medium text-xs mb-1">0-6 ساعات</div>
                                <div class="text-red-600 text-xs">محدود</div>
                            </button>
                            
                            <button type="button" onclick="setHours(10)" 
                                    class="bg-gradient-to-br from-yellow-50 to-amber-100 rounded-xl p-3 border-2 border-yellow-200 
                                           hover:shadow-md transition-all duration-300 hover:scale-105 cursor-pointer text-center">
                                <i class="bi bi-dash-circle text-lg text-yellow-600 mb-1"></i>
                                <div class="text-yellow-800 font-medium text-xs mb-1">7-12 ساعة</div>
                                <div class="text-yellow-700 text-xs">متوسط</div>
                            </button>
                            
                            <button type="button" onclick="setHours(18)" 
                                    class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-xl p-3 border-2 border-green-200 
                                           hover:shadow-md transition-all duration-300 hover:scale-105 cursor-pointer text-center">
                                <i class="bi bi-check-circle text-lg text-green-600 mb-1"></i>
                                <div class="text-green-800 font-medium text-xs mb-1">13-24 ساعة</div>
                                <div class="text-green-700 text-xs">جيد</div>
                            </button>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-100">
                        <a href="{{ route('step3') }}" 
                           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium 
                                  hover:bg-gray-200 transition-all duration-300 flex items-center gap-2 text-sm">
                            <i class="bi bi-arrow-right"></i>
                            السابق
                        </a>
                        
                        <button type="submit" id="submitBtn"
                                class="px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl 
                                       font-medium hover:shadow-lg transition-all duration-300 hover:scale-105 
                                       flex items-center gap-2">
                            التالي
                            <i class="bi bi-arrow-left"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Column: Status and Info -->
            <div class="lg:w-1/2 bg-gradient-to-br from-blue-50 to-cyan-50 p-8 flex flex-col">
                <!-- Status Indicator -->
                <div id="statusIndicator" class="bg-white rounded-2xl p-6 shadow-lg border-4 border-green-500 transition-all duration-300 mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center">
                                <i class="bi bi-check-lg text-white text-xl"></i>
                            </div>
                            <div>
                                <div class="text-xl font-bold text-gray-800" id="statusTitle">توفر جيد</div>
                                <div class="text-gray-600 text-sm" id="statusDesc">كهرباء متاحة معظم اليوم</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-gray-500 mb-1">احتياج البطاريات</div>
                            <div class="text-2xl font-bold" id="batteryNeed">متوسط</div>
                        </div>
                    </div>
                </div>

                <!-- Battery Impact -->
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl p-6 border-2 border-purple-200 shadow-lg mb-6">
                    <h6 class="font-bold text-gray-800 mb-4">تأثير على البطاريات</h6>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">ساعات كهرباء قليلة</span>
                            <span class="text-sm font-bold text-red-600">بطاريات كبيرة</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">ساعات كهرباء متوسطة</span>
                            <span class="text-sm font-bold text-yellow-600">بطاريات متوسطة</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">ساعات كهرباء كثيرة</span>
                            <span class="text-sm font-bold text-green-600">بطاريات صغيرة</span>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl p-6 border-2 border-amber-200 shadow-lg flex-1">
                    <div class="flex items-center gap-4 mb-4">
                        <i class="bi bi-lightbulb text-2xl text-amber-600"></i>
                        <h6 class="font-bold text-gray-800">نصائح</h6>
                    </div>
                    <ul class="space-y-3 text-sm text-gray-700">
                        <li class="flex items-start gap-2">
                            <i class="bi bi-info-circle text-amber-500 mt-1"></i>
                            <span>كلما زادت ساعات الكهرباء، قل احتياجك للبطاريات</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-info-circle text-amber-500 mt-1"></i>
                            <span>المناطق الحضرية عادةً يكون توفر الكهرباء أفضل</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-info-circle text-amber-500 mt-1"></i>
                            <span>المناطق الريفية قد تحتاج بطاريات أكبر</span>
                        </li>
                    </ul>
                </div>

                <!-- Footer Note -->
                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-600 flex items-center justify-center gap-2">
                        <i class="bi bi-battery-charging text-cyan-600"></i>
                        البطاريات تخزن الطاقة لاستخدامها عند انقطاع الكهرباء
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: linear-gradient(135deg, #10b981, #059669);
        cursor: pointer;
        border: 3px solid white;
        box-shadow: 0 3px 8px rgba(16, 185, 129, 0.4);
        transition: all 0.3s;
    }

    input[type="range"]::-webkit-slider-thumb:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.6);
    }

    input[type="range"]::-moz-range-thumb {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: linear-gradient(135deg, #10b981, #059669);
        cursor: pointer;
        border: 3px solid white;
        box-shadow: 0 3px 8px rgba(16, 185, 129, 0.4);
        transition: all 0.3s;
    }
</style>

<script>
    const rangeInput = document.getElementById('grid_hours_range');
    const hoursDisplay = document.getElementById('hoursDisplay');
    const statusIndicator = document.getElementById('statusIndicator');
    const statusTitle = document.getElementById('statusTitle');
    const statusDesc = document.getElementById('statusDesc');
    const batteryNeed = document.getElementById('batteryNeed');
    const submitBtn = document.getElementById('submitBtn');

    function setHours(value) {
        rangeInput.value = value;
        updateDisplay();
        addPulseAnimation();
    }

    function updateDisplay() {
        const value = parseInt(rangeInput.value);
        hoursDisplay.textContent = value;
        
        // Update slider gradient
        const percent = (value / 24) * 100;
        rangeInput.style.background = `linear-gradient(to left, #10b981 0%, #10b981 ${percent}%, #e5e7eb ${percent}%, #e5e7eb 100%)`;
        
        // Update status
        if (value >= 0 && value <= 6) {
            updateStatus('توفر محدود', 'كهرباء قليلة يومياً', 'عالي', 'from-red-400 to-rose-600', 'border-red-500', 'text-red-700');
        } else if (value >= 7 && value <= 12) {
            updateStatus('توفر متوسط', 'كهرباء معتدلة يومياً', 'متوسط', 'from-yellow-400 to-amber-600', 'border-yellow-500', 'text-yellow-700');
        } else {
            updateStatus('توفر جيد', 'كهرباء متاحة معظم اليوم', 'منخفض', 'from-green-400 to-emerald-600', 'border-green-500', 'text-green-700');
        }
    }

    function updateStatus(title, desc, battery, gradientClass, borderClass, textClass) {
        statusTitle.textContent = title;
        statusDesc.textContent = desc;
        batteryNeed.textContent = battery;
        batteryNeed.className = 'text-2xl font-bold ' + textClass;
        
        const icon = statusIndicator.querySelector('.w-12');
        icon.className = 'w-12 h-12 rounded-full bg-gradient-to-br ' + gradientClass + ' flex items-center justify-center';
        
        statusIndicator.className = 'bg-white rounded-2xl p-6 shadow-lg border-4 transition-all duration-300 ' + borderClass;
    }

    function addPulseAnimation() {
        hoursDisplay.parentElement.classList.add('animate-pulse-success');
        setTimeout(() => {
            hoursDisplay.parentElement.classList.remove('animate-pulse-success');
        }, 600);
    }

    rangeInput.addEventListener('input', updateDisplay);

    // Initialize on load
    updateDisplay();

    document.getElementById('step4Form').addEventListener('submit', function(e) {
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split animate-spin"></i> جاري المعالجة...';
        submitBtn.disabled = true;
    });
</script>

@endsection
```

## File: resources/views/Steps/step5.blade.php
```php
@extends('layouts.app')
@section('title', 'الخطوة 5: عدد الطوابق')

@section('content')
<div class="max-w-6xl mx-auto min-h-screen flex items-center">
    <div class="glass-effect rounded-3xl shadow-2xl overflow-hidden animate-slide-in w-full">
        <div class="flex flex-col lg:flex-row h-full">
            <!-- Left Column: Header and Slider -->
            <div class="lg:w-1/2 p-8 flex flex-col">
                <!-- Header -->
                <div class="gradient-green-soft rounded-2xl p-6 text-center mb-8">
                    <div class="inline-block p-3 bg-white rounded-full shadow-lg mb-4">
                        <i class="bi bi-building text-4xl text-indigo-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">
                        الخطوة 5: عدد الطوابق
                    </h3>
                    <p class="text-gray-600 text-sm">
                        كم عدد الطوابق بين السطح والطابق المراد تركيب النظام فيه؟
                    </p>
                </div>

                <!-- Form -->
                <form action="{{ route('save.step5') }}" method="POST" id="step5Form" class="flex-1">
                    @csrf

                    <div class="mb-6">
                        <label class="block text-lg font-bold text-gray-800 mb-6 text-center">
                            عدد الطوابق في المبنى
                        </label>

                        <!-- Visual Display -->
                        <div class="text-center mb-6">
                            <div class="inline-block">
                                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 text-white rounded-3xl px-10 py-5 shadow-xl">
                                    <div class="text-5xl font-bold mb-2" id="floorsDisplay">
                                        {{ old('floors', $floors ?? 1) }}
                                    </div>
                                    <div class="text-lg">طابق</div>
                                </div>
                            </div>
                        </div>

                        <!-- Range Slider (1 to 15) -->
                        <div class="mb-6">
                            <input type="range"
                                   id="floors_range"
                                   name="floors"
                                   min="1"
                                   max="15"
                                   step="1"
                                   value="{{ old('floors', $floors ?? 1) }}"
                                   class="w-full h-3 rounded-full appearance-none cursor-pointer"
                                   style="background: linear-gradient(to left, #6366f1 0%, #6366f1 6.7%, #e5e7eb 6.7%, #e5e7eb 100%);">
                            <div class="flex justify-between mt-3 text-xs font-semibold text-gray-600">
                                <span>1 ط</span>
                                <span>5</span>
                                <span>10</span>
                                <span>15 ط</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Select Buttons -->
                    <div class="mb-6">
                        <h6 class="font-bold text-gray-800 mb-3 text-sm">اختيار سريع:</h6>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" onclick="setFloors(1)"
                                    class="bg-gradient-to-br from-emerald-50 to-green-100 rounded-xl p-3 border-2 border-green-300
                                           hover:shadow-md transition-all duration-300 hover:scale-105 cursor-pointer text-center">
                                <i class="bi bi-house-door text-lg text-green-600 mb-1"></i>
                                <div class="font-medium text-gray-800 text-xs">فيلا/منزل</div>
                                <div class="text-gray-600 text-xs mt-1">1-2 طابق</div>
                            </button>

                            <button type="button" onclick="setFloors(3)"
                                    class="bg-gradient-to-br from-blue-50 to-cyan-100 rounded-xl p-3 border-2 border-blue-300
                                           hover:shadow-md transition-all duration-300 hover:scale-105 cursor-pointer text-center">
                                <i class="bi bi-building text-lg text-blue-600 mb-1"></i>
                                <div class="font-medium text-gray-800 text-xs">عمارة صغيرة</div>
                                <div class="text-gray-600 text-xs mt-1">3-4 طوابق</div>
                            </button>

                            <button type="button" onclick="setFloors(7)"
                                    class="bg-gradient-to-br from-purple-50 to-pink-100 rounded-xl p-3 border-2 border-purple-300
                                           hover:shadow-md transition-all duration-300 hover:scale-105 cursor-pointer text-center">
                                <i class="bi bi-buildings text-lg text-purple-600 mb-1"></i>
                                <div class="font-medium text-gray-800 text-xs">عمارة متوسطة</div>
                                <div class="text-gray-600 text-xs mt-1">5-10 طوابق</div>
                            </button>

                            <button type="button" onclick="setFloors(12)"
                                    class="bg-gradient-to-br from-orange-50 to-red-100 rounded-xl p-3 border-2 border-orange-300
                                           hover:shadow-md transition-all duration-300 hover:scale-105 cursor-pointer text-center">
                                <i class="bi bi-hospital text-lg text-orange-600 mb-1"></i>
                                <div class="font-medium text-gray-800 text-xs">برج/مبنى</div>
                                <div class="text-gray-600 text-xs mt-1">11-15 طابق</div>
                            </button>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-100">
                        <a href="{{ route('step4') }}"
                           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium
                                  hover:bg-gray-200 transition-all duration-300 flex items-center gap-2 text-sm">
                            <i class="bi bi-arrow-right"></i>
                            السابق
                        </a>
                        <button type="submit" id="submitBtn"
                                class="px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl
                                       font-medium hover:shadow-lg transition-all duration-300 hover:scale-105
                                       flex items-center gap-2">
                            عرض النتائج
                            <i class="bi bi-check-circle"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Column: Info and Impact -->
            <div class="lg:w-1/2 bg-gradient-to-br from-indigo-50 to-purple-50 p-8 flex flex-col">
                <!-- Why Info -->
                <div class="bg-white rounded-2xl p-6 border-2 border-yellow-200 shadow-lg mb-6">
                    <div class="flex items-start gap-4">
                        <i class="bi bi-lightning-charge text-2xl text-yellow-600"></i>
                        <div>
                            <h6 class="font-bold text-lg mb-2 text-gray-800">لماذا نحتاج هذه المعلومة؟</h6>
                            <p class="text-gray-700 text-sm">
                                عدد الطوابق يؤثر على تصميم النظام الشمسي وتوزيع المكونات الكهربائية وطول الكابلات المطلوبة.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Cable Impact -->
                <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl p-6 border-2 border-blue-200 shadow-lg mb-6">
                    <h6 class="font-bold text-gray-800 mb-4">تأثير على طول الكابلات</h6>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">طابق واحد</span>
                            <span class="text-sm font-bold text-green-600">كابلات قصيرة</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">3-5 طوابق</span>
                            <span class="text-sm font-bold text-yellow-600">كابلات متوسطة</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-700">6+ طوابق</span>
                            <span class="text-sm font-bold text-red-600">كابلات طويلة</span>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-6 border-2 border-gray-200 shadow-lg flex-1">
                    <div class="flex items-center gap-4 mb-4">
                        <i class="bi bi-info-circle text-2xl text-gray-600"></i>
                        <h6 class="font-bold text-gray-800">اعتبارات تقنية</h6>
                    </div>
                    <ul class="space-y-3 text-sm text-gray-700">
                        <li class="flex items-start gap-2">
                            <i class="bi bi-rulers text-gray-500 mt-1"></i>
                            <span>طول الكابلات يزيد مع عدد الطوابق</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-diagram-2 text-gray-500 mt-1"></i>
                            <span>يؤثر على توزيع المكونات في المبنى</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="bi bi-lightning text-gray-500 mt-1"></i>
                            <span>يؤثر على هبوط الجهد في الكابلات</span>
                        </li>
                    </ul>
                </div>

                <!-- Footer Note -->
                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-600 flex items-center justify-center gap-2">
                        <i class="bi bi-building text-indigo-600"></i>
                        سيتم تصميم النظام بناءً على عدد الطوابق
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        cursor: pointer;
        border: 3px solid white;
        box-shadow: 0 3px 8px rgba(99, 102, 241, 0.4);
        transition: all 0.3s;
    }

    input[type="range"]::-webkit-slider-thumb:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.6);
    }

    input[type="range"]::-moz-range-thumb {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        cursor: pointer;
        border: 3px solid white;
        box-shadow: 0 3px 8px rgba(99, 102, 241, 0.4);
        transition: all 0.3s;
    }
</style>

<script>
    const rangeInput = document.getElementById('floors_range');
    const floorsDisplay = document.getElementById('floorsDisplay');
    const submitBtn = document.getElementById('submitBtn');

    function setFloors(value) {
        rangeInput.value = value;
        updateDisplay();
        addPulseAnimation();
    }

    function updateDisplay() {
        const value = parseInt(rangeInput.value);
        floorsDisplay.textContent = value;
        
        // الصيغة الصحيحة: (value - min) / (max - min) * 100
        const min = 1;
        const max = 15;
        const percent = ((value - min) / (max - min)) * 100;    

        rangeInput.style.background = `linear-gradient(to left, #6366f1 0%, #6366f1 ${percent}%, #e5e7eb ${percent}%, #e5e7eb 100%)`;
    }

    function addPulseAnimation() {
        floorsDisplay.parentElement.classList.add('animate-pulse-success');
        setTimeout(() => {
            floorsDisplay.parentElement.classList.remove('animate-pulse-success');
        }, 600);
    }

    rangeInput.addEventListener('input', updateDisplay);

    // Initialize on load
    updateDisplay();

    document.getElementById('step5Form').addEventListener('submit', function(e) {
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split animate-spin"></i> جاري حساب النتائج...';
        submitBtn.disabled = true;
    });
</script>
@endsection
```

## File: routes/web.php
```php
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
```
