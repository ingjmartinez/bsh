<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ServicioGeneralVisitaRespuesta extends Model
{
    protected $table = 'servicios_generales_visita_respuestas';
    protected $guarded = [];
    public function item(){ return $this->belongsTo(ServicioGeneralChecklistItem::class, 'checklist_item_id'); }
}
