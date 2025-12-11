/**
 * Doctors Manager (jQuery Version)
 * Handles doctor grid rendering, add/edit/delete logic, and modal interactions.
 */

if (typeof DoctorsManager === 'undefined') {
    class DoctorsManager {
        constructor() {
            this.doctors = [
                { id: 1, name: "Dr. Sarah Smith", specialty: "Cardiology", phone: "+1 234 567 890", avatar: "https://ui-avatars.com/api/?name=Sarah+Smith&background=0D8ABC&color=fff", rating: 4.8 },
                { id: 2, name: "Dr. John Doe", specialty: "Dermatology", phone: "+1 987 654 321", avatar: "https://ui-avatars.com/api/?name=John+Doe&background=567C8D&color=fff", rating: 4.5 },
                { id: 3, name: "Dr. Emily Blunt", specialty: "Pediatrics", phone: "+1 123 456 789", avatar: "https://ui-avatars.com/api/?name=Emily+Blunt&background=2F4156&color=fff", rating: 4.9 },
                { id: 4, name: "Dr. Michael Scott", specialty: "Neurology", phone: "+1 555 123 456", avatar: "https://ui-avatars.com/api/?name=Michael+Scott&background=4aa87e&color=fff", rating: 4.7 }
            ];
            this.currentDeleteId = null;
            // logic moved to init() called via document.ready
        }

        init() {
            // Initial render
            this.render();
            // Bind delegated events only once (using namespace or check)
            this.bindEvents();
        }

        bindEvents() {
            // Unbind first to avoid duplicates if re-initialized
            $(document).off('submit.doctors click.doctors');

            // Form Submit (Delegated)
            $(document).on('submit.doctors', '#doctorForm', (e) => {
                e.preventDefault();
                this.save();
            });

            // Delete Confirm (Delegated)
            $(document).on('click.doctors', '#confirmDeleteBtn', () => this.confirmDelete());

            // Re-render on layout load if we are on doctors page
            $(document).on('layout-loaded', () => {
                if ($('#doctorsGrid').length) {
                    this.render();
                }
            });
        }

        render() {
            const $grid = $('#doctorsGrid');
            if ($grid.length === 0) return;

            const html = this.doctors.map(doctor => `
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
                                <button class="btn btn-soft-primary btn-sm" onclick="doctorsManager.edit(${doctor.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-soft-danger btn-sm" onclick="doctorsManager.deleteRequest(${doctor.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');

            $grid.html(html);
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
                // Edit Mode
                $('#doctorId').val(doctor.id);
                $('#doctorName').val(doctor.name);
                $('#doctorSpecialty').val(doctor.specialty);
                $('#doctorPhone').val(doctor.phone);
                $('#doctorModalTitle').attr('data-i18n', 'editDoctor').text(translations[app.lang].editDoctor);
            } else {
                // Add Mode
                $('#doctorForm')[0].reset();
                $('#doctorId').val('');
                $('#doctorModalTitle').attr('data-i18n', 'addDoctor').text(translations[app.lang].addDoctor);
            }
            $('#doctorModal').modal('show');
        }

        save() {
            const id = $('#doctorId').val();
            const name = $('#doctorName').val();
            const specialty = $('#doctorSpecialty').val();
            const phone = $('#doctorPhone').val();

            if (id) {
                // Update
                const index = this.doctors.findIndex(d => d.id == id);
                if (index !== -1) {
                    this.doctors[index] = { ...this.doctors[index], name, specialty, phone };
                    app.showAlert(translations[app.lang].doctorUpdated, 'success');
                }
            } else {
                // Add
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
                app.showAlert(translations[app.lang].doctorAdded, 'success');
            }

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
                app.showAlert(translations[app.lang].doctorDeleted, 'danger');

                $('#deleteModal').modal('hide');
                this.render();
            }
        }
    }

    // Initialize logic
    // Assign to window to ensure global access for onclick attributes
    window.doctorsManager = new DoctorsManager();
    $(document).ready(() => {
        window.doctorsManager.init();
    });
} else {
    // If re-loaded, just re-initialze
    if (window.doctorsManager) {
        // If loaded via Ajax, document is likely ready, but safety first
        $(document).ready(() => {
            window.doctorsManager.init();
        });
    }
}
