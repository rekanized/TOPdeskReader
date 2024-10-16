<?PHP ?>
<div style="display: flex;height: 100%;justify-content: space-evenly;align-items: center;">
    <img style="height: 50px;cursor:pointer" id="headerlogo" src="/images/mainlogo.svg#colour">
    <script>
        document.querySelector('#headerlogo').addEventListener("click",function(){
            window.location.href = "/";
        });
    </script>
</div>