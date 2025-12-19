<?php

namespace App\Enum;

/**
 * Enums for log service.
 */
enum LogType: string
{
    case ERROR = 'error';
    case DEBUG = 'debug';
    case INFO = 'info';
}
