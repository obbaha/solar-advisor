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