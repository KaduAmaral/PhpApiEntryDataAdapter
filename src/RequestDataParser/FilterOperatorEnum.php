<?php

namespace KaduAmaral\PhpApiEntryDataAdapter\RequestDataParser;

enum FilterOperatorEnum: string {
    case Equal             = '=';
    case NotEqual          = '!=';
    case Different         = '<>';
    case BiggerThan        = '>';
    case BiggerOrEqualThan = '>=';
    case LessThan          = '<';
    case LessOrEqualThan   = '<=';
    case StartsWith        = 'startswith';
    case EndsWith          = 'endswith';
    case Contains          = 'contains';
    case InList            = 'inlist';
}