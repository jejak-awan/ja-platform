import academic from './school/academic.json';
import admission from './school/admission.json';

import hr from './school/hr.json';
import ops from './school/ops.json';

import logistics from './school/logistics.json';
import institution from './school/institution.json';
import levels from './school/levels.json';
import audit from './school/audit.json';
import dashboard from './school/dashboard.json';
import students from './school/students.json';
import misc from './school/misc.json';
import wizard from './school/wizard.json';
import extensions from './school/extensions.json';
import osis from './school/osis.json';
import teacher_dashboard from './school/teacher_dashboard.json';

export default {
    ...misc,
    academic,
    admission,

    hr,
    ops,
    operations: ops,

    logistics,
    institution,
    levels,
    audit,
    dashboard,
    students,
    wizard,
    extensions,
    osis,
    teacher_dashboard,
};
