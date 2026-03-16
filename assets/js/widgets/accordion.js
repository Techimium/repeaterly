class RepeaterlyAccordionHandler extends elementorModules.frontend.handlers.Base {

    bindEvents() {
        const $titles = this.$element.find('.elementor-tab-title');

        $titles.on('click', (e) => {
            const $title = jQuery(e.currentTarget);
            const tab = $title.data('tab');

            const $item = $title.closest('.elementor-accordion-item');
            const $content = this.$element.find(
                '.elementor-tab-content[data-tab="' + tab + '"]'
            );

            if ($item.hasClass('elementor-active')) {
                $item.removeClass('elementor-active');
                $item.find('.elementor-accordion-icon-closed').show();
                $item.find('.elementor-accordion-icon-opened').hide()
                $content.slideUp();
            } else {
                this.$element.find('.elementor-accordion-item').removeClass('elementor-active');
                this.$element.find('.elementor-tab-content').slideUp();

                $item.addClass('elementor-active');
                $item.find('.elementor-accordion-icon-closed').hide()
                $item.find('.elementor-accordion-icon-opened').show()
                $content.slideDown();
            }

        });
    }
}

jQuery(window).on('elementor/frontend/init', () => {
    elementorFrontend.hooks.addAction(
        'frontend/element_ready/repeaterly-accordion.default',
        ($scope) => {
            new RepeaterlyAccordionHandler({ $element: $scope });
        }
    );
});