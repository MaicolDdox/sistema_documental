@php
    $asesorSwitcherList = \App\Support\AsesorSemilleroContext::semillerosDelUsuarioAutenticado();
    $asesorSwitcherActivo = \App\Support\AsesorSemilleroContext::semilleroActivo($asesorSwitcherList);
@endphp
@if($asesorSwitcherList->count() > 1)
<form method="POST" action="{{ route('asesor.semillero-activo.store') }}" class="px-3 mb-3">
    @csrf
    <label class="text-[10px] font-semibold text-slate-400 uppercase tracking-wide">Semillero activo</label>
    <select name="seedling_id" onchange="this.form.submit()" class="mt-1 w-full text-xs border border-slate-200 rounded-lg px-2 py-1.5 bg-white text-slate-800 focus:border-[#39A900] focus:ring-1 focus:ring-[#39A900]/20">
        @foreach($asesorSwitcherList as $s)
            <option value="{{ $s->id }}" @selected($asesorSwitcherActivo && (int) $asesorSwitcherActivo->id === (int) $s->id)>{{ $s->nombre }}</option>
        @endforeach
    </select>
</form>
@elseif($asesorSwitcherActivo)
<p class="px-3 mb-2 text-[11px] text-slate-500 truncate" title="{{ $asesorSwitcherActivo->nombre }}">Semillero: <span class="font-medium text-slate-700">{{ $asesorSwitcherActivo->nombre }}</span></p>
@endif
