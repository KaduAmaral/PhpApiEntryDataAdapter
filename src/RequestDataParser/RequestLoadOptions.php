<?php

namespace KaduAmaral\PhpApiEntryDataAdapter\RequestDataParser;

use Exception;
use KaduAmaral\PhpApiEntryDataAdapter\Errors\InvalidDataStruct;

class RequestLoadOptions {

    /** @var FilterLoadCollection */
    private $_filters;

    /** @var mixed[] */
    private $_invalidFilters;

    public function __construct(
        private readonly mixed $data
    ) { }

    public function take($default = NULL) {
        return $this->getRequestDataValue('take', $default);
    }

    public function skip($default = NULL) {
        return $this->getRequestDataValue('skip', $default);
    }

    public function filters(): FilterLoadCollection {
        $this->buildFilters();
        return $this->_filters;
    }

    private function buildFilters() {
        if (!empty($this->_filters) || !empty($this->_invalidFilters)) return;
        $this->_filters = new FilterLoadCollection();

        if (!empty($filters) && is_array($filters)) {
            return;
        }

        $filters = $this->getRequestDataValue('filters') ?: $this->getRequestDataValue('filter');
        
        if (array_is_list($filters)) {
            foreach ($filters as $filter) {
                if (!is_array($filter) || !array_is_list($filter)) {
                    $this->_invalidFilters[] = $filter;
                    continue;
                }

                if (count($filter) > 2) {
                    $op = $filter[1];
                } else {
                    $op = is_array($filter[1]) ? 'IN' : '=';
                }

                $operator   = FilterOperatorEnum::tryFrom($op);
                if (is_null($operator)) {
                    $this->_invalidFilters[] = $filter;
                    continue;
                }

                $field      = $filter[0];
                $value      = count($filter) > 2 ? $filter[2] : $filter[1];

                $this->_filters->add(new FilterLoadOption($field, $operator, $value));
            }
        } else {
            foreach ($filters as $field => $value) { 
                $op = is_array($value) ? 'IN' : '=';
                $operator   = FilterOperatorEnum::tryFrom($op);
                $this->_filters->add(new FilterLoadOption($field, $operator, $value));
            }
        }
    }

    private function hasDataProperty(mixed $data, string $property): bool {
        if (is_object($data)) {
            return isset($data->$property);
        } else if (is_array($data)) {
            return isset($data[$property]);
        } else {
            return FALSE;
        }
    }

    private function getDataPropertyValue(mixed $data, string $property, mixed $default = NULL) {
        if (is_object($data)) {
            return $this->data->$property;
        } else if (is_array($this->data)) {
            return $this->data[$property];
        } else {
            throw new InvalidDataStruct('Invalid Data Struct');
        }
    }

    private function getRequestDataValue($property, $default = NULL) {
        if ($this->hasDataProperty($this->data, $property)) {
            return $this->getDataPropertyValue($this->data, $property, $default);
        } else {
            return $default;
        }
        
    }

}