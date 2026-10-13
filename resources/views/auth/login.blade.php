<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | ProWave CRM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full bg-slate-950 font-sans flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Ambient Glow Background -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-cyan-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex h-16 w-16 rounded-2xl bg-gradient-to-tr from-brand-600 via-indigo-500 to-cyan-400 p-0.5 shadow-2xl shadow-indigo-500/30 mb-4">
                <div class="h-full w-full bg-slate-950 rounded-[14px] flex items-center justify-center">
                    <svg class="w-9 h-9 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight text-white">ProWave CRM</h1>
            <p class="text-sm text-slate-400 mt-1">Cold Calling & Sales Pipeline Command Center</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-8 backdrop-blur-2xl shadow-2xl">
            <h2 class="text-lg font-bold text-white mb-2">Sign in to your account</h2>
            <p class="text-xs text-slate-400 mb-6">Enter your authorized ProWave credentials below</p>

            @if($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs">
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('success'))
                <div class="mb-5 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Work Email</label>
                    <input type="email" name="email" id="email" required
                           value="{{ old('email', 'admin@prowave.com') }}"
                           class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Password</label>
                    <input type="password" name="password" id="password" required
                           value="password123"
                           class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition">
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-950 border-slate-800 text-brand-600 focus:ring-brand-500">
                        <span class="text-xs text-slate-400">Remember session</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3.5 px-4 rounded-xl font-bold text-sm bg-gradient-to-r from-brand-600 via-indigo-600 to-indigo-500 hover:opacity-95 text-white shadow-lg shadow-brand-500/30 transition">
                    Sign In to Portal &rarr;
                </button>
            </form>

            <!-- Quick Demo Credentials Switcher -->
            <div class="mt-8 pt-6 border-t border-slate-800">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3 text-center">Quick Demo Switcher</div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" onclick="fillCreds('admin@prowave.com', 'password123')"
                            class="p-2.5 rounded-xl bg-purple-950/40 border border-purple-800/50 hover:bg-purple-900/40 text-purple-200 text-left transition">
                        <div class="font-bold text-purple-300">Super Admin</div>
                        <div class="text-[10px] text-purple-400 truncate">admin@prowave.com</div>
                    </button>
                    <button type="button" onclick="fillCreds('ali@prowave.com', 'password123')"
                            class="p-2.5 rounded-xl bg-cyan-950/40 border border-cyan-800/50 hover:bg-cyan-900/40 text-cyan-200 text-left transition">
                        <div class="font-bold text-cyan-300">Ali Khan (Agent)</div>
                        <div class="text-[10px] text-cyan-400 truncate">ali@prowave.com</div>
                    </button>
                    <button type="button" onclick="fillCreds('usman@prowave.com', 'password123')"
                            class="col-span-2 p-2.5 rounded-xl bg-slate-950 border border-slate-800 hover:bg-slate-800/60 text-slate-300 text-left transition">
                        <div class="font-bold text-slate-200">Usman Tariq (Agent 2)</div>
                        <div class="text-[10px] text-slate-400 truncate">usman@prowave.com</div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function fillCreds(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>
