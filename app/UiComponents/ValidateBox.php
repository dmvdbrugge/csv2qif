<?php

namespace Csv2Qif\UiComponents;

use Csv2Qif\Actors\Validator;
use Csv2Qif\Event\Hook;
use Csv2Qif\RuleSet\RuleSetConfig;
use DynamicComponents\AdvancedControls\Combo;
use DynamicComponents\AdvancedControls\Radio;
use DynamicComponents\Controls\Button;
use Parable\DI\Container;
use UI\Controls\Box;
use UI\Controls\Grid;
use UI\Controls\Group;
use UI\Controls\MultilineEntry;
use UI\Window;

use function sort;

class ValidateBox extends Box
{
    /** @var Output */
    private $output;

    /** @var Radio */
    private $ruleset;

    /** @var Combo */
    private $verbose;

    /** @var Window */
    private $window;

    public function __construct(Window $window)
    {
        parent::__construct(Box::Horizontal);

        $this->window = $window;

        $this->setPadded(true);
        $this->append($this->getLeftBox());
        $this->append($this->getRightBox(), true);
    }

    public function __invoke(): void
    {
        $useRuleset = $this->ruleset->getSelectedText();

        if (empty($useRuleset)) {
            $this->window->error('No ruleset selected', 'Please select a ruleset to validate.');

            return;
        }

        $hook = Container::get(Hook::class);
        $hook->reset();
        $this->output->cls();

        $validator  = new Validator($hook, $this->output);
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
        $rulesetOpts = RuleSetConfig::getAvailableRuleSets();

        sort($rulesetOpts);

        $this->ruleset = new Radio($rulesetOpts);
        $rulesetGroup  = new Group('Ruleset');
        $rulesetGroup->setMargin(true);
        $rulesetGroup->append($this->ruleset);

        return $rulesetGroup;
    }

    private function getValidateGrid(): Grid
    {
        $outputEntry = new MultilineEntry(MultilineEntry::NoWrap);
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
        $this->verbose = new Combo($verboseOpts, null, 'Matchers');
        $verboseGroup  = new Group('Verbose level');
        $verboseGroup->setMargin(true);
        $verboseGroup->append($this->verbose);

        return $verboseGroup;
    }
}
