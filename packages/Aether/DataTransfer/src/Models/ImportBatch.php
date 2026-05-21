<?php

namespace Aether\DataTransfer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Aether\DataTransfer\Contracts\ImportBatch as ImportBatchContract;

class ImportBatch extends Model implements ImportBatchContract
{
    /**
     * Indica si el modelo debe tener una marca de tiempo.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'state',
        'data',
        'summary',
        'import_id',
    ];

    /**
     * Los atributos que se deben emitir.
     *
     * @var array
     */
    protected $casts = [
        'summary' => 'array',
        'data' => 'array',
    ];

    /**
     * Obtenga la importación propietaria del lote de importación.
     *
     * @return BelongsTo
     */
    public function import()
    {
        return $this->belongsTo(ImportProxy::modelClass());
    }
}
