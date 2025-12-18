/**
 * Patients Manager - Using API Backend
 */
if (typeof PatientsManager === 'undefined') {
    class PatientsManager {
        constructor() {
            this.patients = [];
            this.currentDeleteId = null;
            this.currentPage = 1;
            this.itemsPerPage = 10;
            this.totalItems = 0;
            this.totalPages = 0;
            this.searchTerm = '';
            this.sortColumn = 'id';
            this.sortOrder = 'desc';
            this.isLoading = false;
        }

        async init() {
            await this.loadPatients();
            this.bindEvents();
        }

        bindEvents() {
            $(document).off('submit.patients click.patients input.patients');

            $(document).on('submit.patients', '#patientForm', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                this.save();
            });

            $(document).on('click.patients', '[data-action="edit-patient"]', (e) => {
                const id = parseInt($(e.currentTarget).data('patient-id'));
                this.edit(id);
            });

            $(document).on('click.patients', '[data-action="delete-patient"]', (e) => {
                const id = parseInt($(e.currentTarget).data('patient-id'));
                this.deleteRequest(id);
            });

            $(document).on('click.patients', '[data-action="history-patient"]', (e) => {
                const id = parseInt($(e.currentTarget).data('patient-id'));
                this.showHistory(id);
            });

            $(document).on('click.patients', '#confirmDeleteBtn', () => this.confirmDelete());

            $(document).on('input.patients', '#patientSearch', Utils.debounce((e) => {
                this.searchTerm = $(e.target).val();
                this.currentPage = 1;
                this.loadPatients();
            }, 300));

            $(document).on('click.patients', '[data-action="prev-page"]', () => this.prevPage());
            $(document).on('click.patients', '[data-action="next-page"]', () => this.nextPage());

            $(document).on('click.patients', '.sortable-header', (e) => {
                const column = $(e.currentTarget).data('sort');
                this.sortBy(column);
            });

            $(document).on('click.patients', '[data-action="export-csv"]', () => this.exportCSV());
            $(document).on('click.patients', '[data-action="export-json"]', () => this.exportJSON());
        }

        async loadPatients() {
            if (this.isLoading) return;
            this.isLoading = true;

            try {
                const response = await API.patients.getAll({
                    page: this.currentPage,
                    per_page: this.itemsPerPage,
                    search: this.searchTerm,
                    sort: this.sortColumn,
                    direction: this.sortOrder
                });

                this.patients = response.data;
                this.totalItems = response.total;
                this.totalPages = response.last_page;
                this.currentPage = response.current_page;

                this.render();
            } catch (error) {
                console.error('Error loading patients:', error);
                this.showError('Failed to load patients');
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
            this.loadPatients();
        }

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadPatients();
            }
        }

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.loadPatients();
            }
        }

        render() {
            const $tbody = $('#patientsTableBody');
            if ($tbody.length === 0) return;

            if (this.patients.length === 0) {
                const emptyMessage = this.searchTerm
                    ? `<div class="empty-state">
                        <i class="fas fa-search"></i>
                        <h5>No results found</h5>
                        <p>Try adjusting your search term</p>
                       </div>`
                    : `<div class="empty-state">
                        <i class="fas fa-user-injured"></i>
                        <h5 data-i18n="noPatients">No patients yet</h5>
                        <p>Click "Add Patient" to get started!</p>
                       </div>`;

                $tbody.closest('.table-responsive').html(emptyMessage);
                this.updatePagination();
                return;
            }

            const html = this.patients.map(patient => `
                <tr>
                    <td class="ps-4 fw-bold text-secondary"><span dir="ltr">${patient.id}</span></td>
                    <td>
                        <h6 class="mb-0 fw-bold text-dark">${patient.name}</h6>
                    </td>
                    <td>${patient.age}</td>
                    <td><span class="badge ${patient.gender === 'male' ? 'bg-info-subtle text-info' : 'bg-danger-subtle text-danger'} text-capitalize" data-i18n="${patient.gender}">${patient.gender}</span></td>
                    <td><span dir="ltr">${patient.phone}</span></td>
                    <td>${patient.address || '-'}</td>
                    <td class="pe-4">
                        <div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-soft-secondary btn-sm" data-action="history-patient" data-patient-id="${patient.id}" title="History">
                                <i class="fas fa-history"></i>
                            </button>
                            <button class="btn btn-soft-primary btn-sm" data-action="edit-patient" data-patient-id="${patient.id}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-soft-danger btn-sm" data-action="delete-patient" data-patient-id="${patient.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');

            $tbody.html(html);
            this.updatePagination();
            this.updateSortIcons();

            if (window.app && window.app.applyLanguage) {
                window.app.applyLanguage(window.app.lang);
            }
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

        updatePagination() {
            const $pagination = $('.pagination-controls');
            if (!$pagination.length) return;

            const start = this.totalItems > 0 ? (this.currentPage - 1) * this.itemsPerPage + 1 : 0;
            const end = Math.min(this.currentPage * this.itemsPerPage, this.totalItems);

            $pagination.find('.pagination-info').html(`
                Showing <strong>${start}-${end}</strong> of <strong>${this.totalItems}</strong> patients
            `);

            $pagination.find('[data-action="prev-page"]').prop('disabled', this.currentPage === 1);
            $pagination.find('[data-action="next-page"]').prop('disabled', this.currentPage >= this.totalPages);
        }

        add() {
            this.openModal();
        }

        async edit(id) {
            try {
                const response = await API.patients.get(id);
                if (response.success && response.data) {
                    this.openModal(response.data);
                }
            } catch (error) {
                console.error('Error loading patient:', error);
                this.showError('Failed to load patient data');
            }
        }

        openModal(patient = null) {
            if (patient) {
                $('#patientId').val(patient.id);
                $('#patientName').val(patient.name);
                $('#patientAge').val(patient.age);
                $('#patientGender').val(patient.gender);
                $('#patientPhone').val(patient.phone);
                $('#patientAddress').val(patient.address || '');
                $('#patientModalTitle').attr('data-i18n', 'editPatient').text(translations[app.lang]?.editPatient || 'Edit Patient');
            } else {
                $('#patientForm')[0].reset();
                $('#patientId').val('');
                $('#patientModalTitle').attr('data-i18n', 'addPatient').text(translations[app.lang]?.addPatient || 'Add Patient');
            }
            $('#patientModal').modal('show');
        }

        async save() {
            const id = $('#patientId').val();
            const data = {
                name: $('#patientName').val().trim(),
                age: parseInt($('#patientAge').val()),
                gender: $('#patientGender').val(),
                phone: $('#patientPhone').val().trim(),
                address: $('#patientAddress').val().trim()
            };

            if (!data.name || !data.age || !data.phone) {
                this.showError('Please fill in all required fields');
                return;
            }

            try {
                let response;
                if (id) {
                    response = await API.patients.update(id, data);
                    this.showSuccess(translations[app.lang]?.patientUpdated || 'Patient updated successfully!');
                } else {
                    response = await API.patients.create(data);
                    this.showSuccess(translations[app.lang]?.patientAdded || 'Patient added successfully!');
                }

                $('#patientModal').modal('hide');
                await this.loadPatients();
            } catch (error) {
                console.error('Error saving patient:', error);
                this.showError(error.message || 'Failed to save patient');
            }
        }

        deleteRequest(id) {
            this.currentDeleteId = id;
            $('#deleteModal').modal('show');
        }

        async confirmDelete() {
            if (this.currentDeleteId) {
                try {
                    await API.patients.delete(this.currentDeleteId);
                    this.showSuccess(translations[app.lang]?.patientDeleted || 'Patient deleted successfully!');
                    this.currentDeleteId = null;
                    $('#deleteModal').modal('hide');
                    await this.loadPatients();
                } catch (error) {
                    console.error('Error deleting patient:', error);
                    this.showError('Failed to delete patient');
                }
            }
        }

        async showHistory(patientId) {
            try {
                const patientResponse = await API.patients.get(patientId);
                const historyResponse = await API.patients.history(patientId);

                const patient = patientResponse.data;
                const appointments = historyResponse.data || [];

                $('#historyPatientName').text(`- ${patient.name}`);

                const lang = (typeof app !== 'undefined' && app.lang) ? app.lang : 'en';
                const t = (key) => translations[lang]?.[key] || key;

                let html = '';

                if (appointments.length === 0) {
                    html = `
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-muted">${t('noAppointments')}</h5>
                            <p class="text-muted">${t('noAppointmentsForPatient')}</p>
                        </div>
                    `;
                } else {
                    const statusColors = {
                        pending: 'warning',
                        confirmed: 'info',
                        completed: 'success',
                        cancelled: 'danger'
                    };

                    html = `<div class="history-timeline">`;
                    appointments.forEach(appt => {
                        const doctorName = appt.doctor?.name || t('unknownDoctor');
                        const statusColor = statusColors[appt.status] || 'secondary';

                        html += `
                            <div class="history-item card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="fas fa-calendar me-2 text-primary"></i>
                                                <span dir="ltr">${appt.date}</span>
                                                <span class="text-muted ms-2" dir="ltr">${appt.time}</span>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="fas fa-user-md me-1"></i>${doctorName}
                                            </small>
                                        </div>
                                        <span class="badge bg-${statusColor}-subtle text-${statusColor}">${t(appt.status)}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                }

                $('#historyContent').html(html);
                const modal = new bootstrap.Modal(document.getElementById('historyModal'));
                modal.show();
            } catch (error) {
                console.error('Error loading history:', error);
                this.showError('Failed to load patient history');
            }
        }

        async exportCSV() {
            try {
                const response = await API.patients.getAll({ per_page: 1000 });
                const data = response.data.map(p => ({
                    ID: p.id,
                    Name: p.name,
                    Age: p.age,
                    Gender: p.gender,
                    Phone: p.phone,
                    Address: p.address || ''
                }));
                Utils.exportToCSV(data, 'patients.csv');
            } catch (error) {
                this.showError('Failed to export data');
            }
        }

        async exportJSON() {
            try {
                const response = await API.patients.getAll({ per_page: 1000 });
                Utils.exportToJSON(response.data, 'patients.json');
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

    window.patientsManager = new PatientsManager();
    $(document).ready(() => {
        if (typeof API !== 'undefined') {
            window.patientsManager.init();
        } else {
            const checkAPI = setInterval(() => {
                if (typeof API !== 'undefined') {
                    clearInterval(checkAPI);
                    window.patientsManager.init();
                }
            }, 100);
        }
    });
}
