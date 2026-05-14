<?php

namespace App\Imports;

use App\Models\Agencia;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class AgenciasImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    public int $importadas = 0;
    public int $omitidasExistentes = 0;
    public int $omitidasDuplicadasArchivo = 0;
    public int $omitidasSinTerminal = 0;

    private array $terminalesExistentes = [];
    private array $terminalesArchivo = [];

    public function __construct()
    {
        $this->terminalesExistentes = Agencia::query()
            ->whereNotNull('terminal')
            ->pluck('terminal')
            ->map(fn($terminal) => $this->normalizarTerminal((string) $terminal))
            ->filter(fn($terminal) => $terminal !== '0')
            ->unique()
            ->flip()
            ->all();
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $terminal = trim((string) ($this->valorColumna($row, ['terminal']) ?? ''));
        $terminalKey = $this->normalizarTerminal($terminal);

        if ($terminalKey === '0') {
            $this->omitidasSinTerminal++;
            return null;
        }

        if (isset($this->terminalesExistentes[$terminalKey])) {
            $this->omitidasExistentes++;
            return null;
        }

        if (isset($this->terminalesArchivo[$terminalKey])) {
            $this->omitidasDuplicadasArchivo++;
            return null;
        }

        $this->terminalesArchivo[$terminalKey] = true;
        $this->terminalesExistentes[$terminalKey] = true;
        $this->importadas++;

        return new Agencia([
            'agencia' => $this->valorTexto($row, ['agencia']),
            'terminal' => $terminal,
            'horario_am' => $this->valorTexto($row, ['horario_am', 'horario am']),
            'horario_pm' => $this->valorTexto($row, ['horario_pm', 'horario pm']),
            'nombre_agencia' => $this->valorTexto($row, ['nombre_agencia', 'nombre agencia']),
            'sistema' => $this->valorTexto($row, ['sistema']),
            'empresa' => $this->valorTexto($row, ['empresa']),
            'ciudad' => $this->valorTexto($row, ['ciudad']),
            'ruta' => $this->valorTexto($row, ['ruta']),
            'operador' => $this->valorTexto($row, ['operador']),
            'coordinador' => $this->valorTexto($row, ['coordinador']),
            'estatus' => $this->parseEstatus($this->valorColumna($row, ['estatus']) ?? 1),
            'aplica_incentivo' => $this->parseAplicaIncentivo($this->valorColumna($row, ['aplica_incentivo', 'aplica incentivo']) ?? 1),
        ]);
    }

    public function rules(): array
    {
        return [
            'agencia' => 'required',
            'terminal' => 'nullable',
            'horario_am' => 'nullable',
            'horario_pm' => 'nullable',
            'nombre_agencia' => 'nullable',
            'sistema' => 'nullable',
            'empresa' => 'nullable',
            'ciudad' => 'nullable',
            'ruta' => 'nullable',
            'operador' => 'nullable',
            'coordinador' => 'nullable',
            'estatus' => 'nullable',
            'aplica_incentivo' => 'nullable',
        ];
    }

    public function totalOmitidas(): int
    {
        return $this->omitidasExistentes + $this->omitidasDuplicadasArchivo + $this->omitidasSinTerminal;
    }

    private function valorTexto(array $row, array $aliases): ?string
    {
        $valor = $this->valorColumna($row, $aliases);
        if ($valor === null) {
            return null;
        }

        $texto = trim((string) $valor);
        return $texto === '' ? null : $texto;
    }

    private function valorColumna(array $row, array $aliases): mixed
    {
        foreach ($aliases as $alias) {
            $clave = strtolower(trim((string) $alias));
            $claveConGuionBajo = str_replace(' ', '_', $clave);

            if (array_key_exists($clave, $row)) {
                return $row[$clave];
            }

            if (array_key_exists($claveConGuionBajo, $row)) {
                return $row[$claveConGuionBajo];
            }
        }

        return null;
    }

    private function normalizarTerminal(?string $terminal): string
    {
        if (!$terminal) {
            return '0';
        }

        $valor = ltrim(trim($terminal), '0');
        return $valor === '' ? '0' : $valor;
    }

    private function parseEstatus($value): int
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        $normalized = strtoupper(trim((string) $value));
        if ($normalized === '1' || $normalized === 'ACTIVO' || $normalized === 'ACTIVE' || $normalized === 'SI' || $normalized === 'S') {
            return 1;
        }

        return 0;
    }

    private function parseAplicaIncentivo($value): int
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        $normalized = strtoupper(trim((string) $value));
        if ($normalized === 'SI' || $normalized === 'S' || $normalized === 'YES' || $normalized === 'Y' || $normalized === '1') {
            return 1;
        }

        return 0;
    }
}
