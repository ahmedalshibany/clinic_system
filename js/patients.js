/**
 * Patients Manager
 * Handles patients table rendering and interactions.
 */

if (typeof PatientsManager === 'undefined') {
    class PatientsManager {
        constructor() {
            this.storageKey = 'clinic_patients';
            this.patients = this.loadFromStorage() || [
                { id: 1, name: "Ahmed Ali", age: 35, gender: "male", phone: "+966 50 123 4567", address: "Riyadh, Saudi Arabia", avatar: "https://ui-avatars.com/api/?name=Ahmed+Ali&background=0D8ABC&color=fff" },
                { id: 2, name: "Sara Smith", age: 28, gender: "female", phone: "+966 55 987 6543", address: "Jeddah, Saudi Arabia", avatar: "https://ui-avatars.com/api/?name=Sara+Smith&background=567C8D&color=fff" },
                { id: 3, name: "Mohammed Khan", age: 45, gender: "male", phone: "+966 54 111 2222", address: "Dammam, Saudi Arabia", avatar: "https://ui-avatars.com/api/?name=Mohammed+Khan&background=2F4156&color=fff" },
                { id: 4, name: "Emily Davis", age: 62, gender: "female", phone: "+1 555 123 456", address: "New York, USA", avatar: "https://ui-avatars.com/api/?name=Emily+Davis&background=4aa87e&color=fff" }
            ];
            this.currentDeleteId = null;
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
            $(document).off('submit.patients click.patients');

            // Form Submit
            $(document).on('submit.patients', '#patientForm', (e) => {
                e.preventDefault();
                this.save();
            });

            // Edit Patient Button
            $(document).on('click.patients', '[data-action="edit-patient"]', (e) => {
                const id = parseInt($(e.currentTarget).data('patient-id'));
                this.edit(id);
            });

            // Delete Patient Button
            $(document).on('click.patients', '[data-action="delete-patient"]', (e) => {
                const id = parseInt($(e.currentTarget).data('patient-id'));
                this.deleteRequest(id);
            });

            // Delete Confirm
            $(document).on('click.patients', '#confirmDeleteBtn', () => this.confirmDelete());

            // Re-render on layout load
            $(document).on('layout-loaded', () => {
                if ($('#patientsTableBody').length) {
                    this.render();
                }
            });
        }

        render() {
            const $tbody = $('#patientsTableBody');
            if ($tbody.length === 0) return;

            const html = this.patients.map(patient => `
                <tr>
                    <td class="ps-4 fw-bold text-secondary">${patient.id}</td>
                    <td>
                        <h6 class="mb-0 fw-bold text-dark">${patient.name}</h6>
                    </td>
                    <td>${patient.age}</td>
                    <td><span class="badge ${patient.gender === 'male' ? 'bg-info-subtle text-info' : 'bg-danger-subtle text-danger'} text-capitalize" data-i18n="${patient.gender}">${patient.gender}</span></td>
                    <td dir="ltr" class="text-end">${patient.phone}</td>
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

            // Re-run translations for injected content (gender badges)
            if (window.app && window.app.updateContent) {
                window.app.updateContent();
            }
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
                // Edit Mode
                $('#patientId').val(patient.id);
                $('#patientName').val(patient.name);
                $('#patientAge').val(patient.age);
                $('#patientGender').val(patient.gender);
                $('#patientPhone').val(patient.phone);
                $('#patientAddress').val(patient.address);
                $('#patientModalTitle').attr('data-i18n', 'editPatient').text(translations[app.lang]?.editPatient || 'Edit Patient');
            } else {
                // Add Mode
                $('#patientForm')[0].reset();
                $('#patientId').val('');
                $('#patientModalTitle').attr('data-i18n', 'addPatient').text(translations[app.lang]?.addPatient || 'Add Patient');
            }
            $('#patientModal').modal('show');
        }

        save() {
            const id = $('#patientId').val();
            const name = $('#patientName').val();
            const age = $('#patientAge').val();
            const gender = $('#patientGender').val();
            const phone = $('#patientPhone').val();
            const address = $('#patientAddress').val();

            if (id) {
                // Update
                const index = this.patients.findIndex(p => p.id == id);
                if (index !== -1) {
                    this.patients[index] = { ...this.patients[index], name, age, gender, phone, address };
                    app.showAlert(translations[app.lang]?.patientUpdated || 'Patient updated successfully!', 'success');
                }
            } else {
                // Add
                const newId = this.patients.length > 0 ? Math.max(...this.patients.map(p => p.id)) + 1 : 1;
                const avatarColor = ['0D8ABC', '567C8D', '2F4156', '4aa87e'][Math.floor(Math.random() * 4)];
                const newPatient = {
                    id: newId,
                    name,
                    age,
                    gender,
                    phone,
                    address,
                    avatar: `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=${avatarColor}&color=fff`
                };
                this.patients.push(newPatient);
                app.showAlert(translations[app.lang]?.patientAdded || 'Patient added successfully!', 'success');
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
                app.showAlert(translations[app.lang]?.patientDeleted || 'Patient deleted successfully!', 'danger');

                $('#deleteModal').modal('hide');
                this.render();
            }
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
