var laukums = 0; // START
var randomkaulins = 1; // Default/start value
var circusfield_min = 0; // START
var circusfield_max = 23; // active fields without START and FINISH
var circusfield_last = 24; // we start with zero
var laukums_old = 0;
var class2ad = 'border-dark rounded-0 bg-info active';
jQuery(document)
    .ready(function () {

        /**
         * roll the dice
         */
        jQuery(".mest").click(function () {
            if (jQuery('.mest').data('canroll') === 'no') {
                return;
            }
            randomkaulins = Math.floor(Math.random() * 6) + 1 || 1;
            laukums_old = laukums;
            laukums = laukums + randomkaulins;
            rollTheDice(randomkaulins);

            /**
             * if more than {circusfield_max} (25-1).. we are done
             */
            if (laukums > circusfield_max) {
                laukums = circusfield_last;
            }

            jQuery("#uzmeta").val(randomkaulins);
            jQuery("#laukums").html(laukums);

            jQuery('.grid5x5-single[data-mpgridnr = ' + Math.floor(laukums - randomkaulins) + ']').removeClass(class2ad);

            if (laukums > circusfield_max) {
                jQuery('.grid5x5-single[data-mpgridnr = ' + Math.floor(laukums_old) + ']').removeClass(class2ad);
            }
            jQuery('.grid5x5-single[data-mpgridnr = ' + laukums + ']').addClass(class2ad);
            jQuery('.mest').data('canroll', 'no');

        });
        /**
         * inspired by https://jsfiddle.net/estelle/6d5Z6/
         * @param {*} randomkaulins 
         */
        var rollTheDice = function (randomkaulins = 1) {
            var diceValue = 1, output = '';
            diceValue = randomkaulins - 1;
            output += "&#x268" + diceValue + "; ";
            document.getElementById('dice').innerHTML = output;
        }
        /**
         * reset moves
         */
        jQuery(".nojauna").click(function () {
            laukums_old = laukums;
            laukums = laukums + randomkaulins;
            jQuery('.mest').data('canroll', 'yes');
            jQuery("#laukums").html(0);
            /**
             * if more than {circusfield_max} (25).. we are done
             */
            if (laukums > circusfield_max) {
                laukums = circusfield_last;
            }

            jQuery('.grid5x5-single[data-mpgridnr = ' + Math.floor(laukums_old) + ']').removeClass(class2ad);
            jQuery('.grid5x5-single[data-mpgridnr = ' + laukums + ']').removeClass(class2ad);

            randomkaulins = 1;
            laukums_old = 0;
            laukums = 0;
        });

        jQuery('.grid-mpquestion').click(function () {
            var postid = jQuery(this).data('postid');
            var question = jQuery(this).data('title');
            var nrpk = jQuery(this).data('nrpk');
            var question_f = jQuery(this);

            /**
             * check the current firld
             */
            var iscurrentnr = nrpk == laukums;
            if (iscurrentnr == false) {
                // not the same = do not open on click
                return;
            }

            var data = {
                'action': 'mpq_action',
                'postid': postid
            };
            jQuery.post(ajaxurl, data, function (response) {
                jQuery('.modal-body').html(response);

                // Display Modal
                jQuery('#empModal').modal('show');
                jQuery('#empModal').data('postid', postid);
                jQuery('.mpq_answer').click(function () {

                    var mpqcorrect = jQuery(this).data('mpqcorrect');
                    jQuery('.mpq_description').removeClass('d-none');

                    laukums_old = laukums;
                    if (mpqcorrect === 1) {
                        jQuery(this).addClass('bg-success p-2');
                        if (jQuery('#empModal').data('postid') === postid) {
                            laukums = laukums + solis;
                        }
                    }
                    if (mpqcorrect === 0) {
                        jQuery(this).addClass('bg-danger p-2');
                        if (jQuery('#empModal').data('postid') === postid) {
                            laukums = laukums - solis;
                        }

                    }

                    jQuery(this).closest('#empModal').on('hide.bs.modal', function () {

                        jQuery("#laukums").html(laukums);
                        if (laukums > circusfield_max) {
                            jQuery('.grid5x5-single[data-mpgridnr = ' + Math.floor(laukums_old) + ']').removeClass(class2ad);
                            laukums = circusfield_max;
                        }
                        jQuery('.grid5x5-single[data-mpgridnr = ' + Math.floor(laukums_old) + ']').removeClass(class2ad);
                        if (laukums < circusfield_min) {
                            laukums = circusfield_min;
                        }
                        jQuery('.grid5x5-single[data-mpgridnr = ' + laukums + ']').addClass(class2ad);

                        solis = 0;
                        laukums_old = laukums;
                    });

                    jQuery('#empModal').removeData("postid");

                    if (laukums == circusfield_min || laukums == circusfield_max) {
                        jQuery('.mest').data('canroll', 'yes');
                    }

                });

            });

        });
    });