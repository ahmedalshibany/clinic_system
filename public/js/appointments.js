/**
 * Appointments Manager - Using API Backend
 */
if (typeof AppointmentsManager === 'undefined') {
    class AppointmentsManager {
        constructor() {
            this.appointments = [];
            this.patients = [];
            this.doctors = [];
            this.searchTerm = '';
            this.statusFilter = 'all';
            this.currentPage = 1;
            this.itemsPerPage = 10;
            this.totalItems = 0;
            this.totalPages = 0;
            this.sortColumn = 'date';
            this.sortOrder = 'desc';
            this.deleteId = null;
            this.isLoading = false;
        }

        getLang() {
            return (typeof app !== 'undefined' && app.lang) ? app.lang : 'en';
        }

        t(key) {
            const lang = this.getLang();
            if (typeof translations !== 'undefined' && translations[lang] && translations[lang][key]) {
                return translations[lang][key];
            }
            return key;
        }

        async init() {
            await this.loadAppointments();
            this.bindEvents();
        }

        bindEvents() {
            $(document).off('submit.appointments click.appointments input.appointments change.appointments');

            $(document).on('submit.appointments', '#appointmentForm', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                this.save();
            });

            $(document).on('click.appointments', '[data-action="add-appointment"]', () => {
                this.resetForm();
                $('#appointmentModalTitle').text(this.t('bookAppt'));
                $('#appointmentModal').modal('show');
            });

            $(document).on('click.appointments', '[data-action="edit-appointment"]', (e) => {
                const id = parseInt($(e.currentTarget).data('id'));
                this.edit(id);
            });

            $(document).on('click.appointments', '[data-action="delete-appointment"]', (e) => {
                const id = parseInt($(e.currentTarget).data('id'));
                this.deleteRequest(id);
            });

            $(document).on('click.appointments', '#confirmDeleteBtn', () => this.confirmDelete());

            $(document).on('input.appointments', '#appointmentSearch', Utils.debounce((e) => {
                this.searchTerm = $(e.target).val();
                this.currentPage = 1;
                this.loadAppointments();
            }, 300));

            $(document).on('change.appointments', '#statusFilter', (e) => {
                this.statusFilter = $(e.target).val();
                this.currentPage = 1;
                this.loadAppointments();
            });

            $(document).on('click.appointments', '[data-action="prev-page"]', () => this.prevPage());
            $(document).on('click.appointments', '[data-action="next-page"]', () => this.nextPage());

            $(document).on('click.appointments', '.sortable-header', (e) => {
                const column = $(e.currentTarget).data('sort');
                this.sortBy(column);
            });

            $(document).on('click.appointments', '[data-action="export-csv"]', () => this.exportCSV());
            $(document).on('click.appointments', '[data-action="export-json"]', () => this.exportJSON());

            // Setup autocomplete for patient and doctor search
            this.setupPatientAutocomplete();
            this.setupDoctorAutocomplete();
        }

        setupPatientAutocomplete() {
            const $input = $('#patientSearchInput');
            const $results = $('#patientSearchResults');
            const $hidden = $('#appointmentPatientId');
            const self = this;

            $input.off('input').on('input', Utils.debounce(async function () {
                const val = $(this).val();
                $hidden.val('');
                $results.empty().hide();

                if (val.length > 1) {
                    try {
                        const patients = await API.patients.search(val);
                        if (patients.length > 0) {
                            patients.forEach(patient => {
                                const $div = $('<div>')
                                    .addClass('autocomplete-item')
                                    .html(`<strong>${patient.name}</strong><br><small class="text-muted">${patient.phone || ''}</small>`)
                                    .on('click', () => {
                                        $input.val(patient.name);
                                        $hidden.val(patient.id);
                                        $results.empty().hide();
                                    });
                                $results.append($div);
                            });
                            $results.show();
                        } else {
                            $results.append(`<div class="autocomplete-item text-muted">${self.t('noMatchesFound')}</div>`).show();
                        }
                    } catch (error) {
                        console.error('Error searching patients:', error);
                    }
                }
            }, 300));

            $(document).on('click', (e) => {
                if (!$(e.target).closest('#patientSearchInput').length && !$(e.target).closest('#patientSearchResults').length) {
                    $results.hide();
                }
            });
        }

        setupDoctorAutocomplete() {
            const $input = $('#doctorSearchInput');
            const $results = $('#doctorSearchResults');
            const $hidden = $('#appointmentDoctorId');
            const self = this;

            $input.off('input').on('input', Utils.debounce(async function () {
                const val = $(this).val();
                $hidden.val('');
                $results.empty().hide();

                if (val.length > 1) {
                    try {
                        const doctors = await API.doctors.search(val);
                        if (doctors.length > 0) {
                            doctors.forEach(doctor => {
                                const $div = $('<div>')
                                    .addClass('autocomplete-item')
                                    .html(`<strong>${doctor.name}</strong><br><small class="text-muted">${doctor.specialty}</small>`)
                                    .on('click', () => {
                                        $input.val(doctor.name);
                                        $hidden.val(doctor.id);
                                        $results.empty().hide();
                                    });
                                $results.append($div);
                            });
                            $results.show();
                        } else {
                            $results.append(`<div class="autocomplete-item text-muted">${self.t('noMatchesFound')}</div>`).show();
                        }
                    } catch (error) {
                        console.error('Error searching doctors:', error);
                    }
                }
            }, 300));

            $(document).on('click', (e) => {
                if (!$(e.target).closest('#doctorSearchInput').length && !$(e.target).closest('#doctorSearchResults').length) {
                    $results.hide();
                }
            });
        }

        async loadAppointments() {
            if (this.isLoading) return;
            this.isLoading = true;

            try {
                const response = await API.appointments.getAll({
                    page: this.currentPage,
                    per_page: this.itemsPerPage,
                    search: this.searchTerm,
                    status: this.statusFilter,
                    sort: this.sortColumn,
                    direction: this.sortOrder
                });

                this.appointments = response.data;
                this.totalItems = response.total;
                this.totalPages = response.last_page;
                this.currentPage = response.current_page;

                this.render();
            } catch (error) {
                console.error('Error loading appointments:', error);
                this.showError('Failed to load appointments');
            } finally {
                this.isLoading = false;
            }
        }

        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortOrder = 'asc';
            }
            this.loadAppointments();
        }

        updateSortIcons() {
            $('.sortable-header').removeClass('active').find('i').attr('class', 'fas fa-sort');

            if (this.sortColumn) {
                const $header = $(`.sortable-header[data-sort="${this.sortColumn}"]`);
                $header.addClass('active');
                const icon = this.sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
                $header.find('i').attr('class', `fas ${icon}`);
            }
        }

        render() {
            const $tbody = $('#appointmentsTableBody');
            if ($tbody.length === 0) return;

            $tbody.empty();

            if (this.appointments.length === 0) {
                $tbody.html(`
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-calendar-times mb-3" style="font-size: 2rem;"></i>
                            <p>${this.t('noAppointments')}</p>
                        </td>
                    </tr>
                `);
            } else {
                this.appointments.forEach(appt => {
                    const patientName = appt.patient?.name || this.t('unknownPatient');
                    const doctorName = appt.doctor?.name || this.t('unknownDoctor');

                    const statusColors = {
                        pending: 'warning',
                        confirmed: 'success',
                        completed: 'info',
                        cancelled: 'danger'
                    };
                    const badgeColor = statusColors[appt.status] || 'secondary';

                    // Format date
                    let displayDate = appt.date;
                    if (appt.date && typeof appt.date === 'object' && appt.date.formatted) {
                        displayDate = appt.date.formatted;
                    } else if (typeof appt.date === 'string') {
                        displayDate = appt.date.split('T')[0];
                    }

                    // Format time
                    let displayTime = appt.time || '';
                    if (typeof displayTime === 'string' && displayTime.includes(':')) {
                        displayTime = displayTime.substring(0, 5);
                    }

                    const row = `
                        <tr>
                            <td class="ps-4 fw-bold text-secondary"><span dir="ltr">${appt.id}</span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="fw-medium">${patientName}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center"> 
                                    <span>${doctorName}</span>
                                </div>
                            </td>
                            <td>${displayDate}</td>
                            <td>${displayTime}</td>
                            <td><span class="badge bg-${badgeColor} bg-opacity-10 text-${badgeColor} px-3 py-2 rounded-pill">${this.t(appt.status)}</span></td>
                            <td class="pe-4">
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-soft-primary btn-sm" data-action="edit-appointment" data-id="${appt.id}" title="${this.t('edit')}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-soft-danger btn-sm" data-action="delete-appointment" data-id="${appt.id}" title="${this.t('delete')}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                    $tbody.append(row);
                });
            }

            this.updatePagination();
            this.updateSortIcons();
        }

        updatePagination() {
            const start = this.totalItems > 0 ? (this.currentPage - 1) * this.itemsPerPage + 1 : 0;
            const end = Math.min(this.currentPage * this.itemsPerPage, this.totalItems);

            $('.pagination-info').html(`
                Showing <strong>${start}-${end}</strong> of <strong>${this.totalItems}</strong> appointments
            `);

            $('[data-action="prev-page"]').prop('disabled', this.currentPage === 1);
            $('[data-action="next-page"]').prop('disabled', this.currentPage >= this.totalPages);
        }

        async save() {
            const id = $('#appointmentId').val();
            const patientId = $('#appointmentPatientId').val();
            const doctorId = $('#appointmentDoctorId').val();

            if (!patientId || !doctorId) {
                this.showError(this.t('selectPatientDoctor'));
                return;
            }

            const data = {
                patient_id: parseInt(patientId),
                doctor_id: parseInt(doctorId),
                date: $('#appointmentDate').val(),
                time: $('#appointmentTime').val(),
                type: $('#appointmentType').val(),
                status: $('#appointmentStatus').val()
            };

            try {
                if (id) {
                    await API.appointments.update(id, data);
                    this.showSuccess(this.t('appointmentUpdated'));
                } else {
                    await API.appointments.create(data);
                    this.showSuccess(this.t('appointmentBooked'));
                }

                $('#appointmentModal').modal('hide');
                await this.loadAppointments();
            } catch (error) {
                console.error('Error saving appointment:', error);
                this.showError(error.message || 'Failed to save appointment');
            }
        }

        async edit(id) {
            try {
                const response = await API.appointments.get(id);
                if (response.success && response.data) {
                    const appt = response.data;
                    this.resetForm();

                    $('#appointmentId').val(appt.id);
                    $('#appointmentPatientId').val(appt.patient_id);
                    $('#appointmentDoctorId').val(appt.doctor_id);

                    $('#patientSearchInput').val(appt.patient?.name || '');
                    $('#doctorSearchInput').val(appt.doctor?.name || '');

                    // Format date for input
                    let dateVal = appt.date;
                    if (typeof dateVal === 'string' && dateVal.includes('T')) {
                        dateVal = dateVal.split('T')[0];
                    }
                    $('#appointmentDate').val(dateVal);

                    // Format time for input
                    let timeVal = appt.time || '';
                    if (typeof timeVal === 'string' && timeVal.includes(':')) {
                        timeVal = timeVal.substring(0, 5);
                    }
                    $('#appointmentTime').val(timeVal);

                    $('#appointmentType').val(appt.type);
                    $('#appointmentStatus').val(appt.status);

                    $('#appointmentModalTitle').text(this.t('editAppointment'));
                    $('#appointmentModal').modal('show');
                }
            } catch (error) {
                console.error('Error loading appointment:', error);
                this.showError('Failed to load appointment');
            }
        }

        deleteRequest(id) {
            this.deleteId = id;
            $('#deleteModal').modal('show');
        }

        async confirmDelete() {
            if (this.deleteId) {
                try {
                    await API.appointments.delete(this.deleteId);
                    this.showSuccess(this.t('appointmentCancelled'));
                    this.deleteId = null;
                    $('#deleteModal').modal('hide');
                    await this.loadAppointments();
                } catch (error) {
                    console.error('Error deleting appointment:', error);
                    this.showError('Failed to delete appointment');
                }
            }
        }

        resetForm() {
            const form = $('#appointmentForm')[0];
            if (form) form.reset();
            $('#appointmentId').val('');
            $('#appointmentPatientId').val('');
            $('#appointmentDoctorId').val('');
            $('#patientSearchInput').val('').removeClass('is-invalid');
            $('#doctorSearchInput').val('').removeClass('is-invalid');
        }

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadAppointments();
            }
        }

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.loadAppointments();
            }
        }

        async exportCSV() {
            try {
                const response = await API.appointments.getAll({ per_page: 1000 });
                const data = response.data.map(appt => ({
                    ID: appt.id,
                    Patient: appt.patient?.name || '',
                    Doctor: appt.doctor?.name || '',
                    Date: appt.date,
                    Time: appt.time,
                    Type: appt.type,
                    Status: appt.status
                }));
                Utils.exportToCSV(data, 'appointments.csv');
            } catch (error) {
                this.showError('Failed to export data');
            }
        }

        async exportJSON() {
            try {
                const response = await API.appointments.getAll({ per_page: 1000 });
                Utils.exportToJSON(response.data, 'appointments.json');
            } catch (error) {
                this.showError('Failed to export data');
            }
        }

        showSuccess(message) {
            if (window.app && window.app.showAlert) {
                window.app.showAlert(message, 'success');
            } else {
                alert(message);
            }
        }

        showError(message) {
            if (window.app && window.app.showAlert) {
                window.app.showAlert(message, 'danger');
            } else {
                alert(message);
            }
        }
    }

    window.appointmentsManager = new AppointmentsManager();
    $(document).ready(() => {
        if (typeof API !== 'undefined') {
            window.appointmentsManager.init();
        } else {
            const checkAPI = setInterval(() => {
                if (typeof API !== 'undefined') {
                    clearInterval(checkAPI);
                    window.appointmentsManager.init();
                }
            }, 100);
        }
    });
}
