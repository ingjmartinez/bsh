<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ServicioGeneralChecklistItem extends Model
{
    protected $table = 'servicios_generales_checklist_items';
    protected $guarded = [];
    protected $casts = ['requerido'=>'boolean','requiere_evidencia_fallo'=>'boolean','activo'=>'boolean'];
}
