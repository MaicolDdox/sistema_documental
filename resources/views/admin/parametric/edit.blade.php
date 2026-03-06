<x-app-layout>
    <x-slot name="header">{{ $title }}</x-slot>
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-slate-900">{{ $title }}</h2>
        <a href="{{ route($routePrefix.'.index') }}" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">Volver</a>
    </div>

    @php
        $segment = last(explode('.', $routePrefix));
        $updateAction = url()->to(str_replace('.', '/', $routePrefix) . '/' . ($item->id ?? $item->getKey()));
    @endphp
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden max-w-3xl">
        <div class="p-5">
            <form method="POST" action="{{ $updateAction }}" class="space-y-5">
                @csrf
                @method('PUT')
                @foreach($fields as $key => $fieldDef)
                    <div>
                        <label for="{{ $key }}" class="block text-sm font-medium text-slate-700 mb-1.5">{{ ucfirst(str_replace('_id', '', $key)) }} <span class="text-red-500">*</span></label>
                        @if($fieldDef['type'] == 'relation')
                            <select name="{{ $key }}" id="{{ $key }}" required class="w-full border @error($key) border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                                <option value="">Seleccione...</option>
                                @foreach($fieldDef['options'] as $opt)
                                    <option value="{{ $opt->id }}" {{ old($key, $item->$key) == $opt->id ? 'selected' : '' }}>{{ $opt->nombre ?? $opt->codigo ?? $opt->id }}</option>
                                @endforeach
                            </select>
                        @elseif($fieldDef['type'] == 'enum')
                            <select name="{{ $key }}" id="{{ $key }}" required class="w-full border @error($key) border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                                <option value="">Seleccione...</option>
                                @foreach($fieldDef['options'] as $opt)
                                    <option value="{{ $opt }}" {{ old($key, $item->$key) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        @else
                            @php $currentValue = old($key, $item && is_object($item) ? $item->getAttribute($key) : data_get($item, $key, '')); @endphp
                            <input type="{{ $fieldDef['type'] }}" name="{{ $key }}" id="{{ $key }}" value="{{ e($currentValue ?? '') }}" required class="w-full border @error($key) border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        @endif
                        @error($key) <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                @endforeach

                <div class="pt-5 flex justify-end gap-3">
                    <button type="submit" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>