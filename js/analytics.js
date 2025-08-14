/**
 * Analytics Tracking System for Akademia 360
 * Comprehensive tracking system for frontend user behavior analysis
 */

class AkademiaAnalytics {
    constructor() {
        this.sessionId = this.getSessionId();
        this.startTime = Date.now();
        this.pageStartTime = Date.now();
        this.init();
    }

    /**
     * Generate or retrieve session ID for user tracking
     */
    getSessionId() {
        let sessionId = localStorage.getItem('akademia_session_id');
        if (!sessionId) {
            sessionId = 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('akademia_session_id', sessionId);
        }
        return sessionId;
    }

    /**
     * Initialize all tracking functionality
     */
    init() {
        // Track page view on load
        this.trackPageView();

        // Track navigation events
        this.trackNavigation();

        // Track form interactions
        this.trackForms();

        // Track content engagement
        this.trackContent();

        // Track time spent on page
        this.trackTimeOnPage();

        // Track scroll behavior
        this.trackScroll();

        // Track important user interactions
        this.trackImportantClicks();
    }

    /**
     * Track page view events
     */
    trackPageView() {
        const page = window.location.pathname + window.location.search;
        this.sendEvent('page_view', 'navigation', 'view', page);
    }

    /**
     * Track navigation and link interactions
     */
    trackNavigation() {
        // Track clicks on navigation links
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link && link.href && !link.href.startsWith('javascript:')) {
                const href = link.href;
                const text = link.textContent.trim();
                
                // Track important content links
                if (href.includes('/curso/') || href.includes('/noticia/') || 
                    href.includes('/relator/') || href.includes('/servicio/')) {
                    this.trackEvent('link_click', 'navigation', 'click', text, href);
                }
            }
        });
    }

    /**
     * Track form submissions and interactions
     */
    trackForms() {
        document.addEventListener('submit', (e) => {
            const form = e.target;
            const formId = form.id || form.className || 'unknown_form';
            const formAction = form.action || 'unknown_action';
            
            this.trackEvent('form_submit', 'form', 'submit', formId, formAction);
        });
    }

    /**
     * Track content engagement and visibility
     */
    trackContent() {
        // Track course card visibility
        const courseCards = document.querySelectorAll('.course-card, .curso-card');
        courseCards.forEach(card => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const courseId = card.dataset.courseId || card.dataset.cursoId;
                        if (courseId) {
                            this.trackEvent('content_view', 'curso', 'view', 'curso', courseId);
                        }
                    }
                });
            });
            observer.observe(card);
        });

        // Track news card visibility
        const newsCards = document.querySelectorAll('.news-card, .noticia-card');
        newsCards.forEach(card => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const newsId = card.dataset.newsId || card.dataset.noticiaId;
                        if (newsId) {
                            this.trackEvent('content_view', 'noticia', 'view', 'noticia', newsId);
                        }
                    }
                });
            });
            observer.observe(card);
        });
    }

    /**
     * Track time spent on page and engagement metrics
     */
    trackTimeOnPage() {
        window.addEventListener('beforeunload', () => {
            const timeOnPage = Date.now() - this.pageStartTime;
            this.sendEvent('time_on_page', 'engagement', 'time', 'page_time', timeOnPage);
        });

        // Track engagement every 30 seconds
        setInterval(() => {
            const timeOnPage = Date.now() - this.pageStartTime;
            if (timeOnPage > 30000) { // Only after 30 seconds
                this.sendEvent('time_on_page', 'engagement', 'time', 'page_time', timeOnPage);
            }
        }, 30000);
    }

    /**
     * Track scroll behavior and engagement
     */
    trackScroll() {
        let scrollTracked = false;
        
        window.addEventListener('scroll', () => {
            if (!scrollTracked) {
                const scrollPercent = Math.round((window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100);
                if (scrollPercent > 50) { // Track when 50% scroll is reached
                    this.trackEvent('scroll', 'engagement', 'scroll', 'scroll_percent', scrollPercent);
                    scrollTracked = true;
                }
            }
        });
    }

    /**
     * Track important user interactions and conversions
     */
    trackImportantClicks() {
        // Track contact button interactions
        document.addEventListener('click', (e) => {
            const button = e.target.closest('button, .btn');
            if (button) {
                const text = button.textContent.trim();
                const classes = button.className;
                
                if (text.toLowerCase().includes('contacto') || 
                    text.toLowerCase().includes('contact') ||
                    classes.includes('contact')) {
                    this.trackEvent('button_click', 'engagement', 'click', 'contact_button', text);
                }
                
                if (text.toLowerCase().includes('inscribir') || 
                    text.toLowerCase().includes('inscribirse') ||
                    text.toLowerCase().includes('registrar')) {
                    this.trackEvent('button_click', 'engagement', 'click', 'register_button', text);
                }
            }
        });
    }

    /**
     * Track custom events with detailed parameters
     */
    trackEvent(tipo_evento, categoria = '', accion = '', etiqueta = '', valor = '') {
        this.sendEvent(tipo_evento, categoria, accion, etiqueta, valor);
    }

    /**
     * Send tracking data to server
     */
    sendEvent(tipo_evento, categoria, accion, etiqueta, valor) {
        const data = {
            tipo_evento: tipo_evento,
            categoria: categoria,
            accion: accion,
            etiqueta: etiqueta,
            valor: valor,
            session_id: this.sessionId,
            page: window.location.pathname + window.location.search,
            timestamp: Date.now()
        };

        // Send data using fetch API
        fetch('/admin/analytics_track.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        }).catch(error => {
            console.log('Analytics tracking error:', error);
        });
    }

    /**
     * Track business conversions and goals
     */
    trackConversion(tipo, valor = '') {
        this.trackEvent('conversion', 'business', 'conversion', tipo, valor);
    }

    /**
     * Track system errors and issues
     */
    trackError(error, context = '') {
        this.trackEvent('error', 'system', 'error', context, error);
    }
}

// Initialize analytics when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.akademiaAnalytics = new AkademiaAnalytics();
});

// Expose global functions for manual tracking
window.trackEvent = function(tipo_evento, categoria, accion, etiqueta, valor) {
    if (window.akademiaAnalytics) {
        window.akademiaAnalytics.trackEvent(tipo_evento, categoria, accion, etiqueta, valor);
    }
};

window.trackConversion = function(tipo, valor) {
    if (window.akademiaAnalytics) {
        window.akademiaAnalytics.trackConversion(tipo, valor);
    }
};
