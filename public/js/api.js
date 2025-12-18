/**
 * API Service - Centralized API calls for the Clinic System
 */
const API = {
    baseUrl: '/api',

    // Get CSRF token from meta tag
    getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    },

    // Default fetch options
    getDefaultOptions() {
        return {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        };
    },

    // Generic request method
    async request(endpoint, options = {}) {
        try {
            const url = `${this.baseUrl}${endpoint}`;
            const config = {
                ...this.getDefaultOptions(),
                ...options,
                headers: {
                    ...this.getDefaultOptions().headers,
                    ...options.headers
                }
            };

            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Request failed');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    // GET request
    async get(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        return this.request(url, { method: 'GET' });
    },

    // POST request
    async post(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },

    // PUT request
    async put(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },

    // DELETE request
    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    },

    // ==================== AUTH ====================
    auth: {
        async login(username, password) {
            return API.post('/login', { username, password });
        },
        async logout() {
            return API.post('/logout');
        },
        async user() {
            return API.get('/user');
        }
    },

    // ==================== PATIENTS ====================
    patients: {
        async getAll(params = {}) {
            return API.get('/patients', params);
        },
        async get(id) {
            return API.get(`/patients/${id}`);
        },
        async create(data) {
            return API.post('/patients', data);
        },
        async update(id, data) {
            return API.put(`/patients/${id}`, data);
        },
        async delete(id) {
            return API.delete(`/patients/${id}`);
        },
        async search(query) {
            return API.get('/patients/search', { q: query });
        },
        async history(id) {
            return API.get(`/patients/${id}/history`);
        }
    },

    // ==================== DOCTORS ====================
    doctors: {
        async getAll(params = {}) {
            return API.get('/doctors', params);
        },
        async get(id) {
            return API.get(`/doctors/${id}`);
        },
        async create(data) {
            return API.post('/doctors', data);
        },
        async update(id, data) {
            return API.put(`/doctors/${id}`, data);
        },
        async delete(id) {
            return API.delete(`/doctors/${id}`);
        },
        async search(query) {
            return API.get('/doctors/search', { q: query });
        },
        async schedule(id, date) {
            return API.get(`/doctors/${id}/schedule`, { date });
        }
    },

    // ==================== APPOINTMENTS ====================
    appointments: {
        async getAll(params = {}) {
            return API.get('/appointments', params);
        },
        async get(id) {
            return API.get(`/appointments/${id}`);
        },
        async create(data) {
            return API.post('/appointments', data);
        },
        async update(id, data) {
            return API.put(`/appointments/${id}`, data);
        },
        async delete(id) {
            return API.delete(`/appointments/${id}`);
        },
        async today() {
            return API.get('/appointments/today');
        },
        async recent(limit = 5) {
            return API.get('/appointments/recent', { limit });
        },
        async stats(filter = 'all') {
            return API.get('/appointments/stats', { filter });
        },
        async weeklyTrend() {
            return API.get('/appointments/weekly-trend');
        }
    },

    // ==================== DASHBOARD ====================
    dashboard: {
        async stats(filter = 'all') {
            return API.get('/dashboard/stats', { filter });
        },
        async weeklyTrend() {
            return API.get('/dashboard/weekly-trend');
        },
        async recentAppointments(limit = 5) {
            return API.get('/dashboard/recent-appointments', { limit });
        },
        async statusDistribution(filter = 'all') {
            return API.get('/dashboard/status-distribution', { filter });
        }
    }
};

// Make API available globally
window.API = API;
