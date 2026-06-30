class Utils {
    static t(key) {
        const lang = (typeof app !== 'undefined' && app.lang) ? app.lang : 'en';
        if (typeof translations !== 'undefined' && translations[lang] && translations[lang][key]) {
            return translations[lang][key];
        }
        return key;
    }

    static exportToCSV(data, filename = 'export.csv') {
        if (!data || data.length === 0) {
            toast.warning(Utils.t('noDataExport'));
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
            toast.warning(Utils.t('noDataExport'));
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

        toast.success(`${Utils.t('fileDownloaded')}: ${filename}`);
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
                    <span class="visually-hidden">${Utils.t('loading')}</span>
                </div>
            </div>
        `);
        $(target).css('position', 'relative').append(spinner);
    }

    static hideLoading(target = 'body') {
        $(target).find('.loading-overlay').remove();
    }

    static getChartColors() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const s = (v) => getComputedStyle(document.documentElement).getPropertyValue(v).trim();
        const secondary = s('--secondary');
        const success = s('--success');
        const info = s('--info');
        const warning = s('--warning');
        const danger = s('--danger');
        return {
            grid: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)',
            tick: isDark ? s('--text-secondary') : '#888888',
            border: isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)',
            tooltipBg: isDark ? s('--white') : '#ffffff',
            tooltipText: isDark ? s('--text-primary') : '#2c2c2c',
            tooltipBorder: isDark ? 'rgba(42,168,138,0.2)' : 'rgba(0,0,0,0.1)',
            pointBg: isDark ? secondary : '#2aa88a',
            pointBorder: isDark ? s('--body-bg') : '#ffffff',
            fillGradient: isDark ? (secondary ? secondary + '20' : 'rgba(42,168,138,0.08)') : 'rgba(42,168,138,0.08)',
            lineColor: secondary || '#2aa88a',
            pending: isDark ? (warning || '#b08a4a') : 'rgba(176,138,74,0.85)',
            confirmed: isDark ? (success || '#4a8a6a') : 'rgba(74,138,106,0.85)',
            completed: isDark ? (info || '#4a7a9a') : 'rgba(74,122,154,0.85)',
            cancelled: isDark ? (danger || '#b04a4a') : 'rgba(176,74,74,0.85)',
            chartBar: isDark ? secondary : '#2aa88a',
            chartBarHover: isDark ? s('--secondary-light') : '#3bc9a0',
            doughnutColors: [
                secondary || '#2aa88a',
                success || '#4a8a6a',
                info || '#4a7a9a',
                warning || '#b08a4a',
                danger || '#b04a4a'
            ]
        };
    }
}
