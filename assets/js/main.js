/**
 * Main Frontend JavaScript
 * DealsIndia Theme - All frontend interactions
 * Version: 3.0
 */

(function() {
    'use strict';
    
    // =====================================================
    // MOBILE MENU TOGGLE
    // =====================================================
    const mobileMenuInit = () => {
        const toggle = document.querySelector('.mobile-menu-toggle');
        const mobileNav = document.querySelector('.mobile-nav-menu');
        const overlay = document.querySelector('.mobile-nav-overlay');
        const closeBtn = document.querySelector('.mobile-nav-close');
        
        if (!toggle || !mobileNav || !overlay) return;
        
        const openMenu = () => {
            mobileNav.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        };
        
        const closeMenu = () => {
            mobileNav.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        };
        
        toggle.addEventListener('click', openMenu);
        if (closeBtn) closeBtn.addEventListener('click', closeMenu);
        if (overlay) overlay.addEventListener('click', closeMenu);
        
        // Close on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && mobileNav.classList.contains('active')) {
                closeMenu();
            }
        });
    };
    
    
    // =====================================================
    // HERO SLIDER
    // =====================================================
    const heroSliderInit = () => {
        const slides = document.querySelectorAll('.slide-cd');
        if (slides.length <= 1) return; // No slider needed for single slide
        
        let currentSlide = 0;
        const totalSlides = slides.length;
        
        const showSlide = (index) => {
            slides.forEach(slide => slide.classList.remove('active'));
            
            if (index >= totalSlides) {
                currentSlide = 0;
            } else if (index < 0) {
                currentSlide = totalSlides - 1;
            } else {
                currentSlide = index;
            }
            
            slides[currentSlide].classList.add('active');
        };
        
        const nextSlide = () => showSlide(currentSlide + 1);
        const prevSlide = () => showSlide(currentSlide - 1);
        
        // Navigation buttons
        const nextBtn = document.querySelector('.slider-next-cd');
        const prevBtn = document.querySelector('.slider-prev-cd');
        
        if (nextBtn) nextBtn.addEventListener('click', nextSlide);
        if (prevBtn) prevBtn.addEventListener('click', prevSlide);
        
        // Auto-rotate every 5 seconds
        setInterval(nextSlide, 5000);
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') nextSlide();
            if (e.key === 'ArrowLeft') prevSlide();
        });
    };
    
    
    // =====================================================
    // HOT PICKS HORIZONTAL SCROLL
    // =====================================================
    const hotPicksScrollInit = () => {
        const scrollContainer = document.querySelector('.deals-scroll');
        if (!scrollContainer) return;
        
        const scrollAmount = 300; // pixels to scroll
        
        // Create scroll buttons
        const scrollWrapper = scrollContainer.parentElement;
        
        // Left arrow
        const leftArrow = document.createElement('button');
        leftArrow.className = 'scroll-arrow scroll-arrow-left';
        leftArrow.innerHTML = '‹';
        leftArrow.setAttribute('aria-label', 'Scroll left');
        
        // Right arrow
        const rightArrow = document.createElement('button');
        rightArrow.className = 'scroll-arrow scroll-arrow-right';
        rightArrow.innerHTML = '›';
        rightArrow.setAttribute('aria-label', 'Scroll right');
        
        scrollWrapper.style.position = 'relative';
        scrollWrapper.appendChild(leftArrow);
        scrollWrapper.appendChild(rightArrow);
        
        // Scroll functions
        const scrollLeft = () => {
            scrollContainer.scrollBy({
                left: -scrollAmount,
                behavior: 'smooth'
            });
        };
        
        const scrollRight = () => {
            scrollContainer.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
        };
        
        leftArrow.addEventListener('click', scrollLeft);
        rightArrow.addEventListener('click', scrollRight);
        
        // Show/hide arrows based on scroll position
        const updateArrows = () => {
            const isAtStart = scrollContainer.scrollLeft <= 0;
            const isAtEnd = scrollContainer.scrollLeft + scrollContainer.clientWidth >= scrollContainer.scrollWidth - 1;
            
            leftArrow.style.opacity = isAtStart ? '0.3' : '1';
            leftArrow.style.cursor = isAtStart ? 'default' : 'pointer';
            
            rightArrow.style.opacity = isAtEnd ? '0.3' : '1';
            rightArrow.style.cursor = isAtEnd ? 'default' : 'pointer';
        };
        
        scrollContainer.addEventListener('scroll', updateArrows);
        updateArrows(); // Initial state
        
        // Mouse drag to scroll (optional enhancement)
        let isDown = false;
        let startX;
        let scrollLeftPos;
        
        scrollContainer.addEventListener('mousedown', (e) => {
            isDown = true;
            scrollContainer.style.cursor = 'grabbing';
            startX = e.pageX - scrollContainer.offsetLeft;
            scrollLeftPos = scrollContainer.scrollLeft;
        });
        
        scrollContainer.addEventListener('mouseleave', () => {
            isDown = false;
            scrollContainer.style.cursor = 'grab';
        });
        
        scrollContainer.addEventListener('mouseup', () => {
            isDown = false;
            scrollContainer.style.cursor = 'grab';
        });
        
        scrollContainer.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - scrollContainer.offsetLeft;
            const walk = (x - startX) * 2;
            scrollContainer.scrollLeft = scrollLeftPos - walk;
        });
    };
    
    
    // =====================================================
    // COPY COUPON CODE
    // =====================================================
    const copyCouponInit = () => {
        const copyBtns = document.querySelectorAll('.copy-coupon-btn');
        
        copyBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const code = document.getElementById('coupon-code');
                if (!code) return;
                
                const textToCopy = code.textContent;
                
                // Modern clipboard API
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(textToCopy).then(() => {
                        showCopiedState(btn);
                    }).catch(err => {
                        console.error('Failed to copy:', err);
                    });
                } else {
                    // Fallback for older browsers
                    const textarea = document.createElement('textarea');
                    textarea.value = textToCopy;
                    textarea.style.position = 'fixed';
                    textarea.style.opacity = '0';
                    document.body.appendChild(textarea);
                    textarea.select();
                    try {
                        document.execCommand('copy');
                        showCopiedState(btn);
                    } catch (err) {
                        console.error('Fallback copy failed:', err);
                    }
                    document.body.removeChild(textarea);
                }
            });
        });
        
        const showCopiedState = (btn) => {
            const copyText = btn.querySelector('.copy-text');
            const copiedText = btn.querySelector('.copied-text');
            
            if (copyText) copyText.style.display = 'none';
            if (copiedText) copiedText.style.display = 'inline';
            
            setTimeout(() => {
                if (copyText) copyText.style.display = 'inline';
                if (copiedText) copiedText.style.display = 'none';
            }, 2000);
        };
    };
    
    
    // =====================================================
    // STORE ARCHIVE SORTING
    // =====================================================
    const storeSortInit = () => {
        const sortSelect = document.getElementById('store-deals-sort');
        if (!sortSelect) return;
        
        sortSelect.addEventListener('change', function() {
            const sortBy = this.value;
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('orderby', sortBy);
            window.location.href = currentUrl.toString();
        });
        
        // Set selected option based on URL param
        const urlParams = new URLSearchParams(window.location.search);
        const currentSort = urlParams.get('orderby');
        if (currentSort) {
            sortSelect.value = currentSort;
        }
    };
    
    
    // =====================================================
    // SMOOTH SCROLL TO ANCHOR LINKS
    // =====================================================
    const smoothScrollInit = () => {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href === '#') return;
                
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    };
    
    
    // =====================================================
    // LAZY LOAD IMAGES (Optional Enhancement)
    // =====================================================
    const lazyLoadInit = () => {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                        }
                        observer.unobserve(img);
                    }
                });
            });
            
            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    };
    
    
    // =====================================================
    // NEWSLETTER FORM HANDLER (Basic validation)
    // =====================================================
    const newsletterInit = () => {
        const form = document.querySelector('.newsletter-form');
        if (!form) return;
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = this.querySelector('input[type="email"]');
            const email = emailInput.value.trim();
            
            if (!email || !isValidEmail(email)) {
                alert('Please enter a valid email address.');
                return;
            }
            
            // TODO: Add AJAX submission to save newsletter subscribers
            // For now, just show success message
            alert('✅ Thank you for subscribing! You will receive deals in your inbox soon.');
            emailInput.value = '';
        });
    };
    
    const isValidEmail = (email) => {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    };
    
    
    // =====================================================
    // INITIALIZE ALL FUNCTIONS ON DOM READY
    // =====================================================
    const init = () => {
        mobileMenuInit();
        heroSliderInit();
        hotPicksScrollInit();
        copyCouponInit();
        storeSortInit();
        smoothScrollInit();
        lazyLoadInit();
        newsletterInit();
        stickyHeaderInit();
    };
    
    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Sticky Header on Scroll
const stickyHeaderInit = () => {
    const header = document.querySelector('.site-header');
    if (!header) return;
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
};

})();


// =====================================================
// GLOBAL UTILITY FUNCTIONS (if needed by inline code)
// =====================================================

// Store sorting (kept for backward compatibility)
function sortStoreDeals(sortBy) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('orderby', sortBy);
    window.location.href = currentUrl.toString();
}

// Copy coupon (kept for backward compatibility)
function copyCoupon() {
    const code = document.getElementById('coupon-code');
    if (!code) return;
    
    const textToCopy = code.textContent;
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(textToCopy).then(() => {
            const btn = document.querySelector('.copy-coupon-btn');
            if (btn) {
                btn.querySelector('.copy-text').style.display = 'none';
                btn.querySelector('.copied-text').style.display = 'inline';
                
                setTimeout(() => {
                    btn.querySelector('.copy-text').style.display = 'inline';
                    btn.querySelector('.copied-text').style.display = 'none';
                }, 2000);
            }
        });
    }
}

// Latest Deals Carousel Navigation
document.addEventListener('DOMContentLoaded', function() {
    const track = document.querySelector('.deals-carousel-track');
    const leftBtn = document.querySelector('.carousel-nav-left');
    const rightBtn = document.querySelector('.carousel-nav-right');
    
    if (!track || !leftBtn || !rightBtn) return;
    
    const scrollAmount = 300;
    
    leftBtn.addEventListener('click', () => {
        track.scrollBy({
            left: -scrollAmount,
            behavior: 'smooth'
        });
    });
    
    rightBtn.addEventListener('click', () => {
        track.scrollBy({
            left: scrollAmount,
            behavior: 'smooth'
        });
    });
    
    // Hide arrows when at start/end
    track.addEventListener('scroll', () => {
        leftBtn.style.opacity = track.scrollLeft <= 0 ? '0.3' : '1';
        rightBtn.style.opacity = 
            track.scrollLeft >= track.scrollWidth - track.clientWidth ? '0.3' : '1';
    });
});
