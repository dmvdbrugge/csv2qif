<?php

namespace Command;

use Parable\Console\Command;
use UiComponents\MainWindow;

use function extension_loaded;
use function UI\run;

class Ui extends Command
{
    protected $name = 'ui';

    protected $description = 'Shows a UI for convert/validate';

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

        $window = new MainWindow();
        $window->show();

        run();
    }
}
