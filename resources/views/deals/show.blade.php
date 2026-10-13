@extends('layouts.app')

@section('title', 'Contract: ' . $deal->project_title)
@section('page_heading', 'Won Project Contract')
@section('page_subheading', 'Detailed client deliverables, final budget & representative attribution')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="bg-slate-900/90 border border-emerald-500/30 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-slate-800">
            <div>
                <span class="px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                    CLOSED & WON
                </span>
                <h2 class="text-2xl font-black text-white mt-2">{{ $deal->project_title }}</h2>
                <div class="text-sm font-semibold text-slate-400 mt-1">Client: {{ $deal->client_name }}</div>
            </div>

            <div class="bg-slate-950 p-5 rounded-2xl border border-emerald-500/30 text-right">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Agreed Deal Amount</div>
                <div class="text-3xl font-black text-emerald-400 font-mono mt-0.5">
                    ${{ number_format($deal->deal_amount, 2) }} {{ $deal->currency }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 py-6 border-b border-slate-800 text-xs">
            <div>
                <span class="text-slate-400 block mb-1 uppercase tracking-wider">Closing Sales Rep</span>
                <div class="text-sm font-bold text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    {{ $deal->user->name }}
                </div>
                <div class="text-[11px] text-slate-400">{{ $deal->user->email }}</div>
            </div>

            <div>
                <span class="text-slate-400 block mb-1 uppercase tracking-wider">Closed Date</span>
                <div class="text-sm font-bold text-white">{{ $deal->closed_date->format('F d, Y') }}</div>
                <div class="text-[11px] text-slate-400">{{ $deal->closed_date->diffForHumans() }}</div>
            </div>

            <div>
                <span class="text-slate-400 block mb-1 uppercase tracking-wider">Related Lead Profile</span>
                <a href="{{ route('leads.show', $deal->lead) }}" class="text-sm font-bold text-brand-400 hover:underline">
                    View Complete Lead Activity &rarr;
                </a>
                <div class="text-[11px] text-slate-400">Phone: {{ $deal->lead->phone }}</div>
            </div>
        </div>

        <div class="pt-6 space-y-6">
            <div>
                <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Scope of Work & Deliverables</h3>
                <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 text-sm text-slate-200 leading-relaxed whitespace-pre-line">
                    {{ $deal->project_scope ?: 'No specific scope text provided during call closing.' }}
                </div>
            </div>

            @if($deal->notes)
                <div>
                    <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Contract Notes & Remarks</h3>
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 text-sm text-slate-300 leading-relaxed">
                        {{ $deal->notes }}
                    </div>
                </div>
            @endif
        </div>

        <div class="mt-8 pt-6 border-t border-slate-800 flex items-center justify-between">
            <a href="{{ route('deals.index') }}" class="text-xs font-semibold text-slate-400 hover:text-white">
                &larr; Back to Won Deals
            </a>
            <a href="{{ route('leads.show', $deal->lead) }}" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white shadow-lg shadow-brand-600/25 transition">
                Open Lead Workspace
            </a>
        </div>
    </div>

</div>
@endsection
