/**
 * Dashboard Manager - Read-only (Server-Rendered)
 * All stats, charts, and recent activity are rendered
 * server-side by Blade. No background API calls needed.
 */
if (typeof DashboardManager === 'undefined') {
    class DashboardManager {
        constructor() {
            this.activeFilter = 'all';
        }

        init() {
            this.bindEvents();
        }

        bindEvents() {
            // Time filter buttons — reload page with filter param
            $(document).on('click', '.filter-btn', (e) => {
                const filter = $(e.currentTarget).data('filter');
                const url = new URL(window.location.href);
                if (filter && filter !== 'all') {
                    url.searchParams.set('filter', filter);
                } else {
                    url.searchParams.delete('filter');
                }
                window.location.href = url.toString();
            });
        }
    }

    window.dashboardManager = new DashboardManager();
    $(document).ready(() => {
        if ($('#dashboard-greeting').length === 0) return;
        window.dashboardManager.init();
    });
}
