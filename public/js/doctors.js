/**
 * Doctors Manager - Using API Backend
 */
if (typeof DoctorsManager === 'undefined') {
    class DoctorsManager {
        constructor() {
            this.doctors = [];
            this.currentDeleteId = null;
            this.currentPage = 1;
            this.itemsPerPage = 8;
            this.totalItems = 0;
            this.totalPages = 0;
            this.searchTerm = '';
            this.sortColumn = 'id';
            this.sortOrder = 'desc';
            this.isLoading = false;
        }

        async init() {
            await this.loadDoctors();
            this.bindEvents();
        }

        bindEvents() {
            $(document).off('submit.doctors click.doctors input.doctors');

            $(document).on('submit.doctors', '#doctorForm', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                this.save();
            });

            $(document).on('click.doctors', '[data-action="edit-doctor"]', (e) => {
                const id = parseInt($(e.currentTarget).data('doctor-id'));
                this.edit(id);
            });

            $(document).on('click.doctors', '[data-action="delete-doctor"]', (e) => {
                const id = parseInt($(e.currentTarget).data('doctor-id'));
                this.deleteRequest(id);
            });

            $(document).on('click.doctors', '#confirmDeleteBtn', () => this.confirmDelete());

            $(document).on('input.doctors', '#doctorSearch', Utils.debounce((e) => {
                this.searchTerm = $(e.target).val();
                this.currentPage = 1;
                this.loadDoctors();
            }, 300));

            $(document).on('click.doctors', '[data-action="prev-page"]', () => this.prevPage());
            $(document).on('click.doctors', '[data-action="next-page"]', () => this.nextPage());

            $(document).on('click.doctors', '[data-action="export-csv"]', () => this.exportCSV());
            $(document).on('click.doctors', '[data-action="export-json"]', () => this.exportJSON());
        }

        async loadDoctors() {
            if (this.isLoading) return;
            this.isLoading = true;

            try {
                const response = await API.doctors.getAll({
                    page: this.currentPage,
                    per_page: this.itemsPerPage,
                    search: this.searchTerm,
                    sort: this.sortColumn,
                    direction: this.sortOrder
                });

                this.doctors = response.data;
                this.totalItems = response.total;
                this.totalPages = response.last_page;
                this.currentPage = response.current_page;

                this.render();
            } catch (error) {
                console.error('Error loading doctors:', error);
                this.showError('Failed to load doctors');
            } finally {
                this.isLoading = false;
            }
        }

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.loadDoctors();
            }
        }

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.loadDoctors();
            }
        }

        render() {
            const $grid = $('#doctorsGrid');
            if ($grid.length === 0) return;

            if (this.doctors.length === 0) {
                const emptyMessage = this.searchTerm
                    ? `<div class="empty-state col-12">
                        <i class="fas fa-search"></i>
                        <h5>No results found</h5>
                        <p>Try adjusting your search term</p>
                       </div>`
                    : `<div class="empty-state col-12">
                        <i class="fas fa-user-md"></i>
                        <h5 data-i18n="noDoctors">No doctors yet</h5>
                        <p>Click "Add Doctor" to get started!</p>
                       </div>`;

                $grid.html(emptyMessage);
                this.updatePagination();
                return;
            }

            const lang = (typeof app !== 'undefined' && app.lang) ? app.lang : 'en';
            const getSpecialtyKey = (specialty) => {
                const key = 'spec_' + (specialty === 'General Practice' ? 'general' : specialty.toLowerCase());
                return translations[lang]?.[key] || specialty;
            };

            const html = this.doctors.map(doctor => {
                const avatarUrl = doctor.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(doctor.name)}&background=0D8ABC&color=fff`;

                return `
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="card doctor-card h-100">
                            <div class="card-body text-center p-4">
                                <div class="doctor-avatar mb-3">
                                    <img src="${avatarUrl}" alt="${doctor.name}">
                                    <div class="status-indicator ${doctor.is_active ? '' : 'bg-secondary'}"></div>
                                </div>
                                <h5 class="fw-bold text-primary mb-1">${doctor.name}</h5>
                                <p class="text-secondary small mb-3">${getSpecialtyKey(doctor.specialty)}</p>

                                <div class="action-buttons d-flex justify-content-center gap-3">
                                    <button class="btn btn-soft-primary btn-sm" data-action="edit-doctor" data-doctor-id="${doctor.id}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-soft-danger btn-sm" data-action="delete-doctor" data-doctor-id="${doctor.id}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            $grid.html(html);
            this.updatePagination();

            if (window.app && window.app.applyLanguage) {
                window.app.applyLanguage(window.app.lang);
            }
        }

        updatePagination() {
            const $pagination = $('.pagination-controls');
            if (!$pagination.length) return;

            const start = this.totalItems > 0 ? (this.currentPage - 1) * this.itemsPerPage + 1 : 0;
            const end = Math.min(this.currentPage * this.itemsPerPage, this.totalItems);

            $pagination.find('.pagination-info').html(`
                Showing <strong>${start}-${end}</strong> of <strong>${this.totalItems}</strong> doctors
            `);

            $pagination.find('[data-action="prev-page"]').prop('disabled', this.currentPage === 1);
            $pagination.find('[data-action="next-page"]').prop('disabled', this.currentPage >= this.totalPages);
        }

        add() {
            this.openModal();
        }

        async edit(id) {
            try {
                const response = await API.doctors.get(id);
                if (response.success && response.data) {
                    this.openModal(response.data);
                }
            } catch (error) {
                console.error('Error loading doctor:', error);
                this.showError('Failed to load doctor data');
            }
        }

        openModal(doctor = null) {
            const lang = (typeof app !== 'undefined' && app.lang) ? app.lang : 'en';

            if (doctor) {
                $('#doctorId').val(doctor.id);
                $('#doctorName').val(doctor.name);
                $('#doctorSpecialty').val(doctor.specialty);
                $('#doctorPhone').val(doctor.phone);
                $('#doctorModalTitle').attr('data-i18n', 'editDoctor').text(translations[lang]?.editDoctor || 'Edit Doctor');
            } else {
                $('#doctorForm')[0].reset();
                $('#doctorId').val('');
                $('#doctorModalTitle').attr('data-i18n', 'addDoctor').text(translations[lang]?.addDoctor || 'Add Doctor');
            }
            $('#doctorModal').modal('show');
        }

        async save() {
            const id = $('#doctorId').val();
            const data = {
                name: $('#doctorName').val().trim(),
                specialty: $('#doctorSpecialty').val(),
                phone: $('#doctorPhone').val().trim()
            };

            if (!data.name || !data.specialty || !data.phone) {
                this.showError('Please fill in all required fields');
                return;
            }

            const lang = (typeof app !== 'undefined' && app.lang) ? app.lang : 'en';

            try {
                if (id) {
                    await API.doctors.update(id, data);
                    this.showSuccess(translations[lang]?.doctorUpdated || 'Doctor updated successfully!');
                } else {
                    await API.doctors.create(data);
                    this.showSuccess(translations[lang]?.doctorAdded || 'Doctor added successfully!');
                }

                $('#doctorModal').modal('hide');
                await this.loadDoctors();
            } catch (error) {
                console.error('Error saving doctor:', error);
                this.showError(error.message || 'Failed to save doctor');
            }
        }

        deleteRequest(id) {
            this.currentDeleteId = id;
            $('#deleteModal').modal('show');
        }

        async confirmDelete() {
            if (this.currentDeleteId) {
                const lang = (typeof app !== 'undefined' && app.lang) ? app.lang : 'en';

                try {
                    await API.doctors.delete(this.currentDeleteId);
                    this.showSuccess(translations[lang]?.doctorDeleted || 'Doctor deleted successfully!');
                    this.currentDeleteId = null;
                    $('#deleteModal').modal('hide');
                    await this.loadDoctors();
                } catch (error) {
                    console.error('Error deleting doctor:', error);
                    this.showError('Failed to delete doctor');
                }
            }
        }

        async exportCSV() {
            try {
                const response = await API.doctors.getAll({ per_page: 1000 });
                const data = response.data.map(d => ({
                    ID: d.id,
                    Name: d.name,
                    Specialty: d.specialty,
                    Phone: d.phone,
                    Active: d.is_active ? 'Yes' : 'No'
                }));
                Utils.exportToCSV(data, 'doctors.csv');
            } catch (error) {
                this.showError('Failed to export data');
            }
        }

        async exportJSON() {
            try {
                const response = await API.doctors.getAll({ per_page: 1000 });
                Utils.exportToJSON(response.data, 'doctors.json');
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

    window.doctorsManager = new DoctorsManager();
    $(document).ready(() => {
        if (typeof API !== 'undefined') {
            window.doctorsManager.init();
        } else {
            const checkAPI = setInterval(() => {
                if (typeof API !== 'undefined') {
                    clearInterval(checkAPI);
                    window.doctorsManager.init();
                }
            }, 100);
        }
    });
}
