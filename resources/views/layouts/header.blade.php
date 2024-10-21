<?PHP ?>
<div style="display: flex;height: 100%;justify-content: center;align-items: center;">
    <img style="height: 70px;cursor:pointer" id="headerlogo" src="/images/mainlogo.png">
    <button type="button" style="position: absolute; top: 5px; right: 18px;" onclick="toggleDarkMode()" class="btn btn-blue">DarkMode</button>
    <script>
        document.querySelector('#headerlogo').addEventListener("click",function(){
            window.location.href = "/";
        });
    </script>
</div>