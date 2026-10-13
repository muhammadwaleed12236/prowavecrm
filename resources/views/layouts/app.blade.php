<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') | ProWave CRM</title>
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                            950: '#1e1b4b',
                        },
                        accent: {
                            cyan: '#06b6d4',
                            emerald: '#10b981',
                            amber: '#f59e0b',
                            rose: '#f43f5e',
                            violet: '#8b5cf6',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
    </style>
</head>
<body class="h-full bg-slate-950 font-sans antialiased text-slate-200 selection:bg-brand-500 selection:text-white" x-data="{ sidebarOpen: false }">

    <div class="min-h-full flex flex-col md:flex-row">
        <!-- Mobile Backdrop Overlay -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false" 
             x-cloak 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-40 md:hidden"></div>

        <!-- Sidebar Navigation -->
        <aside class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-slate-900/95 border-r border-slate-800/80 backdrop-blur-xl transition-transform duration-300 md:static md:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
            
            <!-- ProWave Technologies Brand Header -->
            <div class="flex h-20 items-center justify-between px-6 border-b border-slate-800/80">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-brand-600 via-indigo-500 to-accent-cyan p-0.5 shadow-lg shadow-brand-500/25 group-hover:shadow-brand-500/40 transition">
                        <div class="h-full w-full bg-slate-950 rounded-[10px] flex items-center justify-center">
                            <svg class="w-6 h-6 text-brand-400 group-hover:scale-110 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-base font-extrabold tracking-tight text-white flex items-center gap-1.5">
                            ProWave <span class="text-brand-400 font-semibold text-xs uppercase px-1.5 py-0.5 rounded bg-brand-500/10 border border-brand-500/20">CRM</span>
                        </div>
                        <div class="text-[11px] font-medium text-slate-400">Sales & Pipeline HQ</div>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Role Badge Banner -->
            <div class="px-6 py-4">
                <div class="rounded-xl p-3 bg-gradient-to-r {{ auth()->user()->isSuperAdmin() ? 'from-purple-900/40 to-brand-900/30 border border-purple-500/30' : 'from-blue-900/40 to-cyan-900/30 border border-cyan-500/30' }} flex items-center gap-3">
                    <div class="h-9 w-9 rounded-lg {{ auth()->user()->isSuperAdmin() ? 'bg-purple-500/20 text-purple-300' : 'bg-cyan-500/20 text-cyan-300' }} flex items-center justify-center font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] font-medium {{ auth()->user()->isSuperAdmin() ? 'text-purple-300' : 'text-cyan-300' }} flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full {{ auth()->user()->isSuperAdmin() ? 'bg-purple-400 animate-pulse' : 'bg-cyan-400' }}"></span>
                            {{ auth()->user()->isSuperAdmin() ? 'Super Administrator' : 'Sales Representative' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-2 space-y-1.5 overflow-y-auto">
                <div class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Main Menu</div>

                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition {{ request()->routeIs('dashboard') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard Overview
                </a>

                <a href="{{ route('leads.index') }}" 
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm transition {{ request()->routeIs('leads.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        {{ auth()->user()->isSuperAdmin() ? 'All Leads Pool' : 'My Assigned Leads' }}
                    </div>
                </a>

                <a href="{{ route('meetings.index') }}" 
                   class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm transition {{ request()->routeIs('meetings.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Meetings & Alerts
                    </div>
                    @php
                        $alertCount = \App\Models\Meeting::visibleTo(auth()->user())->whereDate('scheduled_at', \Carbon\Carbon::today())->where('status', 'scheduled')->count();
                    @endphp
                    @if($alertCount > 0)
                        <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-accent-amber text-slate-950 animate-pulse">
                            {{ $alertCount }} Today
                        </span>
                    @endif
                </a>

                <a href="{{ route('deals.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition {{ request()->routeIs('deals.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <svg class="w-5 h-5 text-accent-emerald" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Won Deals & Revenue
                </a>

                <a href="{{ route('reports.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition {{ request()->routeIs('reports.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Daily Auto Reports
                </a>

                @if(auth()->user()->isSuperAdmin())
                    <div class="pt-4 px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Administration</div>

                    <a href="{{ route('team.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition {{ request()->routeIs('team.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Team & Reps Manager
                    </a>

                    <a href="{{ route('roles.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition {{ request()->routeIs('roles.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Roles & Permissions
                    </a>
                @endif
            </nav>

            <!-- Logout Footer -->
            <div class="p-4 border-t border-slate-800/80">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-400 hover:bg-rose-500/10 border border-rose-500/20 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Sign Out
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Workspace -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-950">
            <!-- Top App Bar -->
            <header class="h-20 border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-xl flex items-center justify-between px-6 sticky top-0 z-40">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="md:hidden text-slate-400 hover:text-white p-2 rounded-lg bg-slate-800/50">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-white">@yield('page_heading', 'Dashboard')</h1>
                        <p class="text-xs text-slate-400 hidden sm:block">@yield('page_subheading', 'ProWave Technologies Sales CRM System')</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Quick Import Excel Button -->
                    <a href="{{ route('leads.import.show') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 border border-emerald-500/30 transition">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span class="hidden sm:inline">Import Excel</span>
                    </a>

                    <!-- Quick New Lead Button -->
                    <a href="{{ route('leads.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-brand-600 hover:bg-brand-500 text-white shadow-lg shadow-brand-600/25 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>New Lead</span>
                    </a>

                    <!-- User Pill -->
                    <div class="h-10 px-3 rounded-xl bg-slate-900 border border-slate-800 flex items-center gap-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></div>
                        <span class="text-xs font-medium text-slate-300 hidden md:inline">{{ auth()->user()->email }}</span>
                    </div>
                </div>
            </header>

            <!-- Flash Alerts -->
            <div class="px-6 pt-4">
                @if(session('success'))
                    <div class="rounded-xl bg-emerald-500/10 border border-emerald-500/30 p-4 text-emerald-400 text-sm flex items-center justify-between animate-fadeIn">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="rounded-xl bg-rose-500/10 border border-rose-500/30 p-4 text-rose-400 text-sm">
                        <div class="font-semibold mb-1">Please fix the following issues:</div>
                        <ul class="list-disc list-inside text-xs space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Page Content View -->
            <main class="flex-1 p-4 sm:p-6 pb-28 md:pb-6 overflow-y-auto">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Mobile Bottom Navigation Dock (Appears only on mobile/tablet screens < 768px) -->
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-slate-900/95 border-t border-slate-800/90 backdrop-blur-xl px-2 py-2">
        <div class="flex items-center justify-around">
            <!-- Home -->
            <a href="{{ route('dashboard') }}" 
               class="flex flex-col items-center gap-1 py-1 px-2.5 rounded-xl text-[10px] font-semibold transition {{ request()->routeIs('dashboard') ? 'text-brand-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <!-- Leads -->
            <a href="{{ route('leads.index') }}" 
               class="flex flex-col items-center gap-1 py-1 px-2.5 rounded-xl text-[10px] font-semibold transition {{ request()->routeIs('leads.*') ? 'text-brand-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Leads</span>
            </a>

            <!-- Meetings with alert badge -->
            @php
                $mobileAlertCount = \App\Models\Meeting::visibleTo(auth()->user())->whereDate('scheduled_at', \Carbon\Carbon::today())->where('status', 'scheduled')->count();
            @endphp
            <a href="{{ route('meetings.index') }}" 
               class="flex flex-col items-center gap-1 py-1 px-2.5 rounded-xl text-[10px] font-semibold transition relative {{ request()->routeIs('meetings.*') ? 'text-brand-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Meetings</span>
                @if($mobileAlertCount > 0)
                    <span class="absolute top-0.5 right-2 w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                @endif
            </a>

            <!-- Won Deals -->
            <a href="{{ route('deals.index') }}" 
               class="flex flex-col items-center gap-1 py-1 px-2.5 rounded-xl text-[10px] font-semibold transition {{ request()->routeIs('deals.*') ? 'text-emerald-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Deals</span>
            </a>

            <!-- Menu Trigger -->
            <button @click="sidebarOpen = true" 
                    type="button"
                    class="flex flex-col items-center gap-1 py-1 px-2.5 rounded-xl text-[10px] font-semibold text-slate-400 hover:text-slate-200">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <span>Menu</span>
            </button>
        </div>
    </nav>

    @stack('scripts')
</body>
</html>
