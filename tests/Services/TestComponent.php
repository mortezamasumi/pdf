<?php

namespace Tests\Services;

use Livewire\Component;

class TestComponent extends Component
{
    public $message = 'Hello, World!';

    public function render()
    {
        return <<<'blade'
                <div>
                    {{$message}}
                    {{request()->test}}
                </div>
            blade;
    }
}
