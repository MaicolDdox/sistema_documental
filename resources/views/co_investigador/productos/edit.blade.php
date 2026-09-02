@extends('layouts.sgd')

@section('title', 'Editar Producto Minciencias')
@section('header', '')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Editar Producto Minciencias</h1>
    <p class="text-sm text-slate-500 mt-0.5">{{ $producto->nombre }}</p>
</div>

<div class="sgd-card bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <form action="{{ route('co-investigador.productos.update', $producto) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('co_investigador.productos._form')

        <div class="flex items-center gap-3">
            <button type="submit" class="sgd-btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium">
                Guardar cambios
            </button>
            <a href="{{ route('co-investigador.productos.show', $producto) }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">Cancelar</a>
        </div>
    </form>
</div>
@endsection
