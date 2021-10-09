<?php

namespace App\Exceptions;

class NotEnoughBalanceException extends \Exception
{
    protected $message = 'Not Enough Balance';
}