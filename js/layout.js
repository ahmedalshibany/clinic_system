/**
 * Layout Manager
 * Fetches layout.html and injects separate Sidebar and Header into pages
 */

const Layout = {
    init: async function () {
        if (document.querySelector('.login-container')) return; // Don't run on login page

        // Load CSS first - Force no-cache
        this.loadCSS(`css/layout.css?v=${Date.now() + 1}`);

        try {
            // Fetch the layout HTML file - Force no-cache
            const response = await fetch(`layout.html?v=${new Date().getTime()}`);
            if (!response.ok) throw new Error('Failed to load layout.html');
            const text = await response.text();

            // Parse it
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');

            // Inject Sidebar
            const sidebarTemplate = doc.getElementById('sidebar-template');
            if (sidebarTemplate) {
                const sidebarContent = sidebarTemplate.content.cloneNode(true);
                // We need to inject it into the body, but usually before script tags or at top
                document.body.insertAdjacentElement('afterbegin', sidebarContent.querySelector('.sidebar'));
            }

            // Inject Header
            const headerTemplate = doc.getElementById('header-template');
            if (headerTemplate) {
                this.renderHeader(headerTemplate.content.cloneNode(true));
            }

            this.highlightActiveLink();
            this.initMobileToggle();

            // Dispatch event so App knows layout is ready
            document.dispatchEvent(new Event('layout-loaded'));

        } catch (err) {
            console.error('Layout loading failed:', err);
        }
    },

    loadCSS: function (href) {
        if (!document.querySelector(`link[href="${href}"]`)) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            document.head.appendChild(link);
        }
    },

    renderHeader: function (headerContent) {
        let mainWrapper = document.querySelector('.main-wrapper');
        if (!mainWrapper) return;

        let topHeader = document.querySelector('.top-header');
        if (!topHeader) {
            topHeader = document.createElement('div');
            topHeader.className = 'top-header';
            mainWrapper.prepend(topHeader);
        }

        // Get existing page title if any (generic h4 check)
        // We will look for an h4 in the body or specifically passed in existing header 
        // But since we wiped it in the HTML files, we need to grab the title from somewhere else or let the page insert it.
        // In my previous step's dashboard.html, I removed the header but not the logic to put it there.
        // Actually, in the previous step I left <div class="top-header">...</div> injected by js/layout.js comment
        // and a MAIN CONTENT header title.
        // Let's grab the title from the main-content if it exists (h4.fw-bold) and move it to header if needed
        // OR just render the header.

        // Let's try to match what we had: 
        // The template has <div id="page-title-container"></div>
        // We can move a page title there if we find one in .main-content h4

        topHeader.innerHTML = ''; // Clear
        topHeader.appendChild(headerContent);

        const pageTitle = document.querySelector('.main-content h4');
        const titleContainer = document.querySelector('#page-title-container');
        if (pageTitle && titleContainer) {
            // Clone or move
            // Moving it cleans up the main content area
            titleContainer.appendChild(pageTitle);
            // Ensure styling is correct
            pageTitle.classList.add('header-title', 'mb-0');
        } else if (titleContainer) {
            titleContainer.innerHTML = '<h4 class="mb-0 fw-bold header-title">Clinic System</h4>';
        }
    },

    highlightActiveLink: function () {
        const currentPage = window.location.pathname.split('/').pop() || 'index.html';
        const links = document.querySelectorAll('.sidebar .nav-link');

        links.forEach(link => {
            // Simple match or partial match
            if (link.dataset.page === currentPage) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    },

    initMobileToggle: function () {
        const toggleBtn = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');

        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });

            document.addEventListener('click', (e) => {
                if (window.innerWidth < 992) {
                    if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target) && sidebar.classList.contains('active')) {
                        sidebar.classList.remove('active');
                    }
                }
            });
        }
    },

    initNavigation: function () {
        document.addEventListener('click', async (e) => {
            const link = e.target.closest('a');
            if (!link) return;

            const url = link.getAttribute('href');
            // Only handle internal HTML pages, ignore # and js calls
            if (!url || url === '#' || url.startsWith('javascript:') || !url.endsWith('.html')) return;

            // If logout or login, let it behave normally (full reload)
            if (url === 'index.html' || link.id === 'logoutBtn') return;

            e.preventDefault();

            await this.loadPage(url);
            window.history.pushState({}, '', url);
        });

        // Handle browser back/forward
        window.addEventListener('popstate', () => {
            this.loadPage(window.location.pathname.split('/').pop());
        });
    },

    loadPage: async function (url) {
        try {
            // Fade out
            const mainContent = document.querySelector('.main-content');
            if (mainContent) mainContent.style.opacity = '0';

            const response = await fetch(url);
            const text = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');

            const newContent = doc.querySelector('.main-content');
            const newTitle = doc.querySelector('title');

            if (newContent && mainContent) {
                setTimeout(() => {
                    mainContent.innerHTML = newContent.innerHTML;
                    mainContent.style.opacity = '1';

                    // Update Page Title and Header Title
                    if (newTitle) {
                        document.title = newTitle.innerText;
                        const headerTitle = document.querySelector('.header-title');
                        if (headerTitle) {
                            headerTitle.innerText = newTitle.innerText;
                            const i18nKey = newTitle.getAttribute('data-i18n');
                            if (i18nKey) {
                                headerTitle.setAttribute('data-i18n', i18nKey);
                            }
                        }
                    }

                    this.highlightActiveLink();

                    // Re-initialize App logic (charts, bindings)
                    document.dispatchEvent(new Event('layout-loaded'));
                }, 200); // Wait for fade out
            }
        } catch (err) {
            console.error('Navigation failed', err);
            window.location.href = url; // Fallback
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    Layout.init();
    Layout.initNavigation(); // Start navigation listener
});
