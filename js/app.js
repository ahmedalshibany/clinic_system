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
        this.init();
    }

    init() {
        this.applyLanguage(this.lang);
        this.bindEvents();
        this.checkAuth();
    }

    bindEvents() {
        const langToggle = document.getElementById('langToggle');
        if (langToggle) {
            langToggle.addEventListener('click', () => this.toggleLanguage());
        }

        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => this.handleLogin(e));
        }

        const forms = document.querySelectorAll('form:not(#loginForm)');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        });

        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.logout();
            });
        }

        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                document.querySelector('.sidebar').classList.toggle('active');
            });
        }
    }

    checkAuth() {

    }

    toggleLanguage() {
        this.lang = this.lang === 'en' ? 'ar' : 'en';
        localStorage.setItem('clinic_lang', this.lang);
        this.applyLanguage(this.lang);
    }

    applyLanguage(lang) {
        document.documentElement.lang = lang;
        document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';

        const elements = document.querySelectorAll('[data-i18n]');
        elements.forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (translations[lang][key]) {
                if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                    el.placeholder = translations[lang][key];
                } else {
                    el.textContent = translations[lang][key];
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
