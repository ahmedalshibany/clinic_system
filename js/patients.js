if (typeof PatientsManager === 'undefined') {
    class PatientsManager {
        constructor() {
            this.storageKey = 'clinic_patients';
            const stored = this.loadFromStorage();

            if (stored && stored.length > 0) {
                this.patients = stored;
            } else {
                this.patients = [
                    { id: 1, name: "Ahmed Ali", age: 35, gender: "male", phone: "+966 50 123 4567", address: "Riyadh, Saudi Arabia", avatar: "https://ui-avatars.com/api/?name=Ahmed+Ali&background=0D8ABC&color=fff" },
                    { id: 2, name: "Sara Smith", age: 28, gender: "female", phone: "+966 55 987 6543", address: "Jeddah, Saudi Arabia", avatar: "https://ui-avatars.com/api/?name=Sara+Smith&background=567C8D&color=fff" },
                    { id: 3, name: "Mohammed Khan", age: 45, gender: "male", phone: "+966 54 111 2222", address: "Dammam, Saudi Arabia", avatar: "https://ui-avatars.com/api/?name=Mohammed+Khan&background=2F4156&color=fff" },
                    { id: 4, name: "Emily Davis", age: 62, gender: "female", phone: "+1 555 123 456", address: "New York, USA", avatar: "https://ui-avatars.com/api/?name=Emily+Davis&background=4aa87e&color=fff" }
                ];
                this.saveToStorage();
            }

            this.currentDeleteId = null;
            this.currentPage = 1;
            this.itemsPerPage = 10;
            this.searchTerm = '';
            this.sortColumn = null;
            this.sortOrder = 'asc';
        }

        loadFromStorage() {
            try {
                const data = localStorage.getItem(this.storageKey);
                return data ? JSON.parse(data) : null;
            } catch (error) {
                console.error('Error loading patients from storage:', error);
                return null;
            }
        }

        saveToStorage() {
            try {
                localStorage.setItem(this.storageKey, JSON.stringify(this.patients));
            } catch (error) {
                console.error('Error saving patients to storage:', error);
            }
        }

        init() {
            this.render();
            this.bindEvents();
        }

        bindEvents() {
            $(document).off('submit.patients click.patients input.patients');

            $(document).on('submit.patients', '#patientForm', (e) => {
                e.preventDefault();
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

            $(document).on('click.patients', '#confirmDeleteBtn', () => this.confirmDelete());

            $(document).on('input.patients', '#patientSearch', Utils.debounce((e) => {
                this.searchTerm = $(e.target).val().toLowerCase();
                this.currentPage = 1;
                this.render();
            }));

            $(document).on('click.patients', '[data-action="prev-page"]', () => this.prevPage());
            $(document).on('click.patients', '[data-action="next-page"]', () => this.nextPage());

            $(document).on('click.patients', '.sortable-header', (e) => {
                const column = $(e.currentTarget).data('sort');
                this.sortBy(column);
            });

            $(document).on('click.patients', '[data-action="export-csv"]', () => this.exportCSV());
            $(document).on('click.patients', '[data-action="export-json"]', () => this.exportJSON());

            $(document).on('layout-loaded', () => {
                if ($('#patientsTableBody').length) {
                    this.render();
                }
            });
        }

        getFilteredPatients() {
            let filtered = this.patients;

            if (this.searchTerm) {
                filtered = filtered.filter(p =>
                    p.name.toLowerCase().includes(this.searchTerm) ||
                    p.phone.includes(this.searchTerm) ||
                    p.address.toLowerCase().includes(this.searchTerm)
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

        getPaginatedPatients() {
            const filtered = this.getFilteredPatients();
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return {
                data: filtered.slice(start, end),
                total: filtered.length,
                totalPages: Math.ceil(filtered.length / this.itemsPerPage)
            };
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

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.render();
            }
        }

        nextPage() {
            const { totalPages } = this.getPaginatedPatients();
            if (this.currentPage < totalPages) {
                this.currentPage++;
                this.render();
            }
        }

        render() {
            const $tbody = $('#patientsTableBody');
            if ($tbody.length === 0) return;

            const { data, total, totalPages } = this.getPaginatedPatients();

            if (data.length === 0) {
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
                this.updatePagination(total, totalPages);
                return;
            }

            const html = data.map(patient => `
                <tr>
                    <td class="ps-4 fw-bold text-secondary"><span dir="ltr">${patient.id}</span></td>
                    <td>
                        <h6 class="mb-0 fw-bold text-dark">${patient.name}</h6>
                    </td>
                    <td>${patient.age}</td>
                    <td><span class="badge ${patient.gender === 'male' ? 'bg-info-subtle text-info' : 'bg-danger-subtle text-danger'} text-capitalize" data-i18n="${patient.gender}">${patient.gender}</span></td>
                    <td><span dir="ltr">${patient.phone}</span></td>
                    <td>${patient.address}</td>
                    <td class="pe-4">
                        <div class="d-flex justify-content-center gap-2">
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
            this.updatePagination(total, totalPages);
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

        updatePagination(total, totalPages) {
            const $pagination = $('.pagination-controls');
            if (!$pagination.length) return;

            const start = (this.currentPage - 1) * this.itemsPerPage + 1;
            const end = Math.min(this.currentPage * this.itemsPerPage, total);

            $pagination.find('.pagination-info').html(`
                Showing <strong>${start}-${end}</strong> of <strong>${total}</strong> patients
            `);

            $pagination.find('[data-action="prev-page"]').prop('disabled', this.currentPage === 1);
            $pagination.find('[data-action="next-page"]').prop('disabled', this.currentPage === totalPages || totalPages === 0);
        }

        add() {
            this.openModal();
        }

        edit(id) {
            const patient = this.patients.find(p => p.id === id);
            if (patient) {
                this.openModal(patient);
            }
        }

        openModal(patient = null) {
            if (patient) {
                $('#patientId').val(patient.id);
                $('#patientName').val(patient.name);
                $('#patientAge').val(patient.age);
                $('#patientGender').val(patient.gender);
                $('#patientPhone').val(patient.phone);
                $('#patientAddress').val(patient.address);
                $('#patientModalTitle').attr('data-i18n', 'editPatient').text(translations[app.lang]?.editPatient || 'Edit Patient');
            } else {
                $('#patientForm')[0].reset();
                $('#patientId').val('');
                $('#patientModalTitle').attr('data-i18n', 'addPatient').text(translations[app.lang]?.addPatient || 'Add Patient');
            }
            $('#patientModal').modal('show');
        }

        save() {
            const id = $('#patientId').val();
            const name = $('#patientName').val().trim();
            const age = $('#patientAge').val();
            const gender = $('#patientGender').val();
            const phone = $('#patientPhone').val().trim();
            const address = $('#patientAddress').val().trim();

            if (!name || !age || !phone || !address) {
                toast.error('Please fill in all required fields');
                return;
            }

            if (!Utils.validateAge(age)) {
                toast.error('Please enter a valid age (0-120)');
                return;
            }

            if (!Utils.validatePhone(phone)) {
                toast.error('Please enter a valid phone number');
                return;
            }

            if (!id && this.patients.some(p => p.phone === phone)) {
                toast.error('A patient with this phone number already exists');
                return;
            }

            if (id) {
                const index = this.patients.findIndex(p => p.id == id);
                if (index !== -1) {
                    this.patients[index] = { ...this.patients[index], name, age: parseInt(age), gender, phone, address };
                    toast.success(translations[app.lang]?.patientUpdated || 'Patient updated successfully!');
                }
            } else {
                const newId = this.patients.length > 0 ? Math.max(...this.patients.map(p => p.id)) + 1 : 1;
                const avatarColor = ['0D8ABC', '567C8D', '2F4156', '4aa87e'][Math.floor(Math.random() * 4)];
                const newPatient = {
                    id: newId,
                    name,
                    age: parseInt(age),
                    gender,
                    phone,
                    address,
                    avatar: `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=${avatarColor}&color=fff`
                };
                this.patients.push(newPatient);
                toast.success(translations[app.lang]?.patientAdded || 'Patient added successfully!');
            }

            this.saveToStorage();
            $('#patientModal').modal('hide');
            this.render();
        }

        deleteRequest(id) {
            this.currentDeleteId = id;
            $('#deleteModal').modal('show');
        }

        confirmDelete() {
            if (this.currentDeleteId) {
                this.patients = this.patients.filter(p => p.id !== this.currentDeleteId);
                this.currentDeleteId = null;
                this.saveToStorage();
                toast.success(translations[app.lang]?.patientDeleted || 'Patient deleted successfully!');

                $('#deleteModal').modal('hide');

                const { totalPages } = this.getPaginatedPatients();
                if (this.currentPage > totalPages && totalPages > 0) {
                    this.currentPage = totalPages;
                }

                this.render();
            }
        }

        exportCSV() {
            const data = this.patients.map(p => ({
                ID: p.id,
                Name: p.name,
                Age: p.age,
                Gender: p.gender,
                Phone: p.phone,
                Address: p.address
            }));
            Utils.exportToCSV(data, 'patients.csv');
        }

        exportJSON() {
            Utils.exportToJSON(this.patients, 'patients.json');
        }
    }

    window.patientsManager = new PatientsManager();
    $(document).ready(() => {
        window.patientsManager.init();
    });
} else {
    if (window.patientsManager) {
        $(document).ready(() => {
            window.patientsManager.init();
        });
    }
}
