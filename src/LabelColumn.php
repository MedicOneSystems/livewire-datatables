<?php

namespace Mediconesystems\LivewireDatatables;

/**
 * Use this column to simply display a custom header ("label") and a fixed content ("content").
 *
 * @example (new LabelColumn())->label('foo')->content('bar'),
 */
class LabelColumn extends Column
{
    public $type = 'label';

    public $content = '';

    /**
     * Which fixed string should always be displayed in every row of this column?
     */
    public function content($content)
    {
        $this->content = $content;

        return $this;
    }
}
