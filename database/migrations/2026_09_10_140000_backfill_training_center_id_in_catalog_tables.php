<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogos por centro de formación (reforma de catálogos, 2026-09-10).
 *
 * El primer centro (menor id) conserva las filas originales de cada catálogo
 * (solo se les asigna training_center_id); los demás centros reciben clones
 * nuevos. Luego se repuntan las FKs de projects/minciencias_products/people/
 * project_learners/grupos_investigacion para que cada fila apunte al clon de
 * SU PROPIO centro, no al del centro base.
 *
 * down() es intencionalmente destructivo-seguro: NO intenta deshacer el
 * repunteo de FKs (no hay forma de reconstruir qué apuntaba a qué sin un
 * mapa inverso guardado aparte), y borrar los clones dispararía CASCADE
 * sobre projects/minciencias_products si algún registro real quedó
 * apuntando a un clon. Por eso down() se niega a correr — restaurar desde
 * backup si hace falta revertir esta migración.
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $tablasIndependientes = [
        'entity_positions',
        'linkage_types',
        'research_lines',
        'technological_lines',
        'thematic_areas',
        'project_modalities',
        'investigation_types',
    ];

    public function up(): void
    {
        // Los uniques globales de nombre() deben caer ANTES de clonar filas
        // por centro, o la primera clonación con un nombre repetido (ej.
        // "Administrador") viola la restricción vieja. La restricción nueva
        // (nombre + training_center_id) se agrega en la migración de
        // endurecimiento, después de que este backfill haya corrido.
        Schema::table('entity_positions', function (Blueprint $table) {
            $table->dropUnique(['nombre']);
        });
        Schema::table('linkage_types', function (Blueprint $table) {
            $table->dropUnique(['nombre']);
        });

        DB::transaction(function () {
            $centros = DB::table('training_centers')->orderBy('id')->pluck('id')->all();

            if (count($centros) === 0) {
                return;
            }

            $centroBase = $centros[0];
            $otrosCentros = array_slice($centros, 1);

            $idMap = [];

            foreach ($this->tablasIndependientes as $tabla) {
                $idMap[$tabla] = $this->clonarTabla($tabla, $centroBase, $otrosCentros);
            }

            $idMap['training_program_types'] = $this->clonarTabla('training_program_types', $centroBase, $otrosCentros);
            $idMap['training_programs'] = $this->clonarTablaHija(
                'training_programs',
                'training_program_type_id',
                $idMap['training_program_types'],
                $centroBase,
                $otrosCentros
            );

            $idMap['minciencias_typologies'] = $this->clonarTabla('minciencias_typologies', $centroBase, $otrosCentros);
            $idMap['minciencias_subcategories'] = $this->clonarTablaHija(
                'minciencias_subcategories',
                'minciencias_typology_id',
                $idMap['minciencias_typologies'],
                $centroBase,
                $otrosCentros
            );

            $this->repuntarProjects($idMap, $centroBase);
            $this->repuntarMincienciasProducts($idMap, $centroBase);
            $this->repuntarPeople($idMap, $centroBase);
            $this->repuntarProjectLearners($idMap, $centroBase);
            $this->repuntarGruposInvestigacion($idMap, $centroBase);

            $tablasCatalogo = array_merge($this->tablasIndependientes, [
                'training_program_types', 'training_programs',
                'minciencias_typologies', 'minciencias_subcategories',
            ]);

            foreach ($tablasCatalogo as $tabla) {
                $huerfanas = DB::table($tabla)->whereNull('training_center_id')->count();
                if ($huerfanas > 0) {
                    throw new \RuntimeException("Migración de datos incompleta: {$huerfanas} fila(s) de '{$tabla}' quedaron sin training_center_id.");
                }
            }
        });
    }

    public function down(): void
    {
        throw new \RuntimeException(
            'Esta migración de datos no soporta rollback automático (el repunteo de FKs '.
            'no es reversible sin un mapa inverso, y borrar los clones dispararía CASCADE '.
            'sobre projects/minciencias_products). Restaura desde backup si necesitas revertirla.'
        );
    }

    /**
     * @return array<int, array<int, int>> [id_original][centro_id] => id real para ese centro
     */
    private function clonarTabla(string $tabla, int $centroBase, array $otrosCentros): array
    {
        $filasOriginales = DB::table($tabla)->get();
        $mapa = [];

        if ($filasOriginales->isEmpty()) {
            return $mapa;
        }

        $idsOriginales = $filasOriginales->pluck('id')->all();
        DB::table($tabla)->whereIn('id', $idsOriginales)->update(['training_center_id' => $centroBase]);

        foreach ($filasOriginales as $fila) {
            $mapa[$fila->id][$centroBase] = $fila->id;

            $atributos = (array) $fila;
            unset($atributos['id']);

            foreach ($otrosCentros as $centroId) {
                $clon = $atributos;
                $clon['training_center_id'] = $centroId;
                $mapa[$fila->id][$centroId] = DB::table($tabla)->insertGetId($clon);
            }
        }

        return $mapa;
    }

    /**
     * @param  array<int, array<int, int>>  $mapaPadre
     * @return array<int, array<int, int>>
     */
    private function clonarTablaHija(string $tabla, string $fkPadre, array $mapaPadre, int $centroBase, array $otrosCentros): array
    {
        $filasOriginales = DB::table($tabla)->get();
        $mapa = [];

        if ($filasOriginales->isEmpty()) {
            return $mapa;
        }

        $idsOriginales = $filasOriginales->pluck('id')->all();
        DB::table($tabla)->whereIn('id', $idsOriginales)->update(['training_center_id' => $centroBase]);

        foreach ($filasOriginales as $fila) {
            $mapa[$fila->id][$centroBase] = $fila->id;

            $atributos = (array) $fila;
            unset($atributos['id']);
            $padreOriginalId = $fila->{$fkPadre};

            foreach ($otrosCentros as $centroId) {
                $clon = $atributos;
                $clon['training_center_id'] = $centroId;
                if ($padreOriginalId !== null && isset($mapaPadre[$padreOriginalId][$centroId])) {
                    $clon[$fkPadre] = $mapaPadre[$padreOriginalId][$centroId];
                }
                $mapa[$fila->id][$centroId] = DB::table($tabla)->insertGetId($clon);
            }
        }

        return $mapa;
    }

    private function repuntarProjects(array $idMap, int $centroBase): void
    {
        $centroPorSeedling = DB::table('seedlings')->pluck('training_center_id', 'id');

        $columnas = [
            'research_line_id' => 'research_lines',
            'technological_line_id' => 'technological_lines',
            'thematic_area_id' => 'thematic_areas',
            'project_modality_id' => 'project_modalities',
            'investigation_type_id' => 'investigation_types',
        ];

        $proyectos = DB::table('projects')->get([
            'id', 'seedling_id', 'research_line_id', 'technological_line_id',
            'thematic_area_id', 'project_modality_id', 'investigation_type_id',
        ]);

        foreach ($proyectos as $proyecto) {
            $centro = $centroPorSeedling[$proyecto->seedling_id] ?? null;
            if ($centro === null || $centro === $centroBase) {
                continue;
            }

            $updates = [];
            foreach ($columnas as $columna => $tabla) {
                $valorActual = $proyecto->$columna;
                if ($valorActual !== null && isset($idMap[$tabla][$valorActual][$centro])) {
                    $updates[$columna] = $idMap[$tabla][$valorActual][$centro];
                }
            }

            if ($updates !== []) {
                DB::table('projects')->where('id', $proyecto->id)->update($updates);
            }
        }
    }

    private function repuntarMincienciasProducts(array $idMap, int $centroBase): void
    {
        $columnas = [
            'research_line_id' => 'research_lines',
            'technological_line_id' => 'technological_lines',
            'thematic_area_id' => 'thematic_areas',
            'project_modality_id' => 'project_modalities',
            'investigation_type_id' => 'investigation_types',
        ];

        $productos = DB::table('minciencias_products')->get([
            'id', 'training_center_id', 'research_line_id', 'technological_line_id',
            'thematic_area_id', 'project_modality_id', 'investigation_type_id',
        ]);

        foreach ($productos as $producto) {
            $centro = $producto->training_center_id;
            if ($centro === null || $centro === $centroBase) {
                continue;
            }

            $updates = [];
            foreach ($columnas as $columna => $tabla) {
                $valorActual = $producto->$columna;
                if ($valorActual !== null && isset($idMap[$tabla][$valorActual][$centro])) {
                    $updates[$columna] = $idMap[$tabla][$valorActual][$centro];
                }
            }

            if ($updates !== []) {
                DB::table('minciencias_products')->where('id', $producto->id)->update($updates);
            }
        }
    }

    private function repuntarPeople(array $idMap, int $centroBase): void
    {
        $centroPorUsuario = DB::table('users')->pluck('training_center_id', 'id');

        $columnas = [
            'entity_position_id' => 'entity_positions',
            'linkage_type_id' => 'linkage_types',
            'training_program_id' => 'training_programs',
        ];

        $personas = DB::table('people')->get(['id', 'user_id', 'entity_position_id', 'linkage_type_id', 'training_program_id']);

        foreach ($personas as $persona) {
            $centro = $persona->user_id !== null ? ($centroPorUsuario[$persona->user_id] ?? null) : null;
            if ($centro === null || $centro === $centroBase) {
                continue;
            }

            $updates = [];
            foreach ($columnas as $columna => $tabla) {
                $valorActual = $persona->$columna;
                if ($valorActual !== null && isset($idMap[$tabla][$valorActual][$centro])) {
                    $updates[$columna] = $idMap[$tabla][$valorActual][$centro];
                }
            }

            if ($updates !== []) {
                DB::table('people')->where('id', $persona->id)->update($updates);
            }
        }
    }

    private function repuntarProjectLearners(array $idMap, int $centroBase): void
    {
        $centroPorSeedling = DB::table('seedlings')->pluck('training_center_id', 'id');
        $centroPorProyecto = DB::table('projects')->pluck('seedling_id', 'id')
            ->map(fn ($seedlingId) => $centroPorSeedling[$seedlingId] ?? null);

        $aprendices = DB::table('project_learners')->get(['id', 'project_id', 'training_program_id']);

        foreach ($aprendices as $aprendiz) {
            $centro = $aprendiz->project_id !== null ? ($centroPorProyecto[$aprendiz->project_id] ?? null) : null;
            if ($centro === null || $centro === $centroBase) {
                continue;
            }

            $valorActual = $aprendiz->training_program_id;
            if ($valorActual !== null && isset($idMap['training_programs'][$valorActual][$centro])) {
                DB::table('project_learners')->where('id', $aprendiz->id)
                    ->update(['training_program_id' => $idMap['training_programs'][$valorActual][$centro]]);
            }
        }
    }

    private function repuntarGruposInvestigacion(array $idMap, int $centroBase): void
    {
        $grupos = DB::table('grupos_investigacion')->get(['id', 'training_center_id', 'linea_investigacion_principal_id']);

        foreach ($grupos as $grupo) {
            $centro = $grupo->training_center_id;
            if ($centro === null || $centro === $centroBase) {
                continue;
            }

            $valorActual = $grupo->linea_investigacion_principal_id;
            if ($valorActual !== null && isset($idMap['research_lines'][$valorActual][$centro])) {
                DB::table('grupos_investigacion')->where('id', $grupo->id)
                    ->update(['linea_investigacion_principal_id' => $idMap['research_lines'][$valorActual][$centro]]);
            }
        }
    }
};
