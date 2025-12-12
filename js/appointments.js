if (typeof AppointmentsManager === 'undefined') {
    class AppointmentsManager {
        constructor() {
            this.appointments = JSON.parse(localStorage.getItem('clinic_appointments')) || [];
            this.patients = JSON.parse(localStorage.getItem('clinic_patients')) || [];
            this.doctors = JSON.parse(localStorage.getItem('clinic_doctors')) || [];
            this.searchTerm = '';
            this.statusFilter = 'all';
            this.currentPage = 1;
            this.itemsPerPage = 10;
            this.sortColumn = null;
            this.sortOrder = 'asc';

            // One-time fix: Renumber appointments to start from 1
            this.renumberAppointments();
        }

        renumberAppointments() {
            if (this.appointments.length > 0) {
                // Check if any ID is larger than the array length (old Date.now() IDs)
                const needsRenumber = this.appointments.some(a => a.id > this.appointments.length);
                if (needsRenumber) {
                    // Sort by date first, then renumber from 1
                    this.appointments.sort((a, b) => new Date(a.date + ' ' + a.time) - new Date(b.date + ' ' + b.time));
                    this.appointments = this.appointments.map((appt, index) => ({
                        ...appt,
                        id: index + 1
                    }));
                    localStorage.setItem('clinic_appointments', JSON.stringify(this.appointments));
                }
            }
        }

        // Safe language getter
        getLang() {
            return (typeof app !== 'undefined' && app.lang) ? app.lang : 'en';
        }

        // Safe translation getter
        t(key) {
            const lang = this.getLang();
            if (typeof translations !== 'undefined' && translations[lang] && translations[lang][key]) {
                return translations[lang][key];
            }
            return key; // Fallback to key name
        }

        init() {
            this.bindEvents();
            this.render();
        }

        bindEvents() {
            $(document).off('submit.appointments click.appointments input.appointments change.appointments');

            // Form Submission
            $(document).on('submit.appointments', '#appointmentForm', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                this.save();
            });

            // Add Button
            $(document).on('click.appointments', '[data-action="add-appointment"]', () => {
                this.resetForm();
                $('#appointmentModalTitle').text(this.t('bookAppt'));
                $('#appointmentModal').modal('show');
            });

            // Edit Button
            $(document).on('click.appointments', '[data-action="edit-appointment"]', (e) => {
                const id = parseInt($(e.currentTarget).data('id'));
                this.edit(id);
            });

            // Delete Button
            $(document).on('click.appointments', '[data-action="delete-appointment"]', (e) => {
                const id = parseInt($(e.currentTarget).data('id'));
                this.deleteRequest(id);
            });

            // Confirm Delete
            $(document).on('click.appointments', '#confirmDeleteBtn', () => this.confirmDelete());

            // Search Appointment (Global Search)
            $(document).on('input.appointments', '#appointmentSearch', Utils.debounce((e) => {
                this.searchTerm = $(e.target).val().toLowerCase();
                this.currentPage = 1;
                this.render();
            }));

            // Filter by Status
            $(document).on('change.appointments', '#statusFilter', (e) => {
                this.statusFilter = $(e.target).val();
                this.currentPage = 1;
                this.render();
            });

            // Pagination
            $(document).on('click.appointments', '[data-action="prev-page"]', () => this.prevPage());
            $(document).on('click.appointments', '[data-action="next-page"]', () => this.nextPage());

            // Sorting
            $(document).on('click.appointments', '.sortable-header', (e) => {
                const column = $(e.currentTarget).data('sort');
                this.sortBy(column);
            });

            // Export
            $(document).on('click.appointments', '[data-action="export-csv"]', () => this.exportCSV());
            $(document).on('click.appointments', '[data-action="export-json"]', () => this.exportJSON());

            // Autocomplete Setup
            this.setupAutocomplete('#patientSearchInput', '#patientSearchResults', '#appointmentPatientId', this.patients, 'name');
            this.setupAutocomplete('#doctorSearchInput', '#doctorSearchResults', '#appointmentDoctorId', this.doctors, 'name');

            // Initial Render check
            $(document).on('layout-loaded.appointments', () => {
                if ($('#appointmentsTableBody').length) {
                    this.render();
                }
            });
        }

        setupAutocomplete(inputSelector, resultsSelector, hiddenInputSelector, dataSource, displayKey) {
            const $input = $(inputSelector);
            const $results = $(resultsSelector);
            const $hidden = $(hiddenInputSelector);
            const self = this;

            // Input Event
            $input.off('input').on('input', function (e) {
                const val = $(e.target).val().toLowerCase();
                $hidden.val(''); // Clear ID if typing changes
                $results.empty().hide();

                if (val.length > 0) {
                    const filtered = dataSource.filter(item =>
                        item[displayKey].toLowerCase().includes(val) ||
                        (item.phone && item.phone.includes(val))
                    );

                    if (filtered.length > 0) {
                        filtered.slice(0, 5).forEach(item => {
                            const $div = $('<div>')
                                .addClass('autocomplete-item')
                                .html(`<strong>${item[displayKey]}</strong><br><small class="text-muted">${item.phone || ''}</small>`)
                                .on('click', () => {
                                    $input.val(item[displayKey]);
                                    $hidden.val(item.id);
                                    $results.empty().hide();
                                });
                            $results.append($div);
                        });
                        $results.show();
                    } else {
                        $results.append(`<div class="autocomplete-item text-muted">${self.t('noMatchesFound')}</div>`).show();
                    }
                }
            });

            // Hide on outside click
            $(document).on('click', (e) => {
                if (!$(e.target).closest(inputSelector).length && !$(e.target).closest(resultsSelector).length) {
                    $results.hide();
                }
            });
        }

        getFilteredAppointments() {
            let filtered = this.appointments.map(appt => {
                const patient = this.patients.find(p => p.id === appt.patientId);
                const doctor = this.doctors.find(d => d.id === appt.doctorId);
                return {
                    ...appt,
                    patientName: patient ? patient.name : '',
                    doctorName: doctor ? doctor.name : ''
                };
            });

            // Search Term (Patient Name, Doctor Name, or Appointment ID)
            if (this.searchTerm) {
                filtered = filtered.filter(appt => {
                    const patientName = appt.patientName.toLowerCase();
                    const doctorName = appt.doctorName.toLowerCase();

                    return patientName.includes(this.searchTerm) ||
                        doctorName.includes(this.searchTerm) ||
                        appt.id.toString().includes(this.searchTerm);
                });
            }

            // Status Filter
            if (this.statusFilter !== 'all') {
                filtered = filtered.filter(appt => appt.status === this.statusFilter);
            }

            // Sorting
            if (this.sortColumn) {
                filtered = [...filtered].sort((a, b) => {
                    let valA, valB;

                    switch (this.sortColumn) {
                        case 'id':
                            valA = a.id;
                            valB = b.id;
                            break;
                        case 'patientName':
                            valA = a.patientName.toLowerCase();
                            valB = b.patientName.toLowerCase();
                            break;
                        case 'doctorName':
                            valA = a.doctorName.toLowerCase();
                            valB = b.doctorName.toLowerCase();
                            break;
                        case 'date':
                            valA = new Date(a.date);
                            valB = new Date(b.date);
                            break;
                        case 'time':
                            valA = a.time;
                            valB = b.time;
                            break;
                        case 'status':
                            valA = a.status;
                            valB = b.status;
                            break;
                        default:
                            return 0;
                    }

                    if (valA < valB) return this.sortOrder === 'asc' ? -1 : 1;
                    if (valA > valB) return this.sortOrder === 'asc' ? 1 : -1;
                    return 0;
                });
            } else {
                // Default sort: Date Descending (Newest First)
                filtered = filtered.sort((a, b) => new Date(b.date + ' ' + b.time) - new Date(a.date + ' ' + a.time));
            }

            return filtered;
        }

        sortBy(column) {
            if (this.sortColumn === column) {
                this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortOrder = 'asc';
            }
            this.render();
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
            // Refresh data references
            this.appointments = JSON.parse(localStorage.getItem('clinic_appointments')) || [];
            this.patients = JSON.parse(localStorage.getItem('clinic_patients')) || [];
            this.doctors = JSON.parse(localStorage.getItem('clinic_doctors')) || [];

            const filtered = this.getFilteredAppointments();
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            const paginated = filtered.slice(start, end);

            const $tbody = $('#appointmentsTableBody');
            if ($tbody.length === 0) return;

            $tbody.empty();

            if (paginated.length === 0) {
                $tbody.html(`
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-calendar-times mb-3" style="font-size: 2rem;"></i>
                            <p>${this.t('noData')}</p>
                        </td>
                    </tr>
                `);
            } else {
                paginated.forEach((appt, index) => {
                    const patient = this.patients.find(p => p.id === appt.patientId) || { name: this.t('unknownPatient') };
                    const doctor = this.doctors.find(d => d.id === appt.doctorId) || { name: this.t('unknownDoctor') };

                    const statusColors = {
                        pending: 'warning',
                        confirmed: 'success',
                        completed: 'info',
                        cancelled: 'danger'
                    };
                    const badgeColor = statusColors[appt.status] || 'secondary';

                    const row = `
                        <tr>
                            <td class="ps-4 fw-bold text-secondary"><span dir="ltr">${appt.id}</span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="fw-medium">${patient.name}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center"> 
                                    <span>${doctor.name}</span>
                                </div>
                            </td>
                            <td>${appt.date}</td>
                            <td><span class="badge bg-light text-dark border"><i class="far fa-clock me-1"></i>${appt.time}</span></td>
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

            // Update Pagination Info
            const total = filtered.length;
            const showingStart = total === 0 ? 0 : start + 1;
            const showingEnd = Math.min(end, total);
            const showingText = this.t('showing');
            const ofText = this.t('of');
            const appointmentsText = this.t('appointmentsLabel');
            $('.pagination-info').html(`${showingText} <strong>${showingStart}-${showingEnd}</strong> ${ofText} <strong>${total}</strong> ${appointmentsText}`);

            $('[data-action="prev-page"]').prop('disabled', this.currentPage === 1);
            $('[data-action="next-page"]').prop('disabled', end >= total);
            this.updateSortIcons();
        }

        save() {
            const id = $('#appointmentId').val();
            const patientId = $('#appointmentPatientId').val();
            const doctorId = $('#appointmentDoctorId').val();

            // Validation
            if (!patientId || !doctorId) {
                if (typeof app !== 'undefined') {
                    app.showAlert(this.t('selectPatientDoctor'), 'error');
                }
                return;
            }

            // Generate new ID starting from 1
            let newId;
            if (id) {
                newId = parseInt(id);
            } else {
                newId = this.appointments.length > 0 ? Math.max(...this.appointments.map(a => a.id)) + 1 : 1;
            }

            const apptData = {
                id: newId,
                patientId: parseInt(patientId),
                doctorId: parseInt(doctorId),
                date: $('#appointmentDate').val(),
                time: $('#appointmentTime').val(),
                type: $('#appointmentType').val(),
                status: $('#appointmentStatus').val()
            };

            if (id) {
                const index = this.appointments.findIndex(a => a.id === parseInt(id));
                if (index !== -1) {
                    this.appointments[index] = apptData;
                    if (typeof app !== 'undefined') app.showAlert(this.t('appointmentUpdated'), 'success');
                }
            } else {
                this.appointments.unshift(apptData);
                if (typeof app !== 'undefined') app.showAlert(this.t('appointmentBooked'), 'success');
            }

            this.saveToStorage();
            $('#appointmentModal').modal('hide');
            this.render();
        }

        edit(id) {
            const appt = this.appointments.find(a => a.id === id);
            if (appt) {
                this.resetForm();
                $('#appointmentId').val(appt.id);

                // Set Hidden IDs
                $('#appointmentPatientId').val(appt.patientId);
                $('#appointmentDoctorId').val(appt.doctorId);

                // Find Names for Inputs
                const patient = this.patients.find(p => p.id === appt.patientId);
                const doctor = this.doctors.find(d => d.id === appt.doctorId);

                $('#patientSearchInput').val(patient ? patient.name : '');
                $('#doctorSearchInput').val(doctor ? doctor.name : '');

                $('#appointmentDate').val(appt.date);
                $('#appointmentTime').val(appt.time);
                $('#appointmentType').val(appt.type);
                $('#appointmentStatus').val(appt.status);

                $('#appointmentModalTitle').text(this.t('editAppointment'));
                $('#appointmentModal').modal('show');
            }
        }

        deleteRequest(id) {
            this.deleteId = id;
            $('#deleteModal').modal('show');
        }

        confirmDelete() {
            if (this.deleteId) {
                this.appointments = this.appointments.filter(a => a.id !== this.deleteId);
                this.saveToStorage();
                $('#deleteModal').modal('hide');
                this.deleteId = null;
                if (typeof app !== 'undefined') app.showAlert(this.t('appointmentCancelled'), 'success');
                this.render();
            }
        }

        saveToStorage() {
            localStorage.setItem('clinic_appointments', JSON.stringify(this.appointments));
        }

        resetForm() {
            const form = $('#appointmentForm')[0];
            if (form) form.reset();
            $('#appointmentId').val('');
            $('#appointmentPatientId').val('');
            $('#appointmentDoctorId').val('');
            $('#patientSearchInput').removeClass('is-invalid');
            $('#doctorSearchInput').removeClass('is-invalid');
        }

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.render();
            }
        }

        nextPage() {
            const total = this.getFilteredAppointments().length;
            if (this.currentPage * this.itemsPerPage < total) {
                this.currentPage++;
                this.render();
            }
        }

        exportCSV() {
            const data = this.getFilteredAppointments().map(appt => ({
                ID: appt.id,
                Patient: appt.patientName,
                Doctor: appt.doctorName,
                Date: appt.date,
                Time: appt.time,
                Type: appt.type,
                Status: appt.status
            }));
            Utils.exportToCSV(data, 'appointments.csv');
        }

        exportJSON() {
            const data = this.getFilteredAppointments().map(appt => ({
                id: appt.id,
                patient: appt.patientName,
                doctor: appt.doctorName,
                date: appt.date,
                time: appt.time,
                type: appt.type,
                status: appt.status
            }));
            Utils.exportToJSON(data, 'appointments.json');
        }
    }

    // Create instance and initialize
    window.appointmentsManager = new AppointmentsManager();
    $(document).ready(() => {
        window.appointmentsManager.init();
    });
} else {
    // Class already exists, just re-initialize if manager exists
    if (window.appointmentsManager) {
        $(document).ready(() => {
            window.appointmentsManager.init();
        });
    }
}
