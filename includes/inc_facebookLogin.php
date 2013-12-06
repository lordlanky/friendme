<script>
    window.fbAsyncInit = function() {
        FB.init({
            appId: '260910230726320', 
            status: true,
            cookie: true,
            xfbml: true
        });
    };

    (function(d) {
        var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
        if (d.getElementById(id)) {
            return;
        }
        js = d.createElement('script');
        js.id = id;
        js.async = true;
        js.src = "//connect.facebook.net/en_US/all.js";
        ref.parentNode.insertBefore(js, ref);
    }(document));

    function Login()
    {

        FB.login(function(response) {
            if (response.authResponse)
            {
                location.reload();

            } else
            {
                console.log('Authorization failed.');
            }
        }, {scope: 'email,read_friendlists,friends_online_presence,user_hometown,friends_hometown,user_relationships'});

    }

</script>