/**
 * Hero Banner Carousel - FIXED v2.0
 * Uses CSS active class (display: none/grid) approach
 * Matches homepage.css perfectly
 * 
 * @package DealsIndia
 * @version 2.0 - Production Ready
 */

(function() {
    'use strict';
    
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCarousel);
    } else {
        initCarousel();
    }
    
    function initCarousel() {
        const carousel = document.querySelector('.hero-banners-carousel');
        
        if (!carousel) {
            return;
        }
        
        const slides = carousel.querySelectorAll('.hero-banners-slide');
        const prevBtn = carousel.querySelector('.carousel-arrow-prev');
        const nextBtn = carousel.querySelector('.carousel-arrow-next');
        const dotsContainer = carousel.querySelector('.carousel-dots');
        
        if (!slides || slides.length === 0) {
            return;
        }
        
        let currentIndex = 0;
        const totalSlides = slides.length;
        let autoPlayInterval;
        
        // Only initialize if more than 1 slide
        if (totalSlides <= 1) {
            if (prevBtn) prevBtn.style.display = 'none';
            if (nextBtn) nextBtn.style.display = 'none';
            if (dotsContainer) dotsContainer.style.display = 'none';
            return;
        }
        
        // Create dots dynamically
        function createDots() {
            if (!dotsContainer) return;
            
            dotsContainer.innerHTML = '';
            
            for (let i = 0; i < totalSlides; i++) {
                const dot = document.createElement('button');
                dot.setAttribute('aria-label', 'Go to slide ' + (i + 1));
                dot.setAttribute('type', 'button');
                
                if (i === 0) {
                    dot.classList.add('active');
                }
                
                dot.addEventListener('click', function() {
                    goToSlide(i);
                });
                
                dotsContainer.appendChild(dot);
            }
        }
        
        // Update active dot
        function updateDots() {
            if (!dotsContainer) return;
            
            const dots = dotsContainer.querySelectorAll('button');
            dots.forEach(function(dot, index) {
                if (index === currentIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }
        
        // Show specific slide
        function goToSlide(index) {
            // Remove active class from all slides
            slides.forEach(function(slide) {
                slide.classList.remove('active');
            });
            
            // Add active class to current slide
            currentIndex = index;
            slides[currentIndex].classList.add('active');
            
            // Update dots
            updateDots();
            
            // Reset auto play timer
            resetAutoPlay();
        }
        
        // Next slide
        function nextSlide() {
            let nextIndex = currentIndex + 1;
            if (nextIndex >= totalSlides) {
                nextIndex = 0;
            }
            goToSlide(nextIndex);
        }
        
        // Previous slide
        function prevSlide() {
            let prevIndex = currentIndex - 1;
            if (prevIndex < 0) {
                prevIndex = totalSlides - 1;
            }
            goToSlide(prevIndex);
        }
        
        // Start auto play
        function startAutoPlay() {
            autoPlayInterval = setInterval(nextSlide, 5000); // 5 seconds
        }
        
        // Reset auto play
        function resetAutoPlay() {
            clearInterval(autoPlayInterval);
            startAutoPlay();
        }
        
        // Stop auto play
        function stopAutoPlay() {
            clearInterval(autoPlayInterval);
        }
        
        // Initialize carousel
        function init() {
            // Create dots
            createDots();
            
            // Arrow button events
            if (prevBtn) {
                prevBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    prevSlide();
                });
            }
            
            if (nextBtn) {
                nextBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    nextSlide();
                });
            }
            
            // Pause on hover (desktop)
            carousel.addEventListener('mouseenter', stopAutoPlay);
            carousel.addEventListener('mouseleave', startAutoPlay);
            
            // Touch/swipe support (mobile)
            let touchStartX = 0;
            let touchEndX = 0;
            
            carousel.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
                stopAutoPlay();
            }, { passive: true });
            
            carousel.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
                startAutoPlay();
            }, { passive: true });
            
            function handleSwipe() {
                const swipeThreshold = 50;
                const diff = touchEndX - touchStartX;
                
                if (diff < -swipeThreshold) {
                    nextSlide(); // Swipe left
                } else if (diff > swipeThreshold) {
                    prevSlide(); // Swipe right
                }
            }
            
            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (!carousel.matches(':hover')) return;
                
                if (e.key === 'ArrowLeft') {
                    prevSlide();
                } else if (e.key === 'ArrowRight') {
                    nextSlide();
                }
            });
            
            // Start auto play
            startAutoPlay();
        }
        
        // Run initialization
        init();
    }
})();
