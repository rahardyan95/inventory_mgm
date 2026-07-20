<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enterprise Inventory System</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        // Soft enterprise colors
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            900: '#0c4a6e',
                        },
                        surface: {
                            dark: '#0f172a', // slate-900 (softer than black)
                            card: '#1e293b', // slate-800
                        }
                    },
                    animation: {
                        'blob': 'blob 10s infinite',
                        'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
                    },
                    keyframes: {
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(20px, -30px) scale(1.05)' },
                            '66%': { transform: 'translate(-15px, 15px) scale(0.95)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        },
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(15px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .glass-panel {
            background: rgba(30, 41, 59, 0.6); /* slate-800 with opacity */
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .btn-soft-glow {
            transition: all 0.3s ease;
        }
        .btn-soft-glow:hover {
            box-shadow: 0 4px 20px rgba(14, 165, 233, 0.4);
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-surface-dark text-slate-200 font-sans antialiased overflow-hidden min-h-screen flex items-center justify-center relative selection:bg-brand-500 selection:text-white">

    <!-- Subtle Animated Background Orbs -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <!-- Top Left Soft Blue -->
        <div class="absolute -top-[10%] -left-[5%] w-[500px] h-[500px] bg-brand-900/40 rounded-full mix-blend-screen filter blur-[100px] animate-blob"></div>
        <!-- Bottom Right Soft Teal/Cyan -->
        <div class="absolute -bottom-[10%] -right-[5%] w-[600px] h-[600px] bg-sky-900/30 rounded-full mix-blend-screen filter blur-[120px] animate-blob" style="animation-delay: 3s;"></div>
    </div>

    <!-- Main Content Container -->
    <div class="relative z-10 w-full max-w-5xl px-6 flex flex-col lg:flex-row items-center gap-12 lg:gap-16 opacity-0 animate-fade-in-up">
        
        <!-- Left Side: Typography & Info -->
        <div class="flex-1 text-center lg:text-left">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-slate-800/80 border border-slate-700/50 text-slate-300 text-sm font-medium mb-8">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                Sistem Aktif & Terhubung
            </div>
            
            <h1 class="text-4xl lg:text-5xl font-bold tracking-tight mb-5 text-slate-100 leading-tight">
                Enterprise <br />
                <span class="text-brand-400">
                    Inventory Hub
                </span>
            </h1>
            
            <p class="text-slate-400 text-lg mb-10 max-w-lg mx-auto lg:mx-0 leading-relaxed font-light">
                Platform sentralisasi manajemen stok gudang, pencatatan transaksi masuk/keluar, dan pengelolaan inventaris yang efisien dan aman.
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row items-stretch justify-center lg:justify-start gap-3 w-full max-w-2xl mx-auto lg:mx-0">
                <!-- Button: Login Admin/Manager -->
                <a href="/admin" class="btn-soft-glow flex-1 inline-flex flex-col items-center justify-center px-4 py-4 text-sm font-medium text-white bg-brand-600 hover:bg-brand-500 rounded-xl transition-colors text-center">
                    <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    <span>Login Admin/Manager</span>
                </a>

                <!-- Button: Login Staf -->
                <a href="/admin" class="btn-soft-glow flex-1 inline-flex flex-col items-center justify-center px-4 py-4 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-500 rounded-xl transition-colors text-center">
                    <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span>Login Staf</span>
                </a>
                
                <!-- Button: Mobile App -->
                <a href="#" class="flex-1 inline-flex flex-col items-center justify-center px-4 py-4 text-sm font-medium text-slate-300 bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-slate-600 rounded-xl transition-all duration-300 hover:text-white text-center">
                    <svg class="w-6 h-6 mb-2 text-slate-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    <span>Aplikasi Mobile</span>
                </a>
            </div>
        </div>

        <!-- Right Side: Glassmorphism Info Card -->
        <div class="flex-1 w-full max-w-md hidden md:block">
            <div class="glass-panel p-8 rounded-2xl shadow-xl relative overflow-hidden">
                <h3 class="text-lg font-semibold mb-6 flex items-center text-slate-200">
                    <svg class="w-5 h-5 mr-2 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Informasi Akses
                </h3>
                
                <div class="space-y-4">
                    <div class="bg-slate-800/60 p-4 rounded-xl border border-slate-700/50">
                        <div class="text-xs text-slate-400 mb-1 uppercase tracking-wider font-semibold">Manajemen & Laporan</div>
                        <div class="font-medium text-slate-200">Akses via Web Dashboard</div>
                        <div class="text-sm text-brand-300 mt-1">Gunakan akun Manager/Admin</div>
                    </div>
                    
                    <div class="bg-slate-800/60 p-4 rounded-xl border border-slate-700/50">
                        <div class="text-xs text-slate-400 mb-1 uppercase tracking-wider font-semibold">Scanner & Transaksi</div>
                        <div class="font-medium text-slate-200">Akses via Aplikasi Mobile</div>
                        <div class="text-sm text-emerald-400 mt-1">Gunakan akun Staff Gudang</div>
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-slate-700/50 flex items-center justify-between text-sm text-slate-500">
                    <span>Versi 1.0.0</span>
                    <span class="flex items-center">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 opacity-80"></span>
                        Online
                    </span>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
