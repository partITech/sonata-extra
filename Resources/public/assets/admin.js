
$(document).ready(function() {
    $('.sonata-page-top-bar .dropdown-toggle').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).parent().toggleClass('open');
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.sonata-page-top-bar .dropdown').length) {
            $('.sonata-page-top-bar .dropdown').removeClass('open');
        }
    });

    $('.sonata-page-top-bar .dropdown').on('mouseleave', function() {
        $(this).removeClass('open');
    });

    var toastLiveExample = document.getElementById('liveToast')

    if (toastLiveExample) {
        toastLiveExample.classList.add('show');
    }

    function formatAndResizeTextarea(textarea) {
        //https://beautifier.io/?without-codemirror
        var formattedHtml = html_beautify(textarea.value, {
            "indent_size": "4",
            "indent_char": " ",
            "max_preserve_newlines": "-1",
            "preserve_newlines": false,
            "keep_array_indentation": true,
            "break_chained_methods": true,
            "indent_scripts": "keep",
            "brace_style": "collapse,preserve-inline",
            "space_before_conditional": true,
            "unescape_strings": true,
            "jslint_happy": true,
            "end_with_newline": true,
            "wrap_line_length": "70",
            "indent_inner_html": false,
            "comma_first": false,
            "e4x": false,
            "indent_empty_lines": false
        });
        console.log(textarea.value);
        console.log(formattedHtml);

        textarea.value = formattedHtml;
        // Application du redimensionnement
        // $(textarea).css('overflow-y', 'hidden');
        $(textarea).css('min-height', '450px');
        $(textarea).css('height', 'auto');
        $(textarea).css('height', textarea.scrollHeight + 10  + 'px');
    }

    // Application initiale et gestion des entrées utilisateur
    $('.textarea-auto-resize').each(function() {
        formatAndResizeTextarea(this);
    });



});