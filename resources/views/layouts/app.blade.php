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


<script type="text/javascript">
  (function(d, t) {
      var v = d.createElement(t), s = d.getElementsByTagName(t)[0];
      v.onload = function() {
        window.voiceflow.chat.load({
          verify: { projectID: '697b93de59afd3be3ff814d8' },
          url: 'https://general-runtime.voiceflow.com',
          versionID: 'production',
          voice: {
            url: "https://runtime-api.voiceflow.com"
          }
        });
      }
      v.src = "https://cdn.voiceflow.com/widget-next/bundle.mjs"; v.type = "text/javascript"; s.parentNode.insertBefore(v, s);
  })(document, 'script');
</script>



</body>
</html>