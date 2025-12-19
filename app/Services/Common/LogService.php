<?php

namespace App\Services\Common;

use App\Enum\LogType;
use Illuminate\Support\Facades\Log;

class LogService
{
    /**
     * Log an info message.
     *
     * @param string $title The title of the log
     * @param array<mixed> $context The body of the log
     * @param string|null $channel The channel name of the log
     *
     * @return void
     */
    public static function info(
        string $title,
        array $context,
        ?string $channel = null,
    ): void {
        self::write($title, $context, $channel, LogType::INFO->value);
    }

    /**
     * Log an error message.
     *
     * @param string $title The title of the log
     * @param array<mixed> $context The body of the log
     * @param string|null $channel The channel name of the log
     *
     * @return void
     */
    public static function error(
        string $title,
        array $context,
        ?string $channel = null,
    ): void {
        self::write($title, $context, $channel, LogType::ERROR->value);
    }

    /**
     * Log a debug message.
     *
     * @param string $title The title of the log
     * @param array<mixed> $context The body of the log
     * @param string|null $channel The channel name of the log
     *
     * @return void
     */
    public static function debug(
        string $title,
        array $context,
        ?string $channel = null,
    ): void {
        self::write($title, $context, $channel, LogType::DEBUG->value);
    }

    /**
     * Creates a log.
     *
     * @param string $title The title of the log
     * @param array<string, mixed> $context The body of the log
     * @param string|null $channel The channel name of the log
     * @param string|null $logType (error, info, debug, notice)
     *
     * @return void
     */
    public static function write(
        string $title,
        array $context,
        ?string $channel = null,
        ?string $logType = null,
    ): void {
        $logger = $channel
            ? Log::channel($channel)
            : Log::getFacadeRoot();

        // Sample: $logger->info('This is a dynamic info log.');
        $logger->{$logType}($title, $context);
    }
}
