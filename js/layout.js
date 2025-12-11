/**
 * Layout Manager (jQuery Version)
 * Fetches layout.html and injects separate Sidebar and Header into pages
 */

const Layout = {
    init: function () {
        if ($('.login-container').length > 0) return; // Don't run on login page

        // Load CSS first - Force no-cache
        this.loadCSS(`css/layout.css?v=${Date.now() + 2}`);

        const _this = this;

        // Fetch the layout HTML file - Force no-cache
        $.ajax({
            url: `layout.html?v=${new Date().getTime()}`,
            method: 'GET',
            dataType: 'html',
            success: function (data) {
                // Parse the response
                const $doc = $('<div>').html(data);

                // Inject Sidebar
                const sidebarHtml = $doc.find('#sidebar-template').html();
                if (sidebarHtml) {
                    $('body').prepend(sidebarHtml);
                }

                // Inject Header
                const headerHtml = $doc.find('#header-template').html();
                if (headerHtml) {
                    _this.renderHeader(headerHtml);
                }

                _this.highlightActiveLink();
                _this.initMobileToggle();
                _this.initNavigation(); // Initialize navigation handler (SPA support ready)

                // Dispatch event so App knows layout is ready
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
            // ALWAYS Use document title
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
        $(document).on('click', 'a', function (e) {
            const $link = $(this);
            const url = $link.attr('href');

            // Only handle internal HTML pages, ignore # and js calls
            if (!url || url === '#' || url.startsWith('javascript:') || !url.endsWith('.html')) return;

            // If logout or login, let it behave normally (full reload)
            if (url === 'index.html' || $link.attr('id') === 'logoutBtn') return;

            e.preventDefault();

            Layout.loadPage(url);
            window.history.pushState({}, '', url);
        });

        // Handle browser back/forward
        $(window).on('popstate', function () {
            Layout.loadPage(window.location.pathname.split('/').pop());
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
                            // Always use document title for header logic now
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
                    }
                }, 200);
            },
            error: function (err) {
                console.error('Navigation failed', err);
                window.location.href = url; // Fallback
            }
        });
    }
};

$(document).ready(function () {
    Layout.init();
});
