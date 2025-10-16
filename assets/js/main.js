/**
 * Main JavaScript - Enhanced & Flicker-Free
 * Merges:
 * - main.js (Core functionality)
 * - click-tracker.js (Deal click analytics)
 * 
 * @package ARRZONE
 * @version 3.0 - Flicker-Free Sticky Header
 */

(function($) {
    'use strict';

    // =====================================================
// SECTION 1: SIMPLE STICKY HEADER (NO AUTO-HIDE)
// =====================================================

/**
 * Simple sticky header with shadow effect on scroll
 * NO AUTO-HIDE - Always visible, smooth shadow transition
 */
function initStickyHeader() {
    const header = document.querySelector('.site-header');
    if (!header) return;

    let ticking = false;

    function onScroll() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                updateHeader();
                ticking = false;
            });
            ticking = true;
        }
    }

    function updateHeader() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        // Add scrolled class if scrolled down more than 10px
        if (scrollTop > 10) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    }

    // Attach scroll listener
    window.addEventListener('scroll', onScroll, { passive: true });
    
    // Run once on load
    updateHeader();
}

    // =====================================================
    // SECTION 2: MOBILE MENU
    // =====================================================

    /**
     * Initialize mobile menu toggle
     */
    function initMobileMenu() {
        const menuToggle = document.querySelector('.mobile-menu-toggle');
        const mobileMenu = document.querySelector('.mobile-menu');
        const menuOverlay = document.querySelector('.menu-overlay');
        const body = document.body;

        if (!menuToggle || !mobileMenu || !menuOverlay) return;

        const closeMenu = () => {
            menuToggle.classList.remove('active');
            mobileMenu.classList.remove('active');
            menuOverlay.classList.remove('active');
            body.classList.remove('menu-open');
        };

        // Toggle menu
        menuToggle.addEventListener('click', function() {
            const isActive = this.classList.contains('active');
            
            if (isActive) {
                closeMenu();
            } else {
                this.classList.add('active');
                mobileMenu.classList.add('active');
                menuOverlay.classList.add('active');
                body.classList.add('menu-open');
            }
        });

        // Close menu when clicking overlay
        menuOverlay.addEventListener('click', closeMenu);

        // Close menu when clicking close button inside menu
        const closeBtn = mobileMenu.querySelector('.mobile-menu-close');

        if (closeBtn) {
            closeBtn.addEventListener('click', closeMenu);
        }
    }

    // =====================================================
    // SECTION 3: SEARCH TOGGLE
    // =====================================================

    /**
     * Initialize search form toggle
     */
    function initSearchToggle() {
        const searchToggle = document.querySelector('.search-toggle');
        const searchForm = document.querySelector('.search-form');

        if (!searchToggle || !searchForm) return;

        searchToggle.addEventListener('click', function() {
            searchForm.classList.toggle('active');
        });

        // Close search when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchToggle.contains(e.target) && !searchForm.contains(e.target)) {
                searchForm.classList.remove('active');
            }
        });
    }

    // =====================================================
    // SECTION 4: DEAL CLICK TRACKING
    // =====================================================

    /**
     * Track deal clicks for analytics
     */
    function initDealClickTracking() {
        $(document).on('click', '.cd-shop-now-btn, .deal-affiliate-link', function(e) {
            const dealId = $(this).closest('[data-deal-id]').data('deal-id');
            
            if (!dealId) return;

            // Send AJAX request to track click
            $.ajax({
                url: dealsindia.ajaxUrl || ajaxurl,
                type: 'POST',
                data: {
                    action: 'dealsindia_track_click',
                    deal_id: dealId,
                    nonce: dealsindia.nonce || ''
                },
                success: function(response) {
                    console.log('Click tracked:', dealId);
                }
            });
        });
    }

    // =====================================================
    // SECTION 5: COUPON CODE COPY
    // =====================================================

    /**
     * Copy coupon code to clipboard
     */
    function initCouponCopy() {
        $(document).on('click', '#copyCouponBtn, .copy-coupon-btn-enhanced', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const couponCode = $('#couponCode').text() || button.data('coupon');
            
            if (!couponCode) return;

            // Copy to clipboard
            if (navigator.clipboard) {
                navigator.clipboard.writeText(couponCode).then(function() {
                    showCopySuccess(button);
                }).catch(function() {
                    fallbackCopy(couponCode, button);
                });
            } else {
                fallbackCopy(couponCode, button);
            }
        });
    }

    /**
     * Fallback copy method for older browsers
     */
    function fallbackCopy(text, button) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            document.execCommand('copy');
            showCopySuccess(button);
        } catch (err) {
            console.error('Copy failed:', err);
        }
        
        document.body.removeChild(textarea);
    }

    /**
     * Show copy success feedback
     */
    function showCopySuccess(button) {
        const originalHtml = button.html();
        
        button.html('✓ Copied!').addClass('copied').prop('disabled', true);
        
        setTimeout(function() {
            button.html(originalHtml).removeClass('copied').prop('disabled', false);
        }, 2000);
    }

    // =====================================================
    // SECTION 6: COUNTDOWN TIMER (Single Deal Page)
    // =====================================================

    /**
     * Initialize countdown timer
     */
    function initCountdownTimer() {
        const countdown = document.getElementById('dealCountdown');
        if (!countdown) return;

        const expiryDate = countdown.dataset.expiry;
        if (!expiryDate) return;

        const endDate = new Date(expiryDate).getTime();

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = endDate - now;

            if (distance < 0) {
                countdown.innerHTML = '<div class="countdown-expired">Deal Expired</div>';
                clearInterval(countdownInterval);
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById('days').textContent = String(days).padStart(2, '0');
            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
        }

        updateCountdown();
        const countdownInterval = setInterval(updateCountdown, 1000);
    }

    // =====================================================
    // SECTION 7: SMOOTH SCROLL TO TOP
    // =====================================================

    /**
     * Scroll to top button
     */
    function initScrollToTop() {
        const scrollBtn = document.getElementById('scrollToTop');
        if (!scrollBtn) return;

        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollBtn.classList.add('visible');
            } else {
                scrollBtn.classList.remove('visible');
            }
        }, { passive: true });

        scrollBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // =====================================================
    // INITIALIZATION
    // =====================================================

    /**
     * Initialize all functions when DOM is ready
     */
    $(document).ready(function() {
        initStickyHeader();
        initMobileMenu();
        initSearchToggle();
        initDealClickTracking();
        initCouponCopy();
        initCountdownTimer();
        initScrollToTop();

        console.log('ARRZONE Main JS Loaded');
    });

})(jQuery);
