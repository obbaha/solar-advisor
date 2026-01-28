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