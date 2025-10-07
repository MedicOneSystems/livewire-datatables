<?php

namespace Mediconesystems\LivewireDatatables\Http\Livewire;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\Component;

class ComplexQuery extends Component
{
    public $columns;
    public $persistKey;
    public $savedQueries;
    public $query = [];
    public $rule = [];
    public $rules = [
        [
            'type' => 'group',
            'logic' => 'and',
            'content' => [],
        ],
    ];

    protected $listeners = ['updateSavedQueries', 'resetQuery'];

    public function mount($columns, $persistKey, $savedQueries = null)
    {
        $this->columns = $columns;
        $this->persistKey = $persistKey;
        $this->savedQueries = $savedQueries;
    }

    public function updateSavedQueries($savedQueries = null)
    {
        $this->mount($this->columns, $this->persistKey, $savedQueries ?? $this->savedQueries);
    }

    public function updatedRules($value, $key)
    {
        $this->clearOperandAndValueWhenColumnChanged($key);

        $this->runQuery();
    }

    public function clearOperandAndValueWhenColumnChanged($key)
    {
        if (Str::endsWith($key, 'column')) {
            data_set($this->rules, str_replace('column', 'operand', $key), null);
            data_set($this->rules, str_replace('column', 'value', $key), null);
        }
    }

    public function getRulesStringProperty($rules = null, $logic = 'and')
    {
        return collect($rules ?? $this->rules)->map(function ($rule) {
            return $rule['type'] === 'rule'
                    ? implode(' ', [$this->columns[$rule['content']['column']]['label'] ?? '', $rule['content']['operand'] ?? '', $rule['content']['value'] ?? ''])
                    : '(' . $this->getRulesStringProperty($rule['content'], $rule['logic']) . ')';
        })->join(strtoupper(" $logic "));
    }

    public function runQuery()
    {
        $this->validateRules();

        $this->emit('complexQuery', count($this->rules[0]['content']) ? $this->rules : null);
    }

    public function saveQuery($name)
    {
        $this->emitUp('saveQuery', $name, $this->rules);
    }

    public function loadRules($rules)
    {
        $this->rules = $rules;
        $this->runQuery();
    }

    public function deleteRules($id)
    {
        $this->emitUp('deleteQuery', $id);
    }

    public function resetQuery()
    {
        $this->reset('rules');
        $this->runQuery();
    }

    public function validateRules($rules = null, $key = '')
    {
        $rules = $rules ?? $this->rules[0]['content'];

        collect($rules)->each(function ($rule, $i) {
            if ($rule['type'] === 'rule') {
                $v = Validator::make($rule['content'], ['column' => 'required']);

                $v->sometimes('operand', 'required', function ($rule) {
                    return ! ($rule['value'] === 'true' || $rule['value'] === 'false');
                });

                $v->sometimes('value', 'required', function ($rule) {
                    return ! collect([
                        'is empty',
                        'is not empty',
                    ])->contains($rule['operand']);
                });

                $v->validate();
            } else {
                $this->validateRules($rule['content']);
            }
        });
    }

    public function addRule($index)
    {
        $temp = Arr::get($this->rules, $index);

        $temp[] = [
            'type' => 'rule',
            'content' => [
                'column' => null,
                'operand' => null,
                'value' => null,
            ],
        ];

        Arr::set($this->rules, $index, $temp);

        $this->validateRules();
    }

    public function duplicateRule($index)
    {
        $current = Arr::get($this->rules, Str::beforeLast($index, '.content'));
        $parentGroup = Arr::get($this->rules, Str::beforeLast(Str::beforeLast($index, '.content'), '.'));

        $parentGroup[] = $current;

        Arr::set($this->rules, Str::beforeLast(Str::beforeLast($index, '.content'), '.'), $parentGroup);

        $this->validateRules();
    }

    public function moveRule($from, $to)
    {
        $mover = Arr::get($this->rules, Str::beforeLast($from, '.'));
        $newParent = Arr::get($this->rules, $to);

        if (is_array($newParent) && is_array($mover)) {
            array_push($newParent, $mover);
            Arr::set($this->rules, $to, $newParent);
            Arr::pull($this->rules, Str::beforeLast($from, '.'));
        }

        $this->runQuery();
    }

    public function addGroup($index)
    {
        $temp = Arr::get($this->rules, $index);

        $temp[] = [
            'type' => 'group',
            'logic' => 'and',
            'content' => [],
        ];

        Arr::set($this->rules, $index, $temp);
    }

    public function removeRule($index)
    {
        Arr::pull($this->rules, Str::beforeLast($index, '.'));

        $this->runQuery();
    }

    public function setRuleColumn($index, $value)
    {
        $this->rules[$index]['column'] = $this->columns[$value];
    }

    public function setRuleOperand($index, $value)
    {
        $this->rules[$index]['operand'] = $value;
    }

    public function setRuleValue($index, $value)
    {
        $this->rules[$index]['value'] = $value;
    }

    public function getRuleColumn($key)
    {
        return $this->columns[Arr::get($this->rules, $key . '.column')] ?? null;
    }

    public function getOperands($key)
    {
        $operands = [
            'string' => ['equals', 'does not equal', 'contains', 'does not contain', 'is empty', 'is not empty', 'begins with', 'ends with'],
            'json' => ['equals', 'does not equal', 'contains', 'does not contain', 'is empty', 'is not empty', 'begins with', 'ends with'],
            'editable' => ['equals', 'does not equal', 'contains', 'does not contain', 'is empty', 'is not empty', 'begins with', 'ends with'],
            'number' => ['=', '<>', '<', '<=', '>', '>='],
            'date' => ['=', '<>', '<', '<=', '>', '>='],
            'time' => ['=', '<>', '<', '<=', '>', '>='],
            'boolean' => [],
            'scope' => ['includes'],
        ];

        if (! $this->getRuleColumn($key)) {
            return [];
        }

        return optional($this->getRuleColumn($key))['scopeFilter']
            ? $operands['scope']
            : $operands[$this->getRuleColumn($key)['type']];
    }

    public function render()
    {
        return view('datatables::complex-query');
    }
}
