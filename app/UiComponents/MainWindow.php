<?php

namespace UiComponents;

use Parable\Framework\Config;
use UI\Controls\Tab;
use UI\Size;
use UI\Window;

class MainWindow extends Window
{
    public function __construct(Config $config)
    {
        parent::__construct('csv2qif', new Size(800, 600));

        $convert  = new ConvertBox($config, $this);
        $validate = new ValidateBox($config, $this);

        $tab = new Tab();
        $tab->append(' Convert ', $convert);
        $tab->append(' Validate ', $validate);

        $this->add($tab);
    }
}
