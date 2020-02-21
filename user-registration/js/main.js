(function($) {

    var form = $("#signup-form");
    form.validate({
        errorPlacement: function errorPlacement(error, element) {
            element.before(error);
        },
        rules: {
            username: {
                required: true,
            },
            email: {
                required: true,
                email : true
            }
        },
        messages : {
            email: {
                email: 'Not a valid email address <i class="zmdi zmdi-info"></i>'
            }
        },
        onfocusout: function(element) {
            $(element).valid();
        },
    });
    form.steps({
        headerTag: "h3",
        bodyTag: "fieldset",
        transitionEffect: "slideLeft",
        labels: {
            previous: 'Previous',
            next: 'Next',
            finish: 'Submit',
            current: ''
        },
        titleTemplate: '<div class="title"><span class="number">#index#</span>#title#</div>',
        onStepChanging: function(event, currentIndex, newIndex) {
            form.validate().settings.ignore = ":disabled,:hidden";
            // console.log(form.steps("getCurrentIndex"));
            //var usrname = $("#username").val();
            //var email   = $("#email").val();
            //var pwd     = $("#password").val();   
            
            
                    
            return form.valid();
        },
        onFinishing: function(event, currentIndex) {
            form.validate().settings.ignore = ":disabled";
            //console.log(getCurrentIndex);
                   
            let usrname = $("#username").val(),
                email   = $("#email").val(),
                pwd     = $("#password").val(),
                fullname= $("#full_name").val(),
                country = $("#country").val(),
                gender  = $("#gender").val(),
                mobile  = $("#mobile").val(),
                company = $("#company").val(),
                comp_reg= $("#reg").val(),
                address = $("#address").val(),
                city    = $("#city").val(),
                postal  = $("#postal").val(),
                regcode = $("#reg_session").val();         
            
            $.ajax({
                    type: "POST",
                    url: "setsessions.php",
                    data: { usrname: usrname, 
                            email:email, 
                            pwd:pwd,
                            fullname : fullname,
                            country  : country,
                            gender   : gender,
                            mobile   : mobile,
                            company  : company,
                            comp_reg : comp_reg,
                            address  : address,
                            city     : city,
                            regsession: regcode,
                            postal   : postal },
                    success: function result(res){
                        console.log(res);
                        }                       
                    });
            
            return form.valid();
        },
        onFinished: function(event, currentIndex) {
            
            alert('Sumited');
        },
        // onInit : function (event, currentIndex) {
        //     event.append('demo');
        // }
    });

    jQuery.extend(jQuery.validator.messages, {
        required: "",
        remote: "",
        url: "",
        date: "",
        dateISO: "",
        number: "",
        digits: "",
        creditcard: "",
        equalTo: ""
    });


    $.dobPicker({
        daySelector: '#expiry_date',
        monthSelector: '#expiry_month',
        yearSelector: '#expiry_year',
        dayDefault: 'DD',
        yearDefault: 'YYYY',
        minimumAge: 0,
        maximumAge: 120
    });

    $('#password').pwstrength();

    $('#button').click(function () {
        $("input[type='file']").trigger('click');
    })
    
    $("input[type='file']").change(function () {
        $('#val').text(this.value.replace(/C:\\fakepath\\/i, ''))
    })

})(jQuery);