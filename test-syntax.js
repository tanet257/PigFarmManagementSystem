const fs = require('fs');
const content = fs.readFileSync('resources/views/admin/pig_sales/index.blade.php', 'utf8');

// Extract only script content (between <script> and </script>)
const scriptMatch = content.match(/<script>[\s\S]*?<\/script>/g);

if (!scriptMatch) {
    console.log('❌ ไม่พบ <script> tag');
    process.exit(1);
}

let scriptContent = scriptMatch.join('');

// Remove Blade template syntax {{ }} and @
scriptContent = scriptContent.replace(/\{\{[\s\S]*?\}\}/g, '');  // Remove {{ }}
scriptContent = scriptContent.replace(/@[\w]+/g, ''); // Remove @ directives

const openParen = (scriptContent.match(/\(/g) || []).length;
const closeParen = (scriptContent.match(/\)/g) || []).length;
const openBracket = (scriptContent.match(/\[/g) || []).length;
const closeBracket = (scriptContent.match(/\]/g) || []).length;
const openBrace = (scriptContent.match(/\{/g) || []).length;
const closeBrace = (scriptContent.match(/\}/g) || []).length;

console.log('=== ตรวจสอบสมดุลวงเล็บ (JavaScript เท่านั้น) ===');
console.log('วงเล็บกลม ( ): ' + openParen + ' , ) : ' + closeParen + ' => ' + (openParen === closeParen ? '✅' : '❌'));
console.log('วงเล็บเหลี่ยม [ ]: ' + openBracket + ' , ] : ' + closeBracket + ' => ' + (openBracket === closeBracket ? '✅' : '❌'));
console.log('วงเล็บปีกกา { }: ' + openBrace + ' , } : ' + closeBrace + ' => ' + (openBrace === closeBrace ? '✅' : '❌'));

const allBalanced = (openParen === closeParen) && (openBracket === closeBracket) && (openBrace === closeBrace);
console.log('\n' + (allBalanced ? '✅ ทั้งหมดสมดุลแล้ว!' : '❌ ยังมีการไม่สมดุล'));
