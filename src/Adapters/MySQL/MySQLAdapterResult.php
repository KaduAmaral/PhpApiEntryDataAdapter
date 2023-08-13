<?php

namespace KaduAmaral\PhpApiEntryDataAdapter\Adapters\MySQL;

use KaduAmaral\PhpApiEntryDataAdapter\Adapters\Abstracts\FilterAdapterResult;

class MySQLAdapterResult extends FilterAdapterResult {

    public function __construct(
        public readonly string $sql,
        public readonly array $vars
    ) { }

}