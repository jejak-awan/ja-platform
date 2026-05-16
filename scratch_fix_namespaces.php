<?php

$searchReplace = [
    'Modules\System\Models\SecurityLog' => 'Modules\Security\Models\SecurityLog',
    'Modules\System\Models\DependencyVulnerability' => 'Modules\Security\Models\DependencyVulnerability',
    'Modules\System\Models\IpList' => 'Modules\Security\Models\IpList',
    'Modules\Infra\Models\User' => 'Modules\System\Models\User',
    'Modules\Security\Models\User' => 'Modules\System\Models\User',
    'Modules\Analytics\Models\User' => 'Modules\System\Models\User',
    'Modules\Search\Models\User' => 'Modules\System\Models\User',
    'Modules\System\Http\Controllers\Console\BaseApiController' => 'Modules\System\Http\Controllers\BaseApiController',
    'Modules\Security\Http\Controllers\BaseApiController' => 'Modules\System\Http\Controllers\BaseApiController',
    'Modules\Analytics\Http\Controllers\BaseApiController' => 'Modules\System\Http\Controllers\BaseApiController',
    'Modules\System\Services\BackupService' => 'Modules\Infra\Services\BackupService',
    'Modules\Infra\Models\Media' => 'Modules\Media\Models\File',
    'Modules\System\Rules\SafeUrl' => 'Modules\Security\Rules\SafeUrl',
    'Modules\System\Rules\StrongPassword' => 'Modules\Security\Rules\StrongPassword',
    'Modules\Cms\Models\Theme' => 'Modules\Layout\Models\Theme',
    'Modules\Cms\Support\ThemeViews' => 'Modules\Layout\Support\ThemeViews',
    'Modules\Cms\Models\Menu' => 'Modules\Layout\Models\Menu',
    'Modules\Cms\Models\Redirect' => 'Modules\Layout\Models\UrlRewrite',
    'Modules\Security\Services\SecurityNotificationService' => 'Modules\Security\Services\SecurityNotificationService', // Wait, this one was just missing?
    'Modules\Ai\Services\Ai\OpenAiService' => 'Modules\Ai\Services\Providers\OpenAiService',
    'Modules\Ai\Services\Ai\DeepSeekService' => 'Modules\Ai\Services\Providers\DeepSeekService',
    'Modules\Ai\Services\Ai\GeminiService' => 'Modules\Ai\Services\Providers\GeminiService',
    'Modules\Ai\Services\Ai\AiProviderFactory' => 'Modules\Ai\Services\AiProviderFactory',
];

$dir = new RecursiveDirectoryIterator("backend/Modules");
$ite = new RecursiveIteratorIterator($dir);
foreach($ite as $file) {
    if ($file->isFile() && $file->getExtension() === "php") {
        $content = file_get_contents($file->getPathname());
        $changed = false;
        foreach ($searchReplace as $search => $replace) {
            if ($search === $replace) continue;
            if (strpos($content, $search) !== false) {
                $content = str_replace($search, $replace, $content);
                $changed = true;
            }
        }
        if ($changed) {
            file_put_contents($file->getPathname(), $content);
            echo "Updated " . $file->getPathname() . "\n";
        }
    }
}
