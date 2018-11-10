(function() {

    $("#setLoc").on('click', function (e) {
        var address = $('#location').val();
        geocoder.geocode({'address': address}, function(results, status) {
            if (status === 'OK') {
                map.setCenter(results[0].geometry.location);
                // console.log(results[0].geometry.location);

                getCity(results[0].geometry.location);
                mark.setPosition(results[0].geometry.location);
            } else {
                alert('Geocode was not successful for the following reason: ' + status);
            }
        });
    });

    $("#add_new_tag").on('click', function (e) {
        if ($("#new_tag").val().length == 0)
            return ;
        var ntag = $("#new_tag").val().split(",");

        ntag.forEach(function (t, i) {
            ntag[i] = t.trim().replace(/ /g, '_');
            if (ntag[i] == '')
                ntag.splice(i, 1);
            $("#tag_list").append("<span class=\"badge badge-pill badge-secondary p-2\">"+ ntag[i] +"</span> ");
        });
        var tags_list = $("[name='tags']").val();
        var new_tag_list = tags_list + (tags_list.length ? ',' : '');
        new_tag_list += ntag.join();
        $("[name='tags']").val(new_tag_list);
        $("#new_tag").val('');
        // console.log(new_tag_list);
    });

    $("#edit_form").submit(function (e) {
        var data = {
            firstname:          $("[name='firstname']").val(),
            lastname:           $("[name='lastname']").val(),
            email:              $("[name='email']").val(),
            age:                $("[name='age']").val(),
            gender:             $("[name='gender']").val(),
            sexual_preference:  $("[name='sexual_preference']").val(),
            biography:          $("[name='biography']").val(),
            tags:               $("[name='tags']").val(),
            password:           $("[name='password']").val(),
            location:           $("#location").val(),
            longitude:          $("#longitude").val(),
            latitude:           $("#latitude").val()
        };
        $("#submit").attr("disabled", true);
        $.ajax({
            type: "POST",
            url: "{{ path_for('edit') }}",
            data: data,
            dataType: "json",
            success: function(data){
                if (data.st == 0) {
                    $('#st').append("<p class='alert alert-success m-0 mt-2 p-2' id='st_msg'>"+ data.msg +"</p>");
                    setTimeout(function () {
                        $("#st_msg").remove();
                    }, 2000);
//                            $("#st_msg").attr('class', "alert alert-success m-0 mt-2 p-2").text(data.msg);
                }
                // console.log(data);
                $("#submit").removeAttr("disabled");
            },
            failure: function(errMsg) {
                // console.log(errMsg);
                $("#submit").removeAttr("disabled");
            }
        });
        e.preventDefault();
    });
})();