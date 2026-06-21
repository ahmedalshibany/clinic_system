<?php

namespace App\Exceptions;

use Exception;

class InvalidTransitionException extends Exception
{
    public function render()
    {
        return redirect()->back()->with('error', $this->getMessage());
    }
}
