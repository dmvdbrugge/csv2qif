<?php

namespace UiComponents;

use UI\Controls\Tab;
use UI\Size;
use UI\Window;

class MainWindow extends Window
{
    public function __construct()
    {
        parent::__construct('csv2qif', new Size(800, 600));

        $convert  = new ConvertBox($this);
        $validate = new ValidateBox($this);

        $tab = new Tab();
        $tab->append(' Convert ', $convert);
        $tab->append(' Validate ', $validate);

        $this->add($tab);
    }
}
