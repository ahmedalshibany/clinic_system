/**
 * Dashboard Manager - Using API Backend
 * Handles dashboard stats, charts, and time filtering
 */
if (typeof DashboardManager === 'undefined') {
    class DashboardManager {
        constructor() {
            this.activeFilter = 'all';
            this.statusChart = null;
            this.weeklyChart = null;
            this.stats = {
                totalPatients: 0,
                totalDoctors: 0,
                totalAppointments: 0,
                todayAppointments: 0,
                pending: 0,
                confirmed: 0,
                completed: 0,
                cancelled: 0
            };
        }

        async init() {
            this.bindEvents();
            await this.loadStats();
            await this.initCharts();
            await this.renderRecentActivity();
        }

        bindEvents() {
            // Time filter buttons
            $(document).on('click', '.filter-btn', async (e) => {
                $('.filter-btn').removeClass('active');
                $(e.currentTarget).addClass('active');
                this.activeFilter = $(e.currentTarget).data('filter');
                await this.loadStats();
                await this.updateCharts();
                await this.renderRecentActivity();
            });
        }

        async loadStats() {
            try {
                const response = await API.dashboard.stats(this.activeFilter);
                if (response.success) {
                    this.stats = response.data;
                    this.updateStatsDisplay();
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        updateStatsDisplay() {
            this.animateNumber('#totalPatients', this.stats.totalPatients);
            this.animateNumber('#totalDoctors', this.stats.totalDoctors);
            this.animateNumber('#totalAppointments', this.stats.totalAppointments);
            this.animateNumber('#totalAppointments2', this.stats.totalAppointments);
            this.animateNumber('#todayAppointments', this.stats.todayAppointments);

            this.animateNumber('#pendingCount', this.stats.pending);
            this.animateNumber('#confirmedCount', this.stats.confirmed);
            this.animateNumber('#completedCount', this.stats.completed);
            this.animateNumber('#cancelledCount', this.stats.cancelled);
        }

        animateNumber(selector, target) {
            const $el = $(selector);
            if (!$el.length) return;

            const current = parseInt($el.text()) || 0;
            const duration = 500;
            const stepTime = 20;
            const steps = duration / stepTime;
            const increment = (target - current) / steps;

            let step = 0;
            const timer = setInterval(() => {
                step++;
                const value = Math.round(current + (increment * step));
                $el.text(value);
                if (step >= steps) {
                    $el.text(target);
                    clearInterval(timer);
                }
            }, stepTime);
        }

        async initCharts() {
            await this.initStatusChart();
            await this.initWeeklyChart();
        }

        async updateCharts() {
            await this.updateStatusChart();
            await this.updateWeeklyChart();
        }

        async initStatusChart() {
            const ctx = document.getElementById('statusChart');
            if (!ctx) return;

            const data = [
                this.stats.pending,
                this.stats.confirmed,
                this.stats.completed,
                this.stats.cancelled
            ];

            if (this.statusChart) {
                this.statusChart.destroy();
            }

            this.statusChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'Confirmed', 'Completed', 'Cancelled'],
                    datasets: [{
                        data: data,
                        backgroundColor: [
                            'rgba(255, 193, 7, 0.8)',
                            'rgba(13, 202, 240, 0.8)',
                            'rgba(25, 135, 84, 0.8)',
                            'rgba(220, 53, 69, 0.8)'
                        ],
                        borderColor: [
                            'rgba(255, 193, 7, 1)',
                            'rgba(13, 202, 240, 1)',
                            'rgba(25, 135, 84, 1)',
                            'rgba(220, 53, 69, 1)'
                        ],
                        borderWidth: 2,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            cornerRadius: 8
                        }
                    }
                }
            });
        }

        async updateStatusChart() {
            try {
                const response = await API.dashboard.statusDistribution(this.activeFilter);
                if (response.success && this.statusChart) {
                    const data = [
                        response.data.pending,
                        response.data.confirmed,
                        response.data.completed,
                        response.data.cancelled
                    ];
                    this.statusChart.data.datasets[0].data = data;
                    this.statusChart.update('active');
                }
            } catch (error) {
                console.error('Error updating status chart:', error);
            }
        }

        async initWeeklyChart() {
            const ctx = document.getElementById('weeklyChart');
            if (!ctx) return;

            try {
                const response = await API.dashboard.weeklyTrend();
                if (!response.success) return;

                const { labels, data } = response.data;

                if (this.weeklyChart) {
                    this.weeklyChart.destroy();
                }

                this.weeklyChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Appointments',
                            data: data,
                            fill: true,
                            backgroundColor: 'rgba(47, 65, 86, 0.1)',
                            borderColor: 'rgba(47, 65, 86, 0.8)',
                            borderWidth: 3,
                            tension: 0.4,
                            pointBackgroundColor: 'rgba(47, 65, 86, 1)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleFont: { size: 14, weight: 'bold' },
                                bodyFont: { size: 13 },
                                cornerRadius: 8
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 12 } }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0, 0, 0, 0.05)' },
                                ticks: { stepSize: 1, font: { size: 12 } }
                            }
                        }
                    }
                });
            } catch (error) {
                console.error('Error initializing weekly chart:', error);
            }
        }

        async updateWeeklyChart() {
            try {
                const response = await API.dashboard.weeklyTrend();
                if (response.success && this.weeklyChart) {
                    this.weeklyChart.data.labels = response.data.labels;
                    this.weeklyChart.data.datasets[0].data = response.data.data;
                    this.weeklyChart.update('active');
                }
            } catch (error) {
                console.error('Error updating weekly chart:', error);
            }
        }

        async renderRecentActivity() {
            const $container = $('#recentAppointments');
            if (!$container.length) return;

            const lang = (typeof app !== 'undefined' && app.lang) ? app.lang : 'en';
            const t = (key) => {
                if (typeof translations !== 'undefined' && translations[lang] && translations[lang][key]) {
                    return translations[lang][key];
                }
                return key;
            };

            try {
                const response = await API.dashboard.recentAppointments(5);
                if (!response.success) return;

                const recent = response.data;

                if (recent.length === 0) {
                    $container.html(`
                        <div class="empty-activity text-center py-4">
                            <i class="fas fa-calendar-day text-muted mb-3" style="font-size: 2.5rem;"></i>
                            <p class="text-muted mb-0" data-i18n="noAppointments">${t('noAppointments')}</p>
                        </div>
                    `);
                    return;
                }

                const statusColors = {
                    pending: 'warning',
                    confirmed: 'info',
                    completed: 'success',
                    cancelled: 'danger'
                };

                const statusIcons = {
                    pending: 'fa-clock',
                    confirmed: 'fa-check-circle',
                    completed: 'fa-check-double',
                    cancelled: 'fa-times-circle'
                };

                const html = recent.map(appt => {
                    const statusColor = statusColors[appt.status] || 'secondary';
                    const statusIcon = statusIcons[appt.status] || 'fa-calendar';

                    return `
                        <div class="activity-item">
                            <div class="activity-icon bg-${statusColor}-subtle text-${statusColor}">
                                <i class="fas ${statusIcon}"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-header">
                                    <span class="activity-title">${appt.patientName}</span>
                                    <span class="activity-time">${appt.date} • ${appt.time}</span>
                                </div>
                                <div class="activity-details">
                                    <span class="activity-doctor">
                                        <i class="fas fa-user-md me-1"></i>${appt.doctorName}
                                    </span>
                                    <span class="badge bg-${statusColor}-subtle text-${statusColor}" data-i18n="${appt.status}">${t(appt.status)}</span>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');

                $container.html(html);

                if (window.app && window.app.applyLanguage) {
                    window.app.applyLanguage(window.app.lang);
                }
            } catch (error) {
                console.error('Error loading recent activity:', error);
            }
        }
    }

    window.dashboardManager = new DashboardManager();
    $(document).ready(() => {
        if (typeof API !== 'undefined') {
            window.dashboardManager.init();
        } else {
            const checkAPI = setInterval(() => {
                if (typeof API !== 'undefined') {
                    clearInterval(checkAPI);
                    window.dashboardManager.init();
                }
            }, 100);
        }
    });
}
