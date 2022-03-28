(function ($) {


    $(document).ready(function () {

        /**
         * Retrieve posts
         */
        /**
         * Bind get_posts to tag cloud and navigation
         */

        $('#form').submit(function (e) {
            e.preventDefault();
            // alert('Form Submitted');

            var form = $('#form').serialize();

            //get input data

            let name = $('#fname').val();
            let mail = $('#mail').val();
            let tel = $('#tel').val();
            let msg = $('.msg-text').val();



            $.ajax({
                url: ajaxurl,
                data: {

                    action: 'contact_form',
                    name: name,
                    mail: mail,
                    tel: tel,
                    msg: msg,

                },
                type: 'post',

                success: function (result, textstatus) {

                    e.preventDefault();

                    if (invalid) {

                        submitbtn.value = "إرسال";
                        submitbtn.style = " border: 1.4px solid gold; color: gold; ";
                    } else {
                        formsending();

                        // setTimeout($('.message-sent').css('opacity', '1'), 3000);
                        setTimeout(() => {
                            $('.message-sent').css('opacity', '1');
                            input.forEach(input => input.value = '');
                            document.querySelector('.msg-text').value = '';

                        }, 5000);

                    }


                    setTimeout(() => {

                        $('.message-sent').css('opacity', '0');

                    }, 12000);

                },
                error: function (err) {
                    console.log(err);
                },


            })


        });


    });



})(jQuery);