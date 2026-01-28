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