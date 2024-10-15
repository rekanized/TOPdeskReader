@extends('layouts.layout')

@section('content')
    <search style="padding: 20px 0px; display: flex">
        <input style="width: 300px" type="text" id="searchinput" placeholder="Search..." />
        <button id="searchbtn" style="margin: 0px 8px; border-radius: 4px" class="btn btn-blue">Search</button>
    </search>
    <div id="searchresults">
        <p>Welcome to the TOPdesk Reader, the search values you have available are <b>TicketID</b>, <b>Persons</b> or <b>Brief descriptions!</b></p>
    </div>
    <script>
        let searchBtn = document.querySelector('#searchbtn');
        searchBtn.addEventListener("click",search);

        $(document).keyup(function(event) {
            if (event.keyCode === 13) {
                searchBtn.click();
            }
        });

        function search(){
            let searchBox = document.querySelector('#searchresults');

            function returnTicketBox(id,type,description,person,archived){
                let ticketContainer = document.createElement('div');
                ticketContainer.addEventListener("click",goToTicket);
                ticketContainer.classList.add('ticket');
                if (archived < 0){
                    ticketContainer.classList.add('searchticketarchived');
                }
                
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

                let ticketPerson = document.createElement('div');
                ticketPerson.classList.add('ticketperson');
                ticketPerson.appendChild(document.createTextNode(person));

                ticketContainer.appendChild(ticketId);
                ticketContainer.appendChild(ticketDescription);
                ticketContainer.appendChild(ticketPerson);

                return ticketContainer;
            }

            let searchValue = document.querySelector('#searchinput').value;

            loadJsonData('/tickets?searchvalue='+encodeURIComponent(searchValue),searchBox,function(data) {
                $(searchBox).empty(); // Clear previous results
                data.forEach(function(ticket) {
                    searchBox.append(returnTicketBox(ticket.id,ticket.type,ticket.description,ticket.person,ticket.status));
                });
            });
        }
    </script>
@endsection
