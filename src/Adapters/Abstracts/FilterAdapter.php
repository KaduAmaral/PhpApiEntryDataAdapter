<?php

namespace KaduAmaral\PhpApiEntryDataAdapter\Adapters\Abstracts;

use KaduAmaral\PhpApiEntryDataAdapter\RequestDataParser\FilterLoadCollection;
use KaduAmaral\PhpApiEntryDataAdapter\RequestDataParser\FilterLoadOption;

abstract class FilterAdapter {

    abstract public function dump(FilterLoadOption $filter);

    abstract public function dumpCollection(FilterLoadCollection $collection);

}