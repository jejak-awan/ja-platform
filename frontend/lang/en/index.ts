// Modules - Core
import auth from './modules/core/auth.json';
import users from './modules/core/users.json';
import roles from './modules/core/roles.json';
import media from './modules/core/media.json';
import file_manager from './modules/core/file_manager.json';
import analytics from './modules/core/analytics.json';
import notifications from './modules/core/notifications.json';
import system from './modules/core/system.json';
import settings from './modules/core/settings';

// Modules - CMS
import content from './modules/cms/content.json';
import categories from './modules/cms/categories.json';
import tags from './modules/cms/tags.json';
import comments from './modules/cms/comments.json';
import forms from './modules/cms/forms.json';
import menus from './modules/cms/menus.json';
import themes from './modules/cms/themes.json';
import seo from './modules/cms/seo.json';
import editor from './modules/cms/editor.json';
import newsletter from './modules/cms/newsletter.json';

// Modules - School
import school from './modules/school/index';

// Shared / Common
import actions from './shared/actions.json';
import labels from './shared/labels.json';
import validation from './shared/validation.json';
import messages from './shared/messages.json';
import navigation from './shared/navigation.json';
import placeholders from './shared/placeholders.json';
import pagination from './shared/pagination.json';
import status from './shared/status.json';
import time from './shared/time.json';
import errors from './shared/errors.json';

// Misc Features (Remaining)
import languages from './features/languages.json';
import dashboard from './features/dashboard.json';
import security from './features/security.json';
import redis from './features/redis.json';
import developer from './features/developer.json';
import widgets from './features/widgets.json';
import redirects from './features/redirects.json';
import activityJournal from './features/activity_journal.json';
import accessJournal from './features/access_journal.json';
import journalDashboard from './features/journal_dashboard.json';
import search from './features/search.json';
import frontend from './features/frontend.json';
import content_templates from './features/content_templates.json';
import profile from './features/profile.json';
import content_studio from './features/content_studio.json';
import autosave from './features/autosave.json';
import securityAlerts from './features/security_alerts.json';
import theme_customizer from './features/theme_customizer.json';
import scheduled_tasks from './features/scheduled_tasks.json';
import command_runner from './features/command_runner.json';
import email_templates from './features/email_templates.json';

const modules = {
    core: {
        auth,
        users,
        roles,
        media,
        file_manager,
        analytics,
        notifications,
        system,
        settings,
    },
    cms: {
        content,
        categories,
        tags,
        comments,
        forms,
        menus,
        themes,
        seo,
        editor,
        newsletter,
    },
    school,
};

const common = {
    actions,
    labels,
    validation,
    messages,
    navigation,
    placeholders,
    pagination,
    status,
    time,
    errors,
};

const features = {
    ...modules.core,
    ...modules.cms,
    school: modules.school,
    // Add legacy direct feature names
    auth,
    content,
    comments,
    languages,
    dashboard,
    file_manager,
    newsletter,
    notifications,
    users,
    widgets,
    analytics,
    seo,
    redirects,
    security,
    redis,
    settings,
    categories,
    tags,
    roles,
    themes,
    forms,
    menus,
    activityJournal,
    accessJournal,
    journalDashboard,
    search,
    frontend,
    errors,
    developer,
    content_templates,
    system,
    profile,
    content_studio,
    autosave,
    security_alerts: securityAlerts,
    theme_customizer,
    scheduled_tasks,
    command_runner,
    email_templates,
    editor,
    media,
};

export default {
    // 1. Shared / Common Level
    ...common,
    shared: common,
    common: common,

    // 2. Modular Level
    modules,
    
    // 3. School Module Flattened (for $t('academic...'))
    ...school,

    // 4. Feature Alias Level (Backward Compatibility)
    features,
};
