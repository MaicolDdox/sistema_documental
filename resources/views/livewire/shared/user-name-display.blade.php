<div class="contents">
    @if($mode === 'topbar')
        <span class="text-xs text-slate-500 truncate max-w-[140px] md:max-w-[220px]">
            {{ $displayName }}
        </span>
    @else
        <div class="w-8 h-8 rounded-full bg-[#0a1628] flex items-center justify-center flex-shrink-0">
            <span class="text-white text-xs font-bold">{{ $initials }}</span>
        </div>
        <div class="flex-1 overflow-hidden">
            <p class="text-sm font-medium text-slate-800 truncate">{{ $displayName }}</p>
            <p class="text-xs text-slate-400 truncate">{{ $displayEmail }}</p>
        </div>
    @endif
</div>
