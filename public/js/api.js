/**
 * API Service - Centralized API calls for the Clinic System
 */
const API = {
    baseUrl: '',

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

    // ==================== PATIENTS ====================
    patients: {
        async search(query) {
            return API.get('/api/patients/search', { q: query });
        }
    },

    // ==================== MEDICINES ====================
    medicines: {
        async search(query) {
            return API.get('/api/medicines/search', { q: query });
        }
    },

    // ==================== DASHBOARD ====================
    dashboard: {
        async stats(filter = 'all') {
            return API.get('/api/dashboard/stats', { filter });
        },
        async weeklyTrend() {
            return API.get('/api/dashboard/weekly-trend');
        },
        async recentAppointments(limit = 5) {
            return API.get('/api/dashboard/recent-appointments', { limit });
        },
        async statusDistribution(filter = 'all') {
            return API.get('/api/dashboard/status-distribution', { filter });
        }
    }
};

// Make API available globally
window.API = API;
