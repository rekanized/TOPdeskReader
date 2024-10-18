<?PHP ?>
<div style="display: flex;height: 100%;justify-content: center;align-items: center;">
    <img style="height: 70px;cursor:pointer" id="headerlogo" src="/images/mainlogo.png">
    <button type="button" style="position: absolute; top: 5px; right: 12px;" onclick="toggleDarkMode()" class="btn btn-blue">DarkMode</button>
    <script>
        document.querySelector('#headerlogo').addEventListener("click",function(){
            window.location.href = "/";
        });

        @if(session('darkmode') == 1)
            // Define the CSS to be injected
            var darkModeCSS = `
            <style id="darkModeStyle">
                html {
                color: white !important;
                background-color: #262626 !important;
                }
                .popupheader, header, footer, .ticket, iframe {
                background-color: #6e6e6e !important;
                }
                input[type=text], input[type=number], input[type=date], input[type=datetime-local], select, textarea {
                background-color: #6e6e6e !important;
                color: white !important;
                }
            </style>
            `;

            // Check if dark mode style already exists, if not, add it
            if (!$('#darkModeStyle').length) {
                $('head').append(darkModeCSS);
            }
        @endif

        function toggleDarkMode(){
            $.get("/toggleDarkMode", { _: new Date().getTime() })
            .done(function(darkmode) {
                if (darkmode == 1){

                    // Define the CSS to be injected
                    var darkModeCSS = `
                    <style id="darkModeStyle">
                        html {
                        color: white !important;
                        background-color: #262626 !important;
                        }
                        .popupheader, header, footer, .ticket, iframe {
                        background-color: #6e6e6e !important;
                        }
                        input[type=text], input[type=number], input[type=date], input[type=datetime-local], select, textarea {
                        background-color: #6e6e6e !important;
                        color: white !important;
                        }
                    </style>
                    `;

                    // Check if dark mode style already exists, if not, add it
                    if (!$('#darkModeStyle').length) {
                        $('head').append(darkModeCSS);
                    }

                }
                else {
                    $('#darkModeStyle').remove();
                }
            })
            .fail(function() {
                console.error('Failed to toggle dark mode.');
            });
        }
    </script>
</div>