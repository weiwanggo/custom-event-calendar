jQuery(document).ready(function ($) {
    // Function to update the calendar
    function updateCalendar(month, year) {
        var shortcode = "[custom_event_calendar month='" + month + "' year='" + year + "']";
        $.ajax({
            type: 'GET',
            url: custom_event_calendar_ajax.ajaxurl,
            data: {
                action: 'custom_event_calendar_load_shortcode',
                shortcode: shortcode,
            },
            success: function (response) {
                $('.custom-event-calendar').replaceWith(response);
                attachDateChangeHandlers();
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }

    function attachDateChangeHandlers() {
        $('.update-calendar').click(function () {
            var month = $('.month-select').val();
            var year = $('.year-select').val();
            updateCalendar(month, year);
        });

        $('.prev-month').click(function (event) {
            event.preventDefault(); 
            var month = parseInt($('.month-select').val()) - 1; // Convert to integer
            var year = $('.year-select').val();
            if (month < 1) { // Ensure month is within range
                month = 12; // Set to December
                year--; // Decrement year
            }
            updateCalendar(month, year);
        });

        $('.next-month').click(function (event) {
            event.preventDefault(); 
            var month = parseInt($('.month-select').val()) + 1; // Convert to integer
            var year = $('.year-select').val();
            if (month > 12) { // Ensure month is within range
                month = 1; // Set to January
                year++; // Increment year
            }
            updateCalendar(month, year);
        });

        $('.month-select, .year-select').on('change', function () {
            var month = $('.month-select').val();
            var year = $('.year-select').val();
            updateCalendar(month, year);
        });
    }

    $('.calendar-cell').mouseenter(function(e) {
        var mouseX = e.clientX;
        var mouseY = e.clientY;
        var date = $(this).find('.day').text(); // Extract date from the cell
        var eventId = '#event-list-' + date;
        var $eventList = $(eventId);   
            // Check if the element with the specified ID exists
            if ($eventList.length > 0) {
                $eventList.css({
                    display: 'block',
                top: mouseY + 'px',
                left: mouseX + 'px'
            });
        }
    }).mouseleave(function() {
        $('.event-list').css('display', 'none');
    });
    
    // Initial attachment of click handlers
    attachDateChangeHandlers();
});

var animationInProgress = false;

function toggleBirthdayAnimation() {
    if (!animationInProgress) {
        startBirthdayAnimation();
    }
}

function startBirthdayAnimation() {
/*
    var birthdayAnimation = document.getElementById('happy-birthday-animation');

    // Show the animation
    birthdayAnimation.style.display = 'block';

    // Add animation end event listener
    birthdayAnimation.addEventListener('animationend', animationEndHandler);

    // Add the 'animate-birthday' class to start the animation
    birthdayAnimation.classList.add('animate-birthday');

    animationInProgress = true;
*/
}

function stopBirthdayAnimation() {
    var birthdayAnimation = document.getElementById('happy-birthday-animation');

    // Hide the animation
    birthdayAnimation.style.display = 'none';

    // Remove animation end event listener
    birthdayAnimation.removeEventListener('animationend', animationEndHandler);

    // Remove the 'animate-birthday' class to stop the animation
    birthdayAnimation.classList.remove('animate-birthday');

    animationInProgress = false;
}

function animationEndHandler() {
    // Animation finished, stop the animation
    stopBirthdayAnimation();
}
