<?php

$schema = json_decode('{
    "Department": ["nombre"],
    "City": ["department_id", "nombre"],
    "TrainingCenter": ["nombre", "codigo", "department_id", "city_id"],
    "EntityPosition": ["nombre", "descripccion"],
    "LinkageType": ["nombre", "descripccion"],
    "TrainingRecord": ["codigo", "descripccion"],
    "TrainingProgramType": ["nombre", "descripccion"],
    "TrainingProgram": ["training_record_id", "training_program_type_id", "nombre", "descripccion", "jornada", "modalidad", "estado"],
    "ResearchLine": ["nombre", "descripccion"],
    "TechnologicalLine": ["nombre", "descripccion"],
    "ThematicArea": ["nombre", "descripccion"],
    "ProjectModality": ["nombre", "descripccion"],
    "InvestigationType": ["nombre", "descripccion"],
    "MincienciasTypology": ["nombre", "codigo", "descripccion"],
    "KnowledgeGrandArea": ["nombre", "descripccion"],
    "KnowledgeArea": ["knowledge_grand_area_id", "nombre", "descripccion"]
}', true);

$config = [
    'Department' => ['route' => 'departments', 'title' => 'Departamentos'],
    'City' => ['route' => 'cities', 'title' => 'Municipios', 'rels' => ['department_id' => 'Department']],
    'TrainingCenter' => ['route' => 'training-centers', 'title' => 'Centros de Formación', 'rels' => ['department_id' => 'Department', 'city_id' => 'City']],
    'EntityPosition' => ['route' => 'entity-positions', 'title' => 'Cargos de Entidades'],
    'LinkageType' => ['route' => 'linkage-types', 'title' => 'Tipos de Vinculación'],
    'TrainingRecord' => ['route' => 'training-records', 'title' => 'Fichas de Formación'],
    'TrainingProgramType' => ['route' => 'training-program-types', 'title' => 'Tipos de Programas'],
    'TrainingProgram' => ['route' => 'training-programs', 'title' => 'Programas de Formación', 'rels' => ['training_record_id' => 'TrainingRecord', 'training_program_type_id' => 'TrainingProgramType'], 'enums' => ['jornada' => ['DIURNA', 'NOCTURNA', 'MIXTA', 'VIRTUAL'], 'modalidad' => ['PRESENCIAL', 'VIRTUAL', 'A DISTANCIA'], 'estado' => ['activo', 'inactivo']]],
    'ResearchLine' => ['route' => 'research-lines', 'title' => 'Líneas de Investigación'],
    'TechnologicalLine' => ['route' => 'technological-lines', 'title' => 'Líneas Tecnológicas'],
    'ThematicArea' => ['route' => 'thematic-areas', 'title' => 'Áreas Temáticas'],
    'ProjectModality' => ['route' => 'project-modalities', 'title' => 'Modalidades de Proyectos'],
    'InvestigationType' => ['route' => 'investigation-types', 'title' => 'Tipos de Investigadores'],
    'MincienciasTypology' => ['route' => 'minciencias-typologies', 'title' => 'Tipologías Minciencias'],
    'KnowledgeGrandArea' => ['route' => 'knowledge-grand-areas', 'title' => 'Grandes Áreas de Conocimiento'],
    'KnowledgeArea' => ['route' => 'knowledge-areas', 'title' => 'Áreas de Conocimiento', 'rels' => ['knowledge_grand_area_id' => 'KnowledgeGrandArea']],
];

@mkdir(__DIR__ . '/resources/views/admin/parametric', 0777, true);

// GENERATE GENERIC BLADE VIEWS

// INDEX
$indexView = <<<HTML
<x-app-layout>
    <x-slot name="header">{{ \$title }}</x-slot>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">{{ \$title }}</h2>
            <p class="text-sm text-slate-500 mt-1">Gestión de registros del sistema.</p>
        </div>
        <a href="{{ route(\$routePrefix.'.create') }}" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nuevo Registro
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-slate-50/50 border-b border-slate-100 text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">ID</th>
                        @foreach(array_keys(\$fields) as \$field)
                            <th class="px-5 py-3 font-medium">{{ ucfirst(str_replace('_id', '', \$field)) }}</th>
                        @endforeach
                        <th class="px-5 py-3 font-medium text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse(\$items as \$item)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-3 font-medium">{{ \$item->id }}</td>
                        @foreach(\$fields as \$key => \$fieldDef)
                            <td class="px-5 py-3">
                                @if(\$fieldDef['type'] == 'relation')
                                    {{ \$item->\$key ? (\$item->{str_replace('_id', '', \$key)}->nombre ?? \$item->{str_replace('_id', '', \$key)}->codigo ?? \$item->\$key) : 'N/A' }}
                                @else
                                    {{ \$item->\$key }}
                                @endif
                            </td>
                        @endforeach
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route(\$routePrefix.'.edit', \$item->id) }}" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.89 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.89l10.65-10.65zM16.862 4.487L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                </a>
                                <form action="{{ route(\$routePrefix.'.destroy', \$item->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este registro?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Eliminar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-5 py-8 text-center text-slate-500">No hay registros disponibles.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(\$items->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">
            {{ \$items->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
HTML;

file_put_contents(__DIR__ . '/resources/views/admin/parametric/index.blade.php', $indexView);

// CREATE VIEW
$createView = <<<HTML
<x-app-layout>
    <x-slot name="header">{{ \$title }}</x-slot>
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-slate-900">{{ \$title }}</h2>
        <a href="{{ route(\$routePrefix.'.index') }}" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">Volver</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden max-w-3xl">
        <div class="p-5">
            <form method="POST" action="{{ route(\$routePrefix.'.store') }}" class="space-y-5">
                @csrf
                @foreach(\$fields as \$key => \$fieldDef)
                    <div>
                        <label for="{{ \$key }}" class="block text-sm font-medium text-slate-700 mb-1.5">{{ ucfirst(str_replace('_id', '', \$key)) }} <span class="text-red-500">*</span></label>
                        @if(\$fieldDef['type'] == 'relation')
                            <select name="{{ \$key }}" id="{{ \$key }}" required class="w-full border @error(\$key) border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                                <option value="">Seleccione...</option>
                                @foreach(\$fieldDef['options'] as \$opt)
                                    <option value="{{ \$opt->id }}" {{ old(\$key) == \$opt->id ? 'selected' : '' }}>{{ \$opt->nombre ?? \$opt->codigo ?? \$opt->id }}</option>
                                @endforeach
                            </select>
                        @elseif(\$fieldDef['type'] == 'enum')
                            <select name="{{ \$key }}" id="{{ \$key }}" required class="w-full border @error(\$key) border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                                <option value="">Seleccione...</option>
                                @foreach(\$fieldDef['options'] as \$opt)
                                    <option value="{{ \$opt }}" {{ old(\$key) == \$opt ? 'selected' : '' }}>{{ \$opt }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="{{ \$fieldDef['type'] }}" name="{{ \$key }}" id="{{ \$key }}" value="{{ old(\$key) }}" required class="w-full border @error(\$key) border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        @endif
                        @error(\$key) <p class="text-red-500 text-xs mt-1">{{ \$message }}</p> @enderror
                    </div>
                @endforeach

                <div class="pt-5 flex justify-end gap-3">
                    <button type="submit" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
HTML;

file_put_contents(__DIR__ . '/resources/views/admin/parametric/create.blade.php', $createView);

// EDIT VIEW
$editView = <<<HTML
<x-app-layout>
    <x-slot name="header">{{ \$title }}</x-slot>
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-slate-900">{{ \$title }}</h2>
        <a href="{{ route(\$routePrefix.'.index') }}" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">Volver</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden max-w-3xl">
        <div class="p-5">
            <form method="POST" action="{{ route(\$routePrefix.'.update', \$item->id) }}" class="space-y-5">
                @csrf
                @method('PUT')
                @foreach(\$fields as \$key => \$fieldDef)
                    <div>
                        <label for="{{ \$key }}" class="block text-sm font-medium text-slate-700 mb-1.5">{{ ucfirst(str_replace('_id', '', \$key)) }} <span class="text-red-500">*</span></label>
                        @if(\$fieldDef['type'] == 'relation')
                            <select name="{{ \$key }}" id="{{ \$key }}" required class="w-full border @error(\$key) border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                                <option value="">Seleccione...</option>
                                @foreach(\$fieldDef['options'] as \$opt)
                                    <option value="{{ \$opt->id }}" {{ old(\$key, \$item->\$key) == \$opt->id ? 'selected' : '' }}>{{ \$opt->nombre ?? \$opt->codigo ?? \$opt->id }}</option>
                                @endforeach
                            </select>
                        @elseif(\$fieldDef['type'] == 'enum')
                            <select name="{{ \$key }}" id="{{ \$key }}" required class="w-full border @error(\$key) border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                                <option value="">Seleccione...</option>
                                @foreach(\$fieldDef['options'] as \$opt)
                                    <option value="{{ \$opt }}" {{ old(\$key, \$item->\$key) == \$opt ? 'selected' : '' }}>{{ \$opt }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="{{ \$fieldDef['type'] }}" name="{{ \$key }}" id="{{ \$key }}" value="{{ old(\$key, \$item->\$key) }}" required class="w-full border @error(\$key) border-red-500 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                        @endif
                        @error(\$key) <p class="text-red-500 text-xs mt-1">{{ \$message }}</p> @enderror
                    </div>
                @endforeach

                <div class="pt-5 flex justify-end gap-3">
                    <button type="submit" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
HTML;

file_put_contents(__DIR__ . '/resources/views/admin/parametric/edit.blade.php', $editView);

// GENERATE CONTROLLERS
$basePath = __DIR__ . '/app/Http/Controllers/Web/';

foreach ($schema as $model => $fillable) {
    if (!isset($config[$model])) continue;

    $c = $config[$model];
    $className = $model . 'Controller';
    $route = $c['route'];
    $title = $c['title'];
    $varName = strtolower($model);
    
    // Check if Store/Update Requests exist
    $storeReq = file_exists(__DIR__ . "/app/Http/Requests/Store{$model}Request.php") ? "Store{$model}Request" : 'Request';
    $updateReq = file_exists(__DIR__ . "/app/Http/Requests/Update{$model}Request.php") ? "Update{$model}Request" : 'Request';
    
    $useStore = $storeReq !== 'Request' ? "use App\Http\Requests\\{$storeReq};" : "";
    $useUpdate = $updateReq !== 'Request' && $updateReq !== $storeReq ? "use App\Http\Requests\\{$updateReq};" : "";

    $fieldsExport = "";
    foreach($fillable as $f) {
        $type = 'text';
        $optionsStr = '[]';
        if (isset($c['rels'][$f])) {
            $type = 'relation';
            $relModel = $c['rels'][$f];
            $optionsStr = "\\App\\Models\\{$relModel}::all()";
        } elseif (isset($c['enums'][$f])) {
            $type = 'enum';
            $enumsStr = "['" . implode("','", $c['enums'][$f]) . "']";
            $optionsStr = $enumsStr;
        }
        $fieldsExport .= "            '{$f}' => ['type' => '{$type}', 'options' => {$optionsStr}],\n";
    }

    $stub = <<<PHP
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\\{$model};
use Illuminate\Http\Request;
{$useStore}
{$useUpdate}
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class {$className} extends Controller
{
    private function getFields() {
        return [
{$fieldsExport}
        ];
    }

    public function index(): View
    {
        \$items = {$model}::paginate(10);
        return view('admin.parametric.index', [
            'items' => \$items,
            'title' => '{$title}',
            'routePrefix' => 'admin.{$route}',
            'fields' => \$this->getFields()
        ]);
    }

    public function create(): View
    {
        return view('admin.parametric.create', [
            'title' => 'Crear {$title}',
            'routePrefix' => 'admin.{$route}',
            'fields' => \$this->getFields()
        ]);
    }

    public function store({$storeReq} \$request): RedirectResponse
    {
        \$validated = \$request->validate(\$this->getValidationRules());
        // Custom request logic applies if \$request is not just a base Request.
        if (method_exists(\$request, 'validated') && '{$storeReq}' !== 'Request') {
             \$validated = \$request->validated();
        }
        
        {$model}::create(\$validated);
        
        return redirect()->route('admin.{$route}.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    public function edit({$model} \${$varName}): View
    {
        return view('admin.parametric.edit', [
            'item' => \${$varName},
            'title' => 'Editar {$title}',
            'routePrefix' => 'admin.{$route}',
            'fields' => \$this->getFields()
        ]);
    }

    public function update({$updateReq} \$request, {$model} \${$varName}): RedirectResponse
    {
        \$validated = \$request->validate(\$this->getValidationRules());
        if (method_exists(\$request, 'validated') && '{$updateReq}' !== 'Request') {
             \$validated = \$request->validated();
        }
        
        \${$varName}->update(\$validated);
        
        return redirect()->route('admin.{$route}.index')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    public function destroy({$model} \${$varName}): RedirectResponse
    {
        try {
            \${$varName}->delete();
            return redirect()->route('admin.{$route}.index')
                ->with('success', 'Registro eliminado exitosamente.');
        } catch (\Illuminate\Database\QueryException \$e) {
            return redirect()->route('admin.{$route}.index')
                ->with('error', 'No se puede eliminar porque está asociado a otros registros.');
        }
    }
    
    protected function getValidationRules(): array
    {
        \$rules = [];
        foreach(array_keys(\$this->getFields()) as \$f) {
             \$rules[\$f] = 'required';
        }
        return \$rules;
    }
    
    // Note: Laravel resolves model bindings. 
    // Ensure the parameter name \${$varName} matches route.
}
PHP;

    file_put_contents($basePath . $className . '.php', $stub);
    echo "Generated {$className}\n";
}

echo "MVC Scaffold completed successfully.\n";
