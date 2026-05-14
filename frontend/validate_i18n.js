import fs from 'fs';
import glob from 'glob';

const files = glob.sync('src/locales/**/*.json');

files.forEach(file => {
    const data = JSON.parse(fs.readFileSync(file, 'utf8'));
    
    function checkObj(obj, path) {
        for (const [key, value] of Object.entries(obj)) {
            const currentPath = path ? `${path}.${key}` : key;
            if (typeof value === 'object' && value !== null) {
                checkObj(value, currentPath);
            } else if (typeof value === 'string') {
                // Check for unclosed braces or invalid linked formats
                let openBraces = (value.match(/\{/g) || []).length;
                let closeBraces = (value.match(/\}/g) || []).length;
                if (openBraces !== closeBraces) {
                    console.error(`ERROR: Unmatched braces in ${file} at key '${currentPath}': "${value}"`);
                }
                
                // Check for trailing @: without a target
                if (value.includes('@:') && !value.match(/@:[\w\.]+/)) {
                    console.error(`ERROR: Invalid linked message in ${file} at key '${currentPath}': "${value}"`);
                }
            }
        }
    }
    
    checkObj(data, '');
});

console.log('Validation complete.');
