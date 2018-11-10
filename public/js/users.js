(function() {
    var uid = {{ user.id }};
    $("#dislike").hide();

    $("#like").on('click', function (e) {
        $.ajax({
            type: "POST",
            url: "/like",
            data: { uid: uid },
            dataType: "json",
            success: function(data){
                // console.log(data);
                $("#like").hide();
                $("#dislike").show();
            },
            failure: function(errMsg) {
                // console.log(errMsg);
            }
        });
    });

    $("#dislike").on('click', function (e) {
        $.ajax({
            type: "POST",
            url: "/dislike",
            data: { uid: uid },
            dataType: "json",
            success: function(data){
                // console.log(data);
                $("#like").show();
                $("#dislike").hide();
            },
            failure: function(errMsg) {
                // console.log(errMsg);
            }
        });
    });

})();