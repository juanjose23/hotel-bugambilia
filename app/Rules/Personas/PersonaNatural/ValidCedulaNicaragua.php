<?php

namespace App\Rules\Personas\PersonaNatural;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCedulaNicaragua implements ValidationRule
{
    protected string $letras = 'ABCDEFGHJKLMNPQRSTUVWXY';

    /** @var array<int, string> */
    protected array $municipios = [
        '001','002','003','004','005','006','007','008','009','010',
        '011','012','013','014','015','016','017','018','019','020',
        '021','022','023','024','025','026','027','028','029','030',
        '031','032','033','034','035','036','037','038','039','040',
        '041','042','043','044','045','046','047','048','049','050',
        '051','052','053','054','055','056','057','058','059','060',
        '061','062','063','064','065','066','067','068','069','070',
        '071','072','073','074','075','076','077','078','079','080',
        '081','082','083','084','085','086','087','088','089','090',
        '091','092','093','094','095','096','097','098','099','100'
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = strtoupper(trim($value));

        if (!$this->validateFormat($value)) {
            $fail('El formato de la cédula no es válido. Ejemplo: 001-010102-1234A');
            return;
        }

        $cedula = str_replace('-', '', $value);

        if (!$this->validateMunicipio($cedula)) {
            $fail('El código de municipio de la cédula no es válido.');
            return;
        }

        if (!$this->validateDate($cedula)) {
            $fail('La fecha de nacimiento en la cédula no es válida.');
            return;
        }

        if (!$this->validateLetter($cedula)) {
            $fail('La letra de verificación de la cédula no es correcta.');
        }
    }

    protected function validateFormat(string $cedula): bool
    {
        return preg_match('/^\d{3}-?\d{6}-?\d{4}[A-Z]$/', $cedula) === 1;
    }

    protected function validateMunicipio(string $cedula): bool
    {
        $municipio = substr($cedula, 0, 3);

        return in_array($municipio, $this->municipios);
    }

    protected function validateDate(string $cedula): bool
    {
        $day = substr($cedula, 3, 2);
        $month = substr($cedula, 5, 2);
        $year = substr($cedula, 7, 2);

        $fullYear = intval($year) > 30 ? '19'.$year : '20'.$year;

        return checkdate(intval($month), intval($day), intval($fullYear));
    }

    protected function validateLetter(string $cedula): bool
    {
        $numero = intval(substr($cedula, 0, 13));
        $letraEsperada = substr($cedula, 13, 1);

        $posicion = $numero % 23;
        $letraCalculada = $this->letras[$posicion];

        return $letraEsperada === $letraCalculada;
    }
}