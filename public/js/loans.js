/*jslint browser: true, unparam: true*/
/*global jQuery, ko*/

var loans = (function ($) {
    "use strict";

    var /**
         * URL path to append to the base URL for data submissions
         */
        dataBaseUrl = 'data',

		/**
		 * Stores the currently displayed step index (1-based)
		 */
		currentStep = 1,

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
            },
			messages: {
				all: 'message',
				error: 'ui-state-error-text',
				warning: 'ui-state-error-text ui-state-warning-text'
			}
        },

		/**
		 * Navigate to the given step.
		 *
		 * @param step
		 */
		navigateToStep = function (step) {
			if (step !== currentStep) {
			   $('fieldset').not('#step-' + step).hide('slide', { direction: step > currentStep ? 'left' : 'right' }, 'slow');
			   $('fieldset#step-' + step).css({ position: 'absolute', top: 0 }).show('slide', { direction: step > currentStep ? 'right' : 'left' }, 'slow', function () {
				   $(this).css({ position: 'static' });
			   });
			   currentStep = step;
		   }
		},

		/**
		 * Set the state of the form navigation buttons
		 *
		 * @param $formNavigationButtons
		 * @param $previousButton
		 * @param $nextButton
		 */
		updateFormNavigation = function ($formNavigationButtons, $previousButton, $nextButton) {
			$formNavigationButtons.filter(':nth-child(' + (currentStep * 2 - 1) + ')').attr('checked', true).button('refresh');
			$previousButton.button(currentStep > 1 ? 'enable' : 'disable');
			$nextButton.button(currentStep < 5 ? 'enable' : 'disable');
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
                },
				success: handleResponse
            });
		},

		/**
		 * Handle a message container being returned from the stream.
		 *
		 * @param response The response to process.
		 */
		handleResponse = function (response) {
			if (response.hasOwnProperty('messages') && $.isArray(response.messages) && response.messages.length > 0) {
				$.each(response.messages, function (messageIndex, message) {
					var $elem = $('[name="' + message.entity + '[' + message.field + ']"]'),
						$parent = $elem.parents('.field'),
						$label = $parent.find('label.question'),
						$icon = $label.find('ins');
					$label.removeClass(cssClasses.label.saving).removeClass(cssClasses.label.valid).removeClass(cssClasses.label.invalid).addClass(message.valid ? cssClasses.label.valid : cssClasses.label.invalid);
					$icon.removeClass(cssClasses.icon.saving).removeClass(cssClasses.icon.valid).removeClass(cssClasses.icon.invalid).removeClass(cssClasses.icon.hidden).addClass(message.valid ? cssClasses.icon.valid : cssClasses.icon.invalid);
					$parent.find('.' + cssClasses.messages.all).remove();
					if (message.message) {
						$parent.append(
							$('<p></p>')
								.addClass(cssClasses.messages.all)
								.addClass((message.messageLevel === 'warning') ? cssClasses.messages.warning : cssClasses.messages.error)
								.text(message.message)
						);
					}
				});
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
         * @param baseUrl Base URL for generation of complete URLs
         * @param applicationId Numerical ID of the loan application currently being processed
         * @param applicationToken Security token of the loan application currently being processed
		 * @param messages Array of messages to initialise the form with
         */
        setupApplicationForm: function (baseUrl, applicationId, applicationToken, messages) {
            var maxHeight = 0,
                $formNavigation = $('#form-navigation'),
				$formNavigationButtons = $formNavigation.find('input'),
                $fieldsets = $('fieldset'),
				$previousButton = $('<button>&laquo; Previous</button>'),
				$nextButton = $('<button>Next &raquo;</button>');

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

			// Setup form navigation buttons
            $formNavigationButtons.filter(':first-child').attr('checked', true);
            $formNavigation.buttonset().find('label').click(function () {
				navigateToStep(parseInt($(this).attr('for').replace(/^form\-navigation\-/, ''), 10));
				updateFormNavigation($formNavigationButtons, $previousButton, $nextButton);
            });
			$('form .buttons')
				.append($previousButton.attr({ id: 'previous-button' }).button().click(function (e) {
					if (currentStep > 1) {
						navigateToStep(currentStep - 1);
						updateFormNavigation($formNavigationButtons, $previousButton, $nextButton);
					}
					e.preventDefault();
					return false;
				}))
				.append($nextButton.attr({ id: 'next-button' }).button().click(function (e) {
					if (currentStep < 5) { // TODO && next step is available...
						navigateToStep(currentStep + 1);
						updateFormNavigation($formNavigationButtons, $previousButton, $nextButton);
					}
					e.preventDefault();
					return false;
				}));
			updateFormNavigation($formNavigationButtons, $previousButton, $nextButton);

            // Setup fieldsets to have equal height (plus a bit for error messages), and show the first fieldset
            $fieldsets.each(function (index, fieldset) {
                var $clone = $(fieldset).clone().css({ position: 'absolute', top: '-10000px', left: '-10000px' }).appendTo(document.body);
                maxHeight = Math.max(maxHeight, $clone.height());
                $clone.remove();
            });
            $fieldsets.css({ width: $('.container').width() + 'px', height: (maxHeight + 50) + 'px' }).not('#step-' + currentStep).hide();

			// Process initial validation messages
			console.log(messages);
			$.each(messages, function (entityKey, entity) {
				$.each(entity, function (fieldKey, field) {
					handleResponse(field);
				});
			});
        }
    };
}(jQuery));
