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