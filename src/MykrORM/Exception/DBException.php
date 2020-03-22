<?php

/**
 * PHP Version 7
 *
 * DB Exception
 *
 * @category Exception
 * @package  Breier\MykrORM\Exception
 * @author   Andre Breier <breier.de@gmail.com>
 * @license  GPLv3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Breier\MykrORM\Exception;

use Exception;
use Throwable;

/**
 * DB Exception class
 */
class DBException extends Exception
{
    /**
     * Convert PDO Exception Codes to integer
     */
    public function __construct(
        string $message,
        $code = 0,
        ?Throwable $e = null
    ) {
        if (!is_int($code)) {
            $code = intval(substr($code, 2));
        }

        if (empty($code) && empty($e)) {
            parent::__construct($message);
            return;
        }

        parent::__construct($message, $code, $e);
    }
}
