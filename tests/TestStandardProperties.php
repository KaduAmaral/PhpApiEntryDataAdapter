<?php

namespace Tests;
use KaduAmaral\PhpApiEntryDataAdapter\RequestDataParser\FilterOperatorEnum;
use KaduAmaral\PhpApiEntryDataAdapter\RequestDataParser\RequestLoadOptions;
use PHPUnit\Framework\TestCase;

class TestStandardProperties extends TestCase {

    public function testGetTake() {
        $loadOptions = new RequestLoadOptions(['take' => 20]);
        $this->assertSame(20, $loadOptions->take());
    }

    public function testGetDefaultTake() {
        $loadOptions = new RequestLoadOptions(NULL);
        $this->assertSame(10, $loadOptions->take(10));
    }

    public function testGetSkip() {
        $loadOptions = new RequestLoadOptions(['skip' => 100]);
        $this->assertSame(100, $loadOptions->skip());
    }

    public function testGetDefaultSkip() {
        $loadOptions = new RequestLoadOptions(NULL);
        $this->assertSame(50, $loadOptions->skip(50));
    }
}