/*jslint browser: true, unparam: true*/
/*global jQuery, ko*/

var loans = (function ($, ko) {
    "use strict";

    var /**
         * URL path to append to the base URL for data submissions
         */
        dataBaseUrl = 'data',

		/**
		 * URL path to append to the base URL for streaming updates
		 */
		streamBaseUrl = 'stream',

		/**
		 * Number of milliseconds between successive inspections of the XHR object for streaming updates
		 */
		pollInterval = 500,

        /**
         * CSS classes for the label.question elements and their child icons
         */
        cssClasses = {
            label: {
                saving: 'ui-state-processing',
                valid: 'ui-state-highlight',
                invalid: 'ui-state-error'
            },
            icon: {
				all: 'ui-icon',
                hidden: 'ui-helper-hidden',
                valid: 'ui-icon ui-icon-circle-check', // Not sure why these ones need duplicate "ui-icon"
                invalid: 'ui-icon ui-icon-circle-close'
            }
        },

        /**
         * Submit a single form element individually ("private" method).
         *
         * @param $elem JQuery object encapsulating the single element whose value is to be submitted.
         * @param baseUrl Base URL for generation of complete URLs.
         * @param applicationId Numerical ID of the loan application currently being processed.
         * @param applicationToken Security token of the loan application currently being processed.
         */
        dynamicSubmit = function ($elem, baseUrl, applicationId, applicationToken) {
            var $parent = $elem.parents('.field'),
                $label = $parent.find('label.question'),
                $icon = $label.find('ins'),
                name = $elem.attr('name'),
                entity = name.replace(/([a-zA-Z0-9_]*)\[([a-zA-Z0-9_]*)\]/, '$1'),
                field = name.replace(/([a-zA-Z0-9_]*)\[([a-zA-Z0-9_]*)\]/, '$2'),
                value = $elem.val();
            $label.removeClass(cssClasses.label.valid).removeClass(cssClasses.label.invalid).addClass(cssClasses.label.saving);
			$icon.removeClass(cssClasses.icon.valid).removeClass(cssClasses.icon.invalid);
            $.ajax({
                type: 'post',
                url: baseUrl + '/' + dataBaseUrl + '/' + applicationId + '/' + applicationToken + '/' + entity + '/' + field,
                data: {
                    value: value
                }
            });
		},

		/**
		 * Read the stream.
		 *
		 * TODO Make this a long-running streamed response instead of a dumb poll
		 *
		 * @param baseUrl Base URL for generation of complete URLs.
		 * @param applicationId Numerical ID of the loan application currently being processed.
		 * @param applicationToken Security token of the loan application currently being processed.
		 */
		readStream = function (baseUrl, applicationId, applicationToken) {
			$.ajax({
				type: 'get',
				url: baseUrl + '/' + streamBaseUrl + '/' + applicationId + '/' + applicationToken,
				success: function (data) {
					$.each(data, function (itemIndex, item) {
						if (item.hasOwnProperty('status')) {
							handleMessageItem(item.status);
						}
					});
				}
			});
		},

		/**
		 * Handle a message container being returned from the stream.
		 *
		 * @param item The item to process.
		 */
		handleMessageItem = function (item) {
			console.log(item);
			var $elem = $('[name="' + item.entity + '[' + item.field + ']"]'),
				$parent = $elem.parents('.field'),
				$label = $parent.find('label.question'),
				$icon = $label.find('ins');
			$label.removeClass(cssClasses.label.saving);
			$icon.removeClass(cssClasses.icon.saving);
			if (item.hasOwnProperty('messages') && $.isArray(item.messages) && item.messages.length > 0) {
				$.each(item.messages, handleMessageItemFieldResponse);
			}
		},

		/**
		 * Handle a single message child item.
		 */
		handleMessageItemFieldResponse = function (messageIndex, message) {
			var $elem = $('[name="' + message.entity + '[' + message.field + ']"]'),
				$parent = $elem.parents('.field'),
				$label = $parent.find('label.question'),
				$icon = $label.find('ins'),
				isError = message.hasOwnProperty('message') && message.message.length > 0;
			$label.addClass(isError ? cssClasses.label.invalid : cssClasses.label.valid);
			$icon.removeClass(cssClasses.icon.hidden).addClass(isError ? cssClasses.icon.invalid : cssClasses.icon.valid);
			$parent.find('.ui-state-error-text').remove();
			if (isError) {
				$parent.append($('<div class="ui-state-error-text"></div>').text(message.message));
			}
		};

    return {
        /**
         * Prevention for Flash Of Unstyled Content
         */
        preventFlashOfUnstyledContent: function () {
            $('.no-fouc').show();
            $('script.no-fouc-script').remove();
        },

        /**
         * Initialise the application form.
         *
         * @param baseUrl Base URL for generation of complete URLs.
         * @param applicationId Numerical ID of the loan application currently being processed.
         * @param applicationToken Security token of the loan application currently being processed.
         */
        setupApplicationForm: function (baseUrl, applicationId, applicationToken) {
            var streamingPollTimer = null,
				maxHeight = 0,
                currentStep = 1,
                $formNavigation = $('#form-navigation'),
                $fieldsets = $('fieldset');

            // Hide extraneous stuff
            $('a.top').hide();

			// Add an icon placeholder for all question labels
			$('form .field label.question').append($('<ins></ins>').addClass(cssClasses.icon.all).addClass(cssClasses.icon.hidden));

			// Setup button sets
            $('#loan-term-radios').buttonset();
            $('#australian-resident-radios').buttonset();
            $('#medicare-radios').buttonset();

            // Setup spinners
            $('input.number').spinner();

            // Setup date pickers
            $('input#personal_date_of_birth').datepicker({
                changeMonth: true,
                changeYear: true,
                showOtherMonths: true,
                selectOtherMonths: true,
                showAnim: 'fade',
                hideAnim: 'fade',
                defaultDate: '-30y',
                dateFormat: 'dd/mm/yy',
				onSelect: function (value) {
					$(this).val(value);
					dynamicSubmit($(this), baseUrl, applicationId, applicationToken);
				}
            });

			// Setup dynamic submission
			$('form .dynamic').on('change blur', function () {
				dynamicSubmit($(this), baseUrl, applicationId, applicationToken);
			});

			// Setup submit button
            $('.buttons input').button({
                disabled: true
            });

			// Setup the streaming response
			streamingPollTimer = setInterval(function () {
				readStream(baseUrl, applicationId, applicationToken);
			}, pollInterval);
			$(window).unload(function () {
				if (streamingPollTimer !== null) {
					clearInterval(streamingPollTimer);
				}
			});

			// Setup form navigation buttons
            $formNavigation.find('input:first-child').attr('checked', true);
//			$formNavigation.find('input').not(':first-child').attr('disabled', true);
            $formNavigation.buttonset().find('label').click(function () {
                var step = parseInt($(this).attr('for').replace(/^form\-navigation\-/, ''), 10);
                if (step !== currentStep) {
                    $('fieldset').not('#step-' + step).hide('slide', { direction: step > currentStep ? 'left' : 'right' }, 'slow');
                    $('fieldset#step-' + step).css({ position: 'absolute', top: 0 }).show('slide', { direction: step > currentStep ? 'right' : 'left' }, 'slow', function () {
                        $(this).css({ position: 'static' });
                    });
                    currentStep = step;
                }
            });

            // Setup fieldsets to have equal height (plus a bit for error messages), and show the first fieldset
            $fieldsets.each(function (index, fieldset) {
                var $clone = $(fieldset).clone().css({ position: 'absolute', top: '-10000px', left: '-10000px' }).appendTo(document.body);
                maxHeight = Math.max(maxHeight, $clone.height());
                $clone.remove();
            });
            $fieldsets.css({ width: $('.container').width() + 'px', height: (maxHeight + 50) + 'px' }).not('#step-' + currentStep).hide();
        }
    };
}(jQuery, ko));
