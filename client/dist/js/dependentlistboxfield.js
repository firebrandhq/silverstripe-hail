jQuery.entwine("dependentdropdown", function ($) {

    $("select.dependent-dropdown").entwine({
        onmatch: function () {
            var multiDepends = false;
            var drop = this;
            var nameSelector = drop.data('depends').replace(/[#;&,.+*~':"!^$[\]()=>|\/]/g, "\\$&");
            var depends = ($("select[name=" + nameSelector + "]"));

            //Search for multi select if no depends found
            if (depends.length === 0) {
                depends = ($("select[name=" + nameSelector + "\\[\\]]"));
                multiDepends = true;
            }

            this.parents('.field:first').addClass('dropdown');

            depends.change(function () {
                var dropDefault = drop.parent().find('.search-field .chosen-search-input');

                if (!this.value) {
                    drop.disable(drop.data('unselected'));
                } else {

                    drop.disable();
                    dropDefault.val('Loading...');

                    $.get(drop.data('link'), {
                            val: depends.val()
                        },
                        function (data) {
                            drop.enable();

                            if (drop.data('empty') || drop.data('empty') === "") {
                                drop.append($("<option />").val("").text(drop.data('empty')));
                            }

                            $.each(data, function () {
                                drop.append($("<option />").val(this.k).text(this.v));
                            });
                            drop.trigger("liszt:updated").trigger("chosen:updated").trigger("change");
                        });
                }
            });

            if (!depends.val()) {
                drop.disable(drop.data('unselected'));
            }
        },
        disable: function (text) {
            this.empty().append($("<option />").val("").text(text)).attr("disabled", "disabled").trigger("liszt:updated").trigger("chosen:updated");
        },
        enable: function () {
            this.empty().removeAttr("disabled").next().removeClass('chzn-disabled');
        }
    });

});
