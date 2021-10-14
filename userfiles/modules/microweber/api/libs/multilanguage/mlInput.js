(function ($) {
    $.fn.mlInput = function (options) {
        var settings = $.extend({
            name: false,
            locales: [],
            currentLocale: false,
            translations: [],
        }, options);

        this.each(function (index, obj) {
            var name = settings.name;
            var currentLocale = settings.currentLocale;
            var locales = settings.locales;
            var translations = settings.translations;
            var type = $(obj).attr('type');

            if (!name.length || !locales.length || !currentLocale.length){
                console.log('Please fill the name and locales.');
                return;
            } else if (type != 'text') {
                console.log('Multilanguage input type not supported with this setup.');
                return;
            }

            $(obj).css('opacity', 0.2).css('display','none');
            $(obj).attr('value', translations[currentLocale]);

            var outputHtml = '<div class="input-group">';

                let mlInputLocaleIds = [];
                for (var i = 0; i < locales.length; i++) {
                    var mlInputLocaleId = 'ml-input-'+name+'-'+i;
                    outputHtml +='<input type="text" class="form-control" value="'+translations[locales[i]]+'" id="'+mlInputLocaleId+'" name="multilanguage['+name+']['+locales[i]+']" lang="'+locales[i]+'" value="" />';

                    // If ml input is changed change and the value attr
                    $('body').on('keyup','#' + mlInputLocaleId, function () {
                        if (currentLocale == $(this).attr('lang')) {
                            // Change original field to this current lang value
                            $(obj).attr('value', $(this).val());
                        }
                        $(this).attr('value', $(this).val());
                    });

                    mlInputLocaleIds[i] = mlInputLocaleId;
                }

                var mlInputLocaleChangeId = 'ml-input-'+name+'-change';
                outputHtml += '<div class="input-group-append">';
                    outputHtml += '<span>';
                        outputHtml += '<select class="selectpicker" id="'+mlInputLocaleChangeId+'" data-width="100%">';
                        for (var i = 0; i < locales.length; i++) {
                            outputHtml += '<option value="'+locales[i]+'">' + locales[i].toUpperCase() + '</option>';
                        }
                        outputHtml += '</select>';
                    outputHtml += '</span>';
                outputHtml += '</div>';

            outputHtml += '</div>';
            $(obj).after(outputHtml);

            // Switch fields
            function switchInputFieldsToLanguage(mlCurrentLanguage) {
                $('#' + mlInputLocaleChangeId).selectpicker("val", mlCurrentLanguage);
                for (var i = 0; i < mlInputLocaleIds.length; i++) {
                    // If ml locale is changed hide all fields except current lang
                    if ($('#' + mlInputLocaleIds[i]).attr('lang') !== mlCurrentLanguage) {
                        $('#' + mlInputLocaleIds[i]).hide();
                    } else {
                        $('#' + mlInputLocaleIds[i]).show();
                    }
                }
            }

            // Show for current lang
            switchInputFieldsToLanguage(currentLocale);

            // If dropdown is changed
            $('body').on('change','#' + mlInputLocaleChangeId, function (){
                mw.trigger("mlChangedLanguage", $(this).val());
            });

            // Listen for events
            mw.on("mlChangedLanguage", function (e, mlCurrentLanguage) {
                switchInputFieldsToLanguage(mlCurrentLanguage);
            });

        });
        return this;
    };
}(jQuery));