<?php

namespace Aperture\Backlight;

use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * @author Ivan Pepelko <ivan.pepelko@gmail.com>
 */
final class Util
{

    private static $primaryDisplay;

    /**
     * @return string
     * @throws RuntimeException
     */
    public static function getPrimaryDisplay(): string
    {
        if (self::$primaryDisplay !== null) {
            return self::$primaryDisplay;
        }

        $process = new Process(['xrandr']);

        $output = '';
        $process->run(
            static function ($type, $out) use (&$output) {
                if ($type !== Process::OUT) {
                    return;
                }

                $output .= $out;
            }
        );

        if ($output === '') {
            throw new RuntimeException('Received bad output from xrandr command.');
        }

        $lines = explode(PHP_EOL, $output);
        $primary = '';
        foreach ($lines as $i => $line) {
            if ($i === 0) {
                continue;
            }

            if (strpos($line, 'primary') !== false) {
                $primary = $line;
            }
        }

        if ($primary === '') {
            throw new RuntimeException('Could not determine the default display.');
        }

        self::$primaryDisplay = substr($primary, 0, strpos($primary, ' '));

        return self::$primaryDisplay;
    }

    public static function getBrightness(): float
    {
        $process = new Process(['xrandr', '--current', '--verbose']);

        $output = '';
        $process->run(
            static function ($type, $out) use (&$output) {
                if ($type !== Process::OUT) {
                    return;
                }

                $output .= $out;
            }
        );

        if ($output === '') {
            throw new RuntimeException('Received bad output from xrandr command.');
        }

        $lines = explode(PHP_EOL, $output);
        $primaryDisplay = self::getPrimaryDisplay();
        $inPrimary = false;
        $brightnessStr = '';
        foreach ($lines as $line) {
            if (!$inPrimary && strpos($line, $primaryDisplay) === 0) {
                $inPrimary = true;
            }

            if ($inPrimary) {
                $line = trim($line);
                if (strpos($line, 'Brightness') === 0) {
                    $brightnessStr = substr($line, strpos($line, ':') + 2);
                }
            }
        }

        if (!is_numeric($brightnessStr)) {
            throw new RuntimeException('Could not determine brightness for primary screen.');
        }

        return filter_var($brightnessStr, FILTER_VALIDATE_FLOAT);
    }

    public static function setBrightness(float $value): void
    {
        $process = new Process(['xrandr', '--output', self::getPrimaryDisplay(), '--brightness', (string) $value]);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(sprintf('Could not set the brightness: %s', $process->getErrorOutput()));
        }
    }
}