class Utils {
    static exportToCSV(data, filename = 'export.csv') {
        if (!data || data.length === 0) {
            toast.warning('No data to export');
            return;
        }

        const headers = Object.keys(data[0]);
        const csvRows = [headers.join(',')];

        data.forEach(row => {
            const values = headers.map(header => {
                const value = row[header] || '';
                return `"${String(value).replace(/"/g, '""')}"`;
            });
            csvRows.push(values.join(','));
        });

        const csvContent = csvRows.join('\n');
        this.downloadFile(csvContent, filename, 'text/csv');
    }

    static exportToJSON(data, filename = 'export.json') {
        if (!data) {
            toast.warning('No data to export');
            return;
        }

        const jsonContent = JSON.stringify(data, null, 2);
        this.downloadFile(jsonContent, filename, 'application/json');
    }

    static downloadFile(content, filename, mimeType) {
        const blob = new Blob([content], { type: mimeType });
        const url = URL.createObjectURL(blob);
        const link = $('<a></a>')
            .attr('href', url)
            .attr('download', filename)
            .css('display', 'none');

        $('body').append(link);
        link[0].click();
        link.remove();
        URL.revokeObjectURL(url);

        toast.success(`File downloaded: ${filename}`);
    }

    static validatePhone(phone) {
        const pattern = /^\+?[\d\s\-()]{10,}$/;
        return pattern.test(phone);
    }

    static validateEmail(email) {
        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return pattern.test(email);
    }

    static validateAge(age) {
        const numAge = parseInt(age);
        return !isNaN(numAge) && numAge >= 0 && numAge <= 120;
    }

    static debounce(func, wait = 300) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    static formatDate(date) {
        if (!(date instanceof Date)) {
            date = new Date(date);
        }
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    static showLoading(target = 'body') {
        const spinner = $(`
            <div class="loading-overlay">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        $(target).css('position', 'relative').append(spinner);
    }

    static hideLoading(target = 'body') {
        $(target).find('.loading-overlay').remove();
    }
}
