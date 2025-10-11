/**
 * Hero Banner Auto-Carousel
 * Production Ready Version
 * 
 * @package ARRZONE
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
            console.log('Hero carousel not found on this page');
            return;
        }
        
        const wrapper = carousel.querySelector('.hero-banners-wrapper');
        const slides = carousel.querySelectorAll('.hero-banners-slide');
        const prevBtn = carousel.querySelector('.carousel-arrow-prev');
        const nextBtn = carousel.querySelector('.carousel-arrow-next');
        const dotsContainer = carousel.querySelector('.carousel-dots');
        
        if (!wrapper || slides.length === 0) {
            console.log('No slides found in carousel');
            return;
        }
        
        console.log('Found ' + slides.length + ' slides');
        
        let currentIndex = 0;
        const totalSlides = slides.length;
        let autoPlayInterval;
        
        // Only initialize carousel if more than 1 slide
        if (totalSlides <= 1) {
            console.log('Only 1 slide, hiding controls');
            if (prevBtn) prevBtn.style.display = 'none';
            if (nextBtn) nextBtn.style.display = 'none';
            if (dotsContainer) dotsContainer.style.display = 'none';
            return;
        }
        
        // Create dots
        function createDots() {
            if (!dotsContainer) return;
            
            dotsContainer.innerHTML = '';
            for (let i = 0; i < totalSlides; i++) {
                const dot = document.createElement('button');
                dot.classList.add('carousel-dot');
                if (i === 0) dot.classList.add('active');
                dot.setAttribute('aria-label', 'Go to slide ' + (i + 1));
                dot.setAttribute('type', 'button');
                dot.addEventListener('click', function() {
                    goToSlide(i);
                });
                dotsContainer.appendChild(dot);
            }
            console.log('Created ' + totalSlides + ' dots');
        }
        
        // Update dots
        function updateDots() {
            if (!dotsContainer) return;
            
            const dots = dotsContainer.querySelectorAll('.carousel-dot');
            dots.forEach(function(dot, index) {
                if (index === currentIndex) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }
        
        // Go to specific slide
        function goToSlide(index) {
            currentIndex = index;
            const translateX = -(currentIndex * 100);
            wrapper.style.transform = 'translateX(' + translateX + '%)';
            updateDots();
            resetAutoPlay();
            console.log('Moved to slide ' + (currentIndex + 1));
        }
        
        // Next slide
        function nextSlide() {
            currentIndex = (currentIndex + 1) >= totalSlides ? 0 : currentIndex + 1;
            goToSlide(currentIndex);
        }
        
        // Previous slide
        function prevSlide() {
            currentIndex = (currentIndex - 1) < 0 ? totalSlides - 1 : currentIndex - 1;
            goToSlide(currentIndex);
        }
        
        // Auto play
        function startAutoPlay() {
            autoPlayInterval = setInterval(nextSlide, 5000); // 5 seconds
            console.log('Auto-play started');
        }
        
        // Reset auto play
        function resetAutoPlay() {
            clearInterval(autoPlayInterval);
            startAutoPlay();
        }
        
        // Stop auto play
        function stopAutoPlay() {
            clearInterval(autoPlayInterval);
            console.log('Auto-play stopped');
        }
        
        // Initialize
        function init() {
            createDots();
            
            // Button events
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
            
            // Pause on hover
            carousel.addEventListener('mouseenter', stopAutoPlay);
            carousel.addEventListener('mouseleave', startAutoPlay);
            
            // Touch support for mobile
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
                if (touchEndX < touchStartX - 50) {
                    nextSlide(); // Swipe left
                }
                if (touchEndX > touchStartX + 50) {
                    prevSlide(); // Swipe right
                }
            }
            
            // Start auto play
            startAutoPlay();
            
            console.log('Carousel initialized successfully');
        }
        
        init();
    }
})();
