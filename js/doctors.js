if (typeof DoctorsManager === 'undefined') {
    class DoctorsManager {
        constructor() {
            this.storageKey = 'clinic_doctors';
            const stored = this.loadFromStorage();

            if (stored && stored.length > 0) {
                this.doctors = stored;
            } else {
                this.doctors = [
                    { id: 1, name: "Dr. Sarah Smith", specialty: "Cardiology", phone: "+1 234 567 890", avatar: "https://ui-avatars.com/api/?name=Sarah+Smith&background=0D8ABC&color=fff", rating: 4.8 },
                    { id: 2, name: "Dr. John Doe", specialty: "Dermatology", phone: "+1 987 654 321", avatar: "https://ui-avatars.com/api/?name=John+Doe&background=567C8D&color=fff", rating: 4.5 },
                    { id: 3, name: "Dr. Emily Blunt", specialty: "Pediatrics", phone: "+1 123 456 789", avatar: "https://ui-avatars.com/api/?name=Emily+Blunt&background=2F4156&color=fff", rating: 4.9 },
                    { id: 4, name: "Dr. Michael Scott", specialty: "Neurology", phone: "+1 555 123 456", avatar: "https://ui-avatars.com/api/?name=Michael+Scott&background=4aa87e&color=fff", rating: 4.7 }
                ];
                this.saveToStorage();
            }

            this.currentDeleteId = null;
            this.currentPage = 1;
            this.itemsPerPage = 8;
            this.searchTerm = '';
            this.sortColumn = null;
            this.sortOrder = 'asc';
        }

        loadFromStorage() {
            try {
                const data = localStorage.getItem(this.storageKey);
                return data ? JSON.parse(data) : null;
            } catch (error) {
                console.error('Error loading doctors from storage:', error);
                return null;
            }
        }

        saveToStorage() {
            try {
                localStorage.setItem(this.storageKey, JSON.stringify(this.doctors));
            } catch (error) {
                console.error('Error saving doctors to storage:', error);
            }
        }

        init() {
            this.render();
            this.bindEvents();
        }

        bindEvents() {
            $(document).off('submit.doctors click.doctors input.doctors');

            $(document).on('submit.doctors', '#doctorForm', (e) => {
                e.preventDefault();
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
                this.searchTerm = $(e.target).val().toLowerCase();
                this.currentPage = 1;
                this.render();
            }));

            $(document).on('click.doctors', '[data-action="prev-page"]', () => this.prevPage());
            $(document).on('click.doctors', '[data-action="next-page"]', () => this.nextPage());

            $(document).on('click.doctors', '[data-action="export-csv"]', () => this.exportCSV());
            $(document).on('click.doctors', '[data-action="export-json"]', () => this.exportJSON());

            $(document).on('layout-loaded', () => {
                if ($('#doctorsGrid').length) {
                    this.render();
                }
            });
        }

        getFilteredDoctors() {
            let filtered = this.doctors;

            if (this.searchTerm) {
                filtered = filtered.filter(d =>
                    d.name.toLowerCase().includes(this.searchTerm) ||
                    d.specialty.toLowerCase().includes(this.searchTerm) ||
                    d.phone.includes(this.searchTerm)
                );
            }

            if (this.sortColumn) {
                filtered = [...filtered].sort((a, b) => {
                    let valA = a[this.sortColumn];
                    let valB = b[this.sortColumn];

                    if (typeof valA === 'string') valA = valA.toLowerCase();
                    if (typeof valB === 'string') valB = valB.toLowerCase();

                    if (valA < valB) return this.sortOrder === 'asc' ? -1 : 1;
                    if (valA > valB) return this.sortOrder === 'asc' ? 1 : -1;
                    return 0;
                });
            }

            return filtered;
        }

        getPaginatedDoctors() {
            const filtered = this.getFilteredDoctors();
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return {
                data: filtered.slice(start, end),
                total: filtered.length,
                totalPages: Math.ceil(filtered.length / this.itemsPerPage)
            };
        }

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.render();
            }
        }

        nextPage() {
            const { totalPages } = this.getPaginatedDoctors();
            if (this.currentPage < totalPages) {
                this.currentPage++;
                this.render();
            }
        }

        render() {
            const $grid = $('#doctorsGrid');
            if ($grid.length === 0) return;

            const { data, total, totalPages } = this.getPaginatedDoctors();

            if (data.length === 0) {
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
                this.updatePagination(total, totalPages);
                return;
            }

            const html = data.map(doctor => `
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="card doctor-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="doctor-avatar mb-3">
                                <img src="${doctor.avatar}" alt="${doctor.name}">
                                <div class="status-indicator"></div>
                            </div>
                            <h5 class="fw-bold text-primary mb-1">${doctor.name}</h5>
                            <p class="text-secondary small mb-3">
                                ${translations[app.lang]['spec_' + (doctor.specialty === 'General Practice' ? 'general' : doctor.specialty.toLowerCase())] || doctor.specialty}
                            </p>

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
            `).join('');

            $grid.html(html);
            this.updatePagination(total, totalPages);

            if (window.app && window.app.applyLanguage) {
                window.app.applyLanguage(window.app.lang);
            }
        }

        updatePagination(total, totalPages) {
            const $pagination = $('.pagination-controls');
            if (!$pagination.length) return;

            const start = (this.currentPage - 1) * this.itemsPerPage + 1;
            const end = Math.min(this.currentPage * this.itemsPerPage, total);

            $pagination.find('.pagination-info').html(`
                Showing <strong>${start}-${end}</strong> of <strong>${total}</strong> doctors
            `);

            $pagination.find('[data-action="prev-page"]').prop('disabled', this.currentPage === 1);
            $pagination.find('[data-action="next-page"]').prop('disabled', this.currentPage === totalPages || totalPages === 0);
        }

        add() {
            this.openModal();
        }

        edit(id) {
            const doctor = this.doctors.find(d => d.id === id);
            if (doctor) {
                this.openModal(doctor);
            }
        }

        openModal(doctor = null) {
            if (doctor) {
                $('#doctorId').val(doctor.id);
                $('#doctorName').val(doctor.name);
                $('#doctorSpecialty').val(doctor.specialty);
                $('#doctorPhone').val(doctor.phone);
                $('#doctorModalTitle').attr('data-i18n', 'editDoctor').text(translations[app.lang]?.editDoctor || 'Edit Doctor');
            } else {
                $('#doctorForm')[0].reset();
                $('#doctorId').val('');
                $('#doctorModalTitle').attr('data-i18n', 'addDoctor').text(translations[app.lang]?.addDoctor || 'Add Doctor');
            }
            $('#doctorModal').modal('show');
        }

        save() {
            const id = $('#doctorId').val();
            const name = $('#doctorName').val().trim();
            const specialty = $('#doctorSpecialty').val();
            const phone = $('#doctorPhone').val().trim();

            if (!name || !specialty || !phone) {
                toast.error('Please fill in all required fields');
                return;
            }

            if (!Utils.validatePhone(phone)) {
                toast.error('Please enter a valid phone number');
                return;
            }

            if (!id && this.doctors.some(d => d.phone === phone)) {
                toast.error('A doctor with this phone number already exists');
                return;
            }

            if (id) {
                const index = this.doctors.findIndex(d => d.id == id);
                if (index !== -1) {
                    this.doctors[index] = { ...this.doctors[index], name, specialty, phone };
                    toast.success(translations[app.lang].doctorUpdated);
                }
            } else {
                const newId = this.doctors.length > 0 ? Math.max(...this.doctors.map(d => d.id)) + 1 : 1;
                const avatarColor = ['0D8ABC', '567C8D', '2F4156', '4aa87e'][Math.floor(Math.random() * 4)];
                const newDoctor = {
                    id: newId,
                    name,
                    specialty,
                    phone,
                    rating: 5.0,
                    avatar: `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=${avatarColor}&color=fff`
                };
                this.doctors.push(newDoctor);
                toast.success(translations[app.lang].doctorAdded);
            }

            this.saveToStorage();
            $('#doctorModal').modal('hide');
            this.render();
        }

        deleteRequest(id) {
            this.currentDeleteId = id;
            $('#deleteModal').modal('show');
        }

        confirmDelete() {
            if (this.currentDeleteId) {
                this.doctors = this.doctors.filter(d => d.id !== this.currentDeleteId);
                this.currentDeleteId = null;
                this.saveToStorage();
                toast.success(translations[app.lang].doctorDeleted);

                $('#deleteModal').modal('hide');

                const { totalPages } = this.getPaginatedDoctors();
                if (this.currentPage > totalPages && totalPages > 0) {
                    this.currentPage = totalPages;
                }

                this.render();
            }
        }

        exportCSV() {
            const data = this.doctors.map(d => ({
                ID: d.id,
                Name: d.name,
                Specialty: d.specialty,
                Phone: d.phone,
                Rating: d.rating
            }));
            Utils.exportToCSV(data, 'doctors.csv');
        }

        exportJSON() {
            Utils.exportToJSON(this.doctors, 'doctors.json');
        }
    }

    window.doctorsManager = new DoctorsManager();
    $(document).ready(() => {
        if (typeof Utils !== 'undefined') {
            window.doctorsManager.init();
        } else {
            const checkUtils = setInterval(() => {
                if (typeof Utils !== 'undefined') {
                    clearInterval(checkUtils);
                    window.doctorsManager.init();
                }
            }, 100);
        }
    });
} else {
    if (window.doctorsManager) {
        $(document).ready(() => {
            if (typeof Utils !== 'undefined') {
                window.doctorsManager.init();
            } else {
                const checkUtils = setInterval(() => {
                    if (typeof Utils !== 'undefined') {
                        clearInterval(checkUtils);
                        window.doctorsManager.init();
                    }
                }, 100);
            }
        });
    }
}
