{{-- Layout unificado para todos los roles (admin usa x-app-layout directo; director y líder extienden este) --}}
<x-app-layout>
    <x-slot name="header">@yield('header', 'Panel')</x-slot>
    @yield('content')
</x-app-layout>
