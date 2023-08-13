<?php

namespace KaduAmaral\PhpApiEntryDataAdapter\RequestDataParser;

use Countable;
use Iterator;
use KaduAmaral\PhpApiEntryDataAdapter\Adapters\Abstracts\FilterAdapter;
use KaduAmaral\PhpApiEntryDataAdapter\Adapters\Abstracts\FilterAdapterResult;

class FilterLoadCollection implements Iterator, Countable {

    /** @var FilterLoadOption[] */
    private $filters = [];
    private $currentPosition = 0;

    public function __construct(
        array $filters = []
    ) {
        $this->filters = $filters;
    }

    public function add(FilterLoadOption $filter) {
        $this->filters[] = $filter;
    }

    public function current(): FilterLoadOption {
        return $this->filters[$this->currentPosition] ?? FALSE;
    }

    public function key(): int {
        return $this->currentPosition;
    }

    public function next(): void {
        if (count($this->filters) > $this->currentPosition) {
            $this->currentPosition++;
        }
    }
    public function rewind(): void {
        $this->currentPosition = 0;
    }
    public function valid(): bool {
        return isset($this->filters[$this->currentPosition]);
    }

    public function count(): int {
        return count($this->filters);
    }

    public function filterStatement(FilterAdapter $adapter): FilterAdapterResult {
        return $adapter->dumpCollection($this);
    }

}