// Modules
import core from './modules/core/index';
import cms from './modules/cms/index';
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
import genders from './shared/genders.json';

// Features (Remaining cross-cutting concerns)
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
    genders,
};

const modules = {
    core,
    cms,
    school,
};

const features = {
    languages,
    dashboard,
    search,
    frontend,
    profile,
    security,
    redis,
    developer,
    widgets,
    redirects,
    activityJournal,
    accessJournal,
    journalDashboard,
    content_templates,
    content_studio,
    autosave,
    security_alerts: securityAlerts,
    theme_customizer,
    scheduled_tasks,
    command_runner,
    email_templates,
};

export default {
    // 1. Shared / Common Level (Standardized Utilities)
    common,
    shared: common,

    // 2. Modular Level (Strictly Isolated)
    modules,
    
    // 3. Feature Namespace (Cross-cutting concerns)
    features,
};
