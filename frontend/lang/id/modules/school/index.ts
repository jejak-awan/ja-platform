import academic from './academic.json';
import admission from './admission.json';
import hr from './hr.json';
import ops from './ops.json';
import logistics from './logistics.json';
import institution from './institution.json';
import units from './units.json';
import audit from './audit.json';
import dashboard from './dashboard.json';
import students from './students.json';
import misc from './misc.json';
import wizard from './wizard.json';
import extensions from './extensions.json';
import graduation from './graduation.json';
import osis from './osis.json';
import teacher_dashboard from './teacher_dashboard.json';
import lms from './lms.json';

export default {
    ...misc,
    academic,
    admission,
    hr,
    ops,
    operations: ops,
    logistics,
    institution,
    units,
    levels: units,
    audit,
    dashboard,
    students,
    wizard,
    extensions,
    osis,
    teacher_dashboard,
    graduation,
    lms,
};
