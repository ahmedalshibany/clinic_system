if (typeof DashboardManager === 'undefined') {
    class DashboardManager {
        constructor() {
            this.patients = JSON.parse(localStorage.getItem('clinic_patients')) || [];
            this.doctors = JSON.parse(localStorage.getItem('clinic_doctors')) || [];
            this.appointments = JSON.parse(localStorage.getItem('clinic_appointments')) || [];
            this.currentFilter = 'all'; // all, week, today
        }

        init() {
            this.refreshData();
            this.bindFilterEvents();
            this.applyFilter(this.currentFilter);
        }

        refreshData() {
            this.patients = JSON.parse(localStorage.getItem('clinic_patients')) || [];
            this.doctors = JSON.parse(localStorage.getItem('clinic_doctors')) || [];
            this.appointments = JSON.parse(localStorage.getItem('clinic_appointments')) || [];
        }

        bindFilterEvents() {
            const self = this;
            $('.filter-btn').on('click', function () {
                const filter = $(this).data('filter');
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');
                self.applyFilter(filter);
            });
        }

        applyFilter(filter) {
            this.currentFilter = filter;
            this.renderStats();
            this.renderRecentAppointments();
            this.renderCharts();
        }

        // Get date ranges based on filter
        getDateRange() {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const todayStr = today.toISOString().split('T')[0];

            // Start of week (Sunday)
            const weekStart = new Date(today);
            weekStart.setDate(today.getDate() - today.getDay());
            const weekStartStr = weekStart.toISOString().split('T')[0];

            return { todayStr, weekStartStr };
        }

        // Filter appointments based on current filter
        getFilteredAppointments() {
            const { todayStr, weekStartStr } = this.getDateRange();

            switch (this.currentFilter) {
                case 'today':
                    return this.appointments.filter(a => a.date === todayStr);
                case 'week':
                    return this.appointments.filter(a => a.date >= weekStartStr);
                case 'all':
                default:
                    return this.appointments;
            }
        }

        // Filter patients based on registration date (if available) or return all
        getFilteredPatients() {
            const { todayStr, weekStartStr } = this.getDateRange();

            // If patients have registeredAt field, filter by it
            const patientsWithDate = this.patients.filter(p => p.registeredAt);

            if (patientsWithDate.length === 0) {
                // No registration dates, just return all for 'all' filter or 0 for others
                if (this.currentFilter === 'all') return this.patients;
                return [];
            }

            switch (this.currentFilter) {
                case 'today':
                    return this.patients.filter(p => p.registeredAt && p.registeredAt.startsWith(todayStr));
                case 'week':
                    return this.patients.filter(p => p.registeredAt && p.registeredAt >= weekStartStr);
                case 'all':
                default:
                    return this.patients;
            }
        }

        // Filter doctors based on registration date (if available) or return all
        getFilteredDoctors() {
            const { todayStr, weekStartStr } = this.getDateRange();

            const doctorsWithDate = this.doctors.filter(d => d.registeredAt);

            if (doctorsWithDate.length === 0) {
                if (this.currentFilter === 'all') return this.doctors;
                return [];
            }

            switch (this.currentFilter) {
                case 'today':
                    return this.doctors.filter(d => d.registeredAt && d.registeredAt.startsWith(todayStr));
                case 'week':
                    return this.doctors.filter(d => d.registeredAt && d.registeredAt >= weekStartStr);
                case 'all':
                default:
                    return this.doctors;
            }
        }

        getTodayAppointments() {
            const today = new Date().toISOString().split('T')[0];
            return this.getFilteredAppointments().filter(a => a.date === today);
        }

        getAppointmentsByStatus() {
            const stats = { pending: 0, confirmed: 0, completed: 0, cancelled: 0 };
            this.getFilteredAppointments().forEach(a => {
                if (stats.hasOwnProperty(a.status)) {
                    stats[a.status]++;
                }
            });
            return stats;
        }

        getWeeklyAppointments() {
            const days = [];
            const counts = [];
            const today = new Date();
            const filteredAppts = this.getFilteredAppointments();

            // Check current language
            const isArabic = document.documentElement.getAttribute('lang') === 'ar';
            const locale = isArabic ? 'ar-SA' : 'en-US';

            for (let i = 6; i >= 0; i--) {
                const date = new Date(today);
                date.setDate(date.getDate() - i);
                const dateStr = date.toISOString().split('T')[0];
                const dayName = date.toLocaleDateString(locale, { weekday: 'short' });

                days.push(dayName);
                counts.push(filteredAppts.filter(a => a.date === dateStr).length);
            }

            return { days, counts };
        }

        renderStats() {
            const filteredAppts = this.getFilteredAppointments();
            const todayAppts = this.getTodayAppointments();
            const statusStats = this.getAppointmentsByStatus();

            // Patients and Doctors always show total (not filtered by time)
            this.animateNumber('#totalPatients', this.patients.length);
            this.animateNumber('#totalDoctors', this.doctors.length);

            // Appointments are filtered by time
            this.animateNumber('#totalAppointments', filteredAppts.length);
            this.animateNumber('#totalAppointments2', filteredAppts.length);
            this.animateNumber('#todayAppointments', todayAppts.length);

            // Update status counts
            $('#pendingCount').text(statusStats.pending);
            $('#confirmedCount').text(statusStats.confirmed);
            $('#completedCount').text(statusStats.completed);
            $('#cancelledCount').text(statusStats.cancelled);
        }

        animateNumber(selector, target) {
            const $el = $(selector);
            const current = parseInt($el.text()) || 0;
            const increment = target > current ? 1 : -1;
            const duration = 400;
            const steps = Math.abs(target - current);

            if (steps === 0) {
                $el.text(target);
                return;
            }

            const stepTime = Math.max(duration / steps, 20);
            let value = current;

            const timer = setInterval(() => {
                value += increment;
                $el.text(value);
                if (value === target) {
                    clearInterval(timer);
                }
            }, stepTime);
        }

        renderRecentAppointments() {
            const $container = $('#recentAppointments');
            if (!$container.length) return;

            const filteredAppts = this.getFilteredAppointments();
            const recent = [...filteredAppts]
                .sort((a, b) => new Date(b.date + ' ' + b.time) - new Date(a.date + ' ' + a.time))
                .slice(0, 5);

            if (recent.length === 0) {
                const noDataMsg = this.currentFilter === 'today'
                    ? 'No appointments today'
                    : this.currentFilter === 'week'
                        ? 'No appointments this week'
                        : 'No appointments yet';

                $container.html(`
                    <div class="text-center">
                        <i class="fas fa-calendar-times" style="font-size: 3rem;"></i>
                        <p class="mb-0 mt-3">${noDataMsg}</p>
                    </div>
                `);
                return;
            }

            const html = recent.map(appt => {
                const patient = this.patients.find(p => p.id === appt.patientId) || { name: 'Unknown Patient' };
                const doctor = this.doctors.find(d => d.id === appt.doctorId) || { name: 'Unknown Doctor' };
                const statusColors = {
                    pending: 'warning',
                    confirmed: 'success',
                    completed: 'info',
                    cancelled: 'danger'
                };
                const color = statusColors[appt.status] || 'secondary';

                return `
                    <div class="recent-item">
                        <div class="recent-avatar">
                            ${patient.name.charAt(0).toUpperCase()}
                        </div>
                        <div class="recent-info">
                            <div class="recent-name">${patient.name}</div>
                            <div class="recent-meta">
                                <span><i class="fas fa-user-md"></i>${doctor.name}</span>
                                <span><i class="far fa-calendar"></i>${appt.date}</span>
                                <span><i class="far fa-clock"></i>${appt.time}</span>
                            </div>
                        </div>
                        <span class="badge bg-${color} bg-opacity-15 text-${color}">${appt.status}</span>
                    </div>
                `;
            }).join('');

            $container.html(html);
        }

        renderCharts() {
            this.renderStatusChart();
            this.renderWeeklyChart();
        }

        renderStatusChart() {
            const ctx = document.getElementById('statusChart');
            if (!ctx) return;

            const stats = this.getAppointmentsByStatus();
            const total = stats.pending + stats.confirmed + stats.completed + stats.cancelled;

            // Destroy existing chart if exists
            if (window.statusChartInstance) {
                window.statusChartInstance.destroy();
            }

            window.statusChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'Confirmed', 'Completed', 'Cancelled'],
                    datasets: [{
                        data: total > 0 ? [stats.pending, stats.confirmed, stats.completed, stats.cancelled] : [1, 1, 1, 1],
                        backgroundColor: [
                            '#bf8c30',  // Pending - Yellow Ochre
                            '#2e5d34',  // Confirmed - Forest Green
                            '#3d5a80',  // Completed - Prussian Blue
                            '#8b3a3a'   // Cancelled - Venetian Red
                        ],
                        borderWidth: 0,
                        hoverOffset: 8,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: total > 0,
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: true,
                            boxPadding: 6
                        }
                    }
                }
            });
        }

        renderWeeklyChart() {
            const ctx = document.getElementById('weeklyChart');
            if (!ctx) return;

            const { days, counts } = this.getWeeklyAppointments();

            // Destroy existing chart if exists
            if (window.weeklyChartInstance) {
                window.weeklyChartInstance.destroy();
            }

            const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(15, 61, 62, 0.2)');
            gradient.addColorStop(1, 'rgba(15, 61, 62, 0)');

            window.weeklyChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: days,
                    datasets: [{
                        label: 'Appointments',
                        data: counts,
                        fill: true,
                        backgroundColor: gradient,
                        borderColor: '#0f3d3e',
                        borderWidth: 2,
                        tension: 0.4,
                        pointBackgroundColor: '#0f3d3e',
                        pointBorderColor: '#f5f0e8',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: '#1d5c5f',
                        pointHoverBorderColor: '#f5f0e8',
                        pointHoverBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(26, 26, 46, 0.95)',
                            titleColor: '#f5f0e8',
                            bodyColor: '#f5f0e8',
                            padding: 12,
                            cornerRadius: 8,
                            displayColors: false,
                            titleFont: {
                                size: 14,
                                weight: '600'
                            },
                            bodyFont: {
                                size: 24,
                                weight: '700'
                            },
                            callbacks: {
                                label: function (context) {
                                    return context.raw + ' appointments';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            reverse: document.documentElement.getAttribute('lang') === 'ar',
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#8a8a8a',
                                font: {
                                    size: 12,
                                    weight: '500'
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(26, 26, 26, 0.06)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#8a8a8a',
                                font: {
                                    size: 12,
                                    weight: '500'
                                },
                                stepSize: 1,
                                padding: 10
                            }
                        }
                    }
                }
            });
        }
    }

    window.dashboardManager = new DashboardManager();
    $(document).ready(() => {
        window.dashboardManager.init();
    });
} else {
    if (window.dashboardManager) {
        $(document).ready(() => {
            window.dashboardManager.init();
        });
    }
}
