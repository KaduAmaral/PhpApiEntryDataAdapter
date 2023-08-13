<?php

namespace KaduAmaral\PhpApiEntryDataAdapter\RequestDataParser;

use KaduAmaral\PhpApiEntryDataAdapter\Adapters\Abstracts\FilterAdapter;
use KaduAmaral\PhpApiEntryDataAdapter\Adapters\Abstracts\FilterAdapterResult;

class FilterLoadOption {

    public function __construct(
        public readonly string $field,
        public readonly FilterOperatorEnum $operator,
        public readonly mixed $value
    ) { }


    public function getStatement(FilterAdapter $adapter): FilterAdapterResult {
        return $adapter->dump($this);
    }

}