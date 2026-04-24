import ui from './ui.json';
import options from './options.json';
import labels from './labels.json';
import descriptions from './descriptions.json';
import email from './email.json';

/**
 * Settings translations (English)
 * 
 * Split files:
 * - ui.json: tabs, groups, general UI
 * - options.json: select options (timezone, date format, etc.)
 * - labels.json: field labels
 * - descriptions.json: field descriptions
 * - email.json: email test, cache, AI settings
 */
export default {
    ...ui,
    ...options,
    ...labels,
    ...descriptions,
    ...email
};
