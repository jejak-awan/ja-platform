<?php

namespace Modules\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Models\Plugin;

class PluginDeactivated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Plugin $plugin)
    {
        //
    }
}
