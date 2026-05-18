<?php

use App\Providers\AppServiceProvider;
use App\Providers\RedisConfigServiceProvider;
use Modules\Ai\Providers\AiServiceProvider;
use Modules\Analytics\Providers\AnalyticsServiceProvider;
use Modules\Cms\Providers\CmsServiceProvider;
use Modules\Forms\Providers\FormsServiceProvider;
use Modules\Infra\Providers\InfraServiceProvider;
use Modules\Layout\Providers\LayoutServiceProvider;
use Modules\Library\Providers\LibraryServiceProvider;
use Modules\Media\Providers\MediaServiceProvider;
use Modules\Member\Providers\MemberServiceProvider;
use Modules\Newsletter\Providers\NewsletterServiceProvider;
use Modules\School\Providers\SchoolServiceProvider;
use Modules\Search\Providers\SearchServiceProvider;
use Modules\Security\Providers\SecurityServiceProvider;
use Modules\System\Providers\ExtensionAutoloadServiceProvider;
use Modules\System\Providers\SystemServiceProvider;

return [
    AppServiceProvider::class,
    RedisConfigServiceProvider::class,
    SystemServiceProvider::class,
    ExtensionAutoloadServiceProvider::class,
    SecurityServiceProvider::class,
    AnalyticsServiceProvider::class,
    InfraServiceProvider::class,
    AiServiceProvider::class,
    MediaServiceProvider::class,
    CmsServiceProvider::class,
    SchoolServiceProvider::class,
    LibraryServiceProvider::class,
    LayoutServiceProvider::class,
    FormsServiceProvider::class,
    MemberServiceProvider::class,
    NewsletterServiceProvider::class,
    SearchServiceProvider::class,
];
