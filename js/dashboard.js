/**
 * Dashboard Manager
 * Handles dashboard stats, charts, and time filtering
 */
if (typeof DashboardManager === 'undefined') {
    class DashboardManager {
        constructor() {
            this.activeFilter = 'all';
            this.statusChart = null;
            this.weeklyChart = null;
        }

        init() {
            this.bindEvents();
            this.updateStats();
            this.initCharts();
            this.renderRecentActivity();
        }

        bindEvents() {
            // Time filter buttons
            $(document).on('click', '.filter-btn', (e) => {
                $('.filter-btn').removeClass('active');
                $(e.currentTarget).addClass('active');
                this.activeFilter = $(e.currentTarget).data('filter');
                this.updateStats();
                this.updateCharts();
                this.renderRecentActivity();
            });

            // Layout loaded event
            $(document).on('layout-loaded', () => {
                if ($('.dashboard-content').length) {
                    this.updateStats();
                    this.initCharts();
                    this.renderRecentActivity();
                }
            });
        }

        getPatients() {
            return JSON.parse(localStorage.getItem('clinic_patients')) || [];
        }

        getDoctors() {
            return JSON.parse(localStorage.getItem('clinic_doctors')) || [];
        }

        getAppointments() {
            return JSON.parse(localStorage.getItem('clinic_appointments')) || [];
        }

        getFilteredAppointments() {
            const appointments = this.getAppointments();
            const now = new Date();
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            const weekAgo = new Date(today);
            weekAgo.setDate(weekAgo.getDate() - 7);

            switch (this.activeFilter) {
                case 'today':
                    return appointments.filter(a => {
                        const date = new Date(a.date);
                        return date.toDateString() === today.toDateString();
                    });
                case 'week':
                    return appointments.filter(a => {
                        const date = new Date(a.date);
                        return date >= weekAgo && date <= now;
                    });
                default:
                    return appointments;
            }
        }

        updateStats() {
            const patients = this.getPatients();
            const doctors = this.getDoctors();
            const appointments = this.getFilteredAppointments();
            const allAppointments = this.getAppointments();

            // Today's appointments count
            const today = new Date();
            const todayAppts = allAppointments.filter(a => {
                const date = new Date(a.date);
                return date.toDateString() === today.toDateString();
            });

            // Animate numbers
            this.animateNumber('#totalPatients', patients.length);
            this.animateNumber('#totalDoctors', doctors.length);
            this.animateNumber('#totalAppointments', appointments.length);
            this.animateNumber('#totalAppointments2', appointments.length);
            this.animateNumber('#todayAppointments', todayAppts.length);

            // Status counts
            const pending = appointments.filter(a => a.status === 'pending').length;
            const confirmed = appointments.filter(a => a.status === 'confirmed').length;
            const completed = appointments.filter(a => a.status === 'completed').length;
            const cancelled = appointments.filter(a => a.status === 'cancelled').length;

            this.animateNumber('#pendingCount', pending);
            this.animateNumber('#confirmedCount', confirmed);
            this.animateNumber('#completedCount', completed);
            this.animateNumber('#cancelledCount', cancelled);
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

        initCharts() {
            this.initStatusChart();
            this.initWeeklyChart();
        }

        updateCharts() {
            this.updateStatusChart();
            this.updateWeeklyChart();
        }

        initStatusChart() {
            const ctx = document.getElementById('statusChart');
            if (!ctx) return;

            const appointments = this.getFilteredAppointments();
            const data = this.getStatusData(appointments);

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
                            'rgba(255, 193, 7, 0.8)',   // Warning - Pending
                            'rgba(13, 202, 240, 0.8)', // Info - Confirmed
                            'rgba(25, 135, 84, 0.8)',  // Success - Completed
                            'rgba(220, 53, 69, 0.8)'   // Danger - Cancelled
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
                        legend: {
                            display: false
                        },
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

        updateStatusChart() {
            if (!this.statusChart) {
                this.initStatusChart();
                return;
            }

            const appointments = this.getFilteredAppointments();
            const data = this.getStatusData(appointments);
            this.statusChart.data.datasets[0].data = data;
            this.statusChart.update('active');
        }

        getStatusData(appointments) {
            return [
                appointments.filter(a => a.status === 'pending').length,
                appointments.filter(a => a.status === 'confirmed').length,
                appointments.filter(a => a.status === 'completed').length,
                appointments.filter(a => a.status === 'cancelled').length
            ];
        }

        initWeeklyChart() {
            const ctx = document.getElementById('weeklyChart');
            if (!ctx) return;

            const data = this.getWeeklyData();

            if (this.weeklyChart) {
                this.weeklyChart.destroy();
            }

            this.weeklyChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Appointments',
                        data: data.values,
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
                        legend: {
                            display: false
                        },
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
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: { size: 12 }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                stepSize: 1,
                                font: { size: 12 }
                            }
                        }
                    }
                }
            });
        }

        updateWeeklyChart() {
            if (!this.weeklyChart) {
                this.initWeeklyChart();
                return;
            }

            const data = this.getWeeklyData();
            this.weeklyChart.data.labels = data.labels;
            this.weeklyChart.data.datasets[0].data = data.values;
            this.weeklyChart.update('active');
        }

        getWeeklyData() {
            const appointments = this.getAppointments();
            const days = [];
            const counts = [];
            const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

            // Get language for day names
            const lang = (typeof app !== 'undefined' && app.lang) ? app.lang : 'en';
            const dayTranslations = {
                en: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                ar: ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت']
            };
            const localDayNames = dayTranslations[lang] || dayTranslations.en;

            for (let i = 6; i >= 0; i--) {
                const date = new Date();
                date.setDate(date.getDate() - i);
                const dateStr = date.toISOString().split('T')[0];
                
                days.push(localDayNames[date.getDay()]);
                counts.push(appointments.filter(a => a.date === dateStr).length);
            }

            return { labels: days, values: counts };
        }

        renderRecentActivity() {
            const $container = $('#recentAppointments');
            if (!$container.length) return;

            const appointments = this.getFilteredAppointments();
            const patients = this.getPatients();
            const doctors = this.getDoctors();

            // Get language
            const lang = (typeof app !== 'undefined' && app.lang) ? app.lang : 'en';
            const t = (key) => {
                if (typeof translations !== 'undefined' && translations[lang] && translations[lang][key]) {
                    return translations[lang][key];
                }
                return key;
            };

            // Sort by date descending and get latest 5
            const recent = [...appointments]
                .sort((a, b) => new Date(b.date + ' ' + b.time) - new Date(a.date + ' ' + a.time))
                .slice(0, 5);

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
                const patient = patients.find(p => p.id === appt.patientId) || { name: t('unknownPatient') };
                const doctor = doctors.find(d => d.id === appt.doctorId) || { name: t('unknownDoctor') };
                const statusColor = statusColors[appt.status] || 'secondary';
                const statusIcon = statusIcons[appt.status] || 'fa-calendar';

                return `
                    <div class="activity-item">
                        <div class="activity-icon bg-${statusColor}-subtle text-${statusColor}">
                            <i class="fas ${statusIcon}"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-header">
                                <span class="activity-title">${patient.name}</span>
                                <span class="activity-time">${appt.date} • ${appt.time}</span>
                            </div>
                            <div class="activity-details">
                                <span class="activity-doctor">
                                    <i class="fas fa-user-md me-1"></i>${doctor.name}
                                </span>
                                <span class="badge bg-${statusColor}-subtle text-${statusColor}" data-i18n="${appt.status}">${t(appt.status)}</span>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            $container.html(html);

            // Apply language translations
            if (window.app && window.app.applyLanguage) {
                window.app.applyLanguage(window.app.lang);
            }
        }
    }

    // Create instance and initialize
    window.dashboardManager = new DashboardManager();
    $(document).ready(() => {
        window.dashboardManager.init();
    });
} else {
    // Class already exists, just re-initialize
    if (window.dashboardManager) {
        $(document).ready(() => {
            window.dashboardManager.init();
        });
    }
}
