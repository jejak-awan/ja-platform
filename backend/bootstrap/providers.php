<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\RedisConfigServiceProvider::class,
    Modules\System\Providers\SystemServiceProvider::class,
    Modules\Security\Providers\SecurityServiceProvider::class,
    Modules\Analytics\Providers\AnalyticsServiceProvider::class,
    Modules\Infra\Providers\InfraServiceProvider::class,
    Modules\Ai\Providers\AiServiceProvider::class,
    Modules\Media\Providers\MediaServiceProvider::class,
    Modules\Cms\Providers\CmsServiceProvider::class,
    Modules\School\Providers\SchoolServiceProvider::class,
    Modules\Library\Providers\LibraryServiceProvider::class,
    Modules\Layout\Providers\LayoutServiceProvider::class,
    Modules\Forms\Providers\FormsServiceProvider::class,
    Modules\Member\Providers\MemberServiceProvider::class,
    Modules\Newsletter\Providers\NewsletterServiceProvider::class,
    Modules\Search\Providers\SearchServiceProvider::class,
];
