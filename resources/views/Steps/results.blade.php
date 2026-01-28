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