<?php

namespace Aether\DataTransfer\Helpers\Sources;

use Illuminate\Support\Facades\Storage;

class CSV extends AbstractSource
{
    /**
     * Lector de archivos CSV.
     */
    protected mixed $reader;

    /**
     * Cree una nueva instancia de ayuda.
     *
     * @return void
     */
    public function __construct(
        string $filePath,
        protected string $delimiter = ','
    ) {
        try {
            $this->reader = fopen(Storage::disk('public')->path($filePath), 'r');

            $this->columnNames = fgetcsv($this->reader, 4096, $delimiter);

            $this->totalColumns = count($this->columnNames);
        } catch (\Exception $e) {
            throw new \LogicException("Unable to open file: '{$filePath}'");
        }
    }

    /**
     * Cerrar identificador de archivo.
     *
     * @return void
     */
    public function __destruct()
    {
        if (! is_object($this->reader)) {
            return;
        }

        $this->reader->close();
    }

    /**
     * Lea la siguiente línea de csv.
     */
    protected function getNextRow(): array
    {
        $parsed = fgetcsv($this->reader, 4096, $this->delimiter);

        if (is_array($parsed) && count($parsed) != $this->totalColumns) {
            foreach ($parsed as $element) {
                if ($element && strpos($element, "'") !== false) {
                    $this->foundWrongQuoteFlag = true;

                    break;
                }
            }
        } else {
            $this->foundWrongQuoteFlag = false;
        }

        return is_array($parsed) ? $parsed : [];
    }

    /**
     * Rebobina el iterador hasta la primera fila.
     */
    public function rewind(): void
    {
        rewind($this->reader);

        parent::rewind();
    }
}
