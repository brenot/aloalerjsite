

<script>
    var isShowing = false;

    // Disable jQuery Mobile loading message
    $.mobile.loading().hide();
    $.mobile.loadingMessage = false;


    jQuery(function()
    {
        jQuery('#btn-telefones-uteis').click(function()
        {
            if ( ! isShowing)
            {
                jQuery('#agenda-tel-uteis').show(function()
                {
                    isShowing = true;
                });
            }
        });

        jQuery(document).click(function(evt)
        {
            if(jQuery(evt.target).closest('.agenda-tel-uteis').length)
            {
                return;
            }

            if(jQuery(evt.target).closest('.indice').length)
            {
                return;
            }

            if (isShowing)
            {
                jQuery('#agenda-tel-uteis').hide();

                isShowing = false;
            }
        });
    });
</script>

<script language="javascript">
    $('#collapseOne').on('show.bs.collapse', function () {
        $('.panel-heading').animate({}, 500);
        $('.panel-heading').addClass('dropdown');
        $('.panel-heading').removeClass('dropup');
    })
    $('#collapseOne').on('hide.bs.collapse', function () {
        $('.panel-heading').animate({}, 500);
        $('.panel-heading').addClass('dropup');
        $('.panel-heading').removeClass('dropdown');

    })
</script>

<script language="javascript">
    jQuery(document).ready(function ($) {
        setInterval(function () {
            moveRight();
        }, 6000);
        var slideCount = $('#slider ul li').length;
        var slideWidth = $('#slider ul li').width();
        var slideHeight = $('#slider ul li').height();
        var sliderUlWidth = slideCount * slideWidth;
        $('#slider').css({ width: slideWidth, height: slideHeight });
        $('#slider ul').css({ width: sliderUlWidth, marginLeft: - slideWidth });
        $('#slider ul li:last-child').prependTo('#slider ul');
        function moveLeft() {
            $('#slider ul').animate({
                left: + slideWidth
            }, 200, function () {
                $('#slider ul li:last-child').prependTo('#slider ul');
                $('#slider ul').css('left', '');
            });
        };
        function moveRight() {
            $('#slider ul').animate({
                left: - slideWidth
            }, 200, function () {
                $('#slider ul li:first-child').appendTo('#slider ul');
                $('#slider ul').css('left', '');
            });
        };
        $('a.control_prev').click(function () {
            moveLeft();
        });
        $('a.control_next').click(function () {
            moveRight();
        });
        $("#slider ul").swipeleft(function()
        {
            moveRight();
        });
        $("#slider ul").swiperight(function()
        {
            moveLeft();
        });
    });
</script>
