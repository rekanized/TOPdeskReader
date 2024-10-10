@extends('layouts.layout')

@section('content')
    <search style="padding: 20px 0px">
        <input style="width: 300px" type="text" id="searchinput" placeholder="Search..." onchange="search()" />
        <div id="searchresults"></div>
    </search>

    <script>
        function search(){
            let searchBox = document.querySelector('#searchresults');
            
            $.ajax({
                url: '/tickets', // Sample API endpoint
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    $('#searchresults').empty(); // Clear previous results
                    data.forEach(function(ticket) {
                        $('#searchresults').append('<h3>' + ticket.id + '</h3><p>' + ticket.type + '</p>');
                    });
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // Handle the error response
                    console.error('Error:', textStatus, errorThrown);
                    $('#result').html('<p>An error occurred while fetching data.</p>');
                }
            });
        }
    </script>
@endsection
