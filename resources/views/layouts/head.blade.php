<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=0.8, user-scalable=no">
<link rel="icon" type="image/png" href="/images/favicon.png" />
<link rel="stylesheet" type="text/css" href="/css/style.css" />
<link rel="stylesheet" type="text/css" href="/css/buttons.css" />
<link rel="icon" type="image/png" href="/favicon.png" />
<meta name="csrf-token" content="{{ csrf_token() }}" />
<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/js/xlsx.js"></script>
<style>
    .material-symbols-outlined {
        font-variation-settings:
        'FILL' 1,
        'wght' 400,
        'GRAD' 0,
        'opsz' 24;
        color: #0369a1;
    }
</style>
<script>
    $(document).ready(function(){
        if (window.self !== window.top){
            document.querySelector('header').style.display = "none";
            document.querySelector('footer').style.display = "none";
            document.querySelector('main').style.paddingTop = "0px";
            document.querySelector('#ticketcontainer > .sectiontitle').style.display = "none";
        }
    });

    function addLoader(container){
        let loader = document.createElement('div');
        loader.classList.add('loader');
        let spinner = document.createElement('div');
        spinner.classList.add('spinner');
        loader.appendChild(spinner);
        container.appendChild(loader);
    }

    function returnSelectOption(displayName,value){
        let option = document.createElement('option');
        option.value = value;
        option.innerText = displayName;
        return option;
    }

    function debounce(func, timeout = 300){
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => { func.apply(this, args); }, timeout);
        };
    }

    function exportDataToExcel(data,reportname) {
        filename = reportname+'.xlsx';
        var ws = XLSX.utils.json_to_sheet(data);
        var wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Report");
        XLSX.writeFile(wb,filename);
    }

    function loadJsonData(url,outputContainer,runFunction){
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            beforeSend: function(){
                addLoader(outputContainer);
                $(outputContainer).find('.loader').fadeIn(100);
            },
            success: runFunction,
            error: function(jqXHR, textStatus, errorThrown) {
                // Handle the error response
                console.error('Error:', textStatus, errorThrown);
                $(outputContainer).html('<p class="errormessage">An error occurred while fetching data.</p>');
                if (errorThrown == "Not Found"){
                    $(outputContainer).html('<p class="errormessage">'+jqXHR.responseJSON.error+'</p>');
                }
            },
            complete: function(){
                $(outputContainer).find('.loader').fadeOut(100,function(){$(this).remove()});
            }
        });
    }

    function goToTicket(){
        let ticketId = this.querySelector('.ticketid').innerHTML;
        window.location.href = "/tickets/"+encodeURIComponent(ticketId);
    }

    function openPopup(headerName,container,popupStyle){
        function closePopup(){
            this.parentNode.parentNode.remove();
        }
        let popupContainer = document.createElement('div');
        popupContainer.classList.add('popupcontainer');

        let popupWindow = document.createElement('div');
        popupWindow.classList.add('popupwindow');

        let popupHeader = document.createElement('div');
        popupHeader.classList.add('popupheader');
        popupHeader.appendChild(document.createTextNode(headerName));

        let popupCloser = document.createElement('div');
        popupCloser.classList.add('popupcloser');
        popupCloser.classList.add('btn');
        popupCloser.classList.add('btn-red');
        popupCloser.innerHTML = '&#x2716;';
        popupCloser.addEventListener("click",closePopup);

        Object.assign(popupWindow.style, popupStyle);

        popupWindow.appendChild(popupHeader);
        popupWindow.appendChild(popupCloser);

        popupWindow.appendChild(container);
        
        popupContainer.appendChild(popupWindow);

        document.querySelector('main').appendChild(popupContainer);
    }

    function goToTicketPopup(){
        let ticketId = this.querySelector('.ticketid').innerHTML;

        let ticketIframe = document.createElement('iframe');
        ticketIframe.classList.add('ticketpopup');
        ticketIframe.setAttribute('src','/tickets/'+encodeURIComponent(ticketId));

        openPopup(ticketId,ticketIframe,{width: "800px",height: "80%"});
    }
</script>
<title>NEXTdesk Database Viewer</title>