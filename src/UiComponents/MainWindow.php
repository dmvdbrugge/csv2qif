<?php

namespace Csv2Qif\UiComponents;

use Csv2Qif\Event\Hook;
use Csv2Qif\RuleSet\RuleSetValidator;
use Parable\Di\Container;
use UI\Controls\Tab;
use UI\Size;
use UI\Window;

class MainWindow extends Window
{
    public function __construct(Container $container)
    {
        parent::__construct('csv2qif', new Size(800, 600));

        $hook             = $container->get(Hook::class);
        $ruleSetValidator = $container->get(RuleSetValidator::class);

        $convert  = new ConvertBox($container, $hook, $this);
        $validate = new ValidateBox($hook, $ruleSetValidator, $this);

        $tab = new Tab();
        $tab->append(' Convert ', $convert);
        $tab->append(' Validate ', $validate);

        $this->add($tab);
    }
}
