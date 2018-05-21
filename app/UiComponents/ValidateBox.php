<?php

namespace UiComponents;

use Actors\Validator;
use Event\Hook;
use Parable\DI\Container;
use Parable\Framework\Config;
use UI\Controls\Box;
use UI\Controls\Grid;
use UI\Controls\Group;
use UI\Controls\MultilineEntry;
use UI\Window;

use function array_keys;
use function sort;

class ValidateBox extends Box
{
    /** @var Config */
    private $config;

    /** @var Output */
    private $output;

    /** @var Radio */
    private $ruleset;

    /** @var Combo */
    private $verbose;

    /** @var Window */
    private $window;

    public function __construct(Config $config, Window $window)
    {
        parent::__construct(Box::Horizontal);

        $this->config = $config;
        $this->window = $window;

        $this->setPadded(true);
        $this->append($this->getLeftBox());
        $this->append($this->getRightBox(), true);
    }

    public function __invoke(): void
    {
        $hook = Container::get(Hook::class);
        $hook->reset();
        $this->output->cls();

        $validator  = new Validator($hook, $this->output);
        $useRuleset = $this->ruleset->getSelectedText();
        $useVerbose = $this->verbose->getSelected();

        try {
            $validator->validate($useRuleset, $useVerbose);
        } catch (\Exception $e) {
            $this->window->error('Error', $e->getMessage());
        }
    }

    private function getLeftBox(): Box
    {
        $box = new Box(Box::Vertical);
        $box->setPadded(true);
        $box->append($this->getVerboseGroup());
        $box->append($this->getRulesetGroup());

        return $box;
    }

    private function getRightBox(): Box
    {
        $box = new Box(Box::Vertical);
        $box->setPadded(true);
        $box->append($this->getValidateGroup(), true);

        return $box;
    }

    private function getRulesetGroup(): Group
    {
        $rulesetOpts = $this->config->get('csv2qif', []);
        $rulesetOpts = array_keys($rulesetOpts);
        sort($rulesetOpts);

        $this->ruleset = new Radio($rulesetOpts);
        $rulesetGroup  = new Group('Ruleset');
        $rulesetGroup->setMargin(true);
        $rulesetGroup->append($this->ruleset);

        return $rulesetGroup;
    }

    private function getValidateGrid(): Grid
    {
        $outputEntry = new MultilineEntry(MultilineEntry::Wrap);
        $outputEntry->setReadOnly(true);
        $this->output = new Output($outputEntry);

        $validate     = new Button('Validate', $this);
        $validateGrid = new Grid();
        $validateGrid->setPadded(true);
        $validateGrid->append($validate, 0, 0, 1, 1, true, Grid::Fill, false, Grid::Leading);
        $validateGrid->append($outputEntry, 0, 1, 1, 1, true, Grid::Fill, true, Grid::Leading);

        return $validateGrid;
    }

    private function getValidateGroup(): Group
    {
        $validateGroup = new Group('Validation');
        $validateGroup->setMargin(true);
        $validateGroup->append($this->getValidateGrid());

        return $validateGroup;
    }

    private function getVerboseGroup(): Group
    {
        $verboseOpts   = ['Result', 'Invalid', 'Valid', 'Matchers'];
        $this->verbose = new Combo($verboseOpts, 'Matchers');
        $verboseGroup  = new Group('Verbose level');
        $verboseGroup->setMargin(true);
        $verboseGroup->append($this->verbose);

        return $verboseGroup;
    }
}
