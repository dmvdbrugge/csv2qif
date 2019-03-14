<?php

namespace Csv2Qif\UiComponents;

use Csv2Qif\Actors\Converter;
use Csv2Qif\Event\Hook;
use Csv2Qif\File\CsvReader;
use Csv2Qif\RuleSet\RuleSetConfig;
use DynamicComponents\AdvancedControls\Combo;
use DynamicComponents\AdvancedControls\Radio;
use DynamicComponents\Controls\Button;
use Parable\Di\Container;
use UI\Controls\Box;
use UI\Controls\Grid;
use UI\Controls\Group;
use UI\Controls\MultilineEntry;
use UI\Window;

use function array_unshift;
use function sort;
use function UI\run;

use const UI\Loop;

class ConvertBox extends Box
{
    /** @var Container */
    private $container;

    /** @var FileSelect */
    private $csv;

    /** @var Combo */
    private $debug;

    /** @var Hook */
    private $hook;

    /** @var UiOutput */
    private $output;

    /** @var FileSelect */
    private $qif;

    /** @var Radio */
    private $ruleset;

    /** @var Window */
    private $window;

    public function __construct(Container $container, Hook $hook, Window $window)
    {
        parent::__construct(Box::Horizontal);

        $this->container = $container;
        $this->hook      = $hook;
        $this->window    = $window;

        $this->setPadded(true);
        $this->append($this->getLeftBox());
        $this->append($this->getRightBox(), true);
    }

    public function __invoke(Button $button): void
    {
        $button->disable();
        $this->output->cls();

        $this->hook->reset();
        $this->hook->listen(CsvReader::TRANSACTION_READ, function () {
            run(Loop);
        });

        $converter  = new Converter($this->container, $this->hook, $this->output);
        $useCsv     = $this->csv->getFile();
        $useQif     = $this->qif->getFile();
        $useRuleset = $this->ruleset->getSelectedText();
        $useDebug   = $this->debug->getSelected();

        if ($useQif === '') {
            $useQif = null;
        }

        if ($useRuleset === 'None') {
            $useRuleset = '';
        }

        try {
            $converter->convert($useCsv, $useQif, $useRuleset, $useDebug);
        } catch (\Exception $e) {
            $this->window->error('Error', $e->getMessage());
        }

        $button->enable();
    }

    private function getConvertGrid(): Grid
    {
        // - Output => Maybe move out of tab?
        $outputEntry = new MultilineEntry(MultilineEntry::NoWrap);
        $outputEntry->setReadOnly(true);
        $this->output = new UiOutput($outputEntry);

        // - Convert button
        $convert     = new Button('Convert', $this);
        $convertGrid = new Grid();
        $convertGrid->setPadded(true);
        $convertGrid->append($convert, 0, 0, 1, 1, true, Grid::Fill, false, Grid::Leading);
        $convertGrid->append($outputEntry, 0, 1, 1, 1, true, Grid::Fill, true, Grid::Leading);

        return $convertGrid;
    }

    private function getConvertGroup(): Group
    {
        $convertGroup = new Group('Conversion');
        $convertGroup->setMargin(true);
        $convertGroup->append($this->getConvertGrid());

        return $convertGroup;
    }

    private function getDebugGroup(): Group
    {
        $debugOpts   = ['None', 'Usage', 'Found', 'Fallback'];
        $this->debug = new Combo($debugOpts, null, 'Fallback');
        $debugGroup  = new Group('Debug level');
        $debugGroup->setMargin(true);
        $debugGroup->append($this->debug);

        return $debugGroup;
    }

    private function getFilesGrid(): Grid
    {
        $filesGrid = new Grid();
        $filesGrid->setPadded(true);

        $this->csv = new FileSelect('     Csv     ', $this->window, FileSelect::OPEN);
        $this->csv->appendToGrid($filesGrid);

        $this->qif = new FileSelect('Qif', $this->window, FileSelect::SAVE, true);
        $this->qif->appendToGrid($filesGrid, 1);

        return $filesGrid;
    }

    private function getFilesGroup(): Group
    {
        $filesGroup = new Group('Files');
        $filesGroup->setMargin(true);
        $filesGroup->append($this->getFilesGrid());

        return $filesGroup;
    }

    private function getLeftBox(): Box
    {
        $box = new Box(Box::Vertical);
        $box->setPadded(true);
        $box->append($this->getDebugGroup());
        $box->append($this->getRulesetGroup());

        return $box;
    }

    private function getRightBox(): Box
    {
        $box = new Box(Box::Vertical);
        $box->setPadded(true);
        $box->append($this->getFilesGroup());
        $box->append($this->getConvertGroup(), true);

        return $box;
    }

    private function getRulesetGroup(): Group
    {
        $rulesetOpts = RuleSetConfig::getAvailableRuleSets();

        sort($rulesetOpts);
        array_unshift($rulesetOpts, 'None');

        $this->ruleset = new Radio($rulesetOpts, null, 'None');
        $rulesetGroup  = new Group('Ruleset');
        $rulesetGroup->setMargin(true);
        $rulesetGroup->append($this->ruleset);

        return $rulesetGroup;
    }
}
