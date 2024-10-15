@extends('layouts.layout')

@section('content')
    <search style="padding: 20px 0px">
        <input style="width: 300px" type="text" id="searchinput" placeholder="Search..." onchange="search()" />
    </search>
    <div id="searchresults"></div>

    <script>
        function search(){
            let searchBox = document.querySelector('#searchresults');
            
            function returnTicketBox(id,type,description){
                let ticketContainer = document.createElement('div');
                ticketContainer.addEventListener("click",goToTicket);
                ticketContainer.classList.add('ticket');
                let ticketId = document.createElement('div');

                if (type == 'ticket'){
                    ticketId.style.backgroundColor = "#2e6da4";
                }
                else if (type == 'change'){
                    ticketId.style.backgroundColor = "#d060ffcc";
                }
                else if (type == 'changeactivity'){
                    ticketId.style.backgroundColor = "#d060ffcc";
                }

                ticketId.appendChild(document.createTextNode(id));
                ticketId.classList.add('ticketid');
                let ticketDescription = document.createElement('div');
                ticketDescription.classList.add('ticketdescription');
                ticketDescription.appendChild(document.createTextNode(description));

                ticketContainer.appendChild(ticketId);
                ticketContainer.appendChild(ticketDescription);

                return ticketContainer;
            }

            let searchValue = document.querySelector('#searchinput').value;

            loadJsonData('/tickets?searchvalue='+encodeURIComponent(searchValue),searchBox,function(data) {
                $(searchBox).empty(); // Clear previous results
                data.forEach(function(ticket) {
                    searchBox.append(returnTicketBox(ticket.id,ticket.type,ticket.description));
                });
            });
        }
    </script>
@endsection
