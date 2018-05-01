jQuery(function ($) {
    var currentJob = null;
    var progressTimer = null;
    var progressBar = $('#hail-fetch-progress');

    function initHailProgress() {
        if (!progressBar.hasClass('hail-fetch-running')) {
            progressBar.addClass('hail-fetch-running');
        }
        //Fetch data and build initial progressbar
        //Send request to Hail Controller
        $.ajax({
            url: "/hail/progress",
            type: 'GET',
            contentType: "application/json",
            success: function (response) {
                currentJob = response;
                if (response.Status === "Starting") {
                    progressBar.text("Waiting for fetch job to start...");
                    startProgressTimer();
                } else if (response.Status === "Running") {
                    if (progressBar.find('#hail-fetch-progress-current').length === 0) {
                        createHailProgressBar(response);
                    } else {
                        refreshHailProgressBar(response);
                    }
                    startProgressTimer();
                } else {
                    progressBar.text("Fetch job is done !");
                    stopProgressTimer();
                    $('#hail-fetch-button').removeClass('disabled');
                }

            }
        });
    }

    function refreshHailProgress() {
        $.ajax({
            url: "/hail/progress",
            type: 'GET',
            contentType: "application/json",
            success: function (response) {
                console.log(response);
                if (response.Status === "Starting") {
                    progressBar.text("Waiting for fetch job to start...");
                } else if (response.Status === "Running") {
                    if (progressBar.find('#hail-fetch-progress-current').length === 0) {
                        createHailProgressBar(response);
                    } else {
                        refreshHailProgressBar(response);
                    }
                } else {
                    progressBar.text("Fetch job is done !");
                    stopProgressTimer();
                    $('#hail-fetch-button').removeClass('disabled');
                }
            }
        });
    }

    function createHailProgressBar(job) {
        console.log('creating');
        progressBar.empty();
        if (job.ToFetch === "*") {
            progressBar.append('<div id="hail-fetch-progress-global">Global progress: ' + job.GlobalDone + '/' + job.GlobalTotal + '</div>')
        }
        progressBar.append('<div id="hail-fetch-progress-current">Currently fetching ' + job.CurrentObject + ': </div>')

        var percentage = Math.round((job.CurrentDone / job.CurrentTotal) * 100);
        if (percentage > 100) percentage = 100;
        progressBar.append('<div class="progress"><div class="progress-bar progress-bar-striped progress-bar-animated" style="width:' + percentage + '%;"role="progressbar" aria-valuenow="' + job.CurrentDone + '" aria-valuemin="0" aria-valuemax="' + job.CurrentTotal + '">' + percentage + '%</div></div>');
    }

    function refreshHailProgressBar(job) {
        console.log('refreshing');
        if (job.ToFetch === "*") {
            progressBar.find('#hail-fetch-progress-global').text('Global progress: ' + job.GlobalDone + '/' + job.GlobalTotal)
        }
        var percentage = Math.round((job.CurrentDone / job.CurrentTotal) * 100);
        if (percentage > 100) percentage = 100;
        progressBar.find('.progress-bar').css('width', percentage + '%');
        progressBar.find('.progress-bar').html(percentage + '%');
    }

    function startProgressTimer() {
        if (progressTimer == null) {
            progressTimer = setInterval(refreshHailProgress, 5000);
        }
    }

    function stopProgressTimer() {
        clearInterval(progressTimer);
        progressTimer = null;
    }

    $(document).ready(function () {
        //init click handlers for the fetch button
        $('.hail-fetch-items a.dropdown-item').on('click', function (e) {
            e.preventDefault();
            //Disable button to avoid spam
            $('#hail-fetch-button').addClass('disabled');
            //Class to fetch
            var className = $(this).data('to-fetch');
            console.log(className);
            //Send request to Hail Controller
            $.ajax({
                url: "/hail/fetch/" + className,
                type: 'GET',
                contentType: "application/json",
                success: function (response) {
                    initHailProgress();
                },
                error: function (response) {
                    console.log(response);
                    if (typeof response.message !== "undefined") {
                        var message = "Error fetching from Hail: " + response.message;
                        alert(message);
                    }
                }
            });
        });

        //Check if we need to start the progress bar straight away
        if (progressBar.hasClass('hail-fetch-running')) {
            initHailProgress();
        }
    })
});