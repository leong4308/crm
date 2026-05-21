<?php

namespace Aether\DataTransfer\Helpers;

class Error
{
    /**
     * Elementos de error.
     */
    protected array $items = [];

    /**
     * Filas no válidas.
     */
    protected array $invalidRows = [];

    /**
     * Filas saltadas.
     */
    protected array $skippedRows = [];

    /**
     * Los errores cuentan.
     */
    protected int $errorsCount = 0;

    /**
     * Plantilla de mensaje de error.
     */
    protected array $messageTemplate = [];

    /**
     * Agregar plantilla de mensaje de error.
     */
    public function addErrorMessage(string $code, string $template): self
    {
        $this->messageTemplate[$code] = $template;

        return $this;
    }

    /**
     * Agregar mensaje de error.
     */
    public function addError(string $code, ?int $rowNumber = null, ?string $columnName = null, ?string $message = null): self
    {
        if ($this->isErrorAlreadyAdded($rowNumber, $code, $columnName)) {
            return $this;
        }

        $this->addRowToInvalid($rowNumber);

        $message = $this->getErrorMessage($code, $message, $columnName);

        $this->items[$rowNumber][] = [
            'code' => $code,
            'column' => $columnName,
            'message' => $message,
        ];

        $this->errorsCount++;

        return $this;
    }

    /**
     * Compruebe si ya se agregó el error para la fila, el código y la columna.
     */
    public function isErrorAlreadyAdded(?int $rowNumber, string $code, ?string $columnName): bool
    {
        return collect($this->items[$rowNumber] ?? [])
            ->where('code', $code)
            ->where('column', $columnName)
            ->isNotEmpty();
    }

    /**
     * Agregue una fila específica a una lista no válida mediante el número de fila.
     */
    protected function addRowToInvalid(?int $rowNumber): self
    {
        if (is_null($rowNumber)) {
            return $this;
        }

        if (! in_array($rowNumber, $this->invalidRows)) {
            $this->invalidRows[] = $rowNumber;
        }

        return $this;
    }

    /**
     * Agregue una fila específica a una lista no válida mediante el número de fila.
     */
    public function addRowToSkip(?int $rowNumber): self
    {
        if (is_null($rowNumber)) {
            return $this;
        }

        if (! in_array($rowNumber, $this->skippedRows)) {
            $this->skippedRows[] = $rowNumber;
        }

        return $this;
    }

    /**
     * Compruebe si la fila no es válida por número de fila.
     */
    public function isRowInvalid(int $rowNumber): bool
    {
        return in_array($rowNumber, array_merge($this->invalidRows, $this->skippedRows));
    }

    /**
     * Cree un mensaje de error mediante código, mensaje y nombre de columna.
     */
    protected function getErrorMessage(?string $code, ?string $message, ?string $columnName): string
    {
        if (
            empty($message)
            && isset($this->messageTemplate[$code])
        ) {
            $message = (string) $this->messageTemplate[$code];
        }

        if (
            $columnName
            && $message
        ) {
            $message = sprintf($message, $columnName);
        }

        if (! $message) {
            $message = $code;
        }

        return $message;
    }

    /**
     * Obtenga el número de filas no válidas.
     */
    public function getInvalidRowsCount(): int
    {
        return count($this->invalidRows);
    }

    /**
     * Obtenga el recuento de errores actual.
     */
    public function getErrorsCount(): int
    {
        return $this->errorsCount;
    }

    /**
     * Obtenga todos los errores de un proceso de importación.
     */
    public function getAllErrors(): array
    {
        return $this->items;
    }

    /**
     * Devuelve todos los errores agrupados por código.
     */
    public function getAllErrorsGroupedByCode(): array
    {
        $errors = [];

        foreach ($this->items as $rowNumber => $rowErrors) {
            foreach ($rowErrors as $error) {
                if ($rowNumber === '') {
                    $errors[$error['code']][$error['message']] = null;
                } else {
                    $errors[$error['code']][$error['message']][] = $rowNumber;
                }
            }
        }

        return $errors;
    }
}
