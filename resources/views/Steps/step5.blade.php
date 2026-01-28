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