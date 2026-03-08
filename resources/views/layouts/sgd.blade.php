{{-- Layout unificado para todos los roles (admin usa x-app-layout directo; director y líder extienden este) --}}
<x-app-layout>
    <x-slot name="header">@yield('header', 'Panel')</x-slot>
    @if(session('success'))
    <div class="mb-6 bg-green-50 border-l-4 border-[#39A900] p-4 rounded-r-lg"><p class="text-sm text-green-700">{{ session('success') }}</p></div>
    @endif
    @if(session('error'))
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg"><p class="text-sm text-red-700">{{ session('error') }}</p></div>
    @endif
    @if(session('warning'))
    <div class="mb-6 bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg"><p class="text-sm text-amber-700">{{ session('warning') }}</p></div>
    @endif
    @yield('content')
</x-app-layout>
