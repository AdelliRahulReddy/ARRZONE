/**
 * Flip Card Functionality
 * Handles card flip animations and coupon copy
 * 
 * @package ARRZONE
 * @version 1.0
 */

(function() {
    'use strict';
    
    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFlipCards);
    } else {
        initFlipCards();
    }
    
    function initFlipCards() {
        const cards = document.querySelectorAll('.cd-flip-card');
        
        cards.forEach(card => {
            // Click anywhere on card to flip (except buttons/links)
            card.addEventListener('click', function(e) {
                // Don't flip if clicking buttons or links
                if (e.target.closest('a, button')) {
                    return;
                }
                
                toggleFlip(this);
            });
            
            // Close button on back
            const closeBtn = card.querySelector('.cd-flip-back-btn');
            if (closeBtn) {
                closeBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    card.classList.remove('flipped');
                });
            }
        });
        
        // Auto-flip back after 15 seconds
        cards.forEach(card => {
            let flipTimeout;
            
            card.addEventListener('click', function() {
                clearTimeout(flipTimeout);
                
                if (this.classList.contains('flipped')) {
                    flipTimeout = setTimeout(() => {
                        this.classList.remove('flipped');
                    }, 15000); // 15 seconds
                }
            });
        });
    }
    
    function toggleFlip(card) {
        card.classList.toggle('flipped');
        
        // Track flip event (optional analytics)
        if (typeof gtag !== 'undefined') {
            const dealId = card.closest('[data-deal-id]')?.dataset.dealId;
            gtag('event', 'card_flip', {
                'deal_id': dealId
            });
        }
    }
    
    // Make function global for inline onclick
    window.copyCardCoupon = function(button) {
        const couponCode = button.dataset.coupon;
        
        if (!couponCode) {
            console.error('No coupon code found');
            return;
        }
        
        // Copy to clipboard
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(couponCode).then(() => {
                showCopySuccess(button);
            }).catch(err => {
                console.error('Clipboard API failed:', err);
                fallbackCopy(couponCode, button);
            });
        } else {
            fallbackCopy(couponCode, button);
        }
    };
    
    function fallbackCopy(text, button) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();
        textarea.setSelectionRange(0, 99999); // For mobile
        
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showCopySuccess(button);
            } else {
                console.error('Failed to copy');
            }
        } catch (err) {
            console.error('Fallback copy failed:', err);
        }
        
        document.body.removeChild(textarea);
    }
    
    function showCopySuccess(button) {
        const originalHTML = button.innerHTML;
        button.classList.add('copied');
        button.innerHTML = '✓ Copied!';
        button.disabled = true;
        
        setTimeout(() => {
            button.classList.remove('copied');
            button.innerHTML = originalHTML;
            button.disabled = false;
        }, 2000);
        
        // Optional: Track copy event
        if (typeof gtag !== 'undefined') {
            gtag('event', 'coupon_copy', {
                'coupon_code': button.dataset.coupon
            });
        }
    }
    
    // Handle keyboard accessibility
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const flippedCards = document.querySelectorAll('.cd-flip-card.flipped');
            flippedCards.forEach(card => {
                card.classList.remove('flipped');
            });
        }
    });
    
})();
