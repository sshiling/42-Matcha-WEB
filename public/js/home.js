$( function() {
    $sr1 = $("#slider-range-1");
    $sr2 = $("#slider-range-2");
    $sr3 = $("#slider-range-3");

    $sr1.slider({ range: true,
        min: 18, max: 100, values: [ 18, 100 ],
        slide: function(event, ui) {
            $("#st1").html("" + ui.values[0] + " - " + ui.values[1]);
        },
        stop: function( event, ui ) { handleRangeInputChange(); }
    });

    $sr2.slider({range: true,
        min: 0, max: 15000, values: [ 0, 15000 ],
        slide: function(event, ui) {
            $("#st2").html("" + ui.values[0] + " - " + ui.values[1]);
        },
        stop: function( event, ui ) { handleRangeInputChange(); }
    });

    $sr3.slider({ range: true,
        min: 0, max: 10000, values: [ 0, 10000 ],
        slide: function(event, ui) {
            $("#st3").html("" + ui.values[0] + " - " + ui.values[1]);
        },
        stop: function( event, ui ) { handleRangeInputChange(); }
    });

    $("#st1").html("" + $sr1.slider("values", 0) + " - " + $sr1.slider("values", 1));
    $("#st2").html("" + $sr2.slider("values", 0) + " - " + $sr2.slider("values", 1));
    $("#st3").html("" + $sr3.slider("values", 0) + " - " + $sr3.slider("values", 1));

    var container = document.querySelector('#users_list');
    var mixer = mixitup(container, {
        load: {
            sort: 'i:desc'
        },
        controls: {
            toggleLogic: 'and'
        },
        animation: {
            "duration": 250,
            "nudge": false,
            "reverseOut": false,
            "effects": "fade"
        },
        classNames: {
            block: '',
            elementSort: '',
            elementToggle: ''
        },
        callbacks: {
            onMixStart: function(state, futureState) {
                $('.block_scrin').show();
                // console.log('Starting operation...');
            },
            onMixEnd: function(state) {
                $('.block_scrin').hide();
                // console.log('Operation complete');
            }
        }
    });

    function getRange() {
        return {
            age_min: $sr1.slider("values", 0), age_max: $sr1.slider("values", 1),
            dis_min: $sr2.slider("values", 0), dis_max: $sr2.slider("values", 1),
            rat_min: $sr3.slider("values", 0), rat_max: $sr3.slider("values", 1)
        };
    }

    function handleRangeInputChange() {
        mixer.filter(mixer.getState().activeFilter);
    }

    function filterTestResult(testResult, target) {
        var a = Number(target.dom.el.getAttribute('data-a'));
        var d = Number(target.dom.el.getAttribute('data-d'));
        var r = Number(target.dom.el.getAttribute('data-r'));
        var range = getRange();
        if (a < range.age_min || a > range.age_max ||
            d < range.dis_min || d > range.dis_max ||
            r < range.rat_min || r > range.rat_max) {
            testResult = false;
        }
        return testResult;
    }

    mixitup.Mixer.registerFilter('testResultEvaluateHideShow', 'range', filterTestResult);
} );