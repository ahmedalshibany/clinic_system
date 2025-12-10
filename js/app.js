const translations = {
    en: {
        appTitle: "Clinic System",
        loginTitle: "Welcome Back",
        loginSubtitle: "Sign in to access your dashboard",
        emailLabel: "Email Address",
        passwordLabel: "Password",
        loginBtn: "Login",
        dashboard: "Dashboard",
        patients: "Patients",
        appointments: "Appointments",
        doctors: "Doctors",
        settings: "Settings",
        logout: "Logout",
        totalPatients: "Total Patients",
        todayAppts: "Today's Appointments",
        totalDoctors: "Total Doctors",
        addPatient: "Add Patient",
        fullName: "Full Name",
        age: "Age",
        phone: "Phone Number",
        gender: "Gender",
        male: "Male",
        female: "Female",
        address: "Address",
        submit: "Submit",
        save: "Save Changes",
        bookAppt: "Book Appointment",
        doctorName: "Doctor Name",
        date: "Date",
        time: "Time",
        status: "Status",
        pending: "Pending",
        confirmed: "Confirmed",
        completed: "Completed",
        specialty: "Specialty",
        workingDays: "Working Days",
        changeUser: "Change Username",
        changePass: "Change Password",
        newUsername: "New Username",
        newPassword: "New Password",
        loginSuccess: "Login Successful! Redirecting...",
        validationError: "Please fill in all fields.",
        genericSuccess: "Action completed successfully!",
        langBtn: "العربية",
        welcomeDr: "Welcome, Dr. Smith",
        recentPatients: "Recent Patients",
        patientName: "Patient Name",
        actions: "Actions",
        view: "View",
        incomingPatientHistory: "Incoming Patient History",
        weekly: "Weekly",
        monthly: "Monthly",
        yearly: "Yearly",
        consultation: "CONSULTATION",
        inProgress: "IN PROGRESS",
        inReview: "IN REVIEW",
        inPending: "IN PENDING",
        goodMorning: "Good Morning!",
        doctorName2: "Dr. John Jacob",
        specialization: "Orthopedical",
        bookingRate: "Booking Rate",
        yourTotalPatient: "Your Total Patient",
        onFriday: "on Friday",
        bookingHigher: "Your booking is higher than yesterday",
        yourPatientsToday: "Your Patients Today",
        mySchedule: "My Schedule",
        searchPlaceholder: "Search...",
        sun: "SUN",
        mon: "MON",
        tue: "TUE",
        wed: "WED",
        thu: "THU",
        fri: "FRI",
        sat: "SAT",
        role: "Orthopedical",
        addDoctor: "Add Doctor"
    },
    ar: {
        appTitle: "نظام العيادة",
        loginTitle: "مرحباً بعودتك",
        loginSubtitle: "سجل الدخول للوصول إلى لوحة التحكم",
        emailLabel: "البريد الإلكتروني",
        passwordLabel: "كلمة المرور",
        loginBtn: "تحديث الدخول",
        dashboard: "لوحة التحكم",
        patients: "المرضى",
        appointments: "المواعيد",
        doctors: "الأطباء",
        settings: "الإعدادات",
        logout: "تسجيل الخروج",
        totalPatients: "إجمالي المرضى",
        todayAppts: "مواعيد اليوم",
        totalDoctors: "إجمالي الأطباء",
        addPatient: "إضافة مريض",
        fullName: "الاسم الكامل",
        age: "العمر",
        phone: "رقم الهاتف",
        gender: "الجنس",
        male: "ذكر",
        female: "أنثى",
        address: "العنوان",
        submit: "إرسال",
        save: "حفظ التغييرات",
        bookAppt: "حجز موعد",
        doctorName: "اسم الطبيب",
        date: "التاريخ",
        time: "الوقت",
        status: "الحالة",
        pending: "قيد الانتظار",
        confirmed: "مؤكد",
        completed: "مكتمل",
        specialty: "التخصص",
        workingDays: "أيام العمل",
        changeUser: "تغيير اسم المستخدم",
        changePass: "تغيير كلمة المرور",
        newUsername: "اسم المستخدم الجديد",
        newPassword: "كلمة المرور الجديدة",
        loginSuccess: "تم تسجيل الدخول بنجاح! جاري التحويل...",
        validationError: "يرجى ملء جميع الحقول.",
        genericSuccess: "تم تنفيذ الإجراء بنجاح!",
        langBtn: "English",
        welcomeDr: "مرحباً، د. سميث",
        recentPatients: "المرضى الجدد",
        patientName: "اسم المريض",
        actions: "الإجراءات",
        view: "عرض",
        incomingPatientHistory: "تاريخ المرضى الواردين",
        weekly: "أسبوعي",
        monthly: "شهري",
        yearly: "سنوي",
        consultation: "استشارة",
        inProgress: "قيد التنفيذ",
        inReview: "قيد المراجعة",
        inPending: "قيد الانتظار",
        goodMorning: "صباح الخير!",
        doctorName2: "د. جون جاكوب",
        specialization: "جراحة العظام",
        bookingRate: "معدل الحجز",
        yourTotalPatient: "إجمالي مرضاك",
        onFriday: "يوم الجمعة",
        bookingHigher: "حجزك أعلى من الأمس",
        yourPatientsToday: "مرضاك اليوم",
        mySchedule: "جدولي",
        searchPlaceholder: "بحث...",
        sun: "الأحد",
        mon: "الاثنين",
        tue: "الثلاثاء",
        wed: "الأربعاء",
        thu: "الخميس",
        fri: "الجمعة",
        sat: "السبت",
        role: "جراحة العظام",
        addDoctor: "إضافة طبيب"
    }
};

class App {
    constructor() {
        this.lang = localStorage.getItem('clinic_lang') || 'en';
        this.theme = localStorage.getItem('clinic_theme') || 'light';
        this.init();
    }

    init() {
        this.applyLanguage(this.lang);
        this.applyTheme(this.theme); // Apply saved theme
        this.bindEvents();
        this.initCharts(); // Initialize charts
        this.checkAuth();

        // Re-bind events when layout is injected dynamically
        document.addEventListener('layout-loaded', () => {
            this.bindEvents();
            this.applyLanguage(this.lang);
            this.applyTheme(this.theme); // Re-apply theme to new button
        });
    }

    initCharts() {
        const ctx = document.getElementById('bookingChart');
        if (ctx && typeof Chart !== 'undefined') {
            // Basic config, can be enhanced
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'],
                    datasets: [{
                        label: 'Patients',
                        data: [30, 45, 40, 50, 42, 85, 25],
                        backgroundColor: '#2dd4bf', /* Neon teal matching theme */
                        borderRadius: 50,
                        barThickness: 12,
                        hoverBackgroundColor: '#00f2fe'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(47, 65, 86, 0.9)',
                            padding: 12,
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        y: {
                            display: false,
                            grid: { display: false }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: '#94a3b8',
                                font: { family: "'Inter', sans-serif" }
                            }
                        }
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart'
                    }
                }
            });
        }
    }

    bindEvents() {
        // Language Toggle - Fix duplicate listeners
        const langToggle = document.getElementById('langToggle');
        if (langToggle) {
            if (this.langHandler) langToggle.removeEventListener('click', this.langHandler);
            this.langHandler = () => this.toggleLanguage();
            langToggle.addEventListener('click', this.langHandler);
        }

        // Theme Toggle - Fix duplicate listeners
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            if (this.themeHandler) themeToggle.removeEventListener('click', this.themeHandler);
            this.themeHandler = () => this.toggleTheme();
            themeToggle.addEventListener('click', this.themeHandler);
        }

        // Login Form (only on login page, not static)
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => this.handleLogin(e));
        }

        // Dynamic Forms (re-bind safely? these usually strictly inside content so replaced on nav)
        const forms = document.querySelectorAll('form:not(#loginForm)');
        forms.forEach(form => {
            // Forms inside main-content are replaced, so strictly simple addEventListener is fine
            form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        });

        // Logout Button (Static in Sidebar)
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            if (this.logoutHandler) logoutBtn.removeEventListener('click', this.logoutHandler);
            this.logoutHandler = (e) => {
                e.preventDefault();
                this.logout();
            };
            logoutBtn.addEventListener('click', this.logoutHandler);
        }

        // Sidebar Toggle (Static in Header)
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            if (this.sidebarHandler) sidebarToggle.removeEventListener('click', this.sidebarHandler);
            this.sidebarHandler = () => {
                document.querySelector('.sidebar').classList.toggle('active');
            };
            sidebarToggle.addEventListener('click', this.sidebarHandler);
        }
    }

    checkAuth() {

    }

    toggleLanguage() {
        this.lang = this.lang === 'en' ? 'ar' : 'en';
        localStorage.setItem('clinic_lang', this.lang);
        this.applyLanguage(this.lang);
    }

    toggleTheme() {
        this.theme = this.theme === 'light' ? 'dark' : 'light';
        localStorage.setItem('clinic_theme', this.theme);
        this.applyTheme(this.theme);
    }

    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        const btn = document.getElementById('themeToggle');
        if (btn) {
            btn.innerHTML = theme === 'dark' ? '<i class="fas fa-sun text-warning"></i>' : '<i class="fas fa-moon"></i>';
        }
    }

    applyLanguage(lang) {
        document.documentElement.lang = lang;
        document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';

        const elements = document.querySelectorAll('[data-i18n]');
        elements.forEach(el => {
            const key = el.getAttribute('data-i18n');
            // Safe check for translation existence
            if (translations[lang] && translations[lang][key]) {
                if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                    el.placeholder = translations[lang][key];
                } else {
                    // Prevent wiping icons if present (simple check)
                    if (el.children.length > 0 && el.lastChild.nodeType === 3) {
                        el.lastChild.textContent = translations[lang][key];
                    } else {
                        el.textContent = translations[lang][key];
                    }
                }
            }
        });

        const placeholders = document.querySelectorAll('[data-i18n-placeholder]');
        placeholders.forEach(el => {
            const key = el.getAttribute('data-i18n-placeholder');
            if (translations[lang][key]) {
                el.placeholder = translations[lang][key];
            }
        });

        const langBtn = document.getElementById('langToggleText');
        if (langBtn) {
            langBtn.textContent = translations[lang].langBtn;
        }
    }

    handleLogin(e) {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        if (email && password) {
            const btn = e.target.querySelector('button');
            const originalText = btn.textContent;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
            btn.disabled = true;

            setTimeout(() => {
                this.showAlert(translations[this.lang].loginSuccess, 'success');
                setTimeout(() => {
                    window.location.href = 'dashboard.html';
                }, 1000);
            }, 1000);
        } else {
            this.showAlert(translations[this.lang].validationError, 'danger');
        }
    }

    handleFormSubmit(e) {
        e.preventDefault();
        const inputs = e.target.querySelectorAll('input, select, textarea');
        let isValid = true;

        inputs.forEach(input => {
            if (input.hasAttribute('required') && !input.value) {
                isValid = false;
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
            }
        });

        if (isValid) {
            const btn = e.target.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            btn.disabled = true;

            setTimeout(() => {
                this.showAlert(translations[this.lang].genericSuccess, 'success');
                e.target.reset();
                btn.textContent = originalText;
                btn.disabled = false;
            }, 1000);
        } else {
            this.showAlert(translations[this.lang].validationError, 'danger');
        }
    }

    logout() {
        window.location.href = 'index.html';
    }

    showAlert(message, type) {
        const alertEl = document.createElement('div');
        alertEl.className = `alert alert-${type} custom-alert show`;
        alertEl.textContent = message;
        document.body.appendChild(alertEl);

        alertEl.style.display = 'block';

        setTimeout(() => {
            alertEl.remove();
        }, 3000);
    }
}

// Initialize App
const app = new App();
