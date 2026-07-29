<?php

namespace App\Exceptions;

use App\Models\Coleta;
use Exception;

class DuplicateColetaException extends Exception
{
    public Coleta $coleta;

    public function __construct(Coleta $coleta)
    {
        $this->coleta = $coleta;
        parent::__construct('Já existe uma coleta com este EAN, área, loja e validade.');
    }
}
