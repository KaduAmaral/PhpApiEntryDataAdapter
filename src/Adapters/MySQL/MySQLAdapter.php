<?php

namespace KaduAmaral\PhpApiEntryDataAdapter\Adapters\MySQL;

use KaduAmaral\PhpApiEntryDataAdapter\Adapters\Abstracts\FilterAdapter;
use KaduAmaral\PhpApiEntryDataAdapter\RequestDataParser\FilterLoadCollection;
use KaduAmaral\PhpApiEntryDataAdapter\RequestDataParser\FilterLoadOption;
use KaduAmaral\PhpApiEntryDataAdapter\RequestDataParser\FilterOperatorEnum;

class MySQLAdapter extends FilterAdapter {

    public function __construct(
        private readonly array $mapping
    ) { }

    public function dumpCollection(FilterLoadCollection $collection): MySQLAdapterResult {
        if (empty($collection->count())) {
            return new MySQLAdapterResult(NULL, NULL);
        }

        $sqls = [];
        $vars = [];
        /** @var FilterLoadOption */
        foreach ($collection as $filter) {
            $result = $this->dump($filter);

            $sqls[] = $result->sql;
            $vars = array_merge($vars, $result->vars);
        }


        return new MySQLAdapterResult(implode(' AND ', $sqls), $vars);
    }

    public function dump(FilterLoadOption $filter): MySQLAdapterResult
    {
        $column = $this->getColumn($filter);
        $op     = $this->getOperator($filter->operator);
        $value  = $this->getValueStatement($filter);
        $vars =  $this->getVariables($filter);
        
        $sql    = "{$column} {$op} {$value}";
        return new MySQLAdapterResult($sql, $vars);
    }

    private function getColumn(FilterLoadOption $filter) {
        $map = $this->mapping[$filter->field] ?? [];
        $column = is_string($map) ? $map : $map['column'] ?? $map[0] ?? NULL;

        return $column ?: $filter->field;
    }

    private function getVarName(FilterLoadOption $filter) {
        $map = $this->mapping[$filter->field] ?? [];
        $varName = is_string($map) ? $map : $map['var'] ?? $map[1] ?? NULL;
        return $varName ?: $filter->field;
    }
    
    private function getValueStatement(FilterLoadOption $filter) {
        $varName    = $this->getVarName($filter);

        switch ($filter->operator) {
            case FilterOperatorEnum::StartsWith: return "CONCAT(:$varName, '%')";
            case FilterOperatorEnum::EndsWith:   return "CONCAT('%', :$varName)";
            case FilterOperatorEnum::Contains:   return "CONCAT('%', :$varName, '%')";
            case FilterOperatorEnum::InList:
                $varList = array_fill(0, count($filter->value), $varName);
                array_walk($varList, fn(&$v, $i) => $v = $this->generateVarName($v, $i));
                return '(' . implode(',', $varList) . ')';
            default: return ":$varName";
        }
    }

    private function getVariables(FilterLoadOption $filter): array {
        $varName    = $this->getVarName($filter);
        $vars = [];
        
        if ($filter->operator == FilterOperatorEnum::InList) {
            $i = 0;
            foreach ($filter->value as $value) {
                $vars[$this->generateVarName($varName, $i++)] = $value;
            }
        } else {
            $vars[$this->generateVarName($varName)] = $filter->value;
        }

        return $vars;
    }

    

    private function generateVarName(string $var, ?int $position = NULL) {
        $name = ':' . $var;
        if (!is_null($position)) $name .= $position;
        return $name;
    }

    private function getOperator(FilterOperatorEnum $operator): string {
        return match($operator) {
            FilterOperatorEnum::Equal             => '=',
            FilterOperatorEnum::NotEqual          => '!=',
            FilterOperatorEnum::Different         => '<>',
            FilterOperatorEnum::BiggerThan        => '>',
            FilterOperatorEnum::BiggerOrEqualThan => '>=',
            FilterOperatorEnum::LessThan          => '<',
            FilterOperatorEnum::LessOrEqualThan   => '<=',
            FilterOperatorEnum::StartsWith        => 'LIKE',
            FilterOperatorEnum::EndsWith          => 'LIKE',
            FilterOperatorEnum::Contains          => 'LIKE',
            FilterOperatorEnum::InList            => 'IN',
        };
    }

}