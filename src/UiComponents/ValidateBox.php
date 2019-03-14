<?php

namespace Csv2Qif\UiComponents;

use Csv2Qif\Actors\Validator;
use Csv2Qif\Event\Hook;
use Csv2Qif\RuleSet\RuleSetConfig;
use Csv2Qif\RuleSet\RuleSetValidator;
use DynamicComponents\AdvancedControls\Combo;
use DynamicComponents\AdvancedControls\Radio;
use DynamicComponents\Controls\Button;
use UI\Controls\Box;
use UI\Controls\Grid;
use UI\Controls\Group;
use UI\Controls\MultilineEntry;
use UI\Window;

use function sort;
use function UI\run;

use const UI\Loop;

class ValidateBox extends Box
{
    /** @var Hook */
    private $hook;

    /** @var UiOutput */
    private $output;

    /** @var Radio */
    private $ruleset;

    /** @var RuleSetValidator */
    private $ruleSetValidator;

    /** @var Combo */
    private $verbose;

    /** @var Window */
    private $window;

    public function __construct(Hook $hook, RuleSetValidator $ruleSetValidator, Window $window)
    {
        parent::__construct(Box::Horizontal);

        $this->hook             = $hook;
        $this->ruleSetValidator = $ruleSetValidator;
        $this->window           = $window;

        $this->setPadded(true);
        $this->append($this->getLeftBox());
        $this->append($this->getRightBox(), true);
    }

    public function __invoke(Button $button): void
    {
        $useRuleSet = $this->ruleset->getSelectedText();

        if (empty($useRuleSet)) {
            $this->window->error('No ruleset selected', 'Please select a ruleset to validate.');

            return;
        }

        $button->disable();
        $this->output->cls();

        $this->hook->reset();
        $this->hook->listenAll(function () {
            run(Loop);
        });

        $validator  = new Validator($this->hook, $this->output, $this->ruleSetValidator);
        $useVerbose = $this->verbose->getSelected();

        try {
            $validator->validate($useRuleSet, $useVerbose);
        } catch (\Exception $e) {
            $this->window->error('Error', $e->getMessage());
        }

        $button->enable();
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
        $this->output = new UiOutput($outputEntry);

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
