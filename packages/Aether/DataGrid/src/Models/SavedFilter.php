<?php

namespace Aether\DataGrid\Models;

use Aether\DataGrid\Contracts\SavedFilter as SavedFilterContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedFilter extends Model implements SavedFilterContract
{
    use HasFactory;

    /**
     * Definir el nombre de la tabla del modelo.
     *
     * @var string
     */
    protected $table = 'datagrid_saved_filters';

    /**
     * Propiedad rellenable para el modelo.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'src',
        'name',
        'applied',
    ];

    /**
     * Los atributos que se deben emitir.
     *
     * @var array
     */
    protected $casts = [
        'applied' => 'json',
    ];
}
