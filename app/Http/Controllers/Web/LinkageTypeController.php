<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\LinkageType;
use Illuminate\Http\Request;
use App\Http\Requests\StoreLinkageTypeRequest;
use App\Http\Requests\UpdateLinkageTypeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LinkageTypeController extends Controller
{
    private function getFields() {
        return [
            'nombre' => ['type' => 'text', 'options' => []],
            'descripcion' => ['type' => 'text', 'options' => []],

        ];
    }

    public function index(): View
    {
        $items = LinkageType::paginate(10);
        return view('admin.parametric.index', [
            'items' => $items,
            'title' => 'Tipos de Vinculación',
            'routePrefix' => 'admin.linkage-types',
            'fields' => $this->getFields()
        ]);
    }

    public function create(): View
    {
        return view('admin.parametric.create', [
            'title' => 'Crear Tipos de Vinculación',
            'routePrefix' => 'admin.linkage-types',
            'fields' => $this->getFields()
        ]);
    }

    public function store(StoreLinkageTypeRequest $request): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        // Custom request logic applies if $request is not just a base Request.
        if (method_exists($request, 'validated') && 'StoreLinkageTypeRequest' !== 'Request') {
             $validated = $request->validated();
        }
        
        LinkageType::create($validated);

        if ($request->input('_from_simples')) {
            return redirect()->route('admin.catalogos.simples')->with('success', 'Tipo de vinculación creado correctamente.');
        }
        return redirect()->route('admin.linkage-types.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    public function edit(LinkageType $linkagetype): View
    {
        return view('admin.parametric.edit', [
            'item' => $linkagetype,
            'title' => 'Editar Tipos de Vinculación',
            'routePrefix' => 'admin.linkage-types',
            'fields' => $this->getFields()
        ]);
    }

    public function update(UpdateLinkageTypeRequest $request, LinkageType $linkagetype): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        if (method_exists($request, 'validated') && 'UpdateLinkageTypeRequest' !== 'Request') {
             $validated = $request->validated();
        }
        
        $linkagetype->update($validated);

        if ($request->has('_from_simples')) {
            return redirect()->route('admin.catalogos.simples')
                ->with('success', 'Registro actualizado exitosamente.');
        }
        return redirect()->route('admin.linkage-types.index')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    public function destroy(LinkageType $linkagetype): RedirectResponse
    {
        try {
            $linkagetype->delete();
            return redirect()->route('admin.linkage-types.index')
                ->with('success', 'Registro eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.linkage-types.index')
                ->with('error', 'No se puede eliminar porque está asociado a otros registros.');
        }
    }
    
    protected function getValidationRules(): array
    {
        $rules = [];
        foreach(array_keys($this->getFields()) as $f) {
             $rules[$f] = 'required';
        }
        return $rules;
    }
    
    // Note: Laravel resolves model bindings. 
    // Ensure the parameter name $linkagetype matches route.
}