<?php

namespace App\Livewire\Forms\Admin;

use App\Models\Module;
use Livewire\Form;

class ModuleForm extends Form
{
    public ?Module $module;
    public bool $is_enabled = false;

    public function setModule(Module $module)
    {
        $this->module = $module;
        $this->is_enabled = $module->is_enabled;
    }

    public function update()
    {
        $this->module->update(['is_enabled' => $this->is_enabled]);
    }
}
