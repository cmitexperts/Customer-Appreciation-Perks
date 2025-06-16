jQuery(document).ready(function($) {
    // Initialize Swiper
    if ($('.cmitcap-swiper').length) {
        new Swiper('.cmitcap-swiper', {
            slidesPerView: 3,
            spaceBetween: 30,
            grid: {
                rows: 2,
                fill: 'row',
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                0: { slidesPerView: 1 },
                600: { slidesPerView: 2 },
                900: { slidesPerView: 3 }
            }
        });
    }

    // Show popup after 1.5 seconds
    setTimeout(function() {
        $('.cmitcap-popup-overlay, .cmitcap-popup').fadeIn(300);
    }, 1500);

    // Close popup when clicking the close button or overlay
    $('.cmitcap-close-button, .cmitcap-popup-overlay').on('click', function() {
        $('.cmitcap-popup-overlay, .cmitcap-popup').fadeOut(300);
    });

    // Prevent popup from closing when clicking inside it
    $('.cmitcap-popup').on('click', function(e) {
        e.stopPropagation();
    });

    // Handle freebie selection
    $('.cmitcap-select-button').on('click', function() {
        const button = $(this);
        const productId = button.data('product-id');
        
        button.prop('disabled', true).text('Adding...');

        $.ajax({
            url: cmitcapData.ajaxurl,
            type: 'POST',
            data: {
                action: 'cmitcap_add_freebie',
                product_id: productId,
                nonce: cmitcapData.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    const successMessage = $('<div class="cmitcap-success-message">')
                        .text(response.data.message)
                        .insertAfter(button);
                    
                    // Close popup after 2 seconds
                    setTimeout(function() {
                        $('.cmitcap-popup-overlay, .cmitcap-popup').fadeOut(300);
                        // Reload page to show updated cart
                        window.location.reload();
                    }, 2000);
                } else {
                    // Show error message
                    const errorMessage = $('<div class="cmitcap-error-message">')
                        .text(response.data.message)
                        .insertAfter(button);
                    
                    button.prop('disabled', false).text('Select Perk');
                }
            },
            error: function() {
                // Show error message
                const errorMessage = $('<div class="cmitcap-error-message">')
                    .text('An error occurred. Please try again.')
                    .insertAfter(button);
                
                button.prop('disabled', false).text('Select Perk');
            }
        });
    });
}); 