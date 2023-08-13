<?php

namespace Tests;

use KaduAmaral\PhpApiEntryDataAdapter\Adapters\MySQL\MySQLAdapter;
use KaduAmaral\PhpApiEntryDataAdapter\Adapters\MySQL\MySQLAdapterResult;
use KaduAmaral\PhpApiEntryDataAdapter\RequestDataParser\FilterOperatorEnum;
use KaduAmaral\PhpApiEntryDataAdapter\RequestDataParser\RequestLoadOptions;
use PHPUnit\Framework\TestCase;

class TestFilterMySQLAdapter extends TestCase {

    /**
     * @dataProvider filterUseCases
     */
    public function testFilter($filters, $field, $operator, $value, $sql, $vars, $column = NULL, $varName = NULL) {
        $loadOptions = new RequestLoadOptions([
            'filters' => $filters
        ]);
        
        $filterCollection = $loadOptions->filters();
        
        $this->assertCount(count($filters), $filterCollection);

        $filter = $filterCollection->current();

        $this->assertSame($field, $filter->field);
        $this->assertSame($operator, $filter->operator);
        $this->assertSame($value, $filter->value);

        $mysqlAdapter = new MySQLAdapter([
            $field => [$column, $varName]
        ]);

        /** @var MySQLAdapterResult */
        $statement = $filter->getStatement($mysqlAdapter);
        $this->assertSame($sql, $statement->sql);
        $this->assertTrue(is_array($statement->vars));
        $this->assertEquals($vars, $statement->vars);
    }

    public static function filterUseCases() {
        return [
            'Equal' => [
                'filters'  => [['foo', '=', 'bar']], 
                'field'    => 'foo', 
                'operator' => FilterOperatorEnum::Equal, 
                'value'    => 'bar', 
                'sql'      => 'foo = :foo', 
                'vars'     => [':foo' => 'bar']
            ],
            'NotEqual' => [
                'filters'  => [['baz', '!=', 'bar']], 
                'field'    => 'baz', 
                'operator' => FilterOperatorEnum::NotEqual, 
                'value'    => 'bar', 
                'sql'      => 'baz != :baz', 
                'vars'     => [':baz' => 'bar']
            ],
            'Different' => [
                'filters'  => [['foo', '<>', 'bar']], 
                'field'    => 'foo', 
                'operator' => FilterOperatorEnum::Different, 
                'value'    => 'bar', 
                'sql'      => 'foo <> :foo', 
                'vars'     => [':foo' => 'bar']
            ],
            'BiggerThan' => [
                'filters'  => [['foo', '>', 1]], 
                'field'    => 'foo', 
                'operator' => FilterOperatorEnum::BiggerThan, 
                'value'    => 1, 
                'sql'      => 'foo > :foo', 
                'vars'     => [':foo' => 1]
            ],
            'BiggerOrEqualThan' => [
                'filters'  => [['foo', '>=', 1]], 
                'field'    => 'foo', 
                'operator' => FilterOperatorEnum::BiggerOrEqualThan, 
                'value'    => 1, 
                'sql'      => 'foo >= :foo', 
                'vars'     => [':foo' => 1]
            ],
            'LessThan' => [
                'filters'  => [['foo', '<', 5]], 
                'field'    => 'foo', 
                'operator' => FilterOperatorEnum::LessThan, 
                'value'    => 5, 
                'sql'      => 'foo < :foo', 
                'vars'     => [':foo' => 5]
            ],
            'LessOrEqualThan' => [
                'filters'  => [['foo', '<=', 5]], 
                'field'    => 'foo', 
                'operator' => FilterOperatorEnum::LessOrEqualThan, 
                'value'    => 5, 
                'sql'      => 'foo <= :foo', 
                'vars'     => [':foo' => 5]
            ],
            'StartsWith' => [
                'filters'  => [['foo', 'startswith', 'baz']], 
                'field'    => 'foo', 
                'operator' => FilterOperatorEnum::StartsWith, 
                'value'    => 'baz', 
                'sql'      => 'foo LIKE CONCAT(:foo, \'%\')', 
                'vars'     => [':foo' => 'baz']
            ],
            'EndsWith' => [
                'filters'  => [['foo', 'endswith', 'baz']], 
                'field'    => 'foo', 
                'operator' => FilterOperatorEnum::EndsWith, 
                'value'    => 'baz', 
                'sql'      => 'foo LIKE CONCAT(\'%\', :foo)', 
                'vars'     => [':foo' => 'baz']
            ],
            'Contains' => [
                'filters'  => [['foo', 'contains', 'baz']], 
                'field'    => 'foo', 
                'operator' => FilterOperatorEnum::Contains, 
                'value'    => 'baz', 
                'sql'      => 'foo LIKE CONCAT(\'%\', :foo, \'%\')', 
                'vars'     => [':foo' => 'baz']
            ],
            'InList' => [
                'filters'  => [['foo', 'inlist', [1,2,3]]], 
                'field'    => 'foo', 
                'operator' => FilterOperatorEnum::InList, 
                'value'    => [1,2,3], 
                'sql'      => 'foo IN (:foo0,:foo1,:foo2)', 
                'vars'     => [':foo0' => 1,':foo1' => 2,':foo2' => 3]
            ],
            'AsJsonSctruct' => [
                'filters'  => ['baz' => 'fob'], 
                'field'    => 'baz', 
                'operator' => FilterOperatorEnum::Equal,
                'value'    => 'fob', 
                'sql'      => 'baz = :baz', 
                'vars'     => [':baz' => 'fob']
            ],
            'EqualOmitting' => [
                'filters'  => [['foo', 'bar']], 
                'field'    => 'foo', 
                'operator' => FilterOperatorEnum::Equal,
                'value'    => 'bar', 
                'sql'      => 'foo = :foo', 
                'vars'     => [':foo' => 'bar']
            ],
            'SettingColumnAndVar' => [
                'filters'  => [['car', 'caz']], 
                'field'    => 'car', 
                'operator' => FilterOperatorEnum::Equal,
                'value'    => 'caz', 
                'sql'      => 'doc.num = :docNum', 
                'vars'     => [':docNum' => 'caz'],
                'column'   => 'doc.num',
                'varName'  => 'docNum'
            ],
        ];
    }

    public function testFilterCollectionQuery() {
        $filtersUseCases = self::filterUseCases();

        $loadOptions = new RequestLoadOptions([
            'filters' => [
                $filtersUseCases['Equal']['filters'][0],
                $filtersUseCases['NotEqual']['filters'][0],
                $filtersUseCases['SettingColumnAndVar']['filters'][0]
            ]
        ]);

        $filters = $loadOptions->filters();

        $mysqlAdapter = new MySQLAdapter([
            $filtersUseCases['SettingColumnAndVar']['field'] => [
                'column' => $filtersUseCases['SettingColumnAndVar']['column'],
                'var' => $filtersUseCases['SettingColumnAndVar']['varName']
            ]
        ]);

        /** @var MySQLAdapterResult */
        $filterStatement = $filters->filterStatement($mysqlAdapter);

        $expectedSql = $filtersUseCases['Equal']['sql'] . ' AND ' . 
                       $filtersUseCases['NotEqual']['sql'] . ' AND ' . 
                       $filtersUseCases['SettingColumnAndVar']['sql'];

        $this->assertSame(
            $expectedSql,
            $filterStatement->sql
        );

        $expectedVars = array_merge(
            $filtersUseCases['Equal']['vars'],
            $filtersUseCases['NotEqual']['vars'],
            $filtersUseCases['SettingColumnAndVar']['vars']
        );

        $this->assertSame(
            $expectedVars,
            $filterStatement->vars
        );
    }

}