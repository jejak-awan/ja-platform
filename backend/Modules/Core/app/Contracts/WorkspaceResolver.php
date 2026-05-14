<?php

namespace Modules\Core\Contracts;

use Illuminate\Http\Request;

interface WorkspaceResolver
{
    /**
     * Resolve the active workspace ID for the given request.
     *
     * @param Request $request
     * @return int|null
     */
    public function resolve(Request $request): ?int;
}
