<?php

namespace Aether\DataTransfer\Helpers\Sources;

use Aether\DataTransfer\Helpers\Importers\AbstractImporter;

abstract class AbstractSource
{
    /**
     * Nombres de columnas.
     */
    protected array $columnNames = [];

    /**
     * Cantidad de columnas.
     */
    protected int $totalColumns = 0;

    /**
     * Fila actual.
     */
    protected array $currentRowData = [];

    /**
     * Número de fila actual.
     */
    protected int $currentRowNumber = -1;

    /**
     * Bandera para indicar que se encontró una cotización incorrecta.
     */
    protected bool $foundWrongQuoteFlag = false;

    /**
     * Lea la siguiente línea de la fuente.
     */
    abstract protected function getNextRow(): array|bool;

    /**
     * Devuelve la clave de la fila actual.
     */
    public function getCurrentRowNumber(): int
    {
        return $this->currentRowNumber;
    }

    /**
     * Comprueba si la posición actual es válida.
     */
    public function valid(): bool
    {
        return $this->currentRowNumber !== -1;
    }

    /**
     * Lea la siguiente línea de la fuente.
     */
    public function current(): array
    {
        $row = $this->currentRowData;

        if (count($row) != $this->totalColumns) {
            if ($this->foundWrongQuoteFlag) {
                throw new \InvalidArgumentException(AbstractImporter::ERROR_CODE_WRONG_QUOTES);
            } else {
                throw new \InvalidArgumentException(AbstractImporter::ERROR_CODE_COLUMNS_NUMBER);
            }
        }

        return array_combine($this->columnNames, $row);
    }

    /**
     * Lea la siguiente línea de la fuente.
     */
    public function next(): void
    {
        $this->currentRowNumber++;

        $row = $this->getNextRow();

        if ($row === false || $row === []) {
            $this->currentRowData = [];

            $this->currentRowNumber = -1;
        } else {
            $this->currentRowData = $row;
        }
    }

    /**
     * Rebobina el iterador hasta la primera fila.
     */
    public function rewind(): void
    {
        $this->currentRowNumber = 0;

        $this->currentRowData = [];

        $this->getNextRow();

        $this->next();
    }

    /**
     * Captador de nombres de columnas.
     */
    public function getColumnNames(): array
    {
        return $this->columnNames;
    }

    /**
     * Captador de nombres de columnas.
     */
    public function getTotalColumns(): int
    {
        return count($this->columnNames);
    }
}
