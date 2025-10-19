const fs = require('fs');
const content = fs.readFileSync('resources/views/admin/pig_sales/index.blade.php', 'utf8');

// Extract only script content
const scriptMatch = content.match(/<script>[\s\S]*?<\/script>/g);
let scriptContent = scriptMatch.join('');

// Remove Blade template syntax
scriptContent = scriptContent.replace(/\{\{[\s\S]*?\}\}/g, '');
scriptContent = scriptContent.replace(/@[\w]+/g, '');

// Track line by line
const lines = scriptContent.split('\n');
let parenLevel = 0;
let braceLevel = 0;
let bracketLevel = 0;
let issues = [];

lines.forEach((line, idx) => {
    const lineNum = idx + 1;

    for (let char of line) {
        if (char === '(') parenLevel++;
        if (char === ')') parenLevel--;
        if (char === '{') braceLevel++;
        if (char === '}') braceLevel--;
        if (char === '[') bracketLevel++;
        if (char === ']') bracketLevel--;
    }

    if (parenLevel < 0 || braceLevel < 0 || bracketLevel < 0) {
        if (issues.length < 3) {
            issues.push(`บรรทัด ${lineNum}: ${line.trim()}`);
        }
    }
});

console.log('=== ตำแหน่งปัญหา ===');
console.log(`สุดท้าย: (=${parenLevel}, { =${braceLevel}, [ =${bracketLevel}`);

if (issues.length > 0) {
    console.log('\nปัญหาที่พบ (3 อันแรก):');
    issues.forEach(issue => console.log(issue));
}
