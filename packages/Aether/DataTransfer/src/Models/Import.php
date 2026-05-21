<?php

namespace Aether\DataTransfer\Models;

use Aether\DataTransfer\Contracts\Import as ImportContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Import extends Model implements ImportContract
{
    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array
     */
    protected $fillable = [
        'state',
        'process_in_queue',
        'type',
        'action',
        'validation_strategy',
        'validation_strategy',
        'allowed_errors',
        'processed_rows_count',
        'invalid_rows_count',
        'errors_count',
        'errors',
        'field_separator',
        'file_path',
        'error_file_path',
        'summary',
        'started_at',
        'completed_at',
    ];

    /**
     * Los atributos que se deben emitir.
     *
     * @var array
     */
    protected $casts = [
        'summary' => 'array',
        'errors' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Obtenga las opciones.
     */
    public function batches(): HasMany
    {
        return $this->hasMany(ImportBatchProxy::modelClass());
    }

    /**
     * Obtenga el nombre del archivo.
     */
    public function getFileNameAttribute(): string
    {
        return preg_replace('/^.*?\/\d+-/', '', $this->file_path);
    }
}
