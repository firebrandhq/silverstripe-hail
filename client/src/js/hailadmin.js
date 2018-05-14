jQuery(function ($) {
    var currentJob = null;
    var progressTimer = null;
    var requestInProgress = false;

    function initHailProgress() {
        if (!$('#hail-fetch-wrapper').hasClass('hail-fetch-running')) {
            $('#hail-fetch-wrapper').addClass('hail-fetch-running');
        }
        //Fetch data and build initial progress button
        //Send request to Hail Controller
        //Don't send 2 requests at the same time to avoid crash if backend is slow
        if (!requestInProgress) {
            requestInProgress = true;

            $.ajax({
                url: "/hail/progress",
                type: 'GET',
                contentType: "application/json",
                success: (response) => {
                    currentJob = response;
                    if (response.Status === "Starting") {
                        //Set button to active to trigger display change
                        if (!$('#hail-fetch-button').hasClass('state-active')) {
                            $('#hail-fetch-button').addClass('state-active');
                        }

                        //Set the global class so button is using 2 lines
                        if (response.ToFetch === "*" && !$('#hail-fetch-button').hasClass('global-fetch')) {
                            $('#hail-fetch-button').addClass('global-fetch');
                        }

                        if (progressTimer === null) {
                            startProgressTimer();
                        }
                        $('#hail-fetch-button .content').text("Waiting for fetch job to start...");
                    } else if (response.Status === "Running") {
                        //Set button to active to trigger display change
                        if (!$('#hail-fetch-button').hasClass('state-active')) {
                            $('#hail-fetch-button').addClass('state-active');

                        }

                        //Do progress refresh
                        refreshHailProgressBar(response);

                        //In access the job is already in progress on load
                        if (progressTimer === null) {
                            startProgressTimer();
                        }
                    } else {
                        $('#hail-fetch-button .progress-inner').css('width', '0');
                        if ($('#hail-fetch-button').hasClass('state-active')) {
                            $('#hail-fetch-button').removeClass('state-active');
                        }
                        if ($('#hail-fetch-button').hasClass('global-fetch')) {
                            $('#hail-fetch-button').removeClass('global-fetch');
                        }
                        if (progressTimer !== null) {
                            stopProgressTimer();
                        }
                        $('#hail-fetch-button .content').text("FETCH");
                        $('#hail-fetch-button').removeClass('disabled');

                        //Reload the page to display the new objects
                        if (confirm('Fetch successful ! Please confirm to reload the page and display the changes: ')) {
                            //Reload the page if we fetched all objects or display the dedicated object page if not
                            if (response.ToFetch === "*") {
                                window.location.reload();
                            } else {
                                window.location.href = "/admin/hail/" + response.ToFetch;
                            }
                        }
                    }
                },
                complete: () => {
                    requestInProgress = false;
                }
            });
        }
    }

    function refreshHailProgressBar(job) {
        var progressContentHTML = "";
        var percentage = Math.round((job.CurrentDone / job.CurrentTotal) * 100);
        if (percentage > 100) percentage = 100;
        if (isNaN(percentage)) percentage = 0;

        if (job.ToFetch === "*") {
            progressContentHTML += 'Global progress: ' + job.GlobalDone + '/' + job.GlobalTotal + '<br />';
        }

        if (typeof job.CurrentObject !== "undefined") {
            progressContentHTML += 'Fetching ' + job.CurrentObject + ' (' + percentage + '%)';
        } else {
            progressContentHTML += 'Fetching...';
        }
        $('#hail-fetch-button .content').html(progressContentHTML);
        $('#hail-fetch-button .progress-inner').css('width', percentage + '%');
    }

    function startProgressTimer() {
        if (progressTimer == null) {
            progressTimer = setInterval(initHailProgress, 1000);
        }
    }

    function stopProgressTimer() {
        clearInterval(progressTimer);
        progressTimer = null;
    }

    $(document).ready(() => {
        //init click handlers for the fetch button dropdowns
        $('.cms-container').on('click', '.hail-fetch-items a.dropdown-item', function (e) {
            e.preventDefault();
            //Disable button to avoid spam
            $('#hail-fetch-button').addClass('disabled');
            //Class to fetch
            var className = $(this).data('to-fetch');
            //Send request to Hail Controller
            $.ajax({
                url: "/hail/fetch/" + className,
                type: 'GET',
                contentType: "application/json",
                success: (response) => {
                    initHailProgress();
                },
                error: (response) => {
                    if (typeof response.message !== "undefined") {
                        var message = "Error fetching from Hail: " + response.message;
                        alert(message);
                    }
                }
            });
        });

        //Check if we need to start the progress bar straight away
        if ($('#hail-fetch-wrapper').hasClass('hail-fetch-running')) {
            initHailProgress();
        }

        //Fetch One Ajax
        $('.cms-container').on('click', '.hail-fetch-one', function (e) {
            let button = $(this);
            button.addClass('disabled');
            //Show loading gif
            $('.hail-fetch-loading').removeClass('hide');
            //Class to fetch
            let className = button.data('tofetch');
            //Hail ID to fetch
            let hailID = button.data('hailid');
            $.ajax({
                url: '/hail/fetchOne/' + className + '/' + hailID,
                type: 'GET',
                contentType: "application/json",
                success: function (response) {
                    //Reload the page to display the new object
                    if (confirm('Update successful ! Please confirm to reload the page and display the updated data: ')) {
                        window.location.reload();
                    }
                },
                complete: function () {
                    $('.hail-fetch-loading').addClass('hide');
                    button.removeClass('disabled');
                }
            });
        });
    });
});