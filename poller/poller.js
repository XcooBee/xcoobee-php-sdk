var interval;
$(document).ready(function () {
    $("#xcoobee_demo_form").validate();
    $('#stop_poll').on('click', function () {
        clearInterval(serverPollInt);
        $('#start_poll').removeClass('btn-default').addClass('btn-primary');
        $('#stop_poll').removeClass('btn-primary').addClass('btn-default');
        $('button').prop('disabled', false);
        $('.xcoobee_clock').html("<p>waiting for poll</p>");
    });
});

$.validator.addMethod("pollinterval", function (value, element) {
    var value = $('input[name="' + element.name + '"]').val();
    return (value == 0 || (value >= 60 && value <= 600));
}, "Enter interval in seconds 0 or min. 60 - max 600");

$.validator.setDefaults({
    submitHandler: function () {
        var submitButtonName = $(this.submitButton).attr("name");
        var posturl = $('#post_url').val();
        var pollinterval = $('#xcoobee_poll_interval').val();

        if (submitButtonName == 'start_poll' && pollinterval != 0) {

            $('#start_poll').removeClass('btn-primary').addClass('btn-default');
            $('#stop_poll').removeClass('btn-default').addClass('btn-primary');
            //initial request
            DisplayResponse(posturl);
            //set countdown
            pollServer(pollinterval, posturl);
        }

        if (submitButtonName == 'manual_poll' || pollinterval == 0) {
            DisplayResponse(posturl);
        }

        return false;
    }
});
function pollServer(serverPollIntervall, posturl) {
    //do the countdown text
    var count = serverPollIntervall;

    serverPollInt = setInterval(function () {
        count = count - 1;
        $('button').prop('disabled', true);
        $('.xcoobee_clock').html("<p>next poll in <span>" + count + "</span> s</p>");

        if (count == 0)
        {
            count = serverPollIntervall;
            //send the ajax request
            DisplayResponse(posturl);
        }

    }, 1000);
}
var DisplayResponse = function (posturl)
{
    $('button').prop('disabled', true);
    $('.xcoobee_loader').show();
    //console.log(XcooBee.process.env);
    var config = new XcooBee.sdk.Config({
        apiUrlRoot: $("#xcoobee_api_url").val(),
        apiKey: $("#xcoobee_api_key").val(),
        apiSecret: $("#xcoobee_api_secret").val()
    });
    var xcooBeeSdk = new XcooBee.sdk.Sdk(config);
    xcooBeeSdk.system.getEvents()
            .then(function (response) {
                if (response.result
                    && response.result
                    && response.result.data
                    && response.result.data.length
                ) {
                    var html;
                    response.result.data.forEach(function (elm) {
                        html += '<tr>';
                        html += '<td>' + StringToCamelCase(elm.event_type) + '</td>';
                        html += '<td>' + elm.payload + '</td>';
                        html += '<td>' + elm.reference_type + '</td>';
                        html += '<td>' + elm.date_c + '</td>';
                        html += '</tr>';
                    });
                    $('#xcoobee_api_result tbody').html(html);
                    //post to webhook
                    if (posturl) {
                        $.ajax({
                            url: posturl,
                            type: "POST",
                            dataType: "json",
                            data: response,
                            contentType: "application/json"
                        });
                    }
                } else {
                    $('#xcoobee_api_result tbody').html('no running events');
                    //alert('no running events');

                }
                $('.xcoobee_loader').hide();
                $('button').prop('disabled', false);
            });
}
var StringToCamelCase = function (str)
{
    return str.replace(/(\_\w)/g, function (m) {
        return m[1].toUpperCase();
    });
}