<?php

namespace Aperture\Backlight\Command;

use Aperture\Backlight\Util;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Ivan Pepelko <ivan.pepelko@gmail.com>
 */
class GetCommand extends Command
{
    protected static $defaultName = 'get';

    protected function configure()
    {
        $this->setDescription('Get current brightness');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new ConsoleLogger($output);

        $logger->info(sprintf('Using display: %s', Util::getPrimaryDisplay()));
        $output->write(sprintf('Current brightness: %.3f', Util::getBrightness()));

        return 0;
    }

}