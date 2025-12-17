const Layout = {
    init: function () {
        if ($('.login-container').length > 0) return;

        // Check if sidebar already exists (rendered by Laravel Blade template)
        // If so, skip AJAX loading of layout.html
        if ($('.sidebar').length > 0) {
            // Layout already rendered by Laravel, just initialize functionality
            this.highlightActiveLink();
            this.initMobileToggle();
            this.initNavigation();
            $(document).trigger('layout-loaded');
            return;
        }

        // Legacy static HTML mode - load layout.html via AJAX
        this.loadCSS(`css/layout.css?v=${Date.now() + 2}`);

        const _this = this;

        $.ajax({
            url: `layout.html?v=${new Date().getTime()}`,
            method: 'GET',
            dataType: 'html',
            success: function (data) {
                const $doc = $('<div>').html(data);

                const sidebarHtml = $doc.find('#sidebar-template').html();
                if (sidebarHtml) {
                    $('body').prepend(sidebarHtml);
                }

                const headerHtml = $doc.find('#header-template').html();
                if (headerHtml) {
                    _this.renderHeader(headerHtml);
                }

                _this.highlightActiveLink();
                _this.initMobileToggle();
                _this.initNavigation();

                $(document).trigger('layout-loaded');
            },
            error: function (err) {
                console.error('Layout loading failed:', err);
            }
        });
    },

    loadCSS: function (href) {
        if ($(`link[href="${href}"]`).length === 0) {
            $('<link>', {
                rel: 'stylesheet',
                href: href
            }).appendTo('head');
        }
    },

    renderHeader: function (headerContent) {
        let $mainWrapper = $('.main-wrapper');
        if ($mainWrapper.length === 0) return;

        let $topHeader = $('.top-header');
        if ($topHeader.length === 0) {
            $topHeader = $('<div>').addClass('top-header');
            $mainWrapper.prepend($topHeader);
        }

        $topHeader.html(headerContent);

        const $titleContainer = $('#page-title-container');
        if ($titleContainer.length > 0) {
            const docTitle = document.title || 'Clinic System';
            const $docTitleEl = $('title');
            const i18nKey = $docTitleEl.length ? $docTitleEl.attr('data-i18n') : 'appTitle';

            $titleContainer.html(`<h4 class="mb-0 fw-bold header-title" data-i18n="${i18nKey || 'appTitle'}">${docTitle}</h4>`);
        }
    },

    highlightActiveLink: function () {
        const currentPage = window.location.pathname.split('/').pop() || 'index.html';
        const $links = $('.sidebar .nav-link');

        $links.each(function () {
            const $link = $(this);
            if ($link.data('page') === currentPage) {
                $link.addClass('active');
            } else {
                $link.removeClass('active');
            }
        });
    },

    initMobileToggle: function () {
        const $toggleBtn = $('#sidebarToggle');
        const $sidebar = $('.sidebar');

        if ($toggleBtn.length && $sidebar.length) {
            $toggleBtn.on('click', function (e) {
                e.stopPropagation();
                $sidebar.toggleClass('active');
            });

            $(document).on('click', function (e) {
                if ($(window).width() < 992) {
                    if (!$sidebar.is(e.target) && $sidebar.has(e.target).length === 0 &&
                        !$toggleBtn.is(e.target) && $toggleBtn.has(e.target).length === 0 &&
                        $sidebar.hasClass('active')) {
                        $sidebar.removeClass('active');
                    }
                }
            });
        }
    },

    initNavigation: function () {
        // Only handle navigation for sidebar links and internal page links
        $(document).on('click', '.sidebar .nav-link, .panel-action, a[href$=".html"]', function (e) {
            const $link = $(this);
            const url = $link.attr('href');

            // Skip if no url, is a hash, or javascript link
            if (!url || url === '#' || url.startsWith('javascript:')) return;

            // Skip if not an HTML page link
            if (!url.endsWith('.html')) return;

            // Skip login page and logout button - let them navigate normally
            if (url === 'index.html' || $link.attr('id') === 'logoutBtn') return;

            // Skip external links
            if (url.startsWith('http://') || url.startsWith('https://')) return;

            // Check if main content exists before attempting SPA navigation
            const $mainContent = $('.main-content');
            if (!$mainContent.length) {
                // No main content wrapper, use normal navigation
                return;
            }

            e.preventDefault();

            // Close mobile sidebar if open
            $('.sidebar').removeClass('active');

            Layout.loadPage(url);
            window.history.pushState({}, '', url);
        });

        $(window).on('popstate', function () {
            const page = window.location.pathname.split('/').pop() || 'dashboard.html';
            if (page && page !== 'index.html') {
                Layout.loadPage(page);
            }
        });
    },

    loadPage: function (url) {
        const $mainContent = $('.main-content');
        if ($mainContent.length) {
            $mainContent.css('opacity', '0');
        }

        $.ajax({
            url: url,
            method: 'GET',
            success: function (data) {
                setTimeout(function () {
                    const $doc = $('<div>').html(data);
                    const $newContent = $doc.find('.main-content');
                    const $newTitle = $doc.find('title');

                    if ($newContent.length && $mainContent.length) {
                        $mainContent.html($newContent.html()).css('opacity', '1');

                        if ($newTitle.length) {
                            document.title = $newTitle.text();
                            const $headerTitle = $('.header-title');
                            if ($headerTitle.length) {
                                $headerTitle.text($newTitle.text());
                                const i18nKey = $newTitle.attr('data-i18n');
                                if (i18nKey) {
                                    $headerTitle.attr('data-i18n', i18nKey);
                                }
                            }
                        }

                        Layout.highlightActiveLink();
                        $(document).trigger('layout-loaded');

                        Layout.handlePageScripts($doc);
                    }
                }, 200);
            },
            error: function (err) {
                console.error('Navigation failed', err);
                window.location.href = url;
            }
        });
    },

    handlePageScripts: function ($doc) {
        const ignoredScripts = [
            'jquery',
            'bootstrap',
            'app.js',
            'layout.js',
            'utils.js',
            'font-awesome'
        ];

        const scriptsToLoad = [];

        $doc.find('script').each(function () {
            const src = $(this).attr('src');
            if (src) {
                const fileName = src.split('/').pop().toLowerCase();
                let shouldIgnore = false;

                if (fileName === 'toast.js') shouldIgnore = true;

                if (ignoredScripts.includes(fileName)) shouldIgnore = true;
                if (src.includes('jquery') || src.includes('bootstrap')) shouldIgnore = true;

                if (!shouldIgnore) {
                    scriptsToLoad.push(src);
                }
            }
        });

        const loadScript = (index) => {
            if (index >= scriptsToLoad.length) return;

            const src = scriptsToLoad[index];
            const script = document.createElement('script');
            script.src = src;
            script.onload = () => loadScript(index + 1);
            script.onerror = () => loadScript(index + 1);
            document.body.appendChild(script);
        };

        loadScript(0);
    }
};

$(document).ready(function () {
    Layout.init();
});
