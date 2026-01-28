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