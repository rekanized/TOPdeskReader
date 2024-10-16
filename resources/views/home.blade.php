@extends('layouts.layout')

@section('content')
    <search style="padding: 20px 0px; display: flex;align-items: flex-end;">
        <div style="margin: 0px 8px">
            <div>Customer</div>
            <select id="customerfilter" style="padding-top: 8px;"></select>
        </div>
        <div style="margin: 0px 8px">
            <div>Search</div>
            <input style="width: 300px" type="text" id="searchinput" placeholder="Search..." />
        </div>
        <div style="margin: 0px 8px">
            <div>Type</div>
            <select id="typefilter"><option value="all">All</option><option value="ticketid">Ticket ID</option><option value="person">Person</option><option value="briefdescription">Brief description</option></select>
        </div>
        <button id="searchbtn" style="margin: 0px; border-radius: 4px" class="btn btn-blue">Search</button>
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
            let customerFilter = document.querySelector('#customerfilter').value;
            let typeFilter = document.querySelector('#typefilter').value;
            loadJsonData('/tickets?searchvalue='+encodeURIComponent(searchValue)+'&customerfilter='+encodeURIComponent(customerFilter)+'&typefilter='+encodeURIComponent(typeFilter),searchBox,function(data) {
                $(searchBox).empty(); // Clear previous results
                data.forEach(function(ticket) {
                    searchBox.append(returnTicketBox(ticket.id,ticket.type,ticket.description,ticket.person,ticket.status));
                });
            });
        }

        $(document).ready(function(){
            let customerFilter = document.querySelector('#customerfilter');
            loadJsonData('/api/customers',customerFilter,function(data){
                customerFilter.appendChild(returnSelectOption('All','all'));
                data.forEach(function(customer){
                    if (customer.status < 0){
                        customerFilter.appendChild(returnSelectOption(customer.naam+' (Archived)',customer.unid));
                        return;
                    }
                    customerFilter.appendChild(returnSelectOption(customer.naam,customer.unid));
                });
            });
        });
    </script>
@endsection
