<?php

namespace Modules\System\Contracts;

use Illuminate\Http\Request;

interface WorkspaceResolver
{
    /**
     * Resolve the active workspace ID from the request.
     */
    public function resolve(Request $request): ?int;
}
