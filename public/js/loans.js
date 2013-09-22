/*jslint browser*/
/*global jQuery*/

var loans = (function ($) {
	return {
		/**
		 * Prevention for Flash Of Unstyled Content
		 */
		preventFouc: function () {
			$('.no-fouc').show();
			$('script.no-fouc-script').remove();
		},

		setupApplicationForm: function () {
			var maxHeight = 0, currentStep = 1;

			// Hide extraneous stuff
			$('a.top').hide();

			// Setup button sets
			$('#loan-term-radios').buttonset();
			$('#australian-resident-radios').buttonset();
			$('#medicare-radios').buttonset();

			// Setup spinners
			$('input.number').spinner();

			// Setup date pickers
			$('input#date_of_birth').datepicker({
				changeMonth: true,
				changeYear: true,
				showOtherMonths: true,
				selectOtherMonths: true,
				showAnim: 'fade',
				hideAnim: 'fade',
				defaultDate: '-35y',
				dateFormat: 'yy-mm-dd'
			});

			// Setup submit button
			$('.buttons input').button({
				disabled: true
			});

			// Setup form navigation buttons
			$('#form-navigation input:first-child').attr('checked', true);
			$('#form-navigation').buttonset().find('label').click(function () {
				var step = parseInt($(this).attr('for').replace(/^form\-navigation\-/, ''), 10);
				if (step !== currentStep) {
					$('fieldset').not('#step-' + step).hide('slide', { direction: step > currentStep ? 'left' : 'right' }, 'slow');
					$('fieldset#step-' + step).css({ position: 'absolute', top: 0 }).show('slide', { direction: step > currentStep ? 'right' : 'left' }, 'slow', function () {
						$(this).css({ position: 'static' });
					});
					currentStep = step;
				}
			});

			// Setup fieldsets to have equal height
			$('fieldset').each(function (index, fieldset) {
				var $clone = $(fieldset).clone().css({ position: 'absolute', top: '-10000px', left: '-10000px' }).appendTo(document.body);
				maxHeight = Math.max(maxHeight, $clone.height());
				$clone.remove();
			});

			// Show only the first fieldset
			$('fieldset').css({ width: $('.container').width() + 'px', height: maxHeight + 'px' }).not('#step-' + currentStep).hide();
		}
	};
}(jQuery));
