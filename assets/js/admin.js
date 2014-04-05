$(document).ready(function(){
    function Editor(input, preview) {
        this.update = function () {
            preview.innerHTML = markdown.toHTML(input.value);
        };

        input.editor = this;
        this.update();
    }
    new Editor(document.getElementById('content-editor'), document.getElementById('preview'));

    $('#content-editor').on('scroll', function() {
        $('#preview').scrollTop($(this).scrollTop());
    })

    $('.dm-summary').click(function(){
        var summary_input = '<input type="text" id="summary" name="summary" class="form-control" placeholder="Summary" />';
        $('.front-matter').append(summary_input);
    });
        
    $('.dm-tags').click(function(){
        var tag_input = '<input type="text" id="tags" name="tags" class="form-control" placeholder="Tags" />';
        $('.front-matter').append(tag_input);
    });

});
