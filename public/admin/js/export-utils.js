/**
 * Generic CSV Export Function
 * @param {String} tableSelector - CSS selector for table
 * @param {String} filename - Name of CSV file (without extension)
 * @param {Array} excludeColumns - Column indices to exclude (e.g. [0, 7] for first and last)
 */
function exportTableToCSV(tableSelector, filename = 'export', excludeColumns = []) {
    try {
        const tableContainer = document.querySelector(tableSelector);
        if (!tableContainer) {
            alert('ไม่พบตารางข้อมูล');
            return;
        }

        const table = tableContainer.querySelector('table') || tableContainer;
        if (!table) {
            alert('ไม่พบตารางข้อมูล');
            return;
        }

        let csv = [];

        // Get headers
        const headers = [];
        table.querySelectorAll('thead th').forEach((th, index) => {
            if (!excludeColumns.includes(index)) {
                headers.push(th.textContent.trim());
            }
        });
        csv.push(headers.join(','));

        // Get rows
        table.querySelectorAll('tbody tr').forEach(tr => {
            const row = [];
            tr.querySelectorAll('td').forEach((td, index) => {
                if (!excludeColumns.includes(index)) {
                    row.push('"' + td.textContent.trim().replace(/"/g, '""') + '"');
                }
            });
            if (row.length > 0) {
                csv.push(row.join(','));
            }
        });

        // Create download link with UTF-8 BOM for Excel Thai support
        const BOM = '\uFEFF';
        const csvContent = 'data:text/csv;charset=utf-8,' + encodeURIComponent(BOM + csv.join('\n'));
        const link = document.createElement('a');
        link.setAttribute('href', csvContent);

        // Add Thai filename with date
        const dateStr = new Date().toISOString().split('T')[0];
        link.setAttribute('download', filename + '_' + dateStr + '.csv');
        link.click();
    } catch (error) {
        console.error('CSV export error:', error);
        alert('เกิดข้อผิดพลาดในการส่งออก: ' + error.message);
    }
}

/**
 * Export specific table with Thai filename
 * Usage: exportTableToCSV('.table-responsive', 'รายงาน', [7, 8])
 */
function exportAsCSV(tableClass, filename, excludeColumns = []) {
    const selector = tableClass.startsWith('.') ? tableClass : '.' + tableClass;
    exportTableToCSV(selector, filename, excludeColumns);
}
