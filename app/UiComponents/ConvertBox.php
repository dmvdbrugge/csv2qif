<?php

namespace UiComponents;

use Actors\Converter;
use DynamicComponents\AdvancedControls\Combo;
use DynamicComponents\AdvancedControls\Radio;
use DynamicComponents\Controls\Button;
use Event\Hook;
use Parable\DI\Container;
use Parable\Framework\Config;
use UI\Controls\Box;
use UI\Controls\Grid;
use UI\Controls\Group;
use UI\Controls\MultilineEntry;
use UI\Window;

use function array_keys;
use function array_unshift;
use function sort;

class ConvertBox extends Box
{
    /** @var Config */
    private $config;

    /** @var FileSelect */
    private $csv;

    /** @var Combo */
    private $debug;

    /** @var Output */
    private $output;

    /** @var FileSelect */
    private $qif;

    /** @var Radio */
    private $ruleset;

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

        $converter  = new Converter($hook, $this->output);
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
    }

    private function getConvertGrid(): Grid
    {
        // - Output => Maybe move out of tab?
        $outputEntry = new MultilineEntry(MultilineEntry::NoWrap);
        $outputEntry->setReadOnly(true);
        $this->output = new Output($outputEntry);

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
        $rulesetOpts = $this->config->get('csv2qif', []);
        $rulesetOpts = array_keys($rulesetOpts);
        sort($rulesetOpts);
        array_unshift($rulesetOpts, 'None');

        $this->ruleset = new Radio($rulesetOpts);
        $rulesetGroup  = new Group('Ruleset');
        $rulesetGroup->setMargin(true);
        $rulesetGroup->append($this->ruleset);

        return $rulesetGroup;
    }
}
