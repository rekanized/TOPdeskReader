<footer>
    <div style="margin-left: 40px;display: flex"><img style="height: 40px;" src="/images/mainlogo.png"></div>
    <div style="margin-right: 60px"><a href="https://github.com/rekanized/TOPdeskReader" target="_blank">GitHub Project</a></div>
</footer>
<script type="text/javascript">   
    jQuery(document).ready(function() {       
        jQuery.ajaxSetup({        
            headers: {            
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')        
            }
        });    
    });
</script>
@if (session('darkmode') == 1)
    <style>
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
@endif