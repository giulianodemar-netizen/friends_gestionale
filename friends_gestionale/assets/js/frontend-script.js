/**
 * Friends Gestionale - Frontend JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Smooth scroll for anchor links
        $('a[href^="#"]').on('click', function(e) {
            var target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 100
                }, 1000);
            }
        });
        
        // Animate progress bars on scroll
        function animateProgressBars() {
            $('.fg-progress-bar').each(function() {
                var $this = $(this);
                var $fill = $this.find('.fg-progress-fill');
                var width = $fill.data('width') || $fill.css('width');
                
                if (!$fill.data('animated')) {
                    var elementTop = $this.offset().top;
                    var elementBottom = elementTop + $this.outerHeight();
                    var viewportTop = $(window).scrollTop();
                    var viewportBottom = viewportTop + $(window).height();
                    
                    if (elementBottom > viewportTop && elementTop < viewportBottom) {
                        $fill.css('width', '0%');
                        setTimeout(function() {
                            $fill.css('width', width);
                        }, 100);
                        $fill.data('animated', true);
                    }
                }
            });
        }
        
        animateProgressBars();
        
        $(window).on('scroll', function() {
            animateProgressBars();
        });
        
        // Filter form enhancement
        $('.fg-filter-form').on('submit', function(e) {
            var form = $(this);
            var isEmpty = true;
            
            form.find('input, select').each(function() {
                if ($(this).val() !== '') {
                    isEmpty = false;
                    return false;
                }
            });
            
            if (isEmpty) {
                e.preventDefault();
                alert('Seleziona almeno un filtro per continuare.');
            }
        });
        
        // Auto-refresh dashboard statistics
        var $dashboard = $('.fg-dashboard');
        if ($dashboard.length && $dashboard.data('auto-refresh')) {
            var refreshInterval = parseInt($dashboard.data('auto-refresh')) || 60000;
            setInterval(function() {
                $.ajax({
                    url: friendsGestionale.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'fg_refresh_dashboard',
                        nonce: friendsGestionale.nonce
                    },
                    success: function(response) {
                        if (response.success && response.data.html) {
                            $dashboard.html(response.data.html);
                        }
                    }
                });
            }, refreshInterval);
        }
        
        // Donation progress animation counter
        $('.fg-stat-value').each(function() {
            var $this = $(this);
            var text = $this.text();
            
            // Check if it's a number or currency
            var match = text.match(/€?([\d,\.]+)/);
            if (match) {
                var value = parseFloat(match[1].replace(/,/g, ''));
                var isCurrency = text.indexOf('€') > -1;
                var isPercentage = text.indexOf('%') > -1;
                
                $this.prop('Counter', 0).animate({
                    Counter: value
                }, {
                    duration: 2000,
                    easing: 'swing',
                    step: function(now) {
                        var formatted;
                        if (isCurrency) {
                            formatted = '€' + now.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                        } else if (isPercentage) {
                            formatted = now.toFixed(1) + '%';
                        } else {
                            formatted = Math.ceil(now);
                        }
                        $this.text(formatted);
                    }
                });
            }
        });
        
        // Table sorting
        $('.fg-table th').on('click', function() {
            var $table = $(this).closest('table');
            var index = $(this).index();
            var $rows = $table.find('tbody tr').toArray();
            var isAscending = $(this).hasClass('sort-asc');
            
            // Clear previous sort indicators
            $table.find('th').removeClass('sort-asc sort-desc');
            
            $rows.sort(function(a, b) {
                var aValue = $(a).find('td').eq(index).text();
                var bValue = $(b).find('td').eq(index).text();
                
                if (isAscending) {
                    return aValue.localeCompare(bValue);
                } else {
                    return bValue.localeCompare(aValue);
                }
            });
            
            // Add sort indicator
            $(this).addClass(isAscending ? 'sort-desc' : 'sort-asc');
            
            // Reorder rows
            $.each($rows, function(index, row) {
                $table.find('tbody').append(row);
            });
        });
        
        // Lazy load images
        if ('IntersectionObserver' in window) {
            var imageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            $('.lazy').each(function() {
                imageObserver.observe(this);
            });
        }
        
        // Raccolta card hover effect
        $('.fg-raccolta-card').on('mouseenter', function() {
            $(this).addClass('hover');
        }).on('mouseleave', function() {
            $(this).removeClass('hover');
        });
        
        // Share buttons
        $('.fg-share-button').on('click', function(e) {
            e.preventDefault();
            var url = $(this).data('url') || window.location.href;
            var title = $(this).data('title') || document.title;
            var shareType = $(this).data('share');
            
            var shareUrls = {
                facebook: 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url),
                twitter: 'https://twitter.com/intent/tweet?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(title),
                linkedin: 'https://www.linkedin.com/shareArticle?mini=true&url=' + encodeURIComponent(url) + '&title=' + encodeURIComponent(title),
                whatsapp: 'https://api.whatsapp.com/send?text=' + encodeURIComponent(title + ' ' + url)
            };
            
            if (shareUrls[shareType]) {
                window.open(shareUrls[shareType], '_blank', 'width=600,height=400');
            }
        });
        
        // Print functionality
        $('.fg-print-button').on('click', function(e) {
            e.preventDefault();
            window.print();
        });
        
        // Mobile menu toggle
        $('.fg-mobile-menu-toggle').on('click', function() {
            $('.fg-mobile-menu').toggleClass('active');
        });
        
        // Close mobile menu when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.fg-mobile-menu, .fg-mobile-menu-toggle').length) {
                $('.fg-mobile-menu').removeClass('active');
            }
        });
        
    });
    
})(jQuery);
