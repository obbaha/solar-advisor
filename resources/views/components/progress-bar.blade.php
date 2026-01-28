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