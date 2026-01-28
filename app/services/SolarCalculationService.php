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
class SolarcalCulationService
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
    private const DEPTH_OF_DISCHARGE = 0.85;         // عمق تفريغ البطارية المسموح
    private const PEAK_SUN_HOURS = 4.5;              // ساعات الذروة الشمسية اليومية
    private const INVERTER_SURGE_FACTOR = 1.25;      // معامل الحمل الزائد للإنفرتر
    private const SAFETY_MARGIN = 1.05;              // هامش أمان إضافي 5%
    
    /* ==================== معاملات الطاقة الهجينة ==================== */
    
    private const MIN_SOLAR_RATIO = 0.50;            // الحد الأدنى لنسبة الطاقة الشمسية
    private const MAX_SOLAR_RATIO = 0.70;            // الحد الأقصى لنسبة الطاقة الشمسية
    private const SOLAR_BATTERY_CHARGE_RATIO = 0.40; // نسبة شحن البطاريات من الطاقة الشمسية
    private const BATTERY_OVERSIZE_FACTOR = 1.10;    // زيادة حجم البطارية بنسبة %10
    
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
        $batteries = $this->calculateBatteries($consumptionAnalysis['night_kwh'], $energyMix, $gridHours);
        
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
private function calculateBatteries(float $nightConsumption, array $energyMix, int $gridHours): array
{
    // حساب السعة المطلوبة نظراً للاستهلاك الليلي

// كلما زادت ساعات الكهرباء، قلّت الحاجة للبطارية
$coverageRatio = $gridHours >= 12 ? 0.40 : ($gridHours >= 6 ? 0.60 : 0.70);
$requiredCapacityWh = $nightConsumption * 1000 * $coverageRatio;


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