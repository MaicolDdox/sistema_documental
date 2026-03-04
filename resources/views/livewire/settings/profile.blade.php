<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Configuración de Perfil') }}</flux:heading>

    <x-settings.layout :heading="__('Perfil')" :subheading="__('Actualiza tu información personal y de contacto')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            {{-- Nombres --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:input wire:model="primer_nombre" :label="__('Primer nombre')" type="text" required autofocus autocomplete="given-name" />
                <flux:input wire:model="segundo_nombre" :label="__('Segundo nombre')" type="text" autocomplete="additional-name" />
            </div>

            {{-- Apellidos --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:input wire:model="primer_apellido" :label="__('Primer apellido')" type="text" required autocomplete="family-name" />
                <flux:input wire:model="segundo_apellido" :label="__('Segundo apellido')" type="text" />
            </div>

            {{-- Email --}}
            <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

            {{-- Contacto --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:input wire:model="telefono" :label="__('Teléfono')" type="text" autocomplete="tel" />
                <flux:input wire:model="celular" :label="__('Celular')" type="text" autocomplete="tel" />
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Guardar') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Guardado.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
