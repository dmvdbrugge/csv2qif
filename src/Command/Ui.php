<?php

namespace Csv2Qif\Command;

use Csv2Qif\UiComponents\MainWindow;
use Parable\Console\Command;
use Parable\Di\Container;

use function extension_loaded;
use function UI\run;

class Ui extends Command
{
    protected $name = 'csv2qif';

    protected $description = 'Shows a UI for convert/validate.';

    /** @var Container */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function run(): void
    {
        if (!extension_loaded('ui')) {
            $this->output->writeErrorBlock([
                'The ui extension is needed to run the ui command!',
                '- Either install and configure the extension,',
                '- or run the convert or validate command(s) from the cli.',
            ]);

            exit(1);
        }

        $window = $this->container->get(MainWindow::class);
        $window->show();

        run();
    }
}
