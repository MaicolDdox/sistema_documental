<?php

namespace App\Models;

use App\Enums\EstadoRevisionEnum;
use App\Enums\TipoProyectoOrigenEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupProduct extends Model
{
    use HasFactory;

    protected $table = 'group_products';

    protected $fillable = [
        'author_id',
        'product_id',
        'tipo_proyecto_origen',
        'campo_otro',
        'codigo_proyecto_origen',
        'titulo',
        'descripcion',
        'anio_publicacion',
        'nombre_programa_formacion_impacto',
        'minciencias_typology_id',
        'minciencias_subcategory_id',
        'knowledge_grand_area_id',
        'knowledge_area_id',
        'tiene_repositorio',
        'url_repositorio',
        'evidencia',
        'autoriza_datos',
        'estado_revision',
        'observaciones_revision',
    ];

    protected $casts = [
        'tipo_proyecto_origen' => TipoProyectoOrigenEnum::class,
        'estado_revision' => EstadoRevisionEnum::class,
        'tiene_repositorio' => 'boolean',
        'autoriza_datos' => 'boolean',
        'anio_publicacion' => 'integer',
    ];

    // ─────────────────────────────────────────────
    // RELACIONES
    // ─────────────────────────────────────────────

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GroupProductReview::class, 'group_product_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function mincienciasTypology(): BelongsTo
    {
        return $this->belongsTo(MincienciasTypology::class, 'minciencias_typology_id');
    }

    public function mincienciasSubcategory(): BelongsTo
    {
        return $this->belongsTo(MincienciasSubcategory::class, 'minciencias_subcategory_id');
    }

    public function knowledgeGrandArea(): BelongsTo
    {
        return $this->belongsTo(KnowledgeGrandArea::class, 'knowledge_grand_area_id');
    }

    public function knowledgeArea(): BelongsTo
    {
        return $this->belongsTo(KnowledgeArea::class, 'knowledge_area_id');
    }

    // ─────────────────────────────────────────────
    // OBSERVER / LIFECYCLE
    // ─────────────────────────────────────────────

    /**
     * Cuando se actualiza el estado_revision de este GroupProduct,
     * sincroniza automáticamente el mismo valor en el Product base.
     * Esto mantiene consistencia entre el flujo del Líder (products.estado_revision)
     * y el flujo del Director (group_products.estado_revision) sin sync manual.
     */
    protected static function booted(): void
    {
        static::updated(function (self $gp) {
            if ($gp->wasChanged('estado_revision') && $gp->product_id) {
                // Usamos updateQuietly para evitar disparar de nuevo el observer
                Product::where('id', $gp->product_id)->update([
                    'estado_revision'      => $gp->estado_revision,
                    'observacion_revision' => $gp->observaciones_revision,
                ]);
            }
        });
    }
}
