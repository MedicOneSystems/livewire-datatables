<?php

namespace Mediconesystems\LivewireDatatables;

class Rule
{
    public $column;
    public $operand;
    public $value;

    public function __construct()
    {
    }

    public function setColumn($column)
    {
        $this->column = $column;
    }

    public function setOperand($operand)
    {
        $this->operand = $operand;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function __get($name)
    {
        return $this->{$name};
    }
}
