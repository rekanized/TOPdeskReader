<?PHP ?>
<div style="display: flex;height: 100%;justify-content: center;align-items: center;">
    <img style="height: 70px;cursor:pointer" id="headerlogo" src="/images/mainlogo.png">
    <script>
        document.querySelector('#headerlogo').addEventListener("click",function(){
            window.location.href = "/";
        });
    </script>
</div>