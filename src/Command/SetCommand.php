<?php

namespace Aperture\Backlight\Command;

use Aperture\Backlight\Limits;
use Aperture\Backlight\Util;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Ivan Pepelko <ivan.pepelko@gmail.com>
 */
class SetCommand extends Command
{
    private const OPT_FORCE = 'force';
    private const OPT_INC = 'increment';
    private const OPT_DEC = 'decrement';

    private const ARG_VALUE = 'value';

    protected static $defaultName = 'set';

    protected function configure()
    {
        $this->setDescription('Set brightness')
             ->addOption(self::OPT_FORCE, 'f', InputOption::VALUE_NONE, 'Allow to set values outside limits')
             ->addOption(self::OPT_INC, 'i', InputOption::VALUE_NONE, 'Increment brightness by a relative value')
             ->addOption(self::OPT_DEC, 'd', InputOption::VALUE_NONE, 'Decrement brightness by a relative value')
             ->addArgument(self::ARG_VALUE, InputArgument::REQUIRED, sprintf('A value in range [%.2f, %.2f]', Limits::MIN_BRIGHTNESS, Limits::MAX_BRIGHTNESS));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new ConsoleLogger($output);
        $logger->info(sprintf('Using display: %s', Util::getPrimaryDisplay()));

        $force = $input->getOption(self::OPT_FORCE);
        $value = filter_var($input->getArgument(self::ARG_VALUE), FILTER_VALIDATE_FLOAT);

        if ($value === false) {
            $logger->error(sprintf('Invalid input: %s', $input->getArgument(self::ARG_VALUE)));

            return 1;
        }

        $inc = $input->getOption(self::OPT_INC);
        $dec = $input->getOption(self::OPT_DEC);

        if ($inc && $dec) {
            $logger->error(sprintf('Options --%s and --%s are mutually exclusive, specify only one!', self::OPT_INC, self::OPT_DEC));

            return 1;
        }

        if ($inc) {
            $value = Util::getBrightness() + $value;
        } elseif ($dec) {
            $value = Util::getBrightness() - $value;
        }

        if (!$force && ($value < Limits::MIN_BRIGHTNESS || $value > Limits::MAX_BRIGHTNESS)) {
            $logger->error(
                sprintf(
                    'Input is outside of limits [%.2f, %.2f]: %s. If you are sure that you want to change brightness to this value use -f option.',
                    Limits::MIN_BRIGHTNESS,
                    Limits::MAX_BRIGHTNESS,
                    $value
                )
            );

            return 1;
        }

        try {
            Util::setBrightness($value);
            $logger->info(sprintf('Current brightness: %.3f', Util::getBrightness()));
        } catch (Exception $exception) {
            $logger->error($exception->getMessage());

            return 1;
        }

        return 0;
    }
}