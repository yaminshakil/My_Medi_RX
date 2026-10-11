<?php

namespace App\Modules\BloodBank\Exceptions;

use RuntimeException;

/** Expected business-rule failures. Controllers turn the message into a flash error. */
class BloodBankException extends RuntimeException
{
}
